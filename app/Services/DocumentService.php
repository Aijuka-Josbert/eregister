<?php

namespace App\Services;

use App\Jobs\ProcessDocumentOcrJob;
use App\Models\Document;
use App\Models\DocumentTag;
use App\Models\Office;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DocumentService
{
    public function __construct(
        protected FileStorageService $fileStorage,
        protected AuditService $auditService,
        protected OcrService $ocrService
    ) {}

    /**
     * Generate a unique institutional reference ID (e.g. LMO-2026-00001).
     */
    public function generateInternalReference(?int $officeId = null): string
    {
        $office = $officeId ? Office::find($officeId) : null;
        $prefix = $office ? strtoupper($office->code) : 'LMO';
        $year = now()->format('Y');
        $pattern = "{$prefix}-{$year}-%";

        $latest = Document::where('internal_reference', 'like', $pattern)
            ->orderByDesc('id')
            ->value('internal_reference');

        $nextNumber = 1;
        if ($latest && preg_match('/-(\d+)$/', $latest, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        do {
            $candidate = sprintf('%s-%s-%05d', $prefix, $year, $nextNumber);
            $exists = Document::where('internal_reference', $candidate)->exists();
            if ($exists) {
                $nextNumber++;
            }
        } while ($exists);

        return $candidate;
    }

    /**
     * Check if potentially duplicate documents already exist.
     */
    public function findPotentialDuplicates(
        ?string $officialReference,
        ?string $organization,
        ?string $title = null,
        ?int $excludeId = null
    ): Collection {
        if (empty($officialReference) && empty($title)) {
            return new Collection;
        }

        return Document::query()
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->where(function ($q) use ($officialReference, $organization, $title) {
                if (! empty($officialReference)) {
                    $q->where('official_reference', trim($officialReference));
                }
                if (! empty($officialReference) && ! empty($organization)) {
                    $q->orWhere(function ($sub) use ($officialReference, $organization) {
                        $sub->where('official_reference', trim($officialReference))
                            ->where('organization', 'like', '%'.trim($organization).'%');
                    });
                } elseif (! empty($title) && ! empty($organization)) {
                    $q->orWhere(function ($sub) use ($title, $organization) {
                        $sub->where('title', trim($title))
                            ->where('organization', trim($organization));
                    });
                }
            })
            ->limit(5)
            ->get();
    }

    /**
     * Register a new document with uploaded files, camera scans, and tags.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $uploadedFiles
     * @param  array<int, string>  $cameraCapturesBase64
     */
    public function registerDocument(
        array $data,
        User $registrar,
        array $uploadedFiles = [],
        array $cameraCapturesBase64 = [],
        bool $processOcrImmediately = true
    ): Document {
        return DB::transaction(function () use ($data, $registrar, $uploadedFiles, $cameraCapturesBase64, $processOcrImmediately) {
            $officeId = (int) ($data['office_id'] ?? $registrar->office_id ?? Office::first()?->id);
            $internalRef = $this->generateInternalReference($officeId);

            $document = Document::create([
                'uuid' => (string) Str::uuid(),
                'internal_reference' => $internalRef,
                'official_reference' => isset($data['official_reference']) && trim((string) $data['official_reference']) !== ''
                    ? trim((string) $data['official_reference'])
                    : null,
                'title' => trim((string) $data['title']),
                'description' => $data['description'] ?? null,
                'organization' => trim((string) $data['organization']),
                'sender' => $data['sender'] ?? null,
                'recipient' => $data['recipient'] ?? 'The Right Honourable Lord Mayor',
                'direction' => $data['direction'] ?? 'incoming',
                'document_type' => $data['document_type'] ?? 'letter',
                'event_date' => $data['event_date'] ?? null,
                'date_issued' => $data['date_issued'] ?? null,
                'date_received' => $data['date_received'] ?? now()->toDateString(),
                'category_id' => (int) $data['category_id'],
                'office_id' => $officeId,
                'department_id' => ! empty($data['department_id']) ? (int) $data['department_id'] : $registrar->department_id,
                'confidentiality_level' => $data['confidentiality_level'] ?? 'internal',
                'status' => $data['status'] ?? 'registered',
                'ocr_status' => 'pending',
                'registered_by' => $registrar->id,
                'registered_at' => now(),
                'is_archived' => false,
            ]);

            // Attach tags if provided
            if (! empty($data['tags'])) {
                $this->syncTags($document, $data['tags']);
            }

            $pageOrder = 1;
            $primaryPath = null;

            // Store standard uploaded files
            foreach ($uploadedFiles as $file) {
                if ($file instanceof UploadedFile && $file->isValid()) {
                    $docFile = $this->fileStorage->storeUploadedFile(
                        $file,
                        $document,
                        $registrar,
                        $pageOrder++,
                        $data['capture_source'] ?? 'upload'
                    );
                    $primaryPath ??= $docFile->storage_path;
                }
            }

            // Store mobile camera base64 captures
            foreach ($cameraCapturesBase64 as $idx => $base64Data) {
                if (is_string($base64Data) && preg_match('/^data:image\/(jpeg|jpg|png);base64,(.+)$/i', $base64Data, $matches)) {
                    $ext = strtolower($matches[1]) === 'png' ? 'png' : 'jpg';
                    $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
                    $binary = base64_decode($matches[2], true);

                    if ($binary !== false && strlen($binary) > 16) {
                        $scanNum = $idx + 1;
                        $docFile = $this->fileStorage->storeRawContent(
                            $binary,
                            "camera-scan-page-{$scanNum}.{$ext}",
                            $mime,
                            $document,
                            $registrar,
                            $pageOrder++,
                            'camera_scan'
                        );
                        $primaryPath ??= $docFile->storage_path;
                    }
                }
            }

            $fileCount = $pageOrder - 1;
            $document->update([
                'primary_file_path' => $primaryPath,
                'file_count' => $fileCount,
                'ocr_status' => $fileCount > 0 ? 'pending' : 'skipped',
            ]);

            $this->auditService->log(
                'document.registered',
                "Registered document {$document->internal_reference} ({$document->title})",
                $document,
                [
                    'internal_reference' => $document->internal_reference,
                    'official_reference' => $document->official_reference,
                    'organization' => $document->organization,
                    'confidentiality_level' => $document->confidentiality_level,
                    'file_count' => $fileCount,
                ],
                'info',
                $registrar
            );

            if ($fileCount > 0) {
                if ($processOcrImmediately) {
                    $this->ocrService->processDocument($document);
                } else {
                    ProcessDocumentOcrJob::dispatch($document->id);
                }
            }

            return $document->fresh(['category', 'office', 'department', 'files', 'tags', 'registrar']);
        });
    }

    /**
     * Update document metadata and log field diffs to the immutable audit trail.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateDocument(Document $document, array $data, User $editor): Document
    {
        return DB::transaction(function () use ($document, $data, $editor) {
            $trackedFields = [
                'official_reference',
                'title',
                'description',
                'organization',
                'sender',
                'recipient',
                'direction',
                'document_type',
                'event_date',
                'date_issued',
                'date_received',
                'category_id',
                'office_id',
                'department_id',
                'confidentiality_level',
                'status',
            ];

            $before = $document->only($trackedFields);

            $document->update([
                'official_reference' => isset($data['official_reference']) && trim((string) $data['official_reference']) !== ''
                    ? trim((string) $data['official_reference'])
                    : null,
                'title' => trim((string) ($data['title'] ?? $document->title)),
                'description' => $data['description'] ?? $document->description,
                'organization' => trim((string) ($data['organization'] ?? $document->organization)),
                'sender' => $data['sender'] ?? $document->sender,
                'recipient' => $data['recipient'] ?? $document->recipient,
                'direction' => $data['direction'] ?? $document->direction,
                'document_type' => $data['document_type'] ?? $document->document_type,
                'event_date' => $data['event_date'] ?? $document->event_date,
                'date_issued' => $data['date_issued'] ?? $document->date_issued,
                'date_received' => $data['date_received'] ?? $document->date_received,
                'category_id' => (int) ($data['category_id'] ?? $document->category_id),
                'office_id' => (int) ($data['office_id'] ?? $document->office_id),
                'department_id' => array_key_exists('department_id', $data)
                    ? ($data['department_id'] ? (int) $data['department_id'] : null)
                    : $document->department_id,
                'confidentiality_level' => $data['confidentiality_level'] ?? $document->confidentiality_level,
                'status' => $data['status'] ?? $document->status,
            ]);

            if (array_key_exists('tags', $data)) {
                $this->syncTags($document, $data['tags']);
            }

            $after = $document->fresh()->only($trackedFields);
            $changes = [];
            foreach ($trackedFields as $field) {
                if ((string) ($before[$field] ?? '') !== (string) ($after[$field] ?? '')) {
                    $changes[$field] = [
                        'old' => $before[$field] ?? null,
                        'new' => $after[$field] ?? null,
                    ];
                }
            }

            $this->auditService->log(
                'document.updated',
                "Updated metadata for document {$document->internal_reference}",
                $document,
                ['changes' => $changes],
                isset($changes['confidentiality_level']) ? 'warning' : 'info',
                $editor
            );

            return $document->fresh(['category', 'office', 'department', 'files', 'tags']);
        });
    }

    /**
     * Synchronize comma-separated or array tags with the document.
     */
    public function syncTags(Document $document, string|array|null $rawTags): void
    {
        if (empty($rawTags)) {
            $document->tags()->detach();

            return;
        }

        $tagNames = is_array($rawTags)
            ? $rawTags
            : array_map('trim', explode(',', $rawTags));

        $tagIds = [];
        foreach (array_filter($tagNames) as $name) {
            $cleanName = Str::limit(trim($name), 80, '');
            $slug = Str::slug($cleanName);
            if ($slug === '') {
                continue;
            }
            $tag = DocumentTag::firstOrCreate(
                ['slug' => $slug],
                ['name' => $cleanName]
            );
            $tagIds[] = $tag->id;
        }

        $document->tags()->sync(array_unique($tagIds));
    }
}
