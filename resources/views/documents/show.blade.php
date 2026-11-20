@extends('layouts.app')

@section('title', "{$document->title} — LMO Records Registry")

@section('content')
<!-- Top Header: Chips, Subject & Clean Actions -->
<div class="lmo-ph">
    <div style="min-width: 0; max-width: 780px;">
        <div class="d-flex flex-wrap gap-2 mb-2">
            @if($document->category)
                <span class="lmo-chip cat">{{ $document->category->name }}</span>
            @endif
            @if($document->official_reference)
                <span class="lmo-chip cat mono">{{ $document->official_reference }}</span>
            @endif
            <span class="lmo-chip cat mono">{{ $document->internal_reference }}</span>
            @if($document->is_archived)
                <span class="lmo-chip st-filed">Archived</span>
            @endif
            @if(in_array($document->confidentiality_level, ['confidential', 'strictly_confidential'], true))
                <x-confidentiality-badge :level="$document->confidentiality_level" />
            @endif
        </div>
        <h1>{{ $document->title }}</h1>
        <p>{{ $document->organization }} &middot; Registered {{ $document->registered_at?->format('j M Y, H:i') }} by {{ $document->registrar?->name ?? 'Staff' }}</p>
    </div>

    <div class="d-flex flex-wrap gap-2 align-items-center">
        @can('update', $document)
            <a href="{{ route('documents.edit', $document) }}" class="lmo-btn sm">
                <i class="bi bi-pencil"></i> Edit details
            </a>
        @endcan

        @can('archive', $document)
            <button type="button" class="lmo-btn sm danger" data-bs-toggle="modal" data-bs-target="#archiveDocumentModal">
                <i class="bi bi-archive"></i> Archive
            </button>
        @endcan

        @can('restore', $document)
            <button type="button" class="btn-lmo-sage sm" data-bs-toggle="modal" data-bs-target="#restoreDocumentModal">
                <i class="bi bi-arrow-counterclockwise"></i> Restore
            </button>
        @endcan

        <a href="{{ $document->is_archived ? route('archives.index') : route('documents.index') }}" class="lmo-btn sm ghost">
            &larr; Back
        </a>
    </div>
</div>

<!-- Inline Quick Status Bar (matching LMO Records Registry.html) -->
@if(! $document->is_archived)
    <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
        <span class="muted" style="font-size: 14px; font-weight: 600;">Status</span>
        @can('update', $document)
            <form action="{{ route('documents.status', $document) }}" method="POST" class="d-inline-flex align-items-center gap-2 m-0">
                @csrf
                @method('PATCH')
                <select name="status" class="form-select form-select-sm" style="width: auto; min-width: 165px;" onchange="this.form.submit()">
                    <option value="registered" {{ $document->status === 'registered' ? 'selected' : '' }}>Received</option>
                    <option value="in_review" {{ $document->status === 'in_review' ? 'selected' : '' }}>With officer</option>
                    <option value="actioned" {{ $document->status === 'actioned' ? 'selected' : '' }}>Action taken</option>
                    <option value="draft" {{ $document->status === 'draft' ? 'selected' : '' }}>Draft</option>
                </select>
            </form>
        @else
            <x-status-chip :status="$document->status" :archived="$document->is_archived" />
        @endcan
    </div>
@endif

