<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-base-url" content="{{ rtrim(url('/'), '/') }}">
    <meta name="theme-color" content="#6A1421">
    <title>@yield('title', "LMO Records Registry")</title>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/lmo-icon.svg') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@500&family=Libre+Franklin:wght@500;600;700&family=Source+Sans+3:wght@400;500;600&display=swap">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/civic-institutional.css') }}?v=3" rel="stylesheet">
</head>
<body>
@php
    $user = auth()->user();
    $initials = collect(explode(' ', trim((string) $user->name)))
        ->filter()
        ->take(2)
        ->map(fn ($w) => strtoupper(substr($w, 0, 1)))
        ->implode('');

    $isCategoriesContext = request()->routeIs('categories.*', 'offices.*')
        || (request()->routeIs('documents.index') && (request()->filled('category_id') || request()->filled('office_id') || request()->filled('department_id')));
@endphp

<div class="lmo-app-shell">
    <!-- Deep Burgundy Institutional Sidebar -->
    <aside class="lmo-sidebar" aria-label="Main navigation">
        <a href="{{ route('dashboard') }}" class="lmo-sidebar-brand">
            <x-lmo-crest :size="44" />
            <div>
                <b>Lord Mayor's Office</b>
                <span>Records Registry</span>
            </div>
        </a>

        <nav class="lmo-nav" aria-label="Sections">
            <a href="{{ route('dashboard') }}" class="lmo-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-house"></i>
                <span>Dashboard</span>
            </a>

            @can('create', \App\Models\Document::class)
                <a href="{{ route('documents.create') }}" class="lmo-nav-link {{ request()->routeIs('documents.create') ? 'active' : '' }}">
                    <i class="bi bi-upc-scan"></i>
                    <span>Register a document</span>
                </a>
            @endcan

            <a href="{{ route('documents.index') }}" class="lmo-nav-link {{ request()->routeIs('documents.index', 'documents.show', 'documents.edit') && !request()->filled('category_id') && !request()->filled('office_id') ? 'active' : '' }}">
                <i class="bi bi-folder2"></i>
                <span>All records</span>
                <span class="n">{{ $navCounts['active'] ?? 0 }}</span>
            </a>

            <a href="{{ route('archives.index') }}" class="lmo-nav-link {{ request()->routeIs('archives.*') ? 'active' : '' }}">
                <i class="bi bi-archive"></i>
                <span>Archive</span>
                <span class="n">{{ $navCounts['archived'] ?? 0 }}</span>
            </a>

            @can('viewAny', \App\Models\DocumentActivityLog::class)
                <a href="{{ route('audit.index') }}" class="lmo-nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}">
                    <i class="bi bi-pie-chart"></i>
                    <span>Accountability</span>
                </a>
            @endcan

            <!-- Single Grouped Categories Accordion (includes Document Categories + Offices & Departments) -->
            <details class="lmo-nav-group {{ request()->routeIs('categories.*', 'offices.*') ? 'is-active' : '' }}" {{ $isCategoriesContext ? 'open' : '' }}>
                <summary>
                    <i class="bi bi-collection"></i>
                    <span>Categories</span>
                    <i class="bi bi-chevron-right chev"></i>
                </summary>
                <div class="lmo-nav-sublist">
                    <a href="{{ route('categories.index', ['tab' => 'categories']) }}"
                       class="lmo-nav-sublink {{ request()->routeIs('categories.*') && request('tab', 'categories') !== 'offices' ? 'active' : '' }}">
                        <i class="bi bi-tags"></i>
                        <span>Document categories</span>
                        <span class="n">{{ isset($sidebarCategories) ? $sidebarCategories->count() : 0 }}</span>
                    </a>
                    <a href="{{ route('categories.index', ['tab' => 'offices']) }}"
                       class="lmo-nav-sublink {{ (request()->routeIs('categories.*') && request('tab') === 'offices') || request()->routeIs('offices.*') ? 'active' : '' }}">
                        <i class="bi bi-building"></i>
                        <span>Offices &amp; departments</span>
                        <span class="n">{{ isset($sidebarOffices) ? $sidebarOffices->count() : 0 }}</span>
                    </a>

                    @if(isset($sidebarCategories) && $sidebarCategories->isNotEmpty())
                        <div class="lmo-nav-subsep">Filter register</div>
                        @foreach($sidebarCategories as $cat)
                            <a href="{{ route('documents.index', ['category_id' => $cat->id]) }}"
                               class="lmo-nav-sublink {{ request()->routeIs('documents.index') && (int) request('category_id') === $cat->id ? 'active' : '' }}">
                                <span class="text-truncate">{{ $cat->name }}</span>
                                <span class="n">{{ $cat->documents_count }}</span>
                            </a>
                        @endforeach
                    @endif
                </div>
            </details>

            @if($user->hasPermission('reports.view'))
                <a href="{{ route('reports.index') }}" class="lmo-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    <span>Reports &amp; backups</span>
                </a>
            @endif

            @can('viewAny', \App\Models\User::class)
                <a href="{{ route('users.index') }}" class="lmo-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>Staff &amp; access</span>
                </a>
            @endcan
        </nav>

        <div class="lmo-sidefoot">
            <div class="lmo-me">
                <div class="lmo-avatar">{{ $initials ?: 'LM' }}</div>
                <div style="min-width: 0;">
                    <b class="text-truncate">{{ $user->name }}</b>
                    <span class="text-truncate">{{ $user->role?->name }} &middot; {{ $user->office?->code ?? 'LMO' }}</span>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="lmo-signout">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Sign out</span>
                </button>
            </form>
        </div>
    </aside>

    <div class="lmo-scrim" id="mobileNavScrim"></div>

    <!-- Main Workspace -->
    <div class="lmo-main">
        <header class="lmo-topbar">
            <button type="button" class="lmo-btn sm menu-btn" id="mobileMenuBtn" aria-label="Open menu">
                <i class="bi bi-list fs-5"></i>
            </button>

            <form action="{{ route('documents.index') }}" method="GET" class="lmo-search-box">
                <i class="bi bi-search search-ico"></i>
                <input
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    autocomplete="off"
                    placeholder="Search by name, organisation, reference, subject or text inside scans"
                    aria-label="Search all records"
                >
            </form>

            <button id="pwaInstallBtn" type="button" class="lmo-btn sm d-none">
                <i class="bi bi-phone"></i> <span>Install</span>
            </button>

            @can('create', \App\Models\Document::class)
                <a href="{{ route('documents.create') }}" class="btn-lmo-primary newbtn ms-auto">
                    <i class="bi bi-plus-lg"></i>
                    <span>Register document</span>
                </a>
            @endcan
        </header>

        <main class="lmo-view" tabindex="-1">
            @if(session('success'))
                <div class="lmo-alert ok" role="alert">
                    <i class="bi bi-check-circle-fill mt-1"></i>
                    <div class="flex-grow-1">{{ session('success') }}</div>
                </div>
            @endif

            @if(session('error'))
                <div class="lmo-alert bad" role="alert">
                    <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                    <div class="flex-grow-1">{{ session('error') }}</div>
                </div>
            @endif

            @if($errors->any())
                <div class="lmo-alert bad" role="alert">
                    <i class="bi bi-exclamation-octagon-fill mt-1"></i>
                    <div class="flex-grow-1">
                        <div class="fw-semibold mb-1">Please review the following:</div>
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/drms-app.js') }}?v=3"></script>
@stack('scripts')
</body>
</html>
