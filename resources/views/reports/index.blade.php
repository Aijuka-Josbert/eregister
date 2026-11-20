@extends('layouts.app')

@section('title', "Reports & Backups — LMO Records Registry")

@section('content')
<div class="lmo-ph">
    <div>
        <h1>Reports &amp; backups</h1>
        <p>Export register spreadsheets, open printable PDF summaries, and run encrypted system backups.</p>
    </div>

    @if(auth()->user()->hasPermission('reports.export'))
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('reports.export.csv', request()->query()) }}" class="btn-lmo-primary">
                <i class="bi bi-file-earmark-spreadsheet"></i> Export CSV
            </a>
            <a href="{{ route('reports.export.print', request()->query()) }}" target="_blank" class="lmo-btn">
                <i class="bi bi-printer"></i> Printable report
            </a>
        </div>
    @endif
</div>

<div class="lmo-cols">
    <!-- Left Column: Filter & Preview -->
    <div class="d-flex flex-column gap-3" style="min-width: 0;">
        <form action="{{ route('reports.index') }}" method="GET" class="lmo-filters mb-0">
            <label>
                Category
                <select name="category_id" onchange="this.form.submit()">
                    <option value="">All categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (int) ($filters['category_id'] ?? 0) === $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label>
                From date
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
            </label>
            <label>
                To date
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
            </label>
            <button type="submit" class="lmo-btn sm sage">Filter</button>
            <a href="{{ route('reports.index') }}" class="lmo-btn sm ghost">Reset</a>
        </form>

        <section class="lmo-panel">
            <header>
                <h2>Register preview</h2>
                <span class="muted" style="font-size: 13px;">{{ number_format($documents->total()) }} records</span>
            </header>
            @if($documents->isNotEmpty())
                <div class="lmo-rows">
                    @foreach($documents as $doc)
                        <a href="{{ route('documents.show', $doc) }}" class="lmo-row compact">
                            <span class="d">{{ $doc->date_received?->format('j M Y') }}</span>
                            <span class="doc-main">
                                <span class="s">{{ $doc->title }}</span>
                                <span class="o">
                                    {{ $doc->organization }}
                                    &middot; <span class="mono">{{ $doc->official_reference ?: $doc->internal_reference }}</span>
                                    &middot; {{ $doc->category?->name }}
                                </span>
                            </span>
                            <span class="stc text-end">
                                <x-status-chip :status="$doc->status" :archived="$doc->is_archived" />
                            </span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="lmo-empty">
                    <p class="mb-0">No records match the chosen report filters.</p>
                </div>
            @endif
        </section>
    </div>

    <!-- Right Column: Encrypted Backup Vault -->
    <section class="lmo-panel">
        <header>
            <h2>Encrypted backups</h2>
            @if(auth()->user()->isAdmin())
                <form action="{{ route('reports.backup') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn-lmo-sage sm">
                        <i class="bi bi-shield-lock"></i> Run snapshot
                    </button>
                </form>
            @endif
        </header>
        <div class="lmo-panel-body">
            <p class="muted mb-3" style="font-size: 13.5px;">
                Packages the database, scans, OCR text, and audit trail into an <code>AES-256-CBC</code> encrypted snapshot verified by <code>SHA-256</code>.
            </p>

            @forelse($backups as $bkp)
                <div class="p-3 rounded mb-2" style="background: var(--bg); border: 1px solid var(--line); font-size: 13.5px;">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <strong class="mono">{{ $bkp->backup_reference }}</strong>
                        <span class="lmo-chip {{ in_array($bkp->status, ['completed', 'verified']) ? 'st-action' : 'st-received' }}">
                            {{ ucfirst($bkp->status) }}
                        </span>
                    </div>
                    @if($bkp->notes)
                        <div class="muted mt-1" style="font-size: 12.5px;">{{ $bkp->notes }}</div>
                    @endif
                    <div class="muted mt-1" style="font-size: 12px;">
                        {{ number_format($bkp->file_size / 1024, 1) }} KB
                        &middot; {{ $bkp->completed_at?->format('j M Y, H:i') }}
                        &middot; <code>{{ substr((string) $bkp->sha256_hash, 0, 12) }}</code>
                    </div>
                </div>
            @empty
                <p class="muted mb-0 text-center py-3">No backups executed yet.</p>
            @endforelse
        </div>
    </section>
</div>
@endsection
