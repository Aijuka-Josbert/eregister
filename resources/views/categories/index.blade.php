@extends('layouts.app')

@section('title', 'Categories & Retention Policies — LMO DRMS')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 fw-bold mb-1" style="color: var(--lmo-deep-navy);">
            <i class="bi bi-tags me-1"></i> Document Categories &amp; Retention Rules
        </h1>
        <p class="text-muted small mb-0">
            Configure institutional filing classifications, including Official Invitations, Correspondence, Legal Notices, and Retention Periods.
        </p>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-lg-8">
        <div class="lmo-card">
            <div class="lmo-card-header">Configured Categories ({{ $categories->count() }})</div>
            <div class="table-responsive">
                <table class="table table-hover table-lmo mb-0">
                    <thead>
                        <tr>
                            <th>Category Badge</th>
                            <th>Slug</th>
                            <th>Filing Guidelines</th>
                            <th>Retention Period</th>
                            <th>Registered Docs</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $cat)
                            <tr>
                                <td>
                                    <span class="badge rounded-pill px-3 py-2" style="background-color: {{ $cat->color_code }};">
                                        {{ $cat->name }}
                                    </span>
                                </td>
                                <td><code>{{ $cat->slug }}</code></td>
                                <td class="small text-muted">{{ $cat->description ?? 'Standard institutional filing category' }}</td>
                                <td class="small fw-semibold">{{ $cat->retention_years }} Years</td>
                                <td>
                                    <a href="{{ route('documents.index', ['category_id' => $cat->id]) }}" class="badge bg-light text-dark border text-decoration-none">
                                        {{ $cat->documents_count }} records &rarr;
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if(auth()->user()->hasRole(['system_admin', 'records_manager']))
    <div class="col-12 col-lg-4">
        <div class="lmo-card">
            <div class="lmo-card-header">Add New Category</div>
            <div class="p-3">
                <form action="{{ route('categories.store') }}" method="POST" class="small">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Name *</label>
                        <input type="text" name="name" class="form-control form-control-sm" placeholder="e.g. Diplomatic Protocol" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold">Badge Color *</label>
                            <input type="color" name="color_code" value="#245B91" class="form-control form-control-sm form-control-color w-100">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold">Retention (Years) *</label>
                            <input type="number" name="retention_years" value="5" min="1" max="50" class="form-control form-control-sm" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Filing Description</label>
                        <textarea name="description" rows="2" class="form-control form-control-sm"></textarea>
                    </div>
                    <button type="submit" class="btn btn-sm btn-lmo-primary w-100">Create Category</button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
