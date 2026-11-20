@extends('layouts.app')

@section('title', "Accountability & Activity Log — LMO Records Registry")

@section('content')
@php
    $summary = $accountability['summary'];
    $actionSlices = $accountability['actionBreakdown'];
    $staffActivity = $accountability['staffActivity'];
    $dailyTrend = $accountability['dailyTrend'];
    $severityBreakdown = $accountability['severityBreakdown'];
    $circumference = $accountability['circumference'];
@endphp

<div class="lmo-ph">
    <div>
        <h1>Accountability</h1>
        <p>Visual summary of who registered, viewed, downloaded, edited, or archived records — backed by an append-only ledger.</p>
    </div>
    <span class="lmo-chip st-action">
        <i class="bi bi-shield-lock"></i> Append-only audit active
    </span>
</div>

<!-- 4-Stat Summary Strip -->
<div class="lmo-strip">
    <div>
        <div class="v">{{ number_format($summary['total_events']) }}</div>
        <div class="l">Total logged actions</div>
    </div>
    <div class="sage">
        <div class="v">{{ number_format($summary['unique_docs_handled']) }}</div>
        <div class="l">Documents handled</div>
    </div>
    <div>
        <div class="v">{{ number_format($summary['active_staff_count']) }}</div>
        <div class="l">Active staff recorded</div>
    </div>
    <div class="{{ $summary['critical_events'] > 0 ? 'hot' : '' }}">
        <div class="v">{{ number_format($summary['critical_events']) }}</div>
        <div class="l">Access denials &amp; critical alerts</div>
    </div>
</div>

