<?php

namespace App\Http\Controllers;

use App\Models\BackupLog;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Office;
use App\Models\User;
use App\Services\AuditService;
use App\Services\BackupService;
use App\Services\SearchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected SearchService $searchService,
        protected BackupService $backupService,
        protected AuditService $auditService
    ) {}

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        if (! $user->hasPermission('reports.view')) {
            abort(403, 'You do not have permission to view institutional reports.');
        }

        $filters = $request->only([
            'q',
            'category_id',
            'office_id',
            'document_type',
            'direction',
            'confidentiality_level',
            'date_from',
            'date_to',
            'include_archived',
        ]);

        $documents = $this->searchService->search($user, $filters, 50);
        $categories = DocumentCategory::where('is_active', true)->orderBy('name')->get();
        $offices = Office::where('is_active', true)->orderBy('name')->get();
        $backups = BackupLog::with('triggerUser')->latest()->limit(10)->get();

        return view('reports.index', compact('documents', 'filters', 'categories', 'offices', 'backups'));
    }

    /**
     * Export filtered register to Excel-compatible CSV.
     */
    public function exportCsv(Request $request): StreamedResponse
    {
        /** @var User $user */
        $user = $request->user();
        if (! $user->hasPermission('reports.export')) {
            abort(403, 'You do not have permission to export institutional reports.');
        }

        $filters = $request->only([
            'q',
            'category_id',
            'office_id',
            'document_type',
            'direction',
            'confidentiality_level',
            'date_from',
            'date_to',
            'include_archived',
        ]);

        $paginator = $this->searchService->search($user, $filters, 1000);
        $items = $paginator->items();

        $this->auditService->log(
            'report.exported_csv',
            'Exported Document Register to CSV/Excel ('.count($items).' records)',
            null,
            ['filters' => $filters, 'record_count' => count($items)],
            'info',
            $user
        );

        $filename = 'LMO_Document_Register_'.now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($items) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM for Microsoft Excel compatibility
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'Internal Reference',
                'Official Reference No.',
                'Title / Subject',
                'Organization',
                'Sender',
                'Recipient',
                'Direction',
                'Document Type',
                'Category',
                'Office',
                'Department',
                'Confidentiality',
                'Date Issued',
                'Date Received',
                'Status',
                'OCR Status',
                'Registered By',
                'Registered At',
            ]);

            /** @var Document $doc */
            foreach ($items as $doc) {
                fputcsv($out, [
                    $doc->internal_reference,
                    $doc->official_reference ?? 'Unnumbered',
                    $doc->title,
                    $doc->organization,
                    $doc->sender ?? '',
                    $doc->recipient ?? '',
                    strtoupper($doc->direction),
                    ucfirst(str_replace('_', ' ', $doc->document_type)),
                    $doc->category?->name ?? '',
                    $doc->office?->code ?? '',
                    $doc->department?->name ?? '',
                    strtoupper(str_replace('_', ' ', $doc->confidentiality_level)),
                    $doc->date_issued?->format('Y-m-d') ?? '',
                    $doc->date_received?->format('Y-m-d') ?? '',
                    strtoupper($doc->status),
                    strtoupper($doc->ocr_status),
                    $doc->registrar?->name ?? '',
                    $doc->registered_at?->format('Y-m-d H:i') ?? '',
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Render printable / PDF-ready official register report.
     */
    public function exportPrintPdf(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        if (! $user->hasPermission('reports.export')) {
            abort(403, 'You do not have permission to export institutional reports.');
        }

        $filters = $request->only([
            'q',
            'category_id',
            'office_id',
            'document_type',
            'direction',
            'confidentiality_level',
            'date_from',
            'date_to',
            'include_archived',
        ]);

        $documents = $this->searchService->search($user, $filters, 500);

        $this->auditService->log(
            'report.exported_pdf',
            'Generated Official Printable / PDF Document Register Report ('.$documents->count().' records)',
            null,
            ['filters' => $filters, 'record_count' => $documents->count()],
            'info',
            $user
        );

        return view('reports.print', compact('documents', 'filters', 'user'));
    }

    /**
     * Trigger an immediate encrypted backup snapshot from the Admin UI.
     */
    public function runBackup(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        if (! $user->isAdmin()) {
            abort(403);
        }

        $log = $this->backupService->createBackup('full', $user);
        $verification = $this->backupService->verifyBackup($log);

        return back()->with(
            $verification['verified'] ? 'success' : 'error',
            $verification['verified']
                ? "Encrypted backup {$log->backup_reference} created and verified successfully (SHA-256 matched)."
                : "Backup {$log->backup_reference} created, but verification reported: ".($verification['error'] ?? 'Unknown error')
        );
    }
}
