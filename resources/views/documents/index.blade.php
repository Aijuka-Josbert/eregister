@extends('layouts.app')

@section('title', 'Document Register & Search — LMO Digital Records Management System')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h1 class="h4 fw-bold mb-1" style="color: var(--lmo-deep-navy);">
            <i class="bi bi-folder2-open me-1"></i> Official Document Register &amp; Full-Text Search
        </h1>
        <p class="text-muted small mb-0">
            Search across internal tracking IDs, official reference numbers, subjects, senders, organizations, tags, and OCR-extracted document text.
        </p>
    </div>
    <div class="d-flex gap-2">
        @can('create', \App\Models\Document::class)
            <a href="{{ route('documents.create') }}" class="btn btn-lmo-primary">
                <i class="bi bi-plus-lg me-1"></i> Register Document
            </a>
        @endcan
        @if(auth()->user()->hasPermission('reports.export'))
            <a href="{{ route('reports.export.csv', request()->query()) }}" class="btn btn-outline-secondary">
                <i class="bi bi-download me-1"></i> Export Filtered CSV
            </a>
        @endif
    </div>
</div>

<!-- Advanced Search & Filter Panel -->
<div class="lmo-card mb-4">
    <div class="p-3">
        <form action="{{ route('documents.index') }}" method="GET">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold mb-1">Keyword / Reference / OCR Content</label>
                    <input
                        type="search"
                        name="q"
                        value="{{ $filters['q'] ?? '' }}"
                        class="form-control form-control-sm"
                        placeholder="Search words inside documents, ref numbers, sender..."
                    >
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Category</label>
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (int) ($filters['category_id'] ?? 0) === $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Document Type</label>
                    <select name="document_type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        @foreach(['letter' => 'Official Letter', 'invitation' => 'Invitation', 'memo' => 'Internal Memo', 'report' => 'Report', 'legal_notice' => 'Legal Notice', 'petition' => 'Petition', 'circular' => 'Circular'] as $val => $label)
                            <option value="{{ $val }}" {{ ($filters['document_type'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Received From</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control form-control-sm">
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Received To</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control form-control-sm">
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Office</label>
                    <select name="office_id" class="form-select form-select-sm">
                        <option value="">All Authorized Offices</option>
                        @foreach($offices as $off)
                            <option value="{{ $off->id }}" {{ (int) ($filters['office_id'] ?? 0) === $off->id ? 'selected' : '' }}>
                                {{ $off->code }} — {{ $off->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Confidentiality</label>
                    <select name="confidentiality_level" class="form-select form-select-sm">
                        <option value="">All Permitted</option>
                        <option value="public" {{ ($filters['confidentiality_level'] ?? '') === 'public' ? 'selected' : '' }}>Public</option>
                        <option value="internal" {{ ($filters['confidentiality_level'] ?? '') === 'internal' ? 'selected' : '' }}>Internal</option>
                        <option value="confidential" {{ ($filters['confidentiality_level'] ?? '') === 'confidential' ? 'selected' : '' }}>Confidential</option>
                        <option value="strictly_confidential" {{ ($filters['confidentiality_level'] ?? '') === 'strictly_confidential' ? 'selected' : '' }}>Strictly Confidential</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">OCR Status</label>
                    <select name="ocr_status" class="form-select form-select-sm">
                        <option value="">Any OCR Status</option>
                        <option value="completed" {{ ($filters['ocr_status'] ?? '') === 'completed' ? 'selected' : '' }}>OCR Indexed</option>
                        <option value="pending" {{ ($filters['ocr_status'] ?? '') === 'pending' ? 'selected' : '' }}>OCR Pending</option>
                        <option value="failed" {{ ($filters['ocr_status'] ?? '') === 'failed' ? 'selected' : '' }}>OCR Failed</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold mb-1">Sort By</label>
                    <select name="sort_by" class="form-select form-select-sm">
                        <option value="date_received" {{ ($filters['sort_by'] ?? 'date_received') === 'date_received' ? 'selected' : '' }}>Date Received (Newest)</option>
                        <option value="registered_at" {{ ($filters['sort_by'] ?? '') === 'registered_at' ? 'selected' : '' }}>Registration Time</option>
                        <option value="title" {{ ($filters['sort_by'] ?? '') === 'title' ? 'selected' : '' }}>Subject / Title</option>
                        <option value="internal_reference" {{ ($filters['sort_by'] ?? '') === 'internal_reference' ? 'selected' : '' }}>Internal ID</option>
                    </select>
                </div>

                <div class="col-12 col-md-4 d-flex gap-2 justify-content-end">
                    <button type="submit" class="btn btn-sm btn-lmo-primary px-3">
                        <i class="bi bi-funnel me-1"></i> Apply Filters
                    </button>
                    <a href="{{ route('documents.index') }}" class="btn btn-sm btn-outline-secondary">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Results Table -->
<div class="lmo-card">
    <div class="lmo-card-header d-flex justify-content-between align-items-center">
        <span>
            Showing <strong>{{ $documents->total() }}</strong> authorized record(s)
            @if(!empty($filters['q']))
                matching query <code>"{{ $filters['q'] }}"</code>
            @endif
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover table-lmo mb-0">
            <thead>
                <tr>
                    <th>Internal ID</th>
                    <th>Official Ref No.</th>
                    <th>Subject, Organization &amp; OCR Snippet</th>
                    <th>Category &amp; Type</th>
                    <th>Office / Dept</th>
                    <th>Security</th>
                    <th>OCR</th>
                    <th>Date Received</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $doc)
                    <tr>
                        <td>
                            <a href="{{ route('documents.show', $doc) }}" class="text-decoration-none">
                                <span class="lmo-ref-badge">{{ $doc->internal_reference }}</span>
                            </a>
                        </td>
                        <td>
                            @if($doc->official_reference)
                                <span class="lmo-official-ref">{!! \App\Services\SearchService::highlight($doc->official_reference, $filters['q'] ?? null, 50) !!}</span>
                            @else
                                <span class="text-muted small">Unnumbered</span>
                            @endif
                        </td>
                        <td style="max-width: 380px;">
                            <a href="{{ route('documents.show', $doc) }}" class="fw-semibold text-dark text-decoration-none d-block">
                                {!! \App\Services\SearchService::highlight($doc->title, $filters['q'] ?? null, 95) !!}
                            </a>
                            <div class="small text-muted">
                                <i class="bi bi-building me-1"></i>{!! \App\Services\SearchService::highlight($doc->organization, $filters['q'] ?? null, 60) !!}
                                @if($doc->sender)
                                    &bull; From: {!! \App\Services\SearchService::highlight($doc->sender, $filters['q'] ?? null, 45) !!}
                                @endif
                            </div>

                            @if(!empty($filters['q']) && $doc->ocr_text && stripos($doc->ocr_text, $filters['q']) !== false)
                                <div class="mt-1 p-1 px-2 rounded bg-light border small text-muted" style="font-size: 0.76rem;">
                                    <i class="bi bi-file-earmark-text text-primary me-1"></i><strong>OCR Match:</strong>
                                    {!! \App\Services\SearchService::highlight($doc->ocr_text, $filters['q'], 150) !!}
                                </div>
                            @endif

                            @if($doc->tags->isNotEmpty())
                                <div class="mt-1 d-flex flex-wrap gap-1">
                                    @foreach($doc->tags as $tag)
                                        <span class="badge bg-light text-secondary border" style="font-size: 0.68rem;">#{{ $tag->name }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="badge rounded-pill d-inline-block mb-1" style="background-color: {{ $doc->category?->color_code ?? '#245B91' }};">
                                {{ $doc->category?->name }}
                            </span>
                            <div class="small text-muted text-capitalize">{{ str_replace('_', ' ', $doc->document_type) }}</div>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $doc->office?->code }}</span>
                            <div class="small text-muted">{{ $doc->department?->code ?? 'Registry' }}</div>
                        </td>
                        <td>
                            <x-confidentiality-badge :level="$doc->confidentiality_level" />
                        </td>
                        <td>
                            <x-ocr-badge :status="$doc->ocr_status" />
                            @if($doc->file_count > 0)
                                <div class="small text-muted mt-1" style="font-size: 0.7rem;">
                                    <i class="bi bi-paperclip"></i> {{ $doc->file_count }} file(s)
                                </div>
                            @endif
                        </td>
                        <td class="small text-muted">
                            <div class="fw-medium text-dark">{{ $doc->date_received?->format('d M Y') }}</div>
                            @if($doc->date_issued)
                                <div style="font-size: 0.72rem;">Issued: {{ $doc->date_issued->format('d M Y') }}</div>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('documents.show', $doc) }}" class="btn btn-sm btn-outline-primary">
                                Open
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-search fs-3 d-block mb-2"></i>
                            No matching documents found within your authorized office and security clearance.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($documents->hasPages())
        <div class="p-3 border-top">
            {{ $documents->links() }}
        </div>
    @endif
</div>
@endsection
