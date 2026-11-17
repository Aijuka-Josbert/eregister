<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#142B4A">
    <title>@yield('title', 'LMO Digital Records Management System')</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/svg+xml" href="/images/lmo-icon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/css/civic-institutional.css" rel="stylesheet">
</head>
<body>
    <div class="lmo-top-accent"></div>

    <div class="d-flex">
        <!-- Desktop Institutional Sidebar -->
        <aside class="lmo-sidebar d-none d-lg-flex flex-column justify-content-between">
            <div>
                <div class="lmo-sidebar-brand d-flex align-items-center gap-3">
                    <div class="lmo-brand-badge">LMO</div>
                    <div>
                        <div class="fw-bold text-uppercase" style="font-size: 0.68rem; color: var(--lmo-gold-accent); letter-spacing: 0.08em;">
                            Lord Mayor's Office
                        </div>
                        <div class="fw-bold text-white" style="font-size: 0.95rem;">
                            Digital Records
                        </div>
                    </div>
                </div>

                <nav class="mt-2">
                    <div class="lmo-nav-section">Core Operations</div>
                    <a href="{{ route('dashboard') }}" class="lmo-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2"></i> Executive Dashboard
                    </a>

                    @can('create', \App\Models\Document::class)
                        <a href="{{ route('documents.create') }}" class="lmo-nav-link {{ request()->routeIs('documents.create') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-plus"></i> Register / Scan Record
                        </a>
                    @endcan

                    <a href="{{ route('documents.index') }}" class="lmo-nav-link {{ request()->routeIs('documents.index', 'documents.show', 'documents.edit') ? 'active' : '' }}">
                        <i class="bi bi-folder2-open"></i> Document Register
                    </a>

                    <a href="{{ route('documents.index', ['document_type' => 'invitation']) }}" class="lmo-nav-link {{ request('document_type') === 'invitation' ? 'active' : '' }}">
                        <i class="bi bi-calendar2-event"></i> Invitations Register
                    </a>

                    <a href="{{ route('archives.index') }}" class="lmo-nav-link {{ request()->routeIs('archives.*') ? 'active' : '' }}">
                        <i class="bi bi-archive"></i> Digital Archive Vault
                    </a>

                    <div class="lmo-nav-section">Governance &amp; Oversight</div>

                    @if(auth()->user()->hasPermission('reports.view'))
                        <a href="{{ route('reports.index') }}" class="lmo-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                            <i class="bi bi-bar-chart-line"></i> Reports &amp; Backups
                        </a>
                    @endif

                    @can('viewAny', \App\Models\DocumentActivityLog::class)
                        <a href="{{ route('audit.index') }}" class="lmo-nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}">
                            <i class="bi bi-shield-check"></i> Accountability &amp; Audit
                        </a>
                    @endcan

                    <a href="{{ route('categories.index') }}" class="lmo-nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                        <i class="bi bi-tags"></i> Categories &amp; Retention
                    </a>

                    <a href="{{ route('offices.index') }}" class="lmo-nav-link {{ request()->routeIs('offices.*') ? 'active' : '' }}">
                        <i class="bi bi-building"></i> Offices &amp; Departments
                    </a>

                    @can('viewAny', \App\Models\User::class)
                        <a href="{{ route('users.index') }}" class="lmo-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <i class="bi bi-people"></i> Staff &amp; Permissions
                        </a>
                    @endcan
                </nav>
            </div>

            <div class="p-3 border-top border-secondary border-opacity-25 small">
                <div class="d-flex align-items-center justify-content-between text-white-50 mb-2">
                    <span><i class="bi bi-building-check me-1"></i> {{ auth()->user()->office?->code ?? 'LMO' }}</span>
                    <span class="badge bg-secondary bg-opacity-50 text-white">{{ auth()->user()->role?->name }}</span>
                </div>
                <div class="text-white fw-semibold text-truncate">{{ auth()->user()->name }}</div>
                <div class="text-white-50 text-truncate" style="font-size: 0.75rem;">{{ auth()->user()->email }}</div>
            </div>
        </aside>

        <!-- Main Viewport -->
        <div class="flex-grow-1 d-flex flex-column min-vh-100" style="min-width: 0;">
            <!-- Top Navigation & Global Search Header -->
            <header class="lmo-topbar d-flex align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebarOffcanvas">
                        <i class="bi bi-list fs-5"></i>
                    </button>
                    <form action="{{ route('documents.index') }}" method="GET" class="d-flex align-items-center" style="min-width: min(460px, 58vw);">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0 text-muted">
                                <i class="bi bi-search"></i>
                            </span>
                            <input
                                type="search"
                                name="q"
                                value="{{ request('q') }}"
                                class="form-control border-start-0"
                                placeholder="Global search: Reference No, Subject, Sender, Organization, or OCR text..."
                                aria-label="Global document search"
                            >
                            <button class="btn btn-lmo-primary px-3" type="submit">Search</button>
                        </div>
                    </form>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button id="pwaInstallBtn" type="button" class="btn btn-sm btn-outline-primary d-none">
                        <i class="bi bi-phone me-1"></i> Install App
                    </button>

                    @can('create', \App\Models\Document::class)
                        <a href="{{ route('documents.create') }}" class="btn btn-sm btn-lmo-primary d-none d-sm-inline-flex align-items-center gap-1">
                            <i class="bi bi-camera"></i> <span>Scan / Register</span>
                        </a>
                    @endcan

                    <div class="dropdown">
                        <button class="btn btn-sm btn-light border dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-badge text-primary"></i>
                            <span class="d-none d-md-inline fw-medium">{{ auth()->user()->name }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li class="px-3 py-2 border-bottom">
                                <div class="fw-bold small">{{ auth()->user()->name }}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    {{ auth()->user()->role?->name }} &bull; {{ auth()->user()->office?->name ?? 'Lord Mayor\'s Office' }}
                                </div>
                            </li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger small py-2">
                                        <i class="bi bi-box-arrow-right me-2"></i> Sign Out Securely
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Flash Notifications & Validation Alerts -->
            <div class="container-fluid px-3 px-lg-4 pt-3">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 py-2 shadow-sm" role="alert">
                        <i class="bi bi-check-circle-fill"></i>
                        <div>{{ session('success') }}</div>
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 py-2 shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <div>{{ session('error') }}</div>
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show py-2 shadow-sm" role="alert">
                        <div class="fw-bold small mb-1"><i class="bi bi-exclamation-octagon me-1"></i> Please correct the following errors:</div>
                        <ul class="mb-0 small ps-3">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            </div>

            <!-- Page Content -->
            <main class="container-fluid px-3 px-lg-4 py-3 flex-grow-1">
                @yield('content')
            </main>

            <footer class="py-3 px-4 bg-white border-top d-flex flex-wrap justify-content-between align-items-center small text-muted mt-auto">
                <div>
                    <strong>Lord Mayor's Office (LMO)</strong> &bull; Digital Records Management System
                </div>
                <div>
                    <i class="bi bi-shield-lock text-success me-1"></i> Private Storage &amp; Immutable Audit Active
                </div>
            </footer>
        </div>
    </div>

    <!-- Mobile Offcanvas Navigation Drawer -->
    <div class="offcanvas offcanvas-start text-white" tabindex="-1" id="mobileSidebarOffcanvas" style="background-color: var(--lmo-deep-navy); width: 280px;">
        <div class="offcanvas-header border-bottom border-secondary border-opacity-25">
            <div class="d-flex align-items-center gap-2">
                <div class="lmo-brand-badge">LMO</div>
                <h5 class="offcanvas-title fs-6 fw-bold mb-0">LMO Digital Records</h5>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body p-0">
            <nav class="py-2">
                <a href="{{ route('dashboard') }}" class="lmo-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Executive Dashboard
                </a>
                @can('create', \App\Models\Document::class)
                    <a href="{{ route('documents.create') }}" class="lmo-nav-link {{ request()->routeIs('documents.create') ? 'active' : '' }}">
                        <i class="bi bi-camera"></i> Scan / Register Record
                    </a>
                @endcan
                <a href="{{ route('documents.index') }}" class="lmo-nav-link {{ request()->routeIs('documents.index') ? 'active' : '' }}">
                    <i class="bi bi-folder2-open"></i> Document Register
                </a>
                <a href="{{ route('documents.index', ['document_type' => 'invitation']) }}" class="lmo-nav-link">
                    <i class="bi bi-calendar2-event"></i> Invitations
                </a>
                <a href="{{ route('archives.index') }}" class="lmo-nav-link {{ request()->routeIs('archives.*') ? 'active' : '' }}">
                    <i class="bi bi-archive"></i> Digital Archive Vault
                </a>
                @if(auth()->user()->hasPermission('reports.view'))
                    <a href="{{ route('reports.index') }}" class="lmo-nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                        <i class="bi bi-bar-chart-line"></i> Reports &amp; Backups
                    </a>
                @endif
                @can('viewAny', \App\Models\DocumentActivityLog::class)
                    <a href="{{ route('audit.index') }}" class="lmo-nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}">
                        <i class="bi bi-shield-check"></i> Audit Trail
                    </a>
                @endcan
                <a href="{{ route('categories.index') }}" class="lmo-nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <i class="bi bi-tags"></i> Categories
                </a>
                <a href="{{ route('offices.index') }}" class="lmo-nav-link {{ request()->routeIs('offices.*') ? 'active' : '' }}">
                    <i class="bi bi-building"></i> Offices &amp; Units
                </a>
                @can('viewAny', \App\Models\User::class)
                    <a href="{{ route('users.index') }}" class="lmo-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="bi bi-people"></i> Staff Accounts
                    </a>
                @endcan
            </nav>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/drms-app.js"></script>
    @stack('scripts')
</body>
</html>
