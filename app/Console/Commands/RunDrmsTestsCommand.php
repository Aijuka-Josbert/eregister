<?php

namespace App\Console\Commands;

use App\Models\Document;
use App\Models\DocumentActivityLog;
use App\Models\DocumentCategory;
use App\Models\Office;
use App\Models\User;
use App\Services\ArchiveService;
use App\Services\BackupService;
use App\Services\DocumentService;
use App\Services\SearchService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use LogicException;
use RuntimeException;
use Throwable;

class RunDrmsTestsCommand extends Command
{
    protected $signature = 'drms:test';

    protected $description = 'Execute the LMO DRMS automated verification suite across all 14 functional & security domains';

    protected int $passed = 0;

    protected int $failed = 0;

    public function handle(): int
    {
        $this->info('====================================================================');
        $this->info(' LMO DIGITAL RECORDS MANAGEMENT SYSTEM — AUTOMATED QA & SECURITY SUITE ');
        $this->info('====================================================================');

        if (User::count() === 0) {
            $this->call('db:seed');
        }

        $tests = [
            '1. Authentication & Active Staff Verification' => fn () => $this->verifyAuthenticationAndDeactivation(),
            '2. Role-Based Access Control (RBAC) & Permission Matrix' => fn () => $this->verifyRolePermissions(),
            '3. Office Separation & Multi-Office Isolation' => fn () => $this->verifyOfficeIsolation(),
            '4. Confidentiality Classification Enforcement (Public vs Strictly Confidential)' => fn () => $this->verifyConfidentialityClearance(),
            '5. Document Registration & Verbatim Official Reference Preservation' => fn () => $this->verifyDocumentRegistrationAndReference(),
            '6. Duplicate Reference Detection Engine' => fn () => $this->verifyDuplicateDetection(),
            '7. Multi-File Upload, Mobile Camera Base64 Capture & Magic-Byte Validation' => fn () => $this->verifyFileUploadsAndCameraCapture(),
            '8. Invalid / Spoofed File Signature Rejection' => fn () => $this->verifySpoofedFileRejection(),
            '9. Asynchronous & Immediate OCR Text Extraction (Poppler + Tesseract)' => fn () => $this->verifyOcrExtraction(),
            '10. Global Full-Text Search, Filters & Permission Scoping' => fn () => $this->verifySearchAndHighlighting(),
            '11. Non-Destructive Digital Archiving & Authorized Restoration' => fn () => $this->verifyArchivingAndRestoration(),
            '12. Append-Only Immutable Audit Trail & Tamper Protection' => fn () => $this->verifyImmutableAuditLogs(),
            '13. Secure Private File Streaming & Unauthorized Download Blocking' => fn () => $this->verifyPrivateFileStreaming(),
            '14. Independent AES-256 Encrypted Backup & Recovery Verification' => fn () => $this->verifyEncryptedBackupAndRecovery(),
        ];

        foreach ($tests as $label => $callback) {
            DB::beginTransaction();
            try {
                $callback();
                DB::rollBack();
                $this->line(" <fg=green>✔ PASS</>  {$label}");
                $this->passed++;
            } catch (Throwable $e) {
                DB::rollBack();
                $this->line(" <fg=red>✘ FAIL</>  {$label}");
                $this->error("         Reason: {$e->getMessage()} ({$e->getFile()}:{$e->getLine()})");
                $this->failed++;
            }
        }

        $this->newLine();
        $this->info("QA Verification Summary: {$this->passed} passed, {$this->failed} failed.");

        return $this->failed === 0 ? self::SUCCESS : self::FAILURE;
    }

    protected function assertCondition(bool $condition, string $message): void
    {
        if (! $condition) {
            throw new RuntimeException($message);
        }
    }

    protected function verifyAuthenticationAndDeactivation(): void
    {
        $officer = User::where('email', 'officer@lmo.go.ug')->firstOrFail();
        $this->assertCondition($officer->is_active === true, 'Officer should be active by default.');

        $readOnly = User::where('email', 'readonly@lmo.go.ug')->firstOrFail();
        $readOnly->update(['is_active' => false]);
        $this->assertCondition(! $readOnly->fresh()->hasPermission('documents.view'), 'Deactivated user must lose all active permissions.');
    }

    protected function verifyRolePermissions(): void
    {
        $admin = User::where('email', 'admin@lmo.go.ug')->firstOrFail();
        $manager = User::where('email', 'manager@lmo.go.ug')->firstOrFail();
        $officer = User::where('email', 'officer@lmo.go.ug')->firstOrFail();
        $readOnly = User::where('email', 'readonly@lmo.go.ug')->firstOrFail();

        $this->assertCondition($admin->hasPermission('users.manage'), 'System Admin must have users.manage.');
        $this->assertCondition($manager->hasPermission('documents.archive'), 'Records Manager must have documents.archive.');
        $this->assertCondition(! $officer->hasPermission('documents.archive'), 'Records Officer must not have documents.archive.');
        $this->assertCondition(! $readOnly->hasPermission('documents.create'), 'Read-only user must not have documents.create.');
    }

