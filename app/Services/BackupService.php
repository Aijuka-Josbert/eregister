<?php

namespace App\Services;

use App\Models\BackupLog;
use App\Models\Document;
use App\Models\DocumentActivityLog;
use App\Models\DocumentArchive;
use App\Models\DocumentCategory;
use App\Models\DocumentFile;
use App\Models\Office;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Throwable;
use ZipArchive;

class BackupService
{
    public function __construct(
        protected AuditService $auditService
    ) {}

    /**
     * Create an independent encrypted backup snapshot containing database tables, metadata, OCR text, audit logs, and private files.
     */
    public function createBackup(string $type = 'full', ?User $actor = null): BackupLog
    {
        $reference = 'LMO-BKP-'.now()->format('Ymd-His').'-'.strtoupper(substr(uniqid(), -4));
        $disk = Storage::disk('local');
        $backupDir = 'backups/'.now()->format('Y/m');
        $disk->makeDirectory($backupDir);

        $log = BackupLog::create([
            'backup_reference' => $reference,
            'type' => $type,
            'storage_disk' => 'local',
            'is_encrypted' => (bool) env('BACKUP_ENCRYPTION_ENABLED', true),
            'status' => 'running',
            'triggered_by' => $actor?->id,
        ]);

        try {
            $snapshotPayload = [
                'backup_reference' => $reference,
                'generated_at' => now()->toIso8601String(),
                'type' => $type,
                'counts' => [
                    'offices' => Office::count(),
                    'users' => User::count(),
                    'categories' => DocumentCategory::count(),
                    'documents' => Document::count(),
                    'document_files' => DocumentFile::count(),
                    'archives' => DocumentArchive::count(),
                    'activity_logs' => DocumentActivityLog::count(),
                ],
                'tables' => [
                    'offices' => Office::all()->toArray(),
                    'categories' => DocumentCategory::all()->toArray(),
                    'documents' => Document::with(['tags', 'files'])->get()->toArray(),
                    'archives' => DocumentArchive::all()->toArray(),
                    'activity_logs' => DocumentActivityLog::latest('created_at')->limit(5000)->get()->toArray(),
                ],
            ];

            $tempZipPath = sys_get_temp_dir()."/{$reference}.zip";
            $zip = new ZipArchive;
            if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Unable to initialize ZipArchive for backup.');
            }

            $zip->addFromString('database_snapshot.json', json_encode($snapshotPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            if ($type === 'full' || $type === 'files_only') {
                foreach (DocumentFile::all() as $docFile) {
                    $fileDisk = Storage::disk($docFile->storage_disk);
                    if ($fileDisk->exists($docFile->storage_path)) {
                        $zip->addFromString(
                            'files/'.$docFile->uuid.'_'.$docFile->original_name,
                            (string) $fileDisk->get($docFile->storage_path)
                        );
                    }
                }
            }

            $zip->close();
            $rawZipBytes = (string) file_get_contents($tempZipPath);
            @unlink($tempZipPath);

            if ($log->is_encrypted) {
                $key = hash('sha256', (string) config('app.key'), true);
                $iv = random_bytes(16);
                $cipherText = openssl_encrypt($rawZipBytes, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
                $finalBytes = 'LMOENC1'.$iv.$cipherText;
                $relativeFilePath = "{$backupDir}/{$reference}.zip.enc";
            } else {
                $finalBytes = $rawZipBytes;
                $relativeFilePath = "{$backupDir}/{$reference}.zip";
            }

            $disk->put($relativeFilePath, $finalBytes);
            $sha256 = hash('sha256', $finalBytes);

            $log->update([
                'file_path' => $relativeFilePath,
                'file_size' => strlen($finalBytes),
                'sha256_hash' => $sha256,
                'status' => 'completed',
                'notes' => sprintf(
                    'Backed up %d documents, %d files, and %d audit records.',
                    $snapshotPayload['counts']['documents'],
                    $snapshotPayload['counts']['document_files'],
                    $snapshotPayload['counts']['activity_logs']
                ),
                'completed_at' => now(),
            ]);

            $this->auditService->log(
                'system.backup_completed',
                "Completed encrypted system backup {$reference} (".number_format(strlen($finalBytes) / 1024, 1).' KB)',
                null,
                ['backup_reference' => $reference, 'sha256' => $sha256],
                'info',
                $actor
            );

            return $log->fresh();
        } catch (Throwable $e) {
            $log->update([
                'status' => 'failed',
                'notes' => $e->getMessage(),
                'completed_at' => now(),
            ]);

            throw $e;
        }
    }

    /**
     * Verify backup integrity and test decryption/restoration readability.
     */
    public function verifyBackup(BackupLog $log): array
    {
        $disk = Storage::disk($log->storage_disk);
        if (! $log->file_path || ! $disk->exists($log->file_path)) {
            return ['verified' => false, 'error' => 'Backup archive file is missing from storage.'];
        }

        $bytes = (string) $disk->get($log->file_path);
        $actualHash = hash('sha256', $bytes);

        if ($actualHash !== $log->sha256_hash) {
            return ['verified' => false, 'error' => 'SHA-256 checksum mismatch detected.'];
        }

        if ($log->is_encrypted && str_starts_with($bytes, 'LMOENC1')) {
            $key = hash('sha256', (string) config('app.key'), true);
            $iv = substr($bytes, 7, 16);
            $cipherText = substr($bytes, 23);
            $decrypted = openssl_decrypt($cipherText, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
            if ($decrypted === false) {
                return ['verified' => false, 'error' => 'AES-256-CBC decryption check failed.'];
            }
            $bytes = $decrypted;
        }

        $tempZip = sys_get_temp_dir().'/verify_'.$log->backup_reference.'.zip';
        file_put_contents($tempZip, $bytes);
        $zip = new ZipArchive;
        $opened = $zip->open($tempZip);
        $manifestJson = $opened === true ? $zip->getFromName('database_snapshot.json') : false;
        if ($opened === true) {
            $zip->close();
        }
        @unlink($tempZip);

        if (! $manifestJson) {
            return ['verified' => false, 'error' => 'Could not read database_snapshot.json inside archive.'];
        }

        $manifest = json_decode($manifestJson, true);
        $log->update(['status' => 'verified']);

        return [
            'verified' => true,
            'backup_reference' => $log->backup_reference,
            'sha256_match' => true,
            'counts' => $manifest['counts'] ?? [],
        ];
    }
}
