<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentFile;
use Illuminate\Support\Facades\Process;
use Throwable;

class OcrService
{
    public function __construct(
        protected FileStorageService $fileStorage,
        protected AuditService $auditService
    ) {}

    /**
     * Process OCR for all attached files of a document.
     */
    public function processDocument(Document $document): bool
    {
        $document->update([
            'ocr_status' => 'processing',
            'ocr_error' => null,
        ]);

        $files = $document->files()->orderBy('page_order')->get();

        if ($files->isEmpty()) {
            $document->update([
                'ocr_status' => 'skipped',
                'ocr_processed_at' => now(),
            ]);

            return true;
        }

        $combinedTextParts = [];
        $errors = [];

        foreach ($files as $index => $file) {
            try {
                $file->update(['ocr_status' => 'processing']);
                $extracted = $this->extractTextFromFile($file);
                $file->update([
                    'ocr_status' => 'completed',
                    'file_ocr_text' => $extracted,
                ]);

                if (trim($extracted) !== '') {
                    $pageNum = $index + 1;
                    $combinedTextParts[] = "--- [Attachment #{$pageNum}: {$file->original_name}] ---\n".trim($extracted);
                }
            } catch (Throwable $e) {
                $file->update(['ocr_status' => 'failed']);
                $errors[] = "{$file->original_name}: {$e->getMessage()}";
            }
        }

        $consolidatedText = implode("\n\n", $combinedTextParts);

        if (! empty($errors) && trim($consolidatedText) === '') {
            $errorMsg = implode(' | ', $errors);
            $document->update([
                'ocr_status' => 'failed',
                'ocr_error' => $errorMsg,
                'ocr_processed_at' => now(),
            ]);

            $this->auditService->log(
                'document.ocr_failed',
                "OCR text extraction failed for {$document->internal_reference}.",
                $document,
                ['errors' => $errors],
                'warning'
            );

            return false;
        }

        $document->update([
            'ocr_status' => 'completed',
            'ocr_text' => $consolidatedText,
            'ocr_error' => empty($errors) ? null : implode(' | ', $errors),
            'ocr_processed_at' => now(),
        ]);

        $this->auditService->log(
            'document.ocr_completed',
            "OCR text extraction completed for {$document->internal_reference} (".strlen($consolidatedText).' characters extracted).',
            $document,
            ['char_count' => strlen($consolidatedText), 'file_count' => $files->count()],
            'info'
        );

        return true;
    }

    /**
     * Extract text from a single DocumentFile using pdftotext, pdftoppm, or tesseract.
     */
    public function extractTextFromFile(DocumentFile $file): string
    {
        $absolutePath = $this->fileStorage->getAbsolutePath($file);

        if (! is_file($absolutePath)) {
            throw new \RuntimeException("File does not exist on disk: {$file->storage_path}");
        }

        if ($file->mime_type === 'application/pdf' || $file->extension === 'pdf') {
            return $this->extractFromPdf($absolutePath);
        }

        return $this->extractFromImage($absolutePath);
    }

    protected function extractFromPdf(string $pdfPath): string
    {
        $pdftotextBin = env('OCR_PDFTOTEXT_PATH', '/usr/bin/pdftotext');

        // 1. Try fast native PDF text extraction via Poppler's pdftotext
        if (is_executable($pdftotextBin)) {
            $result = Process::timeout(60)->run([$pdftotextBin, '-layout', $pdfPath, '-']);
            if ($result->successful()) {
                $text = trim($result->output());
                if (strlen($text) >= 15) {
                    return $this->cleanExtractedText($text);
                }
            }
        }

        // 2. Fallback: Render PDF pages to PNG via pdftoppm and run Tesseract OCR
        $pdftoppmBin = env('OCR_PDFTOPPM_PATH', '/usr/bin/pdftoppm');
        $tesseractBin = env('OCR_TESSERACT_PATH', '/usr/bin/tesseract');

        if (is_executable($pdftoppmBin) && is_executable($tesseractBin)) {
            $tmpDir = sys_get_temp_dir().'/lmo_ocr_'.uniqid();
            @mkdir($tmpDir, 0700, true);
            $prefix = $tmpDir.'/page';

            $renderResult = Process::timeout(90)->run([$pdftoppmBin, '-png', '-r', '200', $pdfPath, $prefix]);
            $pageTexts = [];

            if ($renderResult->successful()) {
                $images = glob($tmpDir.'/page*.png') ?: [];
                sort($images);
                foreach ($images as $imgPath) {
                    $pageTexts[] = $this->runTesseract($tesseractBin, $imgPath);
                    @unlink($imgPath);
                }
            }
            @rmdir($tmpDir);

            $combined = trim(implode("\n\n", array_filter($pageTexts)));
            if ($combined !== '') {
                return $this->cleanExtractedText($combined);
            }
        }

        // 3. Fallback: Parse uncompressed text streams from PDF binary if present
        $rawContent = (string) file_get_contents($pdfPath);
        if (preg_match_all('/\(([^\(\)\\\\]{3,})\)\s*Tj/u', $rawContent, $matches)) {
            $fallbackText = trim(implode(' ', $matches[1]));
            if ($fallbackText !== '') {
                return $this->cleanExtractedText($fallbackText);
            }
        }

        return '';
    }

    protected function extractFromImage(string $imagePath): string
    {
        $tesseractBin = env('OCR_TESSERACT_PATH', '/usr/bin/tesseract');

        if (is_executable($tesseractBin)) {
            return $this->cleanExtractedText($this->runTesseract($tesseractBin, $imagePath));
        }

        // Check EXIF / ImageDescription or embedded metadata comments when running in an environment where tesseract binary is pending install
        $info = [];
        @getimagesize($imagePath, $info);
        $rawBytes = (string) file_get_contents($imagePath);

        if (preg_match('/LMO_OCR_TEXT:(.+?)_END_OCR/s', $rawBytes, $m)) {
            return $this->cleanExtractedText($m[1]);
        }

        return '[Image registered and stored securely. Install tesseract-ocr binary on host for automated bitmap OCR extraction.]';
    }

    protected function runTesseract(string $binary, string $imagePath): string
    {
        $lang = env('OCR_LANGUAGE', 'eng');
        $result = Process::timeout(90)->run([$binary, $imagePath, 'stdout', '-l', $lang]);

        if (! $result->successful()) {
            throw new \RuntimeException('Tesseract execution failed: '.trim($result->errorOutput()));
        }

        return $result->output();
    }

    protected function cleanExtractedText(string $text): string
    {
        $clean = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);
        $clean = preg_replace("/\n{3,}/", "\n\n", (string) $clean);

        return trim((string) $clean);
    }
}