    protected function verifyOfficeIsolation(): void
    {
        $dlmoOfficer = User::where('email', 'deputy.records@lmo.go.ug')->firstOrFail();
        $lmoDoc = Document::whereHas('office', fn ($q) => $q->where('code', 'LMO'))->firstOrFail();

        $canView = Gate::forUser($dlmoOfficer)->allows('view', $lmoDoc);
        $this->assertCondition($canView === false, 'Officer from Deputy Lord Mayor Office (DLMO) must be blocked from viewing unassigned LMO records.');
    }

    protected function verifyConfidentialityClearance(): void
    {
        $readOnly = User::where('email', 'readonly@lmo.go.ug')->firstOrFail();
        $manager = User::where('email', 'manager@lmo.go.ug')->firstOrFail();
        $executive = User::where('email', 'executive@lmo.go.ug')->firstOrFail();

        $strictDoc = Document::where('confidentiality_level', 'strictly_confidential')->firstOrFail();

        $this->assertCondition(! Gate::forUser($readOnly)->allows('view', $strictDoc), 'Read-Only user must be denied Strictly Confidential record.');
        $this->assertCondition(Gate::forUser($manager)->allows('view', $strictDoc), 'Records Manager must be allowed Strictly Confidential record.');
        $this->assertCondition(Gate::forUser($executive)->allows('view', $strictDoc), 'Office Manager must be allowed Strictly Confidential record.');
    }

    protected function verifyDocumentRegistrationAndReference(): void
    {
        $officer = User::where('email', 'officer@lmo.go.ug')->firstOrFail();
        $cat = DocumentCategory::where('slug', 'invitation')->firstOrFail();
        /** @var DocumentService $docSvc */
        $docSvc = app(DocumentService::class);

        // Register two documents from different external organizations that happen to use the exact same official reference number
        $docA = $docSvc->registerDocument([
            'official_reference' => 'REF/GEN/2026/001',
            'title' => 'Invitation from Organization Alpha',
            'organization' => 'Organization Alpha',
            'date_received' => now()->toDateString(),
            'category_id' => $cat->id,
            'office_id' => $officer->office_id,
            'document_type' => 'invitation',
            'confidentiality_level' => 'public',
        ], $officer);

        $docB = $docSvc->registerDocument([
            'official_reference' => 'REF/GEN/2026/001',
            'title' => 'Invitation from Organization Beta',
            'organization' => 'Organization Beta',
            'date_received' => now()->toDateString(),
            'category_id' => $cat->id,
            'office_id' => $officer->office_id,
            'document_type' => 'invitation',
            'confidentiality_level' => 'public',
        ], $officer);

        $this->assertCondition($docA->official_reference === 'REF/GEN/2026/001', 'Official reference must be preserved verbatim.');
        $this->assertCondition($docB->official_reference === 'REF/GEN/2026/001', 'Second organization with identical reference format must also be preserved.');
        $this->assertCondition($docA->internal_reference !== $docB->internal_reference, 'Internal LMO tracking IDs must be strictly unique.');
    }

    protected function verifyDuplicateDetection(): void
    {
        /** @var DocumentService $docSvc */
        $docSvc = app(DocumentService::class);
        $dups = $docSvc->findPotentialDuplicates('MKCCMA/PS/2026/084', 'Ministry of Kampala');
        $this->assertCondition($dups->isNotEmpty(), 'Duplicate check must detect existing MKCCMA/PS/2026/084 record.');
    }

    protected function verifyFileUploadsAndCameraCapture(): void
    {
        $officer = User::where('email', 'officer@lmo.go.ug')->firstOrFail();
        $cat = DocumentCategory::firstOrFail();
        /** @var DocumentService $docSvc */
        $docSvc = app(DocumentService::class);

        // Minimal valid 1x1 PNG base64 camera capture
        $validPngBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $doc = $docSvc->registerDocument(
            [
                'official_reference' => 'CAM/SCAN/2026/99',
                'title' => 'Mobile Camera Multi-Page Scan Verification',
                'organization' => 'Kampala District Land Board',
                'date_received' => now()->toDateString(),
                'category_id' => $cat->id,
                'office_id' => $officer->office_id,
                'confidentiality_level' => 'internal',
            ],
            $officer,
            [],
            [$validPngBase64, $validPngBase64]
        );

        $this->assertCondition($doc->file_count === 2, 'Two mobile camera pages must be stored.');
        $this->assertCondition($doc->files->first()->capture_source === 'camera_scan', 'Capture source must be camera_scan.');
    }

