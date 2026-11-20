@extends('layouts.app')

@section('title', "Dashboard — LMO Records Registry")

@section('content')
@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $firstName = explode(' ', trim((string) auth()->user()->name))[0] ?? '';
    $maxCat = max(1, (int) ($categoryBreakdown->max('documents_count') ?? 1));
    $accSummary = $accountability['summary'];
    $accSlices = $accountability['actionBreakdown'];
    $circumference = $accountability['circumference'];
@endphp

<div class="lmo-ph">
    <div>
        <h1>{{ $greeting }}{{ $firstName ? ', '.$firstName : '' }}</h1>
        <p>{{ now()->format('l, j F Y') }}</p>
    </div>
    @can('create', \App\Models\Document::class)
        <a href="{{ route('documents.create') }}" class="btn-lmo-primary">
            <i class="bi bi-plus-lg"></i> Register a document
        </a>
    @endcan
</div>

<!-- 4-Stat Summary Strip -->
<div class="lmo-strip">
    <div>
        <div class="v">{{ number_format($stats['received_today']) }}</div>
        <div class="l">Received today</div>
    </div>
    <div>
        <div class="v">{{ number_format($stats['received_week']) }}</div>
        <div class="l">Received this week</div>
    </div>
    <div class="{{ $stats['waiting_action'] > 0 ? 'hot' : '' }}">
        <div class="v">{{ number_format($stats['waiting_action']) }}</div>
        <div class="l">Waiting for action</div>
    </div>
    <div class="{{ $stats['missing_scan'] > 0 ? 'hot' : '' }}">
        <div class="v">{{ number_format($stats['missing_scan']) }}</div>
        <div class="l">Registered without a scan</div>
    </div>
</div>

<div class="lmo-cols">
    <!-- Left Column: Recently Registered & Needs a Scan -->
    <div class="d-flex flex-column gap-4" style="min-width: 0;">
        <section class="lmo-panel">
            <header>
                <h2>Recently registered</h2>
                <a href="{{ route('documents.index') }}" class="lmo-btn sm ghost">View all</a>
            </header>
            <div class="lmo-rows">
                @forelse($recentDocuments as $doc)
                    <a href="{{ route('documents.show', $doc) }}" class="lmo-row compact">
                        <span class="d">{{ $doc->date_received?->format('j M Y') }}</span>
                        <span class="doc-main">
                            <span class="s">{{ $doc->title }}</span>
                            <span class="o">
                                {{ $doc->organization }}
                                @if($doc->official_reference)
                                    &middot; <span class="mono">{{ $doc->official_reference }}</span>
                                @endif
                            </span>
                        </span>
                        <span class="stc text-end">
                            <x-status-chip :status="$doc->status" :archived="$doc->is_archived" />
                        </span>
                    </a>
                @empty
                    <div class="lmo-empty">
                        <h3>No documents yet</h3>
                        <p>Register the first document you receive and it appears here.</p>
                        @can('create', \App\Models\Document::class)
                            <a href="{{ route('documents.create') }}" class="btn-lmo-primary mt-2">
                                <i class="bi bi-upc-scan"></i> Register a document
                            </a>
                        @endcan
                    </div>
                @endforelse
            </div>
        </section>

        @if($missingScanDocuments->isNotEmpty())
            <section class="lmo-panel">
                <header>
                    <h2>Needs a scan</h2>
                    <span class="muted" style="font-size: 13.5px;">Open each one and add the scan</span>
                </header>
                <div class="lmo-rows">
                    @foreach($missingScanDocuments as $doc)
                        <a href="{{ route('documents.show', $doc) }}" class="lmo-row compact">
                            <span class="d">{{ $doc->date_received?->format('j M Y') }}</span>
                            <span class="doc-main">
                                <span class="s">{{ $doc->title }}</span>
                                <span class="o">{{ $doc->organization }}</span>
                            </span>
                            <span class="stc text-end">
                                <x-status-chip :status="$doc->status" :archived="$doc->is_archived" />
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    <!-- Right Column: Accountability Summary Pie Chart & By Category -->
    <div class="d-flex flex-column gap-4" style="min-width: 0;">
        <!-- Accountability Donut Summary -->
        <section class="lmo-panel">
            <header>
                <h2>Accountability summary</h2>
                @can('viewAny', \App\Models\DocumentActivityLog::class)
                    <a href="{{ route('audit.index') }}" class="lmo-btn sm ghost">Full graphs</a>
                @endcan
            </header>
            @if($accSummary['total_events'] > 0)
                <div class="lmo-donut-wrap">
                    <div class="lmo-donut-svg-box sm">
                        <svg viewBox="0 0 140 140" width="136" height="136" role="img" aria-label="Accountability breakdown chart">
                            <circle cx="70" cy="70" r="54" fill="none" stroke="#E2E8DA" stroke-width="18"/>
                            <g transform="rotate(-90 70 70)">
                                @foreach($accSlices as $slice)
                                    <circle
                                        class="lmo-donut-slice"
                                        cx="70"
                                        cy="70"
                                        r="54"
                                        fill="none"
                                        stroke="{{ $slice['color'] }}"
                                        stroke-width="18"
                                        stroke-dasharray="{{ $slice['dash'] }} {{ $circumference }}"
                                        stroke-dashoffset="-{{ $slice['offset'] }}"
                                    >
                                        <title>{{ $slice['label'] }}: {{ $slice['count'] }} ({{ $slice['percent'] }}%)</title>
                                    </circle>
                                @endforeach
                            </g>
                        </svg>
                        <div class="lmo-donut-center sm">
                            <strong>{{ number_format($accSummary['total_events']) }}</strong>
                            <span>Logged</span>
                        </div>
                    </div>

                    <div class="lmo-legend">
                        @foreach($accSlices as $slice)
                            <div class="lmo-legend-item">
                                <span class="lmo-legend-dot" style="background: {{ $slice['color'] }};"></span>
                                <span class="lmo-legend-label">{{ $slice['label'] }}</span>
                                <span class="lmo-legend-val">{{ $slice['percent'] }}%</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="lmo-empty" style="padding: 28px 18px;">
                    <p class="mb-0">Activity graphs will appear here as records are registered and handled.</p>
                </div>
            @endif
        </section>

        <!-- By Category -->
        <section class="lmo-panel">
            <header>
                <h2>By category</h2>
                <a href="{{ route('categories.index') }}" class="lmo-btn sm ghost">Manage</a>
            </header>
            <div class="lmo-bars">
                @foreach($categoryBreakdown as $cat)
                    @php
                        $pct = $cat->documents_count > 0 ? max(4, (int) round(($cat->documents_count / $maxCat) * 100)) : 0;
                    @endphp
                    <a href="{{ route('documents.index', ['category_id' => $cat->id]) }}" class="lmo-bar">
                        <span>{{ $cat->name }}</span>
                        <span class="n">{{ $cat->documents_count }}</span>
                        <span class="t"><i style="width: {{ $pct }}%;"></i></span>
                    </a>
                @endforeach
            </div>
        </section>
    </div>
</div>
@endsection