<div class="lmo-cols">
    <!-- Left Column: Details & Scanned Copies -->
    <div class="d-flex flex-column gap-4" style="min-width: 0;">
        <!-- Record Details Definition List -->
        <section class="lmo-panel">
            <dl class="lmo-dl">
                <dt>Organisation</dt>
                <dd>{{ $document->organization }}</dd>

                <dt>From</dt>
                <dd>{{ $document->sender ?: '—' }}</dd>

                <dt>Addressed to</dt>
                <dd>{{ $document->recipient ?: 'Lord Mayor' }}</dd>

                <dt>Date received</dt>
                <dd>{{ $document->date_received?->format('j M Y') }}</dd>

                @if($document->event_date)
                    <dt>Event date</dt>
                    <dd><strong>{{ $document->event_date->format('j M Y') }}</strong></dd>
                @endif

                @if($document->date_issued)
                    <dt>Date issued</dt>
                    <dd>{{ $document->date_issued->format('j M Y') }}</dd>
                @endif

                <dt>Office &amp; unit</dt>
                <dd>{{ $document->office?->name ?? '—' }}{{ $document->department ? ' · '.$document->department->name : '' }}</dd>

                <dt>Access level</dt>
                <dd><x-confidentiality-badge :level="$document->confidentiality_level" /></dd>

                <dt>Registered by</dt>
                <dd>{{ $document->registrar?->name ?? 'Staff' }}, {{ $document->registered_at?->format('j M Y, H:i') }}</dd>

                @if($document->description)
                    <dt>Notes</dt>
                    <dd>{{ $document->description }}</dd>
                @endif

                @if($document->tags->isNotEmpty())
                    <dt>Tags</dt>
                    <dd class="d-flex flex-wrap gap-1">
                        @foreach($document->tags as $tag)
                            <a href="{{ route('documents.index', ['q' => $tag->name]) }}" class="lmo-chip cat text-decoration-none">
                                #{{ $tag->name }}
                            </a>
                        @endforeach
                    </dd>
                @endif
            </dl>
        </section>

        <!-- Scanned Copies -->
        <section class="lmo-panel">
            <header>
                <h2>Scanned copy ({{ $document->files->count() }})</h2>
                <x-ocr-badge :status="$document->ocr_status" />
            </header>
            <div class="lmo-panel-body">
                @forelse($document->files as $file)
                    <div class="lmo-file-item mt-0 mb-2">
                        <div class="th">
                            <i class="bi {{ $file->extension === 'pdf' ? 'bi-file-earmark-pdf' : 'bi-file-earmark-image' }}"></i>
                        </div>
                        <div style="min-width: 0;">
                            <div class="fw-semibold text-truncate">{{ $file->original_name }}</div>
                            <div class="muted" style="font-size: 12.5px;">
                                Page {{ $file->page_order }} &middot; {{ $file->formattedSize() }} &middot; {{ ucfirst(str_replace('_', ' ', $file->capture_source)) }}
                            </div>
                        </div>
                        @can('download', $document)
                            <div class="d-flex gap-2">
                                <a href="{{ route('documents.files.preview', [$document, $file]) }}" target="_blank" rel="noopener" class="lmo-btn sm">
                                    Open
                                </a>
                                <a href="{{ route('documents.files.download', [$document, $file]) }}" class="lmo-btn sm sage" title="Download">
                                    <i class="bi bi-download"></i>
                                </a>
                            </div>
                        @endcan
                    </div>

                    @can('download', $document)
                        @if(in_array($file->extension, ['jpg', 'jpeg', 'png'], true))
                            <div class="mb-3 p-2 rounded text-center" style="background: var(--bg); border: 1px solid var(--line);">
                                <img src="{{ route('documents.files.preview', [$document, $file]) }}" alt="{{ $file->original_name }}" class="img-fluid rounded" style="max-height: 380px;">
                            </div>
                        @elseif($file->extension === 'pdf')
                            <div class="mb-3 ratio ratio-16x9 rounded overflow-hidden" style="border: 1px solid var(--line);">
                                <iframe src="{{ route('documents.files.preview', [$document, $file]) }}" title="{{ $file->original_name }}"></iframe>
                            </div>
                        @endif
                    @endcan
                @empty
                    <p class="mb-0" style="color: var(--redink); font-size: 14px;">
                        No scan attached yet. This document currently only exists on paper.
                    </p>
                @endforelse

                @can('update', $document)
                    <form action="{{ route('documents.files.store', $document) }}" method="POST" enctype="multipart/form-data" class="mt-3 pt-3 border-top" style="border-color: var(--line) !important;">
                        @csrf
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <input type="file" name="files[]" multiple accept=".pdf,.jpg,.jpeg,.png" class="form-control form-control-sm" style="max-width: 340px;" required>
                            <button type="submit" class="btn-lmo-sage sm">
                                <i class="bi bi-upload"></i> Add scan
                            </button>
                            @can('retryOcr', $document)
                                <button type="submit" form="retryOcrForm" class="lmo-btn sm ghost ms-auto">
                                    <i class="bi bi-arrow-repeat"></i> Re-index text
                                </button>
                            @endcan
                        </div>
                    </form>
                @endcan

                @can('retryOcr', $document)
                    <form id="retryOcrForm" action="{{ route('documents.retry-ocr', $document) }}" method="POST" class="d-none">
                        @csrf
                    </form>
                @endcan

                @if($document->ocr_text || $document->ocr_error)
                    <details class="lmo-more-options">
                        <summary>Searchable text extracted from scan</summary>
                        @if($document->ocr_error)
                            <div class="lmo-alert warn mt-2 mb-0">{{ $document->ocr_error }}</div>
                        @endif
                        @if($document->ocr_text)
                            <pre class="p-3 rounded mt-2 mb-0 mono" style="white-space: pre-wrap; max-height: 280px; overflow-y: auto; background: var(--bg); border: 1px solid var(--line); color: var(--fg); font-size: 12.5px;">{{ $document->ocr_text }}</pre>
                        @endif
                    </details>
                @endif
            </div>
        </section>
    </div>

    <!-- Right Column: Handling History & Routing -->
    <div class="d-flex flex-column gap-4" style="min-width: 0;">
        <!-- Handling History Timeline (matching LMO Records Registry.html) -->
        <section class="lmo-panel">
            <header>
                <h2>Handling history</h2>
                <span class="muted" style="font-size: 12.5px;">Append-only</span>
            </header>
            <div class="lmo-panel-body" style="max-height: 460px; overflow-y: auto;">
                @if($document->activityLogs->isNotEmpty())
                    <ul class="lmo-tl">
                        @foreach($document->activityLogs as $log)
                            <li>
                                <strong>{{ $log->description }}</strong>
                                <small>{{ $log->user?->name ?? 'System' }} &middot; {{ $log->created_at?->format('j M Y, H:i') }}</small>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="muted mb-0">No activity logged yet.</p>
                @endif
            </div>
        </section>

        <!-- Internal Routing / Assignment (clean & discreet) -->
        <section class="lmo-panel">
            <header>
                <h2>Routing &amp; assignments</h2>
                <span class="lmo-chip cat">{{ $document->assignments->count() }}</span>
            </header>
            <div class="lmo-panel-body">
                @forelse($document->assignments as $assign)
                    <div class="p-2 px-3 rounded mb-2" style="background: var(--bg); border: 1px solid var(--line); font-size: 13.5px;">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <strong>{{ $assign->assigneeUser?->name ?? $assign->assigneeDepartment?->name }}</strong>
                            <span class="lmo-chip {{ $assign->priority === 'urgent' ? 'st-received' : ($assign->priority === 'high' ? 'st-warn' : 'st-filed') }}">
                                {{ ucfirst($assign->priority) }}
                            </span>
                        </div>
                        <div class="muted mt-1">{{ $assign->instructions }}</div>
                        <div class="muted mt-1" style="font-size: 12px;">
                            Assigned by {{ $assign->assigner?->name }}
                            @if($assign->due_date) &middot; Due {{ $assign->due_date->format('j M Y') }} @endif
                        </div>
                    </div>
                @empty
                    <p class="muted mb-0" style="font-size: 13.5px;">Not routed to a specific officer yet.</p>
                @endforelse

                @can('assign', $document)
                    <details class="lmo-more-options">
                        <summary>Route to an officer</summary>
                        <form action="{{ route('documents.assign', $document) }}" method="POST" class="d-flex flex-column gap-2 mt-3">
                            @csrf
                            <div class="lmo-fld">
                                <label>Officer</label>
                                <select name="assigned_to_user_id" class="form-select form-select-sm" required>
                                    <option value="">Select officer</option>
                                    @foreach($assignableUsers as $u)
                                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="lmo-fgrid">
                                <div class="lmo-fld">
                                    <label>Priority</label>
                                    <select name="priority" class="form-select form-select-sm">
                                        <option value="normal">Normal</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                </div>
                                <div class="lmo-fld">
                                    <label>Due date</label>
                                    <input type="date" name="due_date" class="form-control form-control-sm">
                                </div>
                            </div>
                            <div class="lmo-fld">
                                <label>Instructions <i class="req">*</i></label>
                                <textarea name="instructions" rows="2" class="form-control form-control-sm" placeholder="Action required..." required></textarea>
                            </div>
                            <button type="submit" class="btn-lmo-primary sm mt-1">Assign for action</button>
                        </form>
                    </details>
                @endcan
            </div>
        </section>

        @if($document->archives->isNotEmpty())
            <section class="lmo-panel">
                <header>
                    <h2>Archive history</h2>
                </header>
                <div class="lmo-panel-body">
                    <ul class="lmo-tl">
                        @foreach($document->archives as $arch)
                            <li>
                                <strong>Archived</strong> &middot; {{ $arch->archive_reason }}
                                <small>
                                    {{ $arch->archiver?->name }} &middot; {{ $arch->archived_at?->format('j M Y, H:i') }}
                                    @if($arch->archive_location_code) &middot; {{ $arch->archive_location_code }} @endif
                                </small>
                                @if($arch->restored_at)
                                    <small style="color: var(--sage2);">
                                        Restored {{ $arch->restored_at->format('j M Y, H:i') }} by {{ $arch->restorer?->name }} ({{ $arch->restore_reason }})
                                    </small>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>
        @endif
    </div>
