@extends('layouts.app')

@section('title', "All records — LMO Records Registry")

@section('content')
@php
    $activeCategory = !empty($filters['category_id'])
        ? $categories->firstWhere('id', (int) $filters['category_id'])
        : null;
    $activeOffice = !empty($filters['office_id'])
        ? $offices->firstWhere('id', (int) $filters['office_id'])
        : null;

    $pageTitle = $activeCategory?->name
        ?? ($activeOffice ? $activeOffice->name : 'All records');
    $pageDesc = $activeCategory?->description
        ?? ($activeOffice ? 'Records held by '.$activeOffice->name.'.' : 'Everything currently on the register.');
@endphp

<div class="lmo-ph">
    <div>
        <h1>{{ $pageTitle }}</h1>
        <p>{{ $pageDesc }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if(auth()->user()->hasPermission('reports.export'))
            <a href="{{ route('reports.export.csv', request()->query()) }}" class="lmo-btn">
                <i class="bi bi-download"></i> Export list
            </a>
        @endif
        @can('create', \App\Models\Document::class)
            <a href="{{ route('documents.create', array_filter(['category_id' => $filters['category_id'] ?? null])) }}" class="btn-lmo-primary">
                <i class="bi bi-plus-lg"></i> Register a document
            </a>
        @endcan
    </div>
</div>

<!-- Clean Filter Bar (matching LMO Records Registry.html) -->
<form action="{{ route('documents.index') }}" method="GET" class="lmo-filters">
    @if(!empty($filters['q']))
        <input type="hidden" name="q" value="{{ $filters['q'] }}">
    @endif

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
        Status
        <select name="status" onchange="this.form.submit()">
            <option value="">Any</option>
            <option value="registered" {{ ($filters['status'] ?? '') === 'registered' ? 'selected' : '' }}>Received</option>
            <option value="in_review" {{ ($filters['status'] ?? '') === 'in_review' ? 'selected' : '' }}>With officer</option>
            <option value="actioned" {{ ($filters['status'] ?? '') === 'actioned' ? 'selected' : '' }}>Action taken</option>
        </select>
    </label>

    <label>
        Received from
        <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" onchange="this.form.submit()">
    </label>

    <label>
        Received to
        <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" onchange="this.form.submit()">
    </label>

    <label>
        Scan
        <select name="scan" onchange="this.form.submit()">
            <option value="">Any</option>
            <option value="with" {{ ($filters['scan'] ?? '') === 'with' ? 'selected' : '' }}>Has scan</option>
            <option value="missing" {{ ($filters['scan'] ?? '') === 'missing' ? 'selected' : '' }}>Missing scan</option>
        </select>
    </label>

    @if(collect($filters)->filter()->isNotEmpty())
        <a href="{{ route('documents.index') }}" class="lmo-btn sm ghost">Clear filters</a>
    @endif

    <span class="count">
        {{ number_format($documents->total()) }} record{{ $documents->total() === 1 ? '' : 's' }}
        @if(!empty($filters['q']))
            for &ldquo;{{ $filters['q'] }}&rdquo;
        @endif
    </span>
</form>

<!-- Clean Spacious Record List -->
<section class="lmo-panel">
    @if($documents->isNotEmpty())
        <div class="lmo-rows">
            <div class="lmo-row head">
                <span>Received</span>
                <span>Document</span>
                <span>From / To</span>
                <span class="cc">Category</span>
                <span>Status</span>
                <span></span>
            </div>

            @foreach($documents as $doc)
                <a href="{{ route('documents.show', $doc) }}" class="lmo-row">
                    <span class="d">{{ $doc->date_received?->format('j M Y') }}</span>

                    <span class="doc-main">
                        <span class="s">{!! \App\Services\SearchService::highlight($doc->title, $filters['q'] ?? null, 100) !!}</span>
                        <span class="o">
                            {!! \App\Services\SearchService::highlight($doc->organization, $filters['q'] ?? null, 70) !!}
                            @if($doc->official_reference)
                                &middot; <span class="mono">{!! \App\Services\SearchService::highlight($doc->official_reference, $filters['q'] ?? null, 50) !!}</span>
                            @endif
                            @if($doc->confidentiality_level === 'confidential' || $doc->confidentiality_level === 'strictly_confidential')
                                &middot; <i class="bi bi-lock-fill" style="color: var(--redink);"></i>
                            @endif
                        </span>

                        @if(!empty($filters['q']) && $doc->ocr_text && stripos($doc->ocr_text, $filters['q']) !== false)
                            <div class="mt-1 px-2 py-1 rounded" style="background: var(--bg); border: 1px solid var(--line); font-size: 12.5px; color: var(--muted);">
                                <strong>Inside scan:</strong> {!! \App\Services\SearchService::highlight($doc->ocr_text, $filters['q'], 140) !!}
                            </div>
                        @endif
                    </span>

                    <span class="f">
                        {{ $doc->sender ?: '—' }}<br>
                        <span class="muted">to {{ $doc->recipient ?: 'Lord Mayor' }}</span>
                    </span>

                    <span class="cc">
                        <span class="lmo-chip cat">{{ $doc->category?->name ?? 'General' }}</span>
                    </span>

                    <span class="stc">
                        <x-status-chip :status="$doc->status" :archived="$doc->is_archived" />
                    </span>

                    <span class="sc" title="{{ $doc->file_count > 0 ? $doc->file_count.' scan'.($doc->file_count > 1 ? 's' : '') : 'No scan attached' }}">
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
            <h3>{{ collect($filters)->filter()->isNotEmpty() ? 'Nothing matches' : 'No documents registered yet' }}</h3>
            <p>
                {{ collect($filters)->filter()->isNotEmpty()
                    ? 'Try a different word, reference number, or clear the filters.'
                    : 'Register the first document you receive and it appears here.' }}
            </p>
            @if(collect($filters)->filter()->isEmpty())
                @can('create', \App\Models\Document::class)
                    <a href="{{ route('documents.create') }}" class="btn-lmo-primary mt-2">
                        <i class="bi bi-upc-scan"></i> Register the first document
                    </a>
                @endcan
            @endif
        </div>
    @endif
</section>
@endsection
