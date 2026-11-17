<?php

namespace App\Services;

use App\Models\Document;
use App\Models\DocumentArchive;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ArchiveService
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * Archive a document non-destructively with a mandatory archival reason.
     */
    public function archiveDocument(
        Document $document,
        User $actor,
        string $reason,
        ?string $locationCode = null,
        ?string $retentionUntil = null
    ): DocumentArchive {
        return DB::transaction(function () use ($document, $actor, $reason, $locationCode, $retentionUntil) {
            $retentionYears = $document->category?->retention_years ?? (int) env('DOCUMENT_DEFAULT_RETENTION_YEARS', 5);
            $defaultRetentionDate = now()->addYears($retentionYears)->toDateString();

            $archiveRecord = DocumentArchive::create([
                'document_id' => $document->id,
                'archived_by' => $actor->id,
                'archive_reason' => trim($reason),
                'archive_location_code' => $locationCode ?: 'DIGI-VAULT-'.now()->format('Y'),
                'retention_until' => $retentionUntil ?: $defaultRetentionDate,
                'archived_at' => now(),
            ]);

            $document->update([
                'status' => 'archived',
                'is_archived' => true,
                'archived_at' => now(),
            ]);

            $this->auditService->log(
                'document.archived',
                "Archived document {$document->internal_reference}: {$reason}",
                $document,
                [
                    'archive_id' => $archiveRecord->id,
                    'reason' => $reason,
                    'location_code' => $archiveRecord->archive_location_code,
                    'retention_until' => $archiveRecord->retention_until?->toDateString(),
                ],
                'warning',
                $actor
            );

            return $archiveRecord;
        });
    }

    /**
     * Restore an archived document to the active register.
     */
    public function restoreDocument(Document $document, User $actor, string $restoreReason): Document
    {
        return DB::transaction(function () use ($document, $actor, $restoreReason) {
            $latestArchive = $document->archives()->whereNull('restored_at')->latest('archived_at')->first();

            if ($latestArchive) {
                $latestArchive->update([
                    'restored_by' => $actor->id,
                    'restore_reason' => trim($restoreReason),
                    'restored_at' => now(),
                ]);
            }

            $document->update([
                'status' => 'registered',
                'is_archived' => false,
                'archived_at' => null,
            ]);

            $this->auditService->log(
                'document.restored',
                "Restored document {$document->internal_reference} from Digital Archive: {$restoreReason}",
                $document,
                ['restore_reason' => $restoreReason],
                'warning',
                $actor
            );

            return $document->fresh();
        });
    }
}
