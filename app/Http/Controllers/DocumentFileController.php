<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentFile;
use App\Models\User;
use App\Services\AuditService;
use App\Services\FileStorageService;
use App\Services\OcrService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentFileController extends Controller
{
    public function __construct(
        protected FileStorageService $fileStorage,
        protected OcrService $ocrService,
        protected AuditService $auditService
    ) {}

    /**
     * Stream inline preview of a private file after policy authorization.
     */
    public function preview(Request $request, Document $document, DocumentFile $file): StreamedResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($file->document_id !== $document->id) {
            abort(404);
        }

        if (! $user->can('download', $document)) {
            $this->auditService->log(
                'security.unauthorized_file_preview',
                "User {$user->name} was denied preview access to file {$file->original_name} on {$document->internal_reference}",
                $document,
                ['file_uuid' => $file->uuid],
                'critical',
                $user
            );
            abort(403, 'Unauthorized file preview attempt logged.');
        }

        $this->auditService->log(
            'document.file_previewed',
            "Previewed attachment {$file->original_name} on {$document->internal_reference}",
            $document,
            ['file_uuid' => $file->uuid, 'original_name' => $file->original_name],
            'info',
            $user
        );

        return $this->fileStorage->streamFile($file, inline: true);
    }

    /**
     * Download a private file after policy authorization and audit logging.
     */
    public function download(Request $request, Document $document, DocumentFile $file): StreamedResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($file->document_id !== $document->id) {
            abort(404);
        }

        if (! $user->can('download', $document)) {
            $this->auditService->log(
                'security.unauthorized_file_download',
                "User {$user->name} was denied download access to file {$file->original_name} on {$document->internal_reference}",
                $document,
                ['file_uuid' => $file->uuid],
                'critical',
                $user
            );
            abort(403, 'Unauthorized file download attempt logged.');
        }

        $this->auditService->log(
            'document.downloaded',
            "Downloaded attachment {$file->original_name} ({$file->formattedSize()}) from {$document->internal_reference}",
            $document,
            [
                'file_uuid' => $file->uuid,
                'original_name' => $file->original_name,
                'sha256' => $file->sha256_hash,
            ],
            in_array($document->confidentiality_level, ['confidential', 'strictly_confidential'], true) ? 'warning' : 'info',
            $user
        );

        return $this->fileStorage->streamFile($file, inline: false);
    }

    /**
     * Upload additional files or camera scans to an existing document (never overwrites existing files).
     */
    public function storeAdditional(Request $request, Document $document): RedirectResponse
    {
        Gate::authorize('update', $document);

        $maxKb = ((int) env('DOCUMENT_MAX_UPLOAD_MB', 25)) * 1024;
        $request->validate([
            'files' => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => ['file', 'mimes:pdf,jpg,jpeg,png', "max:{$maxKb}"],
        ]);

        /** @var User $user */
        $user = $request->user();
        $nextOrder = ((int) $document->files()->max('page_order')) + 1;
        $addedCount = 0;

        foreach ($request->file('files', []) as $uploadedFile) {
            $docFile = $this->fileStorage->storeUploadedFile(
                $uploadedFile,
                $document,
                $user,
                $nextOrder++,
                'upload'
            );
            if (! $document->primary_file_path) {
                $document->primary_file_path = $docFile->storage_path;
            }
            $addedCount++;
        }

        $document->update([
            'file_count' => $document->files()->count(),
            'primary_file_path' => $document->primary_file_path,
        ]);

        $this->auditService->log(
            'document.files_added',
            "Attached {$addedCount} additional file(s) to {$document->internal_reference}",
            $document,
            ['added_count' => $addedCount],
            'info',
            $user
        );

        $this->ocrService->processDocument($document);

        return back()->with('success', "{$addedCount} additional file(s) attached and indexed via OCR.");
    }
}