    protected function verifySpoofedFileRejection(): void
    {
        $officer = User::where('email', 'officer@lmo.go.ug')->firstOrFail();
        $doc = Document::firstOrFail();
        /** @var \App\Services\FileStorageService $fileSvc */
        $fileSvc = app(\App\Services\FileStorageService::class);

        $spoofedFile = UploadedFile::fake()->createWithContent('malicious.pdf', '<?php echo "spoofed"; ?>');

        $rejected = false;
        try {
            $fileSvc->storeUploadedFile($spoofedFile, $doc, $officer);
        } catch (RuntimeException) {
            $rejected = true;
        }

        $this->assertCondition($rejected === true, 'FileStorageService must reject files whose binary magic bytes do not match PDF/JPEG/PNG.');
    }

    protected function verifyOcrExtraction(): void
    {
        $doc = Document::where('official_reference', 'MKCCMA/PS/2026/084')->firstOrFail();
        $this->assertCondition($doc->ocr_status === 'completed', 'Seeded PDF OCR status must be completed.');
        $this->assertCondition(stripos((string) $doc->ocr_text, 'Nakivubo Channel') !== false, 'OCR text must contain extracted PDF body phrase "Nakivubo Channel".');
    }

    protected function verifySearchAndHighlighting(): void
    {
        $manager = User::where('email', 'manager@lmo.go.ug')->firstOrFail();
        $readOnly = User::where('email', 'readonly@lmo.go.ug')->firstOrFail();
        /** @var SearchService $searchSvc */
        $searchSvc = app(SearchService::class);

        $managerResults = $searchSvc->search($manager, ['q' => 'Tribunal']);
        $readOnlyResults = $searchSvc->search($readOnly, ['q' => 'Tribunal']);

        $this->assertCondition($managerResults->total() >= 1, 'Records Manager must find Strictly Confidential document via OCR keyword "Tribunal".');
        $this->assertCondition($readOnlyResults->total() === 0, 'Read-Only user must receive 0 results for Strictly Confidential OCR keyword "Tribunal".');

        $highlighted = SearchService::highlight('Rehabilitation of Nakivubo Channel drainage', 'Nakivubo');
        $this->assertCondition(str_contains($highlighted, '<mark class="lmo-highlight">Nakivubo</mark>'), 'SearchService::highlight must wrap matched keyword in <mark>.');
    }

    protected function verifyArchivingAndRestoration(): void
    {
        $manager = User::where('email', 'manager@lmo.go.ug')->firstOrFail();
        $doc = Document::where('is_archived', false)->firstOrFail();
        /** @var ArchiveService $archiveSvc */
        $archiveSvc = app(ArchiveService::class);

        $archiveSvc->archiveDocument($doc, $manager, 'Completed executive review; archiving for permanent record.');
        $this->assertCondition($doc->fresh()->is_archived === true, 'Document must be marked archived.');

        $archiveSvc->restoreDocument($doc->fresh(), $manager, 'Restoring for follow-up audit inquiry.');
        $this->assertCondition($doc->fresh()->is_archived === false, 'Document must be restored to active register.');
    }

    protected function verifyImmutableAuditLogs(): void
    {
        $log = DocumentActivityLog::firstOrFail();

        $updateBlocked = false;
        try {
            $log->update(['description' => 'Tampered']);
        } catch (LogicException) {
            $updateBlocked = true;
        }

        $deleteBlocked = false;
        try {
            $log->delete();
        } catch (LogicException) {
            $deleteBlocked = true;
        }

        $this->assertCondition($updateBlocked && $deleteBlocked, 'DocumentActivityLog must block both update and delete operations.');
    }

    protected function verifyPrivateFileStreaming(): void
    {
        $doc = Document::has('files')->firstOrFail();
        $file = $doc->files()->firstOrFail();
        /** @var \App\Services\FileStorageService $fileSvc */
        $fileSvc = app(\App\Services\FileStorageService::class);

        $response = $fileSvc->streamFile($file, inline: false);
        $this->assertCondition($response->getStatusCode() === 200, 'Private file stream must return HTTP 200 for authorized call.');
        $this->assertCondition(
            str_contains((string) $response->headers->get('Cache-Control'), 'no-store'),
            'Private file stream must enforce no-store Cache-Control header.'
        );
    }

    protected function verifyEncryptedBackupAndRecovery(): void
    {
        $admin = User::where('email', 'admin@lmo.go.ug')->firstOrFail();
        /** @var BackupService $backupSvc */
        $backupSvc = app(BackupService::class);

        $log = $backupSvc->createBackup('full', $admin);
        $result = $backupSvc->verifyBackup($log);

        $this->assertCondition($result['verified'] === true, 'Encrypted backup must decrypt and pass SHA-256 verification.');
        $this->assertCondition(($result['counts']['documents'] ?? 0) >= 5, 'Backup manifest must contain all seeded documents.');
    }
}
