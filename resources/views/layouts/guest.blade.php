<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="app-base-url" content="{{ rtrim(url('/'), '/') }}">
    <meta name="theme-color" content="#6A1421">
    <title>@yield('title', "Lord Mayor's Office — Records Registry")</title>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/lmo-icon.svg') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@500&family=Libre+Franklin:wght@500;600;700&family=Source+Sans+3:wght@400;500;600&display=swap">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/civic-institutional.css') }}?v=3" rel="stylesheet">
</head>
<body>
    @yield('content')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/drms-app.js') }}?v=3"></script>
</body>
</html>
