<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\DocumentCategory;
use App\Models\Office;
use App\Models\Permission;
use App\Models\Role;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\ArchiveService;
use App\Services\DocumentService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the LMO Digital Records Management System with realistic fictional institutional data.
     */
    public function run(): void
    {
        // 1. Seed Institutional Offices
        $lmo = Office::updateOrCreate(
            ['code' => 'LMO'],
            [
                'name' => "Lord Mayor's Office",
                'description' => 'Executive Office of the Right Honourable Lord Mayor, City Hall.',
                'location' => 'City Hall, 2nd Floor, Executive Wing',
                'is_active' => true,
            ]
        );

        $dlmo = Office::updateOrCreate(
            ['code' => 'DLMO'],
            [
                'name' => "Deputy Lord Mayor's Office",
                'description' => 'Office of the Deputy Lord Mayor.',
                'location' => 'City Hall, 2nd Floor, East Wing',
                'is_active' => true,
            ]
        );

        $csec = Office::updateOrCreate(
            ['code' => 'CSEC'],
            [
                'name' => 'Council Speaker & Secretariat Office',
                'description' => 'City Council Speaker and Legislative Records Liaison.',
                'location' => 'City Hall, 1st Floor, Council Chambers',
                'is_active' => true,
            ]
        );

        // 2. Seed Internal Departments
        $regDept = Department::updateOrCreate(
            ['office_id' => $lmo->id, 'code' => 'REG'],
            ['name' => 'Central Registry & Records Unit', 'is_active' => true]
        );
        $protDept = Department::updateOrCreate(
            ['office_id' => $lmo->id, 'code' => 'PROT'],
            ['name' => 'Protocol, Invitations & Public Relations', 'is_active' => true]
        );
        $legDept = Department::updateOrCreate(
            ['office_id' => $lmo->id, 'code' => 'LEG'],
            ['name' => 'Legal & Policy Advisory Unit', 'is_active' => true]
        );
        $execDept = Department::updateOrCreate(
            ['office_id' => $lmo->id, 'code' => 'EXEC'],
            ['name' => 'Executive Secretariat & Administration', 'is_active' => true]
        );
        $dlmoRegDept = Department::updateOrCreate(
            ['office_id' => $dlmo->id, 'code' => 'DREG'],
            ['name' => 'Deputy Lord Mayor Registry', 'is_active' => true]
        );

        // 3. Seed Granular Permissions
        $permissionDefinitions = [
            ['slug' => 'documents.view', 'name' => 'View Authorized Documents', 'module' => 'documents'],
            ['slug' => 'documents.create', 'name' => 'Register & Scan New Documents', 'module' => 'documents'],
            ['slug' => 'documents.edit', 'name' => 'Edit Document Metadata', 'module' => 'documents'],
            ['slug' => 'documents.download', 'name' => 'Preview & Download Attached Files', 'module' => 'documents'],
            ['slug' => 'documents.view_confidential', 'name' => 'View Confidential Records', 'module' => 'security'],
            ['slug' => 'documents.view_strictly_confidential', 'name' => 'View Strictly Confidential Records', 'module' => 'security'],
            ['slug' => 'documents.archive', 'name' => 'Archive Documents to Vault', 'module' => 'archives'],
            ['slug' => 'documents.restore', 'name' => 'Restore Archived Documents', 'module' => 'archives'],
            ['slug' => 'documents.assign', 'name' => 'Assign & Route Documents for Action', 'module' => 'workflow'],
            ['slug' => 'documents.approve', 'name' => 'Approve / Endorse Documents', 'module' => 'workflow'],
            ['slug' => 'ocr.retry', 'name' => 'Trigger / Retry OCR Processing', 'module' => 'ocr'],
            ['slug' => 'reports.view', 'name' => 'View Institutional Reports', 'module' => 'reports'],
            ['slug' => 'reports.export', 'name' => 'Export PDF & Excel/CSV Reports', 'module' => 'reports'],
            ['slug' => 'audit.view', 'name' => 'Inspect Immutable Audit Logs', 'module' => 'security'],
            ['slug' => 'users.manage', 'name' => 'Manage Staff Accounts & Roles', 'module' => 'administration'],
            ['slug' => 'settings.manage', 'name' => 'Manage System Settings & Backups', 'module' => 'administration'],
        ];

        $permMap = [];
        foreach ($permissionDefinitions as $p) {
            $perm = Permission::updateOrCreate(['slug' => $p['slug']], $p);
            $permMap[$p['slug']] = $perm->id;
        }

        // 4. Seed Configurable Institutional Roles
        $adminRole = Role::updateOrCreate(
            ['slug' => 'system_admin'],
            ['name' => 'System Administrator', 'description' => 'Full institutional system, user, audit, and backup administration.', 'is_system' => true]
        );
        $adminRole->permissions()->sync(array_values($permMap));

        $recordsManagerRole = Role::updateOrCreate(
            ['slug' => 'records_manager'],
            ['name' => 'Records Manager', 'description' => 'Oversees the Registry, OCR queue, confidential records, digital archiving, and reports.', 'is_system' => true]
        );
        $recordsManagerRole->permissions()->sync([
            $permMap['documents.view'],
            $permMap['documents.create'],
            $permMap['documents.edit'],
            $permMap['documents.download'],
            $permMap['documents.view_confidential'],
            $permMap['documents.view_strictly_confidential'],
            $permMap['documents.archive'],
            $permMap['documents.restore'],
            $permMap['documents.assign'],
            $permMap['ocr.retry'],
            $permMap['reports.view'],
            $permMap['reports.export'],
            $permMap['audit.view'],
        ]);

        $recordsOfficerRole = Role::updateOrCreate(
            ['slug' => 'records_officer'],
            ['name' => 'Records Officer', 'description' => 'Day-to-day scanning, document registration, file upload, and search.', 'is_system' => true]
        );
        $recordsOfficerRole->permissions()->sync([
            $permMap['documents.view'],
            $permMap['documents.create'],
            $permMap['documents.edit'],
            $permMap['documents.download'],
            $permMap['ocr.retry'],
            $permMap['reports.view'],
        ]);

        $officeManagerRole = Role::updateOrCreate(
            ['slug' => 'office_manager'],
            ['name' => 'Office Manager / Executive', 'description' => 'Executive leadership access to all office correspondence, confidential records, routing, and audit trails.', 'is_system' => true]
        );
        $officeManagerRole->permissions()->sync([
            $permMap['documents.view'],
            $permMap['documents.download'],
            $permMap['documents.view_confidential'],
            $permMap['documents.view_strictly_confidential'],
            $permMap['documents.assign'],
            $permMap['documents.approve'],
            $permMap['reports.view'],
            $permMap['reports.export'],
            $permMap['audit.view'],
        ]);

        $readOnlyRole = Role::updateOrCreate(
            ['slug' => 'read_only'],
            ['name' => 'Read-Only User', 'description' => 'Authorized to search and view Public/Internal office records only.', 'is_system' => true]
        );
        $readOnlyRole->permissions()->sync([
            $permMap['documents.view'],
            $permMap['documents.download'],
        ]);

        // 5. Seed Fictional Demonstration Staff Accounts (Password: Lmo@2026!)
        $defaultPassword = Hash::make('Lmo@2026!');

        $adminUser = User::updateOrCreate(
            ['email' => 'admin@lmo.go.ug'],
            [
                'office_id' => $lmo->id,
                'department_id' => $execDept->id,
                'role_id' => $adminRole->id,
                'employee_number' => 'LMO-IT-001',
                'name' => 'Eng. Daniel Kato (System Admin)',
                'title' => 'Principal ICT & Security Officer',
                'phone' => '+256 772 100001',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );

        $managerUser = User::updateOrCreate(
            ['email' => 'manager@lmo.go.ug'],
            [
                'office_id' => $lmo->id,
                'department_id' => $regDept->id,
                'role_id' => $recordsManagerRole->id,
                'employee_number' => 'LMO-REG-002',
                'name' => 'Grace Nabirye (Records Manager)',
                'title' => 'Chief Records Manager',
                'phone' => '+256 772 100002',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );

        $officerUser = User::updateOrCreate(
            ['email' => 'officer@lmo.go.ug'],
            [
                'office_id' => $lmo->id,
                'department_id' => $regDept->id,
                'role_id' => $recordsOfficerRole->id,
                'employee_number' => 'LMO-REG-003',
                'name' => 'Samuel Mugisha (Records Officer)',
                'title' => 'Registry & Scanning Officer',
                'phone' => '+256 772 100003',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );

        $execUser = User::updateOrCreate(
            ['email' => 'executive@lmo.go.ug'],
            [
                'office_id' => $lmo->id,
                'department_id' => $execDept->id,
                'role_id' => $officeManagerRole->id,
                'employee_number' => 'LMO-EXEC-004',
                'name' => 'Patricia Achieng (Office Manager)',
                'title' => 'Principal Private Secretary / Office Manager',
                'phone' => '+256 772 100004',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );

        $readOnlyUser = User::updateOrCreate(
            ['email' => 'readonly@lmo.go.ug'],
            [
                'office_id' => $lmo->id,
                'department_id' => $protDept->id,
                'role_id' => $readOnlyRole->id,
                'employee_number' => 'LMO-PROT-005',
                'name' => 'Ronald Ssemanda (Read-Only Staff)',
                'title' => 'Protocol Assistant',
                'phone' => '+256 772 100005',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );

        $dlmoOfficer = User::updateOrCreate(
            ['email' => 'deputy.records@lmo.go.ug'],
            [
                'office_id' => $dlmo->id,
                'department_id' => $dlmoRegDept->id,
                'role_id' => $recordsOfficerRole->id,
                'employee_number' => 'DLMO-REG-006',
                'name' => 'Brenda Atim (DLMO Records Officer)',
                'title' => 'Deputy Office Records Officer',
                'phone' => '+256 772 100006',
                'password' => $defaultPassword,
                'is_active' => true,
            ]
        );

        // 6. Seed Document Categories (Explicitly including Official Invitations)
        $categories = [
            'invitation' => DocumentCategory::updateOrCreate(
                ['slug' => 'invitation'],
                ['name' => 'Official Invitation', 'description' => 'State functions, diplomatic receptions, civic ceremonies, summits, and stakeholder invitations.', 'color_code' => '#C5A15B', 'retention_years' => 3, 'is_active' => true]
            ),
            'correspondence' => DocumentCategory::updateOrCreate(
                ['slug' => 'inter-ministerial-correspondence'],
                ['name' => 'Inter-Ministerial & Government Correspondence', 'description' => 'Official letters from Ministries, Departments, and Agencies (MDAs).', 'color_code' => '#245B91', 'retention_years' => 10, 'is_active' => true]
            ),
            'legal' => DocumentCategory::updateOrCreate(
                ['slug' => 'legal-and-statutory'],
                ['name' => 'Legal Notice & Statutory Matter', 'description' => 'Legal opinions, court pleadings, contracts, and statutory notices.', 'color_code' => '#C74646', 'retention_years' => 15, 'is_active' => true]
            ),
            'petition' => DocumentCategory::updateOrCreate(
                ['slug' => 'citizen-petition'],
                ['name' => 'Citizen & Community Petition', 'description' => 'Public petitions, market vendor appeals, and community grievances.', 'color_code' => '#21865B', 'retention_years' => 7, 'is_active' => true]
            ),
            'finance' => DocumentCategory::updateOrCreate(
                ['slug' => 'budget-and-finance'],
                ['name' => 'Budget, Audit & Financial Report', 'description' => 'Municipal budget allocations, audit queries, and financial statements.', 'color_code' => '#142B4A', 'retention_years' => 10, 'is_active' => true]
            ),
            'memo' => DocumentCategory::updateOrCreate(
                ['slug' => 'internal-memorandum'],
                ['name' => 'Internal Office Memorandum', 'description' => 'Internal directives, circulars, and administrative memos.', 'color_code' => '#3283B8', 'retention_years' => 5, 'is_active' => true]
            ),
        ];

        // 7. Seed System Settings
        SystemSetting::updateOrCreate(
            ['key' => 'institution_name'],
            ['value' => "Lord Mayor's Office (LMO)", 'type' => 'string', 'group' => 'general', 'description' => 'Primary Institutional Title']
        );

        // 8. Seed Realistic Fictional Sample Documents & Real PDF Attachments with OCR Text
        if (\App\Models\Document::count() === 0) {
            /** @var DocumentService $docService */
            $docService = app(DocumentService::class);
            /** @var ArchiveService $archiveService */
            $archiveService = app(ArchiveService::class);

            $sampleRecords = [
                [
                    'official_reference' => 'MKCCMA/PS/2026/084',
                    'title' => 'Official Invitation to the Greater Kampala Metropolitan Infrastructure & Drainage Summit 2026',
                    'description' => 'Formal invitation addressed to the Right Honourable Lord Mayor to deliver the opening keynote address on urban flood resilience and municipal road upgrading at Serena Conference Centre.',
                    'organization' => 'Ministry of Kampala Capital City and Metropolitan Affairs',
                    'sender' => 'Permanent Secretary, MKCCMA',
                    'recipient' => 'The Right Honourable Lord Mayor',
                    'direction' => 'incoming',
                    'document_type' => 'invitation',
                    'event_date' => now()->addDays(12)->setTime(9, 0)->toDateTimeString(),
                    'date_issued' => now()->subDays(2)->toDateString(),
                    'date_received' => now()->toDateString(),
                    'category_id' => $categories['invitation']->id,
                    'office_id' => $lmo->id,
                    'department_id' => $protDept->id,
                    'confidentiality_level' => 'public',
                    'tags' => 'Invitation, Keynote, Infrastructure Summit, Drainage, MKCCMA',
                    'pdf_body' => 'REPUBLIC OF UGANDA - MINISTRY OF KAMPALA CAPITAL CITY AND METROPOLITAN AFFAIRS. Ref: MKCCMA/PS/2026/084. Subject: Official Invitation to the Greater Kampala Metropolitan Infrastructure and Drainage Summit 2026. The Ministry cordially invites the Right Honourable Lord Mayor to officiate at the opening session concerning Nakivubo Channel rehabilitation and solar street lighting across all five urban divisions.',
                ],
                [
                    'official_reference' => 'KCCA/ED/FIN/2026/319',
                    'title' => 'Submission of Q2 Municipal Road Maintenance & Street Lighting Expenditure Report FY 2026/27',
                    'description' => 'Quarterly financial and engineering progress report covering tarmac restoration, drainage desilting, and solar lighting installations across Central, Kawempe, Makindye, Nakawa, and Rubaga divisions.',
                    'organization' => 'Kampala Capital City Authority — Directorate of Engineering & Technical Services',
                    'sender' => 'Director of Engineering & Technical Services',
                    'recipient' => 'The Right Honourable Lord Mayor',
                    'direction' => 'incoming',
                    'document_type' => 'report',
                    'date_issued' => now()->subDays(4)->toDateString(),
                    'date_received' => now()->subDays(1)->toDateString(),
                    'category_id' => $categories['finance']->id,
                    'office_id' => $lmo->id,
                    'department_id' => $execDept->id,
                    'confidentiality_level' => 'internal',
                    'tags' => 'Engineering, Budget FY2026/27, Roads, Street Lighting',
                    'pdf_body' => 'KAMPALA CAPITAL CITY AUTHORITY. Ref: KCCA/ED/FIN/2026/319. Quarterly Engineering and Financial Report FY 2026/27. Total disbursement for road patching and culvert installation reached UGX 14.8 Billion with 92 percent physical completion verified by municipal engineers.',
                ],
                [
                    'official_reference' => 'LMO/LEG/CONF/2026/017',
                    'title' => 'Confidential Legal Advisory on Municipal Property Valuation & Commercial Lease Renewals',
                    'description' => 'Strictly privileged legal opinion regarding ongoing arbitration and lease renewal terms for prime municipal assets.',
                    'organization' => 'Directorate of Legal Affairs',
                    'sender' => 'Principal Legal Counsel',
                    'recipient' => 'The Right Honourable Lord Mayor',
                    'direction' => 'incoming',
                    'document_type' => 'legal_notice',
                    'date_issued' => now()->subDays(3)->toDateString(),
                    'date_received' => now()->toDateString(),
                    'category_id' => $categories['legal']->id,
                    'office_id' => $lmo->id,
                    'department_id' => $legDept->id,
                    'confidentiality_level' => 'strictly_confidential',
                    'tags' => 'Legal Opinion, Confidential, Property Valuation, Arbitration',
                    'pdf_body' => 'STRICTLY CONFIDENTIAL LEGAL PRIVILEGE. Ref: LMO/LEG/CONF/2026/017. Legal Advisory on Municipal Commercial Lease Renewals and Valuation Tribunal proceedings. Unauthorized disclosure is strictly prohibited.',
                ],
                [
                    'official_reference' => 'KACITA/PET/2026/052',
                    'title' => 'Joint Petition by Downtown Traders & Market Vendors on Trade Order and Loading Zones',
                    'description' => 'Petition signed by representatives of St. Balikuddembe Market and downtown arcade traders requesting a consultative meeting with the Lord Mayor.',
                    'organization' => 'Kampala City Traders Association (KACITA) & United Market Vendors',
                    'sender' => 'Chairperson, KACITA Uganda',
                    'recipient' => 'The Right Honourable Lord Mayor',
                    'direction' => 'incoming',
                    'document_type' => 'petition',
                    'date_issued' => now()->subDays(6)->toDateString(),
                    'date_received' => now()->subDays(5)->toDateString(),
                    'category_id' => $categories['petition']->id,
                    'office_id' => $lmo->id,
                    'department_id' => $regDept->id,
                    'confidentiality_level' => 'public',
                    'tags' => 'Petition, Market Vendors, KACITA, Trade Order',
                    'pdf_body' => 'PETITION TO THE LORD MAYOR. Ref: KACITA/PET/2026/052. Downtown traders and market vendors respectfully request a stakeholder dialogue on designated delivery hours and sanitation fees.',
                ],
                [
                    'official_reference' => 'EACF/INV/2026/112',
                    'title' => 'Diplomatic Invitation: East African Mayors Forum on Climate Finance & Green Cities',
                    'description' => 'Invitation for the Lord Mayor and delegation to attend the regional mayors roundtable on urban greening and solid waste recycling.',
                    'organization' => 'East African Local Governments Forum Secretariat',
                    'sender' => 'Secretary General, EALGF',
                    'recipient' => 'The Right Honourable Lord Mayor',
                    'direction' => 'incoming',
                    'document_type' => 'invitation',
                    'event_date' => now()->addDays(25)->setTime(10, 0)->toDateTimeString(),
                    'date_issued' => now()->subDays(15)->toDateString(),
                    'date_received' => now()->subDays(10)->toDateString(),
                    'category_id' => $categories['invitation']->id,
                    'office_id' => $lmo->id,
                    'department_id' => $protDept->id,
                    'confidentiality_level' => 'internal',
                    'tags' => 'Invitation, Diplomatic, Climate Finance, Mayors Forum',
                    'pdf_body' => 'EAST AFRICAN LOCAL GOVERNMENTS FORUM. Ref: EACF/INV/2026/112. Official Invitation to the East African Mayors Roundtable on Climate Finance and Solid Waste Management.',
                    'archive_after_seed' => true,
                ],
            ];

            foreach ($sampleRecords as $idx => $rec) {
                $pdfContent = $this->generateValidSamplePdf($rec['title'], $rec['official_reference'], $rec['pdf_body']);
                $shouldArchive = $rec['archive_after_seed'] ?? false;
                unset($rec['pdf_body'], $rec['archive_after_seed']);

                $doc = $docService->registerDocument($rec, $officerUser, [], [], processOcrImmediately: false);

                app(\App\Services\FileStorageService::class)->storeRawContent(
                    $pdfContent,
                    str_replace('/', '_', $rec['official_reference']).'.pdf',
                    'application/pdf',
                    $doc,
                    $officerUser,
                    1,
                    'upload'
                );

                $doc->update(['file_count' => 1]);
                app(\App\Services\OcrService::class)->processDocument($doc);

                if ($shouldArchive) {
                    $archiveService->archiveDocument(
                        $doc,
                        $managerUser,
                        'Delegation attendance confirmed and protocol arrangements concluded; archived for historical reference.',
                        'LMO-VAULT-2026'
                    );
                }
            }
        }
    }

    /**
     * Generate a valid, standards-compliant PDF binary containing searchable text for `pdftotext` & browser preview.
     */
    protected function generateValidSamplePdf(string $title, string $ref, string $body): string
    {
        $cleanTitle = preg_replace('/[^A-Za-z0-9 \-.,:\/]/', '', $title);
        $cleanRef = preg_replace('/[^A-Za-z0-9 \-.,:\/]/', '', $ref);
        $cleanBody = preg_replace('/[^A-Za-z0-9 \-.,:\/]/', '', $body);

        $lines = [
            "LORD MAYOR'S OFFICE - OFFICIAL DIGITAL RECORD",
            "Official Reference: {$cleanRef}",
            'Subject: '.substr($cleanTitle, 0, 85),
            '------------------------------------------------------------------------',
        ];

        foreach (str_split($cleanBody, 80) as $chunk) {
            $lines[] = $chunk;
        }

        $streamLines = "BT\n/F1 11 Tf\n50 750 Td\n16 TL\n";
        foreach ($lines as $line) {
            $escaped = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $line);
            $streamLines .= "({$escaped}) Tj T*\n";
        }
        $streamLines .= "ET\n";
        $streamLen = strlen($streamLines);

        return "%PDF-1.4\n"
            ."1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n"
            ."2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n"
            ."3 0 obj\n<< /Type /Page /Parent 2 0 R /Resources << /Font << /F1 4 0 R >> >> /MediaBox [0 0 612 792] /Contents 5 0 R >>\nendobj\n"
            ."4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n"
            ."5 0 obj\n<< /Length {$streamLen} >>\nstream\n{$streamLines}endstream\nendobj\n"
            ."xref\n0 6\n0000000000 65535 f \n"
            ."trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n0\n%%EOF";
    }
}
