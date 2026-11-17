<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentActivityLog;
use App\Models\DocumentCategory;
use App\Models\Office;
use App\Models\User;
use App\Services\BackupService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use LogicException;
use Tests\TestCase;

class DrmsEndToEndTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (User::count() === 0) {
            $this->seed(DatabaseSeeder::class);
        }
    }

    public function test_login_page_loads_and_staff_can_authenticate(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Sign In to LMO DRMS');

        $loginResponse = $this->post('/login', [
            'email' => 'officer@lmo.go.ug',
            'password' => 'Lmo@2026!',
        ]);

        $loginResponse->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_deactivated_user_is_blocked_from_logging_in_and_logged_in_audit(): void
    {
        $user = User::where('email', 'readonly@lmo.go.ug')->firstOrFail();
        $user->update(['is_active' => false]);

        $response = $this->post('/login', [
            'email' => 'readonly@lmo.go.ug',
            'password' => 'Lmo@2026!',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->assertDatabaseHas('document_activity_logs', [
            'user_id' => $user->id,
            'action' => 'auth.deactivated_login_attempt',
            'severity' => 'critical',
        ]);
    }

    public function test_records_officer_can_register_document_with_pdf_and_ocr_extracts_text(): void
    {
        $officer = User::where('email', 'officer@lmo.go.ug')->firstOrFail();
        $category = DocumentCategory::where('slug', 'invitation')->firstOrFail();
        $office = Office::where('code', 'LMO')->firstOrFail();

        $pdfBytes = "%PDF-1.4\n1 0 obj<< /Type /Catalog /Pages 2 0 R >>endobj\n"
            ."2 0 obj<< /Type /Pages /Kids [3 0 R] /Count 1 >>endobj\n"
            ."3 0 obj<< /Type /Page /Parent 2 0 R /Resources<< /Font<< /F1 4 0 R >> >> /MediaBox [0 0 612 792] /Contents 5 0 R >>endobj\n"
            ."4 0 obj<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>endobj\n"
            ."5 0 obj<< /Length 98 >>stream\nBT /F1 12 Tf 50 700 Td (Official Invitation to Kampala Smart Mobility Conference 2026) Tj ET\nendstream\nendobj\n"
            ."xref\n0 6\n0000000000 65535 f \ntrailer<< /Size 6 /Root 1 0 R >>\nstartxref\n0\n%%EOF";

        $uploadedPdf = UploadedFile::fake()->createWithContent('Smart_Mobility_Invitation.pdf', $pdfBytes);

        $response = $this->actingAs($officer)->post('/documents', [
            'official_reference' => 'MoWT/INV/2026/771',
            'title' => 'Invitation to Kampala Smart Mobility Conference',
            'description' => 'Keynote invitation from Ministry of Works and Transport.',
            'organization' => 'Ministry of Works and Transport',
            'sender' => 'Hon. Minister of Works',
            'recipient' => 'The Right Honourable Lord Mayor',
            'direction' => 'incoming',
            'document_type' => 'invitation',
            'date_issued' => now()->subDay()->toDateString(),
            'date_received' => now()->toDateString(),
            'category_id' => $category->id,
            'office_id' => $office->id,
            'confidentiality_level' => 'public',
            'tags' => 'Invitation, Smart Mobility, Transport',
            'files' => [$uploadedPdf],
        ]);

        $response->assertRedirect();

        $doc = Document::where('official_reference', 'MoWT/INV/2026/771')->firstOrFail();
        $this->assertSame('completed', $doc->ocr_status);
        $this->assertStringContainsString('Smart Mobility Conference', (string) $doc->ocr_text);
        $this->assertSame(1, $doc->file_count);
    }

    public function test_unauthorized_user_cannot_view_strictly_confidential_or_other_office_records(): void
    {
        $readOnlyUser = User::where('email', 'readonly@lmo.go.ug')->firstOrFail();
        $dlmoOfficer = User::where('email', 'deputy.records@lmo.go.ug')->firstOrFail();

        $confidentialDoc = Document::where('confidentiality_level', 'strictly_confidential')->firstOrFail();

        // Read-Only user in LMO should receive 403 on Strictly Confidential document
        $res1 = $this->actingAs($readOnlyUser)->get(route('documents.show', $confidentialDoc));
        $res1->assertStatus(403);

        // Audit log must record the unauthorized access attempt
        $this->assertDatabaseHas('document_activity_logs', [
            'document_id' => $confidentialDoc->id,
            'user_id' => $readOnlyUser->id,
            'action' => 'security.unauthorized_access_attempt',
            'severity' => 'critical',
        ]);

        // Officer from DLMO should receive 403 on LMO internal document (office separation)
        $lmoInternalDoc = Document::where('office_id', $readOnlyUser->office_id)
            ->where('confidentiality_level', 'internal')
            ->firstOrFail();

        $res2 = $this->actingAs($dlmoOfficer)->get(route('documents.show', $lmoInternalDoc));
        $res2->assertStatus(403);
    }

    public function test_search_retrieves_documents_by_ocr_text_and_respects_permissions(): void
    {
        $manager = User::where('email', 'manager@lmo.go.ug')->firstOrFail();
        $readOnly = User::where('email', 'readonly@lmo.go.ug')->firstOrFail();

        // Search word inside OCR body ('Nakivubo Channel')
        $res = $this->actingAs($manager)->get('/documents?q=Nakivubo');
        $res->assertStatus(200);
        $res->assertSee('MKCCMA/PS/2026/084');

        // Search word inside Strictly Confidential OCR body ('Valuation Tribunal')
        $resManager = $this->actingAs($manager)->get('/documents?q=Tribunal');
        $resManager->assertStatus(200);
        $resManager->assertSee('LMO/LEG/CONF/2026/017');

        // Read-only user searching the same word must NEVER see the strictly confidential record
        $resReadOnly = $this->actingAs($readOnly)->get('/documents?q=Tribunal');
        $resReadOnly->assertStatus(200);
        $resReadOnly->assertDontSee('LMO/LEG/CONF/2026/017');
    }

    public function test_records_manager_can_archive_and_restore_document_with_full_history(): void
    {
        $manager = User::where('email', 'manager@lmo.go.ug')->firstOrFail();
        $doc = Document::where('is_archived', false)->firstOrFail();

        $archiveRes = $this->actingAs($manager)->post(route('documents.archive', $doc), [
            'archive_reason' => 'Matter concluded; moving to FY2026 Digital Archive Vault.',
            'archive_location_code' => 'LMO-VAULT-TEST',
        ]);
        $archiveRes->assertRedirect();

        $doc->refresh();
        $this->assertTrue($doc->is_archived);
        $this->assertSame('archived', $doc->status);

        $restoreRes = $this->actingAs($manager)->post(route('documents.restore', $doc), [
            'restore_reason' => 'Reopened for follow-up executive review.',
        ]);
        $restoreRes->assertRedirect();

        $doc->refresh();
        $this->assertFalse($doc->is_archived);
        $this->assertSame('registered', $doc->status);
    }

    public function test_audit_log_is_strictly_append_only_and_blocks_modification_or_deletion(): void
    {
        $log = DocumentActivityLog::firstOrFail();

        $this->expectException(LogicException::class);
        $log->update(['description' => 'Tampered description']);
    }

    public function test_encrypted_backup_service_creates_and_verifies_snapshot(): void
    {
        $admin = User::where('email', 'admin@lmo.go.ug')->firstOrFail();
        /** @var BackupService $backupSvc */
        $backupSvc = app(BackupService::class);

        $log = $backupSvc->createBackup('full', $admin);
        $this->assertSame('completed', $log->status);

        $verification = $backupSvc->verifyBackup($log);
        $this->assertTrue($verification['verified']);
        $this->assertTrue($verification['sha256_match']);
    }
}
