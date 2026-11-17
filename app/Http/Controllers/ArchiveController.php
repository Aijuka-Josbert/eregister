<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArchiveDocumentRequest;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Office;
use App\Models\User;
use App\Services\ArchiveService;
use App\Services\SearchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ArchiveController extends Controller
{
    public function __construct(
        protected ArchiveService $archiveService,
        protected SearchService $searchService
    ) {}

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        Gate::authorize('viewAny', Document::class);

        $filters = array_merge($request->only([
            'q',
            'category_id',
            'office_id',
            'document_type',
            'confidentiality_level',
            'date_from',
            'date_to',
        ]), ['only_archived' => true]);

        $documents = $this->searchService->search($user, $filters, 15);
        $categories = DocumentCategory::where('is_active', true)->orderBy('name')->get();
        $offices = Office::where('is_active', true)->orderBy('name')->get();

        return view('archives.index', compact('documents', 'filters', 'categories', 'offices'));
    }

    public function store(ArchiveDocumentRequest $request, Document $document): RedirectResponse
    {
        $validated = $request->validated();

        $this->archiveService->archiveDocument(
            $document,
            $request->user(),
            $validated['archive_reason'],
            $validated['archive_location_code'] ?? null,
            $validated['retention_until'] ?? null
        );

        return redirect()
            ->route('documents.show', $document)
            ->with('success', "Document {$document->internal_reference} has been placed in the Digital Archive Vault.");
    }

    public function restore(Request $request, Document $document): RedirectResponse
    {
        Gate::authorize('restore', $document);

        $validated = $request->validate([
            'restore_reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $this->archiveService->restoreDocument(
            $document,
            $request->user(),
            $validated['restore_reason']
        );

        return redirect()
            ->route('documents.show', $document)
            ->with('success', "Document {$document->internal_reference} has been restored to the Active Register.");
    }
}
