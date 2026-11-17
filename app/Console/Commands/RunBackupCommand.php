<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class RunBackupCommand extends Command
{
    protected $signature = 'drms:backup {--type=full : Backup type (full, database_only, files_only)} {--verify : Immediately verify backup decryption and checksum}';

    protected $description = 'Create an independent encrypted backup of the LMO Digital Records Management System database and private files';

    public function handle(BackupService $backupService): int
    {
        $type = (string) $this->option('type');
        $this->info("Starting LMO DRMS [{$type}] encrypted backup...");

        $log = $backupService->createBackup($type);
        $this->info("Backup created: {$log->backup_reference} ({$log->file_size} bytes)");
        $this->line("SHA-256: {$log->sha256_hash}");

        if ($this->option('verify')) {
            $result = $backupService->verifyBackup($log);
            if (! $result['verified']) {
                $this->error('Backup verification failed: '.($result['error'] ?? 'Unknown error'));

                return self::FAILURE;
            }
            $this->info('Backup decryption & SHA-256 verification passed.');
        }

        return self::SUCCESS;
    }
}
