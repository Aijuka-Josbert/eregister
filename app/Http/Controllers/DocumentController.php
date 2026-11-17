<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDocumentRequest;
use App\Http\Requests\UpdateDocumentRequest;
use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentAssignment;
use App\Models\DocumentCategory;
use App\Models\Office;
use App\Models\User;
use App\Services\AuditService;
use App\Services\DocumentService;
use App\Services\OcrService;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function __construct(
        protected DocumentService $documentService,
        protected SearchService $searchService,
        protected OcrService $ocrService,
        protected AuditService $auditService
    ) {}

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        Gate::authorize('viewAny', Document::class);

        $filters = $request->only([
            'q',
            'category_id',
            'office_id',
            'department_id',
            'document_type',
            'direction',
            'confidentiality_level',
            'status',
            'ocr_status',
            'date_from',
            'date_to',
            'include_archived',
            'sort_by',
            'sort_dir',
        ]);

        $documents = $this->searchService->search($user, $filters, 15);
        $categories = DocumentCategory::where('is_active', true)->orderBy('name')->get();
        $offices = Office::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('documents.index', compact(
            'documents',
            'filters',
            'categories',
            'offices',
            'departments'
        ));
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', Document::class);

        $categories = DocumentCategory::where('is_active', true)->orderBy('name')->get();
        $offices = Office::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $nextInternalPreview = $this->documentService->generateInternalReference($request->user()->office_id);

        return view('documents.create', compact(
            'categories',
            'offices',
            'departments',
            'nextInternalPreview'
        ));
    }

    public function checkDuplicate(Request $request): JsonResponse
    {
        $duplicates = $this->documentService->findPotentialDuplicates(
            $request->input('official_reference'),
            $request->input('organization'),
            $request->input('title')
        );

        return response()->json([
            'has_duplicates' => $duplicates->isNotEmpty(),
            'duplicates' => $duplicates->map(fn (Document $d) => [
                'uuid' => $d->uuid,
                'internal_reference' => $d->internal_reference,
                'official_reference' => $d->official_reference,
                'title' => $d->title,
                'organization' => $d->organization,
                'date_received' => $d->date_received?->format('Y-m-d'),
                'url' => route('documents.show', $d),
            ]),
        ]);
    }

    public function store(StoreDocumentRequest $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $uploadedFiles = $request->file('files', []) ?: [];
        $cameraCaptures = array_filter((array) $request->input('camera_captures', []));

        // Check for duplicate warning if not explicitly confirmed
        $duplicates = $this->documentService->findPotentialDuplicates(
            $request->input('official_reference'),
            $request->input('organization')
        );

        $document = $this->documentService->registerDocument(
            $request->validated(),
            $user,
            $uploadedFiles,
            $cameraCaptures,
            processOcrImmediately: true
        );

        $msg = "Document registered successfully under tracking ID {$document->internal_reference}.";
        if ($duplicates->where('id', '!=', $document->id)->isNotEmpty()) {
            $msg .= ' Note: Another record shares a similar official reference number.';
        }

        return redirect()
            ->route('documents.show', $document)
            ->with('success', $msg);
    }

    public function show(Request $request, Document $document): View
    {
        /** @var User $user */
        $user = $request->user();

        if (! $user->can('view', $document)) {
            $this->auditService->log(
                'security.unauthorized_access_attempt',
                "User {$user->name} ({$user->email}) was denied access to {$document->confidentiality_level} document {$document->internal_reference}.",
                $document,
                [
                    'user_role' => $user->role?->slug,
                    'user_office_id' => $user->office_id,
                    'document_office_id' => $document->office_id,
                    'confidentiality_level' => $document->confidentiality_level,
                ],
                'critical',
                $user
            );

            abort(403, 'Access Denied: You do not have security clearance or office authorization to view this record.');
        }

        // Log viewing of confidential or strictly confidential documents (or all views for full accountability)
        $isSensitive = in_array($document->confidentiality_level, ['confidential', 'strictly_confidential'], true);
        $this->auditService->log(
            $isSensitive ? 'document.viewed_confidential' : 'document.viewed',
            "Viewed document {$document->internal_reference} ({$document->title})",
            $document,
            ['confidentiality_level' => $document->confidentiality_level],
            $isSensitive ? 'warning' : 'info',
            $user
        );

        $document->load([
            'category',
            'office',
            'department',
            'registrar',
            'files.uploader',
            'tags',
            'assignments.assigneeUser',
            'assignments.assigneeDepartment',
            'assignments.assigner',
            'archives.archiver',
            'archives.restorer',
            'activityLogs.user',
        ]);

        $assignableUsers = User::where('is_active', true)
            ->where('office_id', $document->office_id)
            ->orderBy('name')
            ->get();

        $departments = Department::where('office_id', $document->office_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('documents.show', compact('document', 'assignableUsers', 'departments'));
    }

    public function edit(Request $request, Document $document): View
    {
        Gate::authorize('update', $document);

        $document->load(['tags', 'files']);
        $categories = DocumentCategory::where('is_active', true)->orderBy('name')->get();
        $offices = Office::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        return view('documents.edit', compact('document', 'categories', 'offices', 'departments'));
    }

    public function update(UpdateDocumentRequest $request, Document $document): RedirectResponse
    {
        $this->documentService->updateDocument($document, $request->validated(), $request->user());

        return redirect()
            ->route('documents.show', $document)
            ->with('success', "Document {$document->internal_reference} metadata updated and logged.");
    }

    public function retryOcr(Request $request, Document $document): RedirectResponse
    {
        Gate::authorize('retryOcr', $document);

        $this->auditService->log(
            'document.ocr_retry_triggered',
            "Manually triggered OCR reprocessing for {$document->internal_reference}",
            $document,
            [],
            'info',
            $request->user()
        );

        $success = $this->ocrService->processDocument($document);

        return back()->with(
            $success ? 'success' : 'error',
            $success
                ? "OCR text extraction completed for {$document->internal_reference}."
                : "OCR extraction encountered an issue for {$document->internal_reference}. Check OCR status details."
        );
    }

    public function assign(Request $request, Document $document): RedirectResponse
    {
        Gate::authorize('assign', $document);

        $validated = $request->validate([
            'assigned_to_user_id' => ['nullable', 'exists:users,id', 'required_without:assigned_to_department_id'],
            'assigned_to_department_id' => ['nullable', 'exists:departments,id', 'required_without:assigned_to_user_id'],
            'priority' => ['required', 'in:normal,high,urgent'],
            'due_date' => ['nullable', 'date'],
            'instructions' => ['required', 'string', 'max:1000'],
        ]);

        $assignment = DocumentAssignment::create([
            'document_id' => $document->id,
            'assigned_by' => $request->user()->id,
            'assigned_to_user_id' => $validated['assigned_to_user_id'] ?? null,
            'assigned_to_department_id' => $validated['assigned_to_department_id'] ?? null,
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'] ?? null,
            'instructions' => $validated['instructions'],
            'status' => 'pending',
        ]);

        if ($document->status === 'registered') {
            $document->update(['status' => 'in_review']);
        }

        $targetName = $assignment->assigneeUser?->name ?? $assignment->assigneeDepartment?->name ?? 'Staff';

        $this->auditService->log(
            'document.assigned',
            "Assigned document {$document->internal_reference} to {$targetName} ({$assignment->priority} priority)",
            $document,
            [
                'assigned_to_user_id' => $assignment->assigned_to_user_id,
                'assigned_to_department_id' => $assignment->assigned_to_department_id,
                'priority' => $assignment->priority,
            ],
            'info',
            $request->user()
        );

        return back()->with('success', "Document assigned to {$targetName} for action.");
    }
}