<!-- Visual Accountability Graphs Grid -->
<div class="lmo-charts-grid">
    <!-- Chart 1: Actions Breakdown Donut / Pie Chart -->
    <section class="lmo-panel">
        <header>
            <h2>Actions breakdown</h2>
            <span class="muted" style="font-size: 13px;">Share of all logged activity</span>
        </header>
        @if($summary['total_events'] > 0)
            <div class="lmo-donut-wrap">
                <div class="lmo-donut-svg-box">
                    <svg viewBox="0 0 140 140" width="176" height="176" role="img" aria-label="Accountability actions pie chart" id="accountabilityDonut">
                        <circle cx="70" cy="70" r="54" fill="none" stroke="#E2E8DA" stroke-width="20"/>
                        <g transform="rotate(-90 70 70)">
                            @foreach($actionSlices as $slice)
                                <circle
                                    class="lmo-donut-slice"
                                    cx="70"
                                    cy="70"
                                    r="54"
                                    fill="none"
                                    stroke="{{ $slice['color'] }}"
                                    stroke-width="20"
                                    stroke-dasharray="{{ $slice['dash'] }} {{ $circumference }}"
                                    stroke-dashoffset="-{{ $slice['offset'] }}"
                                    data-donut-label="{{ $slice['label'] }}"
                                    data-donut-count="{{ number_format($slice['count']) }}"
                                    data-donut-pct="{{ $slice['percent'] }}%"
                                >
                                    <title>{{ $slice['label'] }}: {{ number_format($slice['count']) }} ({{ $slice['percent'] }}%)</title>
                                </circle>
                            @endforeach
                        </g>
                    </svg>
                    <div class="lmo-donut-center" id="donutCenterDisplay">
                        <strong id="donutCenterValue">{{ number_format($summary['total_events']) }}</strong>
                        <span id="donutCenterLabel">All actions</span>
                    </div>
                </div>

                <div class="lmo-legend">
                    @foreach($actionSlices as $slice)
                        <div class="lmo-legend-item">
                            <span class="lmo-legend-dot" style="background: {{ $slice['color'] }};"></span>
                            <span class="lmo-legend-label">{{ $slice['label'] }}</span>
                            <span class="lmo-legend-val">{{ number_format($slice['count']) }} ({{ $slice['percent'] }}%)</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="lmo-empty">
                <p class="mb-0">No accountability events recorded yet.</p>
            </div>
        @endif
    </section>

    <!-- Chart 2: Staff Accountability Horizontal Bar Chart -->
    <section class="lmo-panel">
        <header>
            <h2>Activity by staff member</h2>
            <span class="muted" style="font-size: 13px;">Click a name to filter log</span>
        </header>
        @if(!empty($staffActivity))
            <div class="lmo-bars">
                @foreach($staffActivity as $st)
                    <a href="{{ route('audit.index', ['user_id' => $st['user_id']]) }}" class="lmo-bar">
                        <span>
                            <strong>{{ $st['name'] }}</strong>
                            <span class="muted" style="font-size: 12.5px;"> &middot; {{ $st['role'] }}</span>
                        </span>
                        <span class="n">{{ number_format($st['count']) }}</span>
                        <span class="t {{ $loop->first ? 'crimson' : '' }}">
                            <i style="width: {{ max(5, $st['width_pct']) }}%;"></i>
                        </span>
                    </a>
                @endforeach
            </div>
        @else
            <div class="lmo-empty">
                <p class="mb-0">No staff activity recorded yet.</p>
            </div>
        @endif
    </section>

    <!-- Chart 3: 14-Day Activity Trend Bar Graph -->
    <section class="lmo-panel">
        <header>
            <h2>Activity over the last 14 days</h2>
            <div class="d-flex align-items-center gap-3" style="font-size: 12.5px;">
                <span class="d-inline-flex align-items-center gap-1 muted">
                    <span class="lmo-legend-dot" style="background: #7F9B7D;"></span> Normal
                </span>
                <span class="d-inline-flex align-items-center gap-1 muted">
                    <span class="lmo-legend-dot" style="background: #8E1C2A;"></span> Alerts
                </span>
            </div>
        </header>
        <div class="lmo-trend-wrap">
            <div class="lmo-trend-bars">
                @foreach($dailyTrend as $day)
                    @php
                        $normalH = $day['total_pct'] > 0 ? max(0, 100 - $day['alert_pct']) : 0;
                    @endphp
                    <div class="lmo-trend-col" title="{{ $day['label'] }}: {{ $day['total'] }} actions ({{ $day['normal'] }} normal, {{ $day['alerts'] }} alerts)">
                        @if($day['total'] > 0)
                            <span class="mono muted" style="font-size: 10px;">{{ $day['total'] }}</span>
                            <div class="lmo-trend-stack" style="height: {{ $day['total_pct'] }}%;">
                                @if($day['alerts'] > 0)
                                    <div style="height: {{ max(14, $day['alert_pct']) }}%; background: #8E1C2A;"></div>
                                @endif
                                @if($day['normal'] > 0)
                                    <div style="height: {{ $normalH }}%; background: #7F9B7D; flex: 1;"></div>
                                @endif
                            </div>
                        @else
                            <div class="lmo-trend-stack" style="height: 4px; background: #E2E8DA;"></div>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="d-grid" style="grid-template-columns: repeat(14, minmax(0, 1fr)); gap: 6px;">
                @foreach($dailyTrend as $day)
                    <div class="lmo-trend-lbl">{{ $loop->even || $loop->last ? $day['short'] : '' }}</div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Chart 4: Compliance & Security Breakdown -->
    <section class="lmo-panel">
        <header>
            <h2>Security &amp; compliance mix</h2>
            <span class="muted" style="font-size: 13px;">{{ number_format($summary['confidential_views']) }} confidential views</span>
        </header>
        <div class="lmo-panel-body">
            <div class="lmo-sev-bar" role="img" aria-label="Security severity distribution">
                @foreach($severityBreakdown as $sev)
                    @if($sev['count'] > 0)
                        <div style="width: {{ max(2, $sev['percent']) }}%; background: {{ $sev['color'] }};"
                             title="{{ $sev['label'] }}: {{ number_format($sev['count']) }} ({{ $sev['percent'] }}%)"></div>
                    @endif
                @endforeach
            </div>

            <div class="d-flex flex-column gap-3 mt-3">
                @foreach($severityBreakdown as $sev)
                    <div class="d-flex align-items-center justify-content-between gap-2 pb-2 border-bottom" style="border-color: var(--line) !important;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="lmo-legend-dot" style="background: {{ $sev['color'] }};"></span>
                            <span class="fw-medium">{{ $sev['label'] }}</span>
                        </div>
                        <div class="mono muted" style="font-size: 13px;">
                            <strong style="color: var(--fg);">{{ number_format($sev['count']) }}</strong> &middot; {{ $sev['percent'] }}%
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</div>