</div>

<!-- Archive Modal -->
@can('archive', $document)
<div class="modal fade" id="archiveDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('documents.archive', $document) }}" method="POST" class="modal-content" style="background: var(--surface); border-color: var(--line);">
            @csrf
            <div class="modal-header" style="background: var(--side); color: var(--sidefg);">
                <h5 class="modal-title fs-6 fw-bold" style="color: var(--sidefg);">Move to Archive</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="font-size: 14px;">
                <p class="muted">
                    This moves the document to the Archive. It stays searchable with all its scans and can be restored at any time.
                </p>
                <div class="lmo-fld mb-3">
                    <label>Reason for archiving <i class="req">*</i></label>
                    <textarea name="archive_reason" rows="3" class="form-control" placeholder="e.g. Dealt with and closed." required></textarea>
                </div>
                <div class="lmo-fgrid">
                    <div class="lmo-fld">
                        <label>Vault code</label>
                        <input type="text" name="archive_location_code" value="LMO-VAULT-{{ now()->format('Y') }}" class="form-control mono">
                    </div>
                    <div class="lmo-fld">
                        <label>Retention date</label>
                        <input type="date" name="retention_until" value="{{ now()->addYears($document->category?->retention_years ?? 5)->toDateString() }}" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="border-color: var(--line);">
                <button type="button" class="lmo-btn sm" data-bs-dismiss="modal">Keep on register</button>
                <button type="submit" class="btn-lmo-primary sm">Archive document</button>
            </div>
        </form>
    </div>
</div>
@endcan

<!-- Restore Modal -->
@can('restore', $document)
<div class="modal fade" id="restoreDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('documents.restore', $document) }}" method="POST" class="modal-content" style="background: var(--surface); border-color: var(--line);">
            @csrf
            <div class="modal-header" style="background: var(--sage2); color: #fff;">
                <h5 class="modal-title fs-6 fw-bold" style="color: #fff;">Restore to active register</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="font-size: 14px;">
                <div class="lmo-fld">
                    <label>Reason for restoring <i class="req">*</i></label>
                    <textarea name="restore_reason" rows="3" class="form-control" placeholder="e.g. Follow-up inquiry received." required></textarea>
                </div>
            </div>
            <div class="modal-footer" style="border-color: var(--line);">
                <button type="button" class="lmo-btn sm" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn-lmo-sage sm">Restore to register</button>
            </div>
        </form>
    </div>
</div>
@endcan
@endsection
