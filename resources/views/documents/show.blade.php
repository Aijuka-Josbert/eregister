@extends('layouts.app')

@section('title', $document->internal_reference . ' — ' . $document->title)

@section('content')
<!-- Top Breadcrumb & Action Bar -->
<div class="d-flex flex-wrap justify-content-between align-items-start mb-3 gap-2">
    <div>
        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
            <span class="lmo-ref-badge">{{ $document->internal_reference }}</span>
            @if($document->official_reference)
                <span class="lmo-official-ref" title="Sender's Official Reference Number">
                    Official Ref: {{ $document->official_reference }}
                </span>
            @endif
            <x-confidentiality-badge :level="$document->confidentiality_level" />
            <x-ocr-badge :status="$document->ocr_status" />
            @if($document->is_archived)
                <span class="badge bg-warning text-dark"><i class="bi bi-archive-fill me-1"></i>Archived in Vault</span>
            @endif
        </div>
        <h1 class="h4 fw-bold mb-1" style="color: var(--lmo-deep-navy);">{{ $document->title }}</h1>
        <div class="small text-muted">
            <i class="bi bi-building me-1"></i><strong>{{ $document->organization }}</strong>
            &bull; Registered {{ $document->registered_at?->format('d M Y, H:i') }} by {{ $document->registrar?->name }}
        </div>
    </div>

    <div class="d-flex flex-wrap gap-2">
        @can('update', $document)
            <a href="{{ route('documents.edit', $document) }}" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-pencil-square me-1"></i> Edit Metadata
            </a>
        @endcan

        @can('retryOcr', $document)
            <form action="{{ route('documents.retry-ocr', $document) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-cpu me-1"></i> Re-Run OCR
                </button>
            </form>
        @endcan

        @can('archive', $document)
            <button type="button" class="btn btn-sm btn-warning text-dark fw-semibold" data-bs-toggle="modal" data-bs-target="#archiveDocumentModal">
                <i class="bi bi-archive me-1"></i> Archive Record
            </button>
        @endcan

        @can('restore', $document)
            <button type="button" class="btn btn-sm btn-success fw-semibold" data-bs-toggle="modal" data-bs-target="#restoreDocumentModal">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Restore to Active Register
            </button>
        @endcan

        <a href="{{ route('documents.index') }}" class="btn btn-sm btn-light border">
            &larr; Back to Register
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Official Metadata, Attachments & Extracted OCR Text -->
    <div class="col-12 col-xl-8">
        <!-- Metadata Card -->
        <div class="lmo-card mb-4">
            <div class="lmo-card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-card-list me-2"></i>Registered Document Metadata</span>
                <span class="badge rounded-pill" style="background-color: {{ $document->category?->color_code ?? '#245B91' }};">
                    {{ $document->category?->name }}
                </span>
            </div>
            <div class="p-3 p-md-4">
                <div class="row g-3 small">
                    <div class="col-6 col-md-4">
                        <div class="text-muted">Internal Tracking ID</div>
                        <div class="fw-bold font-monospace">{{ $document->internal_reference }}</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-muted">Official Reference Number</div>
                        <div class="fw-bold font-monospace">{{ $document->official_reference ?? 'Unnumbered' }}</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-muted">Direction &amp; Type</div>
                        <div class="fw-semibold text-capitalize">{{ $document->direction }} &bull; {{ str_replace('_', ' ', $document->document_type) }}</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-muted">Originating Organization</div>
                        <div class="fw-semibold">{{ $document->organization }}</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-muted">Sender</div>
                        <div class="fw-semibold">{{ $document->sender ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-muted">Recipient</div>
                        <div class="fw-semibold">{{ $document->recipient ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-muted">Date Issued</div>
                        <div class="fw-semibold">{{ $document->date_issued?->format('d M Y') ?? '—' }}</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-muted">Date Received at Office</div>
                        <div class="fw-semibold">{{ $document->date_received?->format('d M Y') }}</div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="text-muted">Office &amp; Department</div>
                        <div class="fw-semibold">{{ $document->office?->name }} ({{ $document->department?->name ?? 'Central Registry' }})</div>
                    </div>

                    @if($document->event_date)
                        <div class="col-12 col-md-6">
                            <div class="p-2 rounded border bg-light">
                                <span class="text-muted d-block"><i class="bi bi-calendar2-event text-primary me-1"></i>Invitation / Event Date &amp; Time:</span>
                                <strong>{{ $document->event_date->format('l, d F Y \a\t H:i') }}</strong>
                            </div>
                        </div>
                    @endif

                    @if($document->description)
                        <div class="col-12">
                            <div class="text-muted mb-1">Summary / Registration Remarks</div>
                            <div class="p-3 rounded bg-light border">{{ $document->description }}</div>
                        </div>
                    @endif

                    @if($document->tags->isNotEmpty())
                        <div class="col-12">
                            <div class="text-muted mb-1">Tags</div>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($document->tags as $tag)
                                    <a href="{{ route('documents.index', ['q' => $tag->name]) }}" class="badge bg-light text-dark border text-decoration-none">
                                        #{{ $tag->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Scanned Files & Private Storage Attachments -->
        <div class="lmo-card mb-4">
            <div class="lmo-card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-paperclip me-2"></i>Scanned Files &amp; Attachments ({{ $document->files->count() }})</span>
                <span class="small text-muted"><i class="bi bi-lock-fill text-success me-1"></i>Protected in Private Storage</span>
            </div>
            <div class="p-3">
                @forelse($document->files as $file)
                    <div class="border rounded p-3 mb-3 bg-white">
                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <div class="fw-semibold">
                                    <i class="bi {{ $file->extension === 'pdf' ? 'bi-file-earmark-pdf text-danger' : 'bi-file-earmark-image text-primary' }} me-1"></i>
                                    Page / Attachment #{{ $file->page_order }}: {{ $file->original_name }}
                                </div>
                                <div class="small text-muted mt-1">
                                    Size: {{ $file->formattedSize() }}
                                    &bull; Source: <span class="text-capitalize">{{ str_replace('_', ' ', $file->capture_source) }}</span>
                                    &bull; SHA-256: <code style="font-size: 0.7rem;">{{ substr($file->sha256_hash, 0, 16) }}...</code>
                                </div>
                            </div>

                            @can('download', $document)
                                <div class="d-flex gap-2">
                                    <a href="{{ route('documents.files.preview', [$document, $file]) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i> Preview
                                    </a>
                                    <a href="{{ route('documents.files.download', [$document, $file]) }}" class="btn btn-sm btn-lmo-primary">
                                        <i class="bi bi-download me-1"></i> Secure Download
                                    </a>
                                </div>
                            @endcan
                        </div>

                        @can('download', $document)
                            @if(in_array($file->extension, ['jpg', 'jpeg', 'png'], true))
                                <div class="mt-3 text-center bg-light p-2 rounded border">
                                    <img
                                        src="{{ route('documents.files.preview', [$document, $file]) }}"
                                        alt="{{ $file->original_name }}"
                                        class="img-fluid rounded"
                                        style="max-height: 420px;"
                                    >
                                </div>
                            @elseif($file->extension === 'pdf')
                                <div class="mt-3 ratio ratio-16x9 border rounded overflow-hidden">
                                    <iframe src="{{ route('documents.files.preview', [$document, $file]) }}" title="{{ $file->original_name }}"></iframe>
                                </div>
                            @endif
                        @endcan
                    </div>
                @empty
                    <div class="text-center py-4 text-muted small">
                        No digital files attached to this registration yet.
                    </div>
                @endforelse

                @can('update', $document)
                    <form action="{{ route('documents.files.store', $document) }}" method="POST" enctype="multipart/form-data" class="mt-3 pt-3 border-top">
                        @csrf
                        <label class="form-label small fw-semibold">Attach Additional Scanned Pages / Annexures (Originals are never overwritten)</label>
                        <div class="input-group input-group-sm">
                            <input type="file" name="files[]" multiple accept=".pdf,.jpg,.jpeg,.png" class="form-control" required>
                            <button type="submit" class="btn btn-lmo-primary">
                                <i class="bi bi-upload me-1"></i> Upload &amp; Run OCR
                            </button>
                        </div>
                    </form>
                @endcan
            </div>
        </div>

        <!-- Extracted OCR Searchable Text -->
        <div class="lmo-card mb-4">
            <div class="lmo-card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-cpu me-2"></i>Extracted OCR Searchable Content</span>
                <x-ocr-badge :status="$document->ocr_status" />
            </div>
            <div class="p-3">
                @if($document->ocr_error)
                    <div class="alert alert-warning small py-2">
                        <strong>OCR Diagnostic:</strong> {{ $document->ocr_error }}
                    </div>
                @endif

                @if($document->ocr_text)
                    <pre class="p-3 bg-light border rounded small mb-0" style="white-space: pre-wrap; max-height: 340px; overflow-y: auto; font-family: ui-monospace, SFMono-Regular, monospace;">{{ $document->ocr_text }}</pre>
                @else
                    <div class="text-muted small py-3 text-center">
                        No OCR text extracted yet. Upload a scanned PDF or image and click <strong>Re-Run OCR</strong>.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column: Routing/Assignments, Archive History & Immutable Audit Trail -->
    <div class="col-12 col-xl-4">
        <!-- Internal Routing & Action Assignment -->
        <div class="lmo-card mb-4">
            <div class="lmo-card-header">
                <i class="bi bi-send-check me-2"></i>Internal Routing &amp; Action Assignment
            </div>
            <div class="p-3">
                @forelse($document->assignments as $assign)
                    <div class="border rounded p-2 mb-2 bg-light small">
                        <div class="d-flex justify-content-between">
                            <strong>To: {{ $assign->assigneeUser?->name ?? $assign->assigneeDepartment?->name }}</strong>
                            <span class="badge {{ $assign->priority === 'urgent' ? 'bg-danger' : ($assign->priority === 'high' ? 'bg-warning text-dark' : 'bg-secondary') }}">
                                {{ strtoupper($assign->priority) }}
                            </span>
                        </div>
                        <div class="text-muted mt-1">{{ $assign->instructions }}</div>
                        <div class="text-muted mt-1" style="font-size: 0.72rem;">
                            By {{ $assign->assigner?->name }}
                            @if($assign->due_date)
                                &bull; Due: {{ $assign->due_date->format('d M Y') }}
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-muted small mb-2">No internal assignments recorded yet.</div>
                @endforelse

                @can('assign', $document)
                    <form action="{{ route('documents.assign', $document) }}" method="POST" class="mt-2 pt-2 border-top small">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-1">Assign to Officer</label>
                            <select name="assigned_to_user_id" class="form-select form-select-sm">
                                <option value="">-- Select Officer --</option>
                                @foreach($assignableUsers as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role?->name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small fw-semibold mb-1">Priority</label>
                                <select name="priority" class="form-select form-select-sm">
                                    <option value="normal">Normal</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-semibold mb-1">Due Date</label>
                                <input type="date" name="due_date" class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small fw-semibold mb-1">Action Instructions *</label>
                            <textarea name="instructions" rows="2" class="form-control form-control-sm" placeholder="e.g. Prepare response draft for Lord Mayor's signature..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-sm btn-lmo-primary w-100">Assign for Action</button>
                    </form>
                @endcan
            </div>
        </div>

        <!-- Archive History -->
        @if($document->archives->isNotEmpty())
            <div class="lmo-card mb-4">
                <div class="lmo-card-header">
                    <i class="bi bi-archive me-2"></i>Digital Archive History
                </div>
                <div class="p-3 small">
                    @foreach($document->archives as $arch)
                        <div class="border-start border-warning border-3 ps-2 mb-2">
                            <div class="fw-semibold">Archived {{ $arch->archived_at?->format('d M Y H:i') }} by {{ $arch->archiver?->name }}</div>
                            <div>Reason: {{ $arch->archive_reason }}</div>
                            <div class="text-muted" style="font-size: 0.74rem;">
                                Vault Code: <code>{{ $arch->archive_location_code }}</code>
                                &bull; Retention Until: {{ $arch->retention_until?->format('d M Y') }}
                            </div>
                            @if($arch->restored_at)
                                <div class="text-success mt-1">
                                    <i class="bi bi-arrow-counterclockwise"></i> Restored {{ $arch->restored_at->format('d M Y H:i') }} by {{ $arch->restorer?->name }} ({{ $arch->restore_reason }})
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Immutable Document Accountability Log -->
        <div class="lmo-card">
            <div class="lmo-card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-shield-lock me-2"></i>Document Audit Trail</span>
                <span class="badge bg-light text-dark border">Append-Only</span>
            </div>
            <div class="p-3" style="max-height: 460px; overflow-y: auto;">
                @foreach($document->activityLogs as $log)
                    <div class="border-start border-2 ps-3 pb-3 {{ $log->severity === 'critical' ? 'border-danger' : ($log->severity === 'warning' ? 'border-warning' : 'border-primary') }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-light text-dark border" style="font-size: 0.68rem;">{{ $log->action }}</span>
                            <span class="text-muted" style="font-size: 0.7rem;">{{ $log->created_at?->format('d M Y H:i:s') }}</span>
                        </div>
                        <div class="small fw-medium mt-1">{{ $log->description }}</div>
                        <div class="text-muted" style="font-size: 0.72rem;">
                            Actor: <strong>{{ $log->user?->name ?? 'System' }}</strong> &bull; IP: {{ $log->ip_address }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Archive Modal -->
@can('archive', $document)
<div class="modal fade" id="archiveDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('documents.archive', $document) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header" style="background-color: var(--lmo-deep-navy); color: #fff;">
                <h5 class="modal-title fs-6 fw-bold"><i class="bi bi-archive me-2"></i>Archive Document {{ $document->internal_reference }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body small">
                <p class="text-muted">
                    Archiving moves this record from the Active Register into the Digital Archive Vault while preserving all original files, metadata, OCR text, and audit history.
                </p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Archival Reason *</label>
                    <textarea name="archive_reason" rows="3" class="form-control" placeholder="e.g. Action completed and matter closed; moved to FY2026 Digital Archive Vault." required></textarea>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Vault Location Code</label>
                        <input type="text" name="archive_location_code" value="LMO-VAULT-{{ now()->format('Y') }}" class="form-control">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Retention Review Date</label>
                        <input type="date" name="retention_until" value="{{ now()->addYears($document->category?->retention_years ?? 5)->toDateString() }}" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-sm btn-warning fw-semibold">Confirm Archival</button>
            </div>
        </form>
    </div>
</div>
@endcan

<!-- Restore Modal -->
@can('restore', $document)
<div class="modal fade" id="restoreDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('documents.restore', $document) }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fs-6 fw-bold"><i class="bi bi-arrow-counterclockwise me-2"></i>Restore {{ $document->internal_reference }}</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body small">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Reason for Restoration *</label>
                    <textarea name="restore_reason" rows="3" class="form-control" placeholder="e.g. Follow-up inquiry received from Ministry; reopened for active reference." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-sm btn-success fw-semibold">Restore to Active Register</button>
            </div>
        </form>
    </div>
</div>
@endcan
@endsection
