@extends('layouts.app')

@section('title', 'Edit Metadata — ' . $document->internal_reference)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h4 fw-bold mb-1" style="color: var(--lmo-deep-navy);">
            <i class="bi bi-pencil-square me-1"></i> Edit Document Metadata ({{ $document->internal_reference }})
        </h1>
        <p class="text-muted small mb-0">
            All metadata revisions are automatically logged with before-and-after values in the immutable audit trail.
        </p>
    </div>
    <a href="{{ route('documents.show', $document) }}" class="btn btn-sm btn-outline-secondary">&larr; Back to Document</a>
</div>

<form action="{{ route('documents.update', $document) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="lmo-card p-3 p-md-4">
        <div class="row g-3">
            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold">Internal Tracking ID (Immutable)</label>
                <input type="text" class="form-control font-monospace bg-light" value="{{ $document->internal_reference }}" disabled>
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold">Official Reference Number</label>
                <input type="text" name="official_reference" value="{{ old('official_reference', $document->official_reference) }}" class="form-control font-monospace">
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold">Workflow Status *</label>
                <select name="status" class="form-select" required>
                    @foreach(['draft' => 'Draft', 'registered' => 'Registered', 'in_review' => 'In Review / Action', 'actioned' => 'Actioned / Completed'] as $st => $lbl)
                        <option value="{{ $st }}" {{ old('status', $document->status) === $st ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12">
                <label class="form-label small fw-semibold">Subject / Title *</label>
                <input type="text" name="title" value="{{ old('title', $document->title) }}" class="form-control" required>
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold">Originating Organization *</label>
                <input type="text" name="organization" value="{{ old('organization', $document->organization) }}" class="form-control" required>
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold">Sender</label>
                <input type="text" name="sender" value="{{ old('sender', $document->sender) }}" class="form-control">
            </div>

            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold">Recipient</label>
                <input type="text" name="recipient" value="{{ old('recipient', $document->recipient) }}" class="form-control">
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold">Direction *</label>
                <select name="direction" class="form-select" required>
                    <option value="incoming" {{ old('direction', $document->direction) === 'incoming' ? 'selected' : '' }}>Incoming</option>
                    <option value="outgoing" {{ old('direction', $document->direction) === 'outgoing' ? 'selected' : '' }}>Outgoing</option>
                    <option value="internal" {{ old('direction', $document->direction) === 'internal' ? 'selected' : '' }}>Internal</option>
                </select>
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold">Document Type *</label>
                <select name="document_type" class="form-select" required>
                    @foreach(['letter' => 'Official Letter', 'invitation' => 'Invitation', 'memo' => 'Internal Memo', 'report' => 'Report', 'legal_notice' => 'Legal Notice', 'petition' => 'Petition', 'circular' => 'Circular', 'other' => 'Other'] as $tp => $lbl)
                        <option value="{{ $tp }}" {{ old('document_type', $document->document_type) === $tp ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold">Date Issued</label>
                <input type="date" name="date_issued" value="{{ old('date_issued', $document->date_issued?->format('Y-m-d')) }}" class="form-control">
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold">Date Received *</label>
                <input type="date" name="date_received" value="{{ old('date_received', $document->date_received?->format('Y-m-d')) }}" class="form-control" required>
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold">Category *</label>
                <select name="category_id" class="form-select" required>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (int) old('category_id', $document->category_id) === $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold">Office *</label>
                <select name="office_id" class="form-select" required>
                    @foreach($offices as $off)
                        <option value="{{ $off->id }}" {{ (int) old('office_id', $document->office_id) === $off->id ? 'selected' : '' }}>{{ $off->code }} — {{ $off->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold">Department</label>
                <select name="department_id" class="form-select">
                    <option value="">-- Central Registry --</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ (int) old('department_id', $document->department_id) === $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold">Confidentiality *</label>
                <select name="confidentiality_level" class="form-select" required>
                    <option value="public" {{ old('confidentiality_level', $document->confidentiality_level) === 'public' ? 'selected' : '' }}>Public</option>
                    <option value="internal" {{ old('confidentiality_level', $document->confidentiality_level) === 'internal' ? 'selected' : '' }}>Internal</option>
                    <option value="confidential" {{ old('confidentiality_level', $document->confidentiality_level) === 'confidential' ? 'selected' : '' }}>Confidential</option>
                    <option value="strictly_confidential" {{ old('confidentiality_level', $document->confidentiality_level) === 'strictly_confidential' ? 'selected' : '' }}>Strictly Confidential</option>
                </select>
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label small fw-semibold">Event Date &amp; Time (For Invitations)</label>
                <input type="datetime-local" name="event_date" value="{{ old('event_date', $document->event_date?->format('Y-m-d\TH:i')) }}" class="form-control">
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label small fw-semibold">Tags (Comma-separated)</label>
                <input type="text" name="tags" value="{{ old('tags', $document->tags->pluck('name')->implode(', ')) }}" class="form-control">
            </div>

            <div class="col-12">
                <label class="form-label small fw-semibold">Description / Summary</label>
                <textarea name="description" rows="3" class="form-control">{{ old('description', $document->description) }}</textarea>
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                <a href="{{ route('documents.show', $document) }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-lmo-primary px-4">Save Metadata Changes</button>
            </div>
        </div>
    </div>
</form>
@endsection
