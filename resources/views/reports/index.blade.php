@extends('layouts.app')

@section('title', 'Reports, Exports & Encrypted Backups — LMO DRMS')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <div>
        <h1 class="h4 fw-bold mb-1" style="color: var(--lmo-deep-navy);">
            <i class="bi bi-bar-chart-line me-1"></i> Institutional Reports &amp; Encrypted Backups
        </h1>
        <p class="text-muted small mb-0">
            Generate official PDF/printable registers, export Excel/CSV datasets, and manage AES-256 encrypted system backups.
        </p>
    </div>

    @if(auth()->user()->hasPermission('reports.export'))
        <div class="d-flex gap-2">
            <a href="{{ route('reports.export.csv', request()->query()) }}" class="btn btn-sm btn-lmo-primary">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Excel / CSV
            </a>
            <a href="{{ route('reports.export.print', request()->query()) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-printer me-1"></i> Open Printable / PDF Report
            </a>
        </div>
    @endif
</div>

<div class="row g-4">
    <!-- Left Column: Report Filter & Preview -->
    <div class="col-12 col-xl-8">
        <div class="lmo-card mb-4 p-3">
            <form action="{{ route('reports.index') }}" method="GET">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold mb-1">Category</label>
                        <select name="category_id" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ (int) ($filters['category_id'] ?? 0) === $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-semibold mb-1">From Date</label>
                        <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-semibold mb-1">To Date</label>
                        <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control form-control-sm">
                    </div>
                    <div class="col-12 col-md-2">
                        <button type="submit" class="btn btn-sm btn-lmo-primary w-100">Filter Report</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="lmo-card">
            <div class="lmo-card-header d-flex justify-content-between align-items-center">
                <span>Register Report Preview ({{ $documents->total() }} records)</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover table-lmo mb-0">
                    <thead>
                        <tr>
                            <th>Internal ID</th>
                            <th>Official Ref</th>
                            <th>Title &amp; Organization</th>
                            <th>Category</th>
                            <th>Received</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $doc)
                            <tr>
                                <td><span class="lmo-ref-badge">{{ $doc->internal_reference }}</span></td>
                                <td><code class="small">{{ $doc->official_reference ?? 'Unnumbered' }}</code></td>
                                <td>
                                    <div class="fw-semibold small">{{ $doc->title }}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">{{ $doc->organization }}</div>
                                </td>
                                <td class="small">{{ $doc->category?->name }}</td>
                                <td class="small">{{ $doc->date_received?->format('Y-m-d') }}</td>
                                <td><span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.68rem;">{{ $doc->status }}</span></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No records match the report filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Independent Encrypted Backup Vault -->
    <div class="col-12 col-xl-4">
        <div class="lmo-card">
            <div class="lmo-card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-hdd-network me-2"></i>Encrypted Backup Vault</span>
                @if(auth()->user()->isAdmin())
                    <form action="{{ route('reports.backup') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success fw-semibold">
                            <i class="bi bi-shield-lock me-1"></i> Run Snapshot Now
                        </button>
                    </form>
                @endif
            </div>
            <div class="p-3 small">
                <p class="text-muted mb-3">
                    Backups package the database, document metadata, extracted OCR text, audit history, and private document files into an <code>AES-256-CBC</code> encrypted archive verified by <code>SHA-256</code>.
                </p>

                @forelse($backups as $bkp)
                    <div class="border rounded p-2 mb-2 bg-light">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong class="font-monospace">{{ $bkp->backup_reference }}</strong>
                            <span class="badge {{ in_array($bkp->status, ['completed', 'verified']) ? 'bg-success' : 'bg-danger' }}">
                                {{ strtoupper($bkp->status) }}
                            </span>
                        </div>
                        <div class="text-muted mt-1" style="font-size: 0.74rem;">
                            {{ $bkp->notes }}
                        </div>
                        <div class="text-muted mt-1" style="font-size: 0.7rem;">
                            Size: {{ number_format($bkp->file_size / 1024, 1) }} KB
                            &bull; {{ $bkp->completed_at?->format('d M Y H:i') }}
                            &bull; SHA-256: <code>{{ substr((string) $bkp->sha256_hash, 0, 12) }}...</code>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-3 text-muted">No backups executed yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
