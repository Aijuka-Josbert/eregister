@extends('layouts.app')

@section('title', 'Digital Archive Vault — LMO Digital Records Management System')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h1 class="h4 fw-bold mb-1" style="color: var(--lmo-deep-navy);">
            <i class="bi bi-archive me-1"></i> Digital Archive Vault
        </h1>
        <p class="text-muted small mb-0">
            Non-destructive historical vault. Archived records retain 100% of their original files, OCR searchability, and audit logs.
        </p>
    </div>
    <a href="{{ route('documents.index') }}" class="btn btn-sm btn-outline-primary">
        <i class="bi bi-folder2-open me-1"></i> View Active Register
    </a>
</div>

<div class="lmo-card mb-4 p-3">
    <form action="{{ route('archives.index') }}" method="GET">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-5">
                <label class="form-label small fw-semibold mb-1">Search Archived Records &amp; OCR Text</label>
                <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control form-control-sm" placeholder="Search archive by ref number, title, sender, or OCR content...">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold mb-1">Category</label>
                <select name="category_id" class="form-select form-select-sm">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (int) ($filters['category_id'] ?? 0) === $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Office</label>
                <select name="office_id" class="form-select form-select-sm">
                    <option value="">All Offices</option>
                    @foreach($offices as $off)
                        <option value="{{ $off->id }}" {{ (int) ($filters['office_id'] ?? 0) === $off->id ? 'selected' : '' }}>{{ $off->code }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-lmo-primary w-100">Search Vault</button>
                <a href="{{ route('archives.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </div>
    </form>
</div>

<div class="lmo-card">
    <div class="lmo-card-header">
        Archived Records ({{ $documents->total() }})
    </div>
    <div class="table-responsive">
        <table class="table table-hover table-lmo mb-0">
            <thead>
                <tr>
                    <th>Internal ID</th>
                    <th>Official Ref</th>
                    <th>Subject &amp; Organization</th>
                    <th>Category</th>
                    <th>Security</th>
                    <th>Archived At</th>
                    <th class="text-end">Actions</th>
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
                            <span class="lmo-official-ref">{{ $doc->official_reference ?? 'Unnumbered' }}</span>
                        </td>
                        <td>
                            <a href="{{ route('documents.show', $doc) }}" class="fw-semibold text-dark text-decoration-none d-block">
                                {{ $doc->title }}
                            </a>
                            <span class="small text-muted">{{ $doc->organization }}</span>
                        </td>
                        <td>
                            <span class="badge rounded-pill" style="background-color: {{ $doc->category?->color_code ?? '#245B91' }};">
                                {{ $doc->category?->name }}
                            </span>
                        </td>
                        <td>
                            <x-confidentiality-badge :level="$doc->confidentiality_level" />
                        </td>
                        <td class="small text-muted">
                            {{ $doc->archived_at?->format('d M Y H:i') }}
                        </td>
                        <td class="text-end">
                            <a href="{{ route('documents.show', $doc) }}" class="btn btn-sm btn-outline-primary">
                                Inspect / Restore
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-archive fs-3 d-block mb-2"></i>
                            No records currently in the Digital Archive Vault matching your criteria.
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
