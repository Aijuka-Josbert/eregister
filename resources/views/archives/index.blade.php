@extends('layouts.app')

@section('title', "Archive — LMO Records Registry")

@section('content')
<div class="lmo-ph">
    <div>
        <h1>Archive</h1>
        <p>Documents that have been dealt with and put away. They stay searchable and can be restored.</p>
    </div>
    <a href="{{ route('documents.index') }}" class="lmo-btn">
        <i class="bi bi-folder2"></i> Active register
    </a>
</div>

<form action="{{ route('archives.index') }}" method="GET" class="lmo-filters">
    <label>
        Search archive
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Subject, reference, sender or text">
    </label>
    <label>
        Category
        <select name="category_id" onchange="this.form.submit()">
            <option value="">Any category</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ (int) ($filters['category_id'] ?? 0) === $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
            @endforeach
        </select>
    </label>
    <label>
        Office
        <select name="office_id" onchange="this.form.submit()">
            <option value="">Any office</option>
            @foreach($offices as $off)
                <option value="{{ $off->id }}" {{ (int) ($filters['office_id'] ?? 0) === $off->id ? 'selected' : '' }}>
                    {{ $off->code }} — {{ $off->name }}
                </option>
            @endforeach
        </select>
    </label>
    <button type="submit" class="lmo-btn sm sage">Search</button>
    @if(!empty($filters['q']) || !empty($filters['category_id']) || !empty($filters['office_id']))
        <a href="{{ route('archives.index') }}" class="lmo-btn sm ghost">Clear</a>
    @endif
    <span class="count">{{ number_format($documents->total()) }} archived</span>
</form>

<section class="lmo-panel">
    @if($documents->isNotEmpty())
        <div class="lmo-rows">
            <div class="lmo-row head">
                <span>Archived</span>
                <span>Document</span>
                <span>From / To</span>
                <span class="cc">Category</span>
                <span>Status</span>
                <span></span>
            </div>
            @foreach($documents as $doc)
                <a href="{{ route('documents.show', $doc) }}" class="lmo-row">
                    <span class="d">{{ ($doc->archived_at ?? $doc->date_received)?->format('j M Y') }}</span>
                    <span class="doc-main">
                        <span class="s">{{ $doc->title }}</span>
                        <span class="o">
                            {{ $doc->organization }}
                            @if($doc->official_reference)
                                &middot; <span class="mono">{{ $doc->official_reference }}</span>
                            @endif
                        </span>
                    </span>
                    <span class="f">
                        {{ $doc->sender ?: '—' }}<br>
                        <span class="muted">to {{ $doc->recipient ?: 'Lord Mayor' }}</span>
                    </span>
                    <span class="cc">
                        <span class="lmo-chip cat">{{ $doc->category?->name ?? 'General' }}</span>
                    </span>
                    <span class="stc">
                        <x-status-chip :status="$doc->status" :archived="true" />
                    </span>
                    <span class="sc" title="{{ $doc->file_count > 0 ? $doc->file_count.' scan(s)' : 'No scan' }}">
                        @if($doc->file_count > 0)
                            <span class="hasscan"><i class="bi bi-file-earmark-text"></i></span>
                        @else
                            <span class="noscan"><i class="bi bi-exclamation-triangle"></i></span>
                        @endif
                    </span>
                </a>
            @endforeach
        </div>
        @if($documents->hasPages())
            <div class="p-3 border-top" style="border-color: var(--line) !important;">
                {{ $documents->links() }}
            </div>
        @endif
    @else
        <div class="lmo-empty">
            <h3>The archive is empty</h3>
            <p class="mb-0">When a document is archived it moves here while staying fully searchable.</p>
        </div>
    @endif
</section>
@endsection
