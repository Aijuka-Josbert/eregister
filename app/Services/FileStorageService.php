<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentFile;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileStorageService
{
    public const ALLOWED_MIMES = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
    ];

    /**
     * Validate magic bytes and store an uploaded file in private storage.
     */
    public function storeUploadedFile(
        UploadedFile $file,
        Document $document,
        User $uploader,
        int $pageOrder = 1,
        string $captureSource = 'upload'
    ): DocumentFile {
        $realMime = $file->getMimeType() ?: $file->getClientMimeType();

        if (! array_key_exists($realMime, self::ALLOWED_MIMES)) {
            throw new RuntimeException("Unsupported file MIME signature: {$realMime}. Only PDF, JPEG, and PNG are permitted.");
        }

        // Verify magic bytes directly from file header
        $handle = fopen($file->getRealPath(), 'rb');
        $headerBytes = $handle ? fread($handle, 8) : '';
        if ($handle) {
            fclose($handle);
        }

        $isPdf = str_starts_with($headerBytes, '%PDF-');
        $isJpeg = str_starts_with($headerBytes, "\xFF\xD8\xFF");
        $isPng = str_starts_with($headerBytes, "\x89PNG\r\n\x1A\n");

        if (! $isPdf && ! $isJpeg && ! $isPng) {
            throw new RuntimeException('File header signature verification failed. File does not match PDF, JPEG, or PNG binary header.');
        }

        $extension = self::ALLOWED_MIMES[$realMime];
        $uuid = (string) Str::uuid();
        $disk = config('filesystems.default', 'local');
        $directory = sprintf('documents/%s/%s', now()->format('Y'), now()->format('m'));
        $filename = "{$uuid}.{$extension}";
        $storagePath = "{$directory}/{$filename}";

        $sha256 = hash_file('sha256', $file->getRealPath());
        $fileSize = $file->getSize();
        $originalName = $this->sanitizeFilename($file->getClientOriginalName() ?: "scan-page-{$pageOrder}.{$extension}");

        Storage::disk($disk)->putFileAs($directory, $file, $filename);

        return DocumentFile::create([
            'document_id' => $document->id,
            'uuid' => $uuid,
            'original_name' => $originalName,
            'storage_disk' => $disk,
            'storage_path' => $storagePath,
            'mime_type' => $realMime,
            'extension' => $extension,
            'file_size' => $fileSize,
            'sha256_hash' => $sha256,
            'page_order' => $pageOrder,
            'capture_source' => $captureSource,
            'ocr_status' => 'pending',
            'uploaded_by' => $uploader->id,
        ]);
    }

    /**
     * Store raw binary content (e.g. generated sample PDFs or base64 camera scans) into private storage.
     */
    public function storeRawContent(
        string $binaryContent,
        string $originalName,
        string $mimeType,
        Document $document,
        User $uploader,
        int $pageOrder = 1,
        string $captureSource = 'upload'
    ): DocumentFile {
        $extension = self::ALLOWED_MIMES[$mimeType] ?? 'pdf';
        $uuid = (string) Str::uuid();
        $disk = config('filesystems.default', 'local');
        $directory = sprintf('documents/%s/%s', now()->format('Y'), now()->format('m'));
        $storagePath = "{$directory}/{$uuid}.{$extension}";

        Storage::disk($disk)->put($storagePath, $binaryContent);

        return DocumentFile::create([
            'document_id' => $document->id,
            'uuid' => $uuid,
            'original_name' => $this->sanitizeFilename($originalName),
            'storage_disk' => $disk,
            'storage_path' => $storagePath,
            'mime_type' => $mimeType,
            'extension' => $extension,
            'file_size' => strlen($binaryContent),
            'sha256_hash' => hash('sha256', $binaryContent),
            'page_order' => $pageOrder,
            'capture_source' => $captureSource,
            'ocr_status' => 'pending',
            'uploaded_by' => $uploader->id,
        ]);
    }

    /**
     * Stream a private document file securely with cache-busting security headers.
     */
    public function streamFile(DocumentFile $file, bool $inline = false): StreamedResponse
    {
        $disk = Storage::disk($file->storage_disk);

        if (! $disk->exists($file->storage_path)) {
            abort(404, 'The requested document file could not be found in private storage.');
        }

        $disposition = $inline ? 'inline' : 'attachment';
        $safeName = addslashes($file->original_name);

        return $disk->response($file->storage_path, $file->original_name, [
            'Content-Type' => $file->mime_type,
            'Content-Disposition' => "{$disposition}; filename=\"{$safeName}\"",
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function getAbsolutePath(DocumentFile $file): string
    {
        return Storage::disk($file->storage_disk)->path($file->storage_path);
    }

    protected function sanitizeFilename(string $filename): string
    {
        $clean = preg_replace('/[^A-Za-z0-9._\-\s()]/', '_', $filename);

        return Str::limit(trim((string) $clean), 200, '');
    }
}
