@extends('layouts.app')

@section('title', 'Executive Dashboard — LMO Digital Records Management System')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <div>
        <h1 class="h4 fw-bold mb-1" style="color: var(--lmo-deep-navy);">
            Lord Mayor's Office — Digital Records Dashboard
        </h1>
        <p class="text-muted small mb-0">
            Real-time overview of incoming correspondence, official invitations, OCR indexing, and archival accountability.
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @can('create', \App\Models\Document::class)
            <a href="{{ route('documents.create') }}" class="btn btn-lmo-primary">
                <i class="bi bi-camera me-1"></i> Scan / Register Document
            </a>
        @endcan
        @if(auth()->user()->hasPermission('reports.export'))
            <a href="{{ route('reports.export.csv') }}" class="btn btn-outline-secondary">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Excel/CSV
            </a>
            <a href="{{ route('reports.export.print') }}" target="_blank" class="btn btn-outline-secondary">
                <i class="bi bi-printer me-1"></i> Printable PDF Register
            </a>
        @endif
    </div>
</div>

<!-- KPI Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="lmo-stat-card h-100">
            <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Total Records</div>
            <div class="fs-3 fw-bold mt-1" style="color: var(--lmo-deep-navy);">{{ number_format($stats['total_records']) }}</div>
            <div class="small text-muted mt-1"><i class="bi bi-folder2-open me-1"></i>{{ number_format($stats['active_records']) }} active</div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl-2">
        <div class="lmo-stat-card accent-success h-100">
            <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Received Today</div>
            <div class="fs-3 fw-bold mt-1" style="color: var(--lmo-success);">{{ number_format($stats['received_today']) }}</div>
            <div class="small text-muted mt-1"><i class="bi bi-calendar-check me-1"></i>{{ now()->format('d M Y') }}</div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl-2">
        <div class="lmo-stat-card accent-gold h-100">
            <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">This Month</div>
            <div class="fs-3 fw-bold mt-1" style="color: var(--lmo-deep-navy);">{{ number_format($stats['monthly_registrations']) }}</div>
            <div class="small text-muted mt-1"><i class="bi bi-calendar3 me-1"></i>{{ now()->format('F Y') }}</div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl-2">
        <div class="lmo-stat-card accent-gold h-100">
            <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Invitations</div>
            <div class="fs-3 fw-bold mt-1" style="color: var(--lmo-civic-blue);">{{ number_format($stats['invitations_count']) }}</div>
            <div class="small text-muted mt-1">
                <a href="{{ route('documents.index', ['document_type' => 'invitation']) }}" class="text-decoration-none">View Invitations &rarr;</a>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl-2">
        <div class="lmo-stat-card accent-warning h-100">
            <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">Archived Vault</div>
            <div class="fs-3 fw-bold mt-1" style="color: var(--lmo-warning);">{{ number_format($stats['archived_records']) }}</div>
            <div class="small text-muted mt-1">
                <a href="{{ route('archives.index') }}" class="text-decoration-none">Open Vault &rarr;</a>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl-2">
        <div class="lmo-stat-card {{ $stats['ocr_failures'] > 0 ? 'accent-danger' : 'accent-success' }} h-100">
            <div class="text-muted small fw-semibold text-uppercase" style="font-size: 0.7rem;">OCR Indexed</div>
            <div class="fs-3 fw-bold mt-1" style="color: {{ $stats['ocr_failures'] > 0 ? 'var(--lmo-danger)' : 'var(--lmo-success)' }};">
                {{ number_format($stats['ocr_completed']) }}
            </div>
            <div class="small text-muted mt-1">
                @if($stats['ocr_failures'] > 0)
                    <a href="{{ route('documents.index', ['ocr_status' => 'failed']) }}" class="text-danger fw-semibold">{{ $stats['ocr_failures'] }} failed</a>
                @else
                    <i class="bi bi-check2-all text-success me-1"></i>0 OCR failures
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Registered Documents -->
    <div class="col-12 col-xl-8">
        <div class="lmo-card mb-4">
            <div class="lmo-card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-journal-text me-2"></i>Recently Registered Documents</span>
                <a href="{{ route('documents.index') }}" class="btn btn-sm btn-outline-primary">View Full Register</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-lmo mb-0">
                    <thead>
                        <tr>
                            <th>Internal ID</th>
                            <th>Official Ref No.</th>
                            <th>Subject / Organization</th>
                            <th>Category</th>
                            <th>Security</th>
                            <th>OCR</th>
                            <th>Received</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentDocuments as $doc)
                            <tr>
                                <td>
                                    <a href="{{ route('documents.show', $doc) }}" class="text-decoration-none">
                                        <span class="lmo-ref-badge">{{ $doc->internal_reference }}</span>
                                    </a>
                                </td>
                                <td>
                                    @if($doc->official_reference)
                                        <span class="lmo-official-ref">{{ $doc->official_reference }}</span>
                                    @else
                                        <span class="text-muted small">Unnumbered</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('documents.show', $doc) }}" class="fw-semibold text-dark text-decoration-none d-block">
                                        {{ \Illuminate\Support\Str::limit($doc->title, 52) }}
                                    </a>
                                    <span class="text-muted small">{{ $doc->organization }}</span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill" style="background-color: {{ $doc->category?->color_code ?? '#245B91' }};">
                                        {{ $doc->category?->name ?? 'General' }}
                                    </span>
                                </td>
                                <td>
                                    <x-confidentiality-badge :level="$doc->confidentiality_level" />
                                </td>
                                <td>
                                    <x-ocr-badge :status="$doc->ocr_status" />
                                </td>
                                <td class="text-muted small">
                                    {{ $doc->date_received?->format('d M Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No documents registered yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Category & Office Breakdown -->
        <div class="row g-4">
            <div class="col-12 col-md-6">
                <div class="lmo-card h-100">
                    <div class="lmo-card-header">
                        <i class="bi bi-pie-chart me-2"></i>Records by Category
                    </div>
                    <div class="p-3">
                        @foreach($categoryBreakdown as $cat)
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="d-inline-block rounded-circle" style="width:10px; height:10px; background-color: {{ $cat->color_code }};"></span>
                                    <a href="{{ route('documents.index', ['category_id' => $cat->id]) }}" class="text-dark text-decoration-none small fw-medium">
                                        {{ $cat->name }}
                                    </a>
                                </div>
                                <span class="badge bg-light text-dark border">{{ $cat->documents_count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="lmo-card h-100">
                    <div class="lmo-card-header">
                        <i class="bi bi-building me-2"></i>Records by Office &amp; Invitations
                    </div>
                    <div class="p-3">
                        @foreach($officeBreakdown as $off)
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <div>
                                    <span class="badge bg-secondary me-1">{{ $off->code }}</span>
                                    <span class="small fw-medium">{{ $off->name }}</span>
                                </div>
                                <span class="badge bg-light text-dark border">{{ $off->documents_count }}</span>
                            </div>
                        @endforeach

                        <div class="mt-3 pt-2">
                            <div class="fw-bold small text-uppercase text-muted mb-2" style="font-size: 0.72rem;">Recent Official Invitations</div>
                            @forelse($upcomingInvitations as $inv)
                                <div class="py-1 small d-flex justify-content-between align-items-center">
                                    <a href="{{ route('documents.show', $inv) }}" class="text-decoration-none text-truncate" style="max-width: 75%;">
                                        <i class="bi bi-calendar2-event text-primary me-1"></i>{{ $inv->title }}
                                    </a>
                                    <span class="text-muted" style="font-size: 0.75rem;">{{ $inv->date_received?->format('d M') }}</span>
                                </div>
                            @empty
                                <div class="text-muted small">No recent invitations.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Accountability & Audit Feed -->
    <div class="col-12 col-xl-4">
        <div class="lmo-card h-100">
            <div class="lmo-card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-shield-check me-2"></i>Accountability Activity Stream</span>
                @can('viewAny', \App\Models\DocumentActivityLog::class)
                    <a href="{{ route('audit.index') }}" class="small text-decoration-none">Full Audit Log &rarr;</a>
                @endcan
            </div>
            <div class="p-3">
                @forelse($recentActivity as $log)
                    <div class="border-start border-3 ps-3 pb-3 mb-2 {{ $log->severity === 'critical' ? 'border-danger' : ($log->severity === 'warning' ? 'border-warning' : 'border-primary') }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge {{ $log->severity === 'critical' ? 'bg-danger' : ($log->severity === 'warning' ? 'bg-warning text-dark' : 'bg-light text-dark border') }}" style="font-size: 0.68rem;">
                                {{ $log->action }}
                            </span>
                            <span class="text-muted" style="font-size: 0.72rem;">{{ $log->created_at?->diffForHumans() }}</span>
                        </div>
                        <div class="small fw-medium mt-1">{{ $log->description }}</div>
                        <div class="text-muted" style="font-size: 0.74rem;">
                            <i class="bi bi-person me-1"></i>{{ $log->user?->name ?? 'System' }} &bull; IP: {{ $log->ip_address }}
                        </div>
                    </div>
                @empty
                    <div class="text-muted small text-center py-4">No activity recorded yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