<!-- Filterable Activity Log -->
<form action="{{ route('audit.index') }}" method="GET" class="lmo-filters">
    <label>
        Search log
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Staff, reference, action or IP">
    </label>
    <label>
        Action
        <select name="action">
            <option value="">Any action</option>
            @foreach($distinctActions as $act)
                <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ $act }}</option>
            @endforeach
        </select>
    </label>
    <label>
        Severity
        <select name="severity">
            <option value="">Any</option>
            <option value="info" {{ request('severity') === 'info' ? 'selected' : '' }}>Normal</option>
            <option value="warning" {{ request('severity') === 'warning' ? 'selected' : '' }}>Warning</option>
            <option value="critical" {{ request('severity') === 'critical' ? 'selected' : '' }}>Critical</option>
        </select>
    </label>
    <label>
        Staff member
        <select name="user_id">
            <option value="">Anyone</option>
            @foreach($users as $u)
                <option value="{{ $u->id }}" {{ (int) request('user_id') === $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
            @endforeach
        </select>
    </label>
    <button type="submit" class="lmo-btn sm sage">Apply</button>
    <a href="{{ route('audit.index') }}" class="lmo-btn sm ghost">Clear</a>
    <span class="count">{{ number_format($logs->total()) }} entries</span>
</form>

<section class="lmo-panel">
    <header>
        <h2>Activity log</h2>
        <span class="muted" style="font-size: 13px;">Newest first</span>
    </header>
    @if($logs->isNotEmpty())
        <div class="lmo-rows">
            <div class="lmo-row head arow">
                <span>When</span>
                <span>Staff member</span>
                <span>Action</span>
                <span>Details</span>
            </div>
            @foreach($logs as $log)
                <div class="lmo-row arow">
                    <span class="d">{{ $log->created_at?->format('j M Y, H:i') }}</span>
                    <div>
                        <strong style="font-size: 14px;">{{ $log->user?->name ?? 'System' }}</strong>
                        @if($log->user?->role)
                            <div class="muted" style="font-size: 12.5px;">{{ $log->user->role->name }}</div>
                        @endif
                    </div>
                    <div>
                        <span class="lmo-chip {{ $log->severity === 'critical' ? 'st-received' : ($log->severity === 'warning' ? 'st-warn' : 'st-filed') }}">
                            {{ $log->action }}
                        </span>
                        @if($log->document)
                            <div class="mt-1">
                                <a href="{{ route('documents.show', $log->document) }}" class="mono" style="font-size: 12.5px; text-decoration: none;">
                                    {{ $log->document->official_reference ?: $log->document->internal_reference }} &rarr;
                                </a>
                            </div>
                        @endif
                    </div>
                    <div class="o">
                        <div style="color: var(--fg);">{{ $log->description }}</div>
                        <div style="font-size: 12px;" class="muted mt-1">IP: {{ $log->ip_address ?? '—' }}</div>
                        @if(!empty($log->properties))
                            <details class="mt-1">
                                <summary style="font-size: 12px; color: var(--redink); cursor: pointer;">View change details</summary>
                                <pre class="p-2 rounded mt-1 mb-0 mono" style="font-size: 11.5px; background: var(--bg); border: 1px solid var(--line); color: var(--fg); overflow-x: auto;">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                            </details>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        @if($logs->hasPages())
            <div class="p-3 border-top" style="border-color: var(--line) !important;">
                {{ $logs->links() }}
            </div>
        @endif
    @else
        <div class="lmo-empty">
            <h3>No matching activity</h3>
            <p class="mb-0">Try clearing your filters to view all logged accountability records.</p>
        </div>
    @endif
</section>
@endsection
