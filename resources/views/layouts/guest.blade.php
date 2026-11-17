<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#142B4A">
    <title>@yield('title', 'Lord Mayor\'s Office — Digital Records Management System')</title>
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" type="image/svg+xml" href="/images/lmo-icon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/css/civic-institutional.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
    <div class="lmo-top-accent"></div>

    <header class="py-3 bg-white border-bottom shadow-sm">
        <div class="container d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <div class="lmo-brand-badge">LMO</div>
                <div>
                    <div class="fw-bold text-uppercase small" style="color: var(--lmo-gold-accent); letter-spacing: 0.08em;">
                        Office of the Lord Mayor
                    </div>
                    <h1 class="h6 mb-0 fw-bold" style="color: var(--lmo-deep-navy);">
                        Digital Records Management System (DRMS)
                    </h1>
                </div>
            </div>
            <span class="badge rounded-pill px-3 py-2" style="background-color: var(--lmo-light-blue); color: var(--lmo-deep-navy);">
                <i class="bi bi-shield-lock-fill me-1"></i> Official Institutional Portal
            </span>
        </div>
    </header>

    <main class="flex-grow-1 d-flex align-items-center py-5">
        <div class="container">
            @yield('content')
        </div>
    </main>

    <footer class="py-3 bg-white border-top text-center small text-muted">
        <div class="container">
            Lord Mayor's Office &bull; Digital Records &amp; Archival System &bull; Authorized Personnel Only
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/drms-app.js"></script>
</body>
</html>
