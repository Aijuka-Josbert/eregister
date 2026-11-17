@extends('layouts.app')

@section('title', 'Accountability & Immutable Audit Trail — LMO DRMS')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h1 class="h4 fw-bold mb-1" style="color: var(--lmo-deep-navy);">
            <i class="bi bi-shield-check me-1"></i> Accountability &amp; Security Audit Dashboard
        </h1>
        <p class="text-muted small mb-0">
            Append-only institutional ledger recording every registration, confidential view, file download, metadata edit, archival action, and unauthorized access attempt.
        </p>
    </div>
    <span class="badge bg-success px-3 py-2"><i class="bi bi-lock-fill me-1"></i> Tamper-Protected Append-Only Ledger</span>
</div>

<!-- Security Summary KPIs -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="lmo-stat-card">
            <div class="small text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Total Audit Events</div>
            <div class="fs-4 fw-bold mt-1">{{ number_format($securitySummary['total_events']) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="lmo-stat-card accent-danger">
            <div class="small text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Critical / Access Denials</div>
            <div class="fs-4 fw-bold mt-1 text-danger">{{ number_format($securitySummary['critical_events']) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="lmo-stat-card accent-warning">
            <div class="small text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Sensitive Warnings</div>
            <div class="fs-4 fw-bold mt-1" style="color: var(--lmo-warning);">{{ number_format($securitySummary['warning_events']) }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="lmo-stat-card accent-gold">
            <div class="small text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Confidential Record Views</div>
            <div class="fs-4 fw-bold mt-1" style="color: var(--lmo-civic-blue);">{{ number_format($securitySummary['confidential_views']) }}</div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="lmo-card mb-4 p-3">
    <form action="{{ route('audit.index') }}" method="GET">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold mb-1">Search Description / Ref / IP</label>
                <input type="search" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search audit trail...">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Event Action</label>
                <select name="action" class="form-select form-select-sm">
                    <option value="">All Actions</option>
                    @foreach($distinctActions as $act)
                        <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ $act }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Severity</label>
                <select name="severity" class="form-select form-select-sm">
                    <option value="">All Severities</option>
                    <option value="info" {{ request('severity') === 'info' ? 'selected' : '' }}>Info</option>
                    <option value="warning" {{ request('severity') === 'warning' ? 'selected' : '' }}>Warning</option>
                    <option value="critical" {{ request('severity') === 'critical' ? 'selected' : '' }}>Critical</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold mb-1">Staff Member</label>
                <select name="user_id" class="form-select form-select-sm">
                    <option value="">All Staff</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ (int) request('user_id') === $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-lmo-primary w-100">Filter Logs</button>
                <a href="{{ route('audit.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </div>
    </form>
</div>

<!-- Audit Table -->
<div class="lmo-card">
    <div class="table-responsive">
        <table class="table table-hover table-lmo mb-0">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Severity</th>
                    <th>Action</th>
                    <th>Actor (Role)</th>
                    <th>Affected Record</th>
                    <th>Description &amp; Context</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="small font-monospace text-nowrap">
                            {{ $log->created_at?->format('Y-m-d H:i:s') }}
                        </td>
                        <td>
                            <span class="badge {{ $log->severity === 'critical' ? 'bg-danger' : ($log->severity === 'warning' ? 'bg-warning text-dark' : 'bg-light text-dark border') }}">
                                {{ strtoupper($log->severity) }}
                            </span>
                        </td>
                        <td>
                            <code class="small">{{ $log->action }}</code>
                        </td>
                        <td class="small">
                            <div class="fw-semibold">{{ $log->user?->name ?? 'Unauthenticated / System' }}</div>
                            <div class="text-muted" style="font-size: 0.72rem;">{{ $log->user?->role?->name ?? '' }}</div>
                        </td>
                        <td>
                            @if($log->document)
                                <a href="{{ route('documents.show', $log->document) }}" class="text-decoration-none">
                                    <span class="lmo-ref-badge">{{ $log->document->internal_reference }}</span>
                                </a>
                            @else
                                <span class="text-muted small">—</span>
                            @endif
                        </td>
                        <td class="small" style="max-width: 380px;">
                            <div>{{ $log->description }}</div>
                            @if(!empty($log->properties))
                                <details class="mt-1">
                                    <summary class="text-primary" style="font-size: 0.74rem; cursor: pointer;">View Context JSON</summary>
                                    <pre class="p-2 bg-light border rounded mt-1 mb-0" style="font-size: 0.7rem;">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                </details>
                            @endif
                        </td>
                        <td class="small font-monospace text-muted">
                            {{ $log->ip_address }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No matching audit log entries.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div class="p-3 border-top">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
