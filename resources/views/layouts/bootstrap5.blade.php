<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Supply Chain Intelligence'))</title>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap5-shell.css') }}">
    <link rel="stylesheet" href="{{ asset('css/supply-chain.css') }}">
    @yield('css')
</head>
<body class="sc-app">
<div class="wrapper sc-bs5-wrapper">
    <aside class="main-sidebar sc-sidebar" id="appSidebar">
        <a class="brand-link sc-brand" href="{{ route('dashboard') }}">
            <img src="{{ asset('images/supply-chain-mark.svg') }}" alt="Supply Chain Intelligence">
            <span><b>Supply Chain</b><small>Intelligence</small></span>
        </a>
        <nav class="sidebar-nav" aria-label="Main navigation">
            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="fas fa-tachometer-alt"></i><span>Dashboard</span></a>
            <div class="nav-header">MASTER DATA</div>
            <a class="nav-link {{ request()->routeIs('countries.*') ? 'active' : '' }}" href="{{ route('countries.index') }}"><i class="fas fa-globe"></i><span>Negara</span></a>
            <a class="nav-link {{ request()->routeIs('ports.*') ? 'active' : '' }}" href="{{ route('ports.index') }}"><i class="fas fa-anchor"></i><span>Pelabuhan</span></a>
            <a class="nav-link {{ request()->routeIs('shipping-routes.*') ? 'active' : '' }}" href="{{ route('shipping-routes.index') }}"><i class="fas fa-route"></i><span>Rute Pengiriman</span></a>
            <a class="nav-link {{ request()->routeIs('watchlists.*') ? 'active' : '' }}" href="{{ route('watchlists.index') }}"><i class="fas fa-star"></i><span>Monitoring Favorit</span></a>
            <div class="nav-header">API DATA</div>
            <a class="nav-link {{ request()->routeIs('weather.index', 'weather.show') ? 'active' : '' }}" href="{{ route('weather.index') }}"><i class="fas fa-cloud-sun"></i><span>Weather</span></a>
            <a class="nav-link {{ request()->routeIs('weather.map') ? 'active' : '' }}" href="{{ route('weather.map') }}"><i class="fas fa-map-marked-alt"></i><span>Weather Map</span></a>
            <a class="nav-link {{ request()->routeIs('economy.*') ? 'active' : '' }}" href="{{ route('economy.index') }}"><i class="fas fa-chart-line"></i><span>Economy</span></a>
            <a class="nav-link {{ request()->routeIs('exchange.*') ? 'active' : '' }}" href="{{ route('exchange.index') }}"><i class="fas fa-money-bill-wave"></i><span>Exchange Rate</span></a>
            <a class="nav-link {{ request()->routeIs('news.*') ? 'active' : '' }}" href="{{ route('news.index') }}"><i class="fas fa-newspaper"></i><span>News</span></a>
            <div class="nav-header">ANALYSIS</div>
            <a class="nav-link {{ request()->routeIs('risk.*') ? 'active' : '' }}" href="{{ route('risk.index') }}"><i class="fas fa-shield-alt"></i><span>Risk Score</span></a>
            <a class="nav-link {{ request()->routeIs('map.*') ? 'active' : '' }}" href="{{ route('map.index') }}"><i class="fas fa-map-marked-alt"></i><span>Unified Global Map</span></a>
            <a class="nav-link {{ request()->routeIs('comparison.*') ? 'active' : '' }}" href="{{ route('comparison.index') }}"><i class="fas fa-balance-scale"></i><span>Perbandingan Negara</span></a>
            @can('admin')
            <div class="nav-header">ADMIN</div>
            <a class="nav-link {{ request()->routeIs('admin.ports.*') ? 'active' : '' }}" href="{{ route('admin.ports.index') }}"><i class="fas fa-database"></i><span>Port Dataset</span></a>
            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><i class="fas fa-users-cog"></i><span>Users</span></a>
            <a class="nav-link {{ request()->routeIs('admin.articles.*') ? 'active' : '' }}" href="{{ route('admin.articles.index') }}"><i class="fas fa-file-alt"></i><span>Articles</span></a>
            <a class="nav-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}" href="{{ route('admin.settings') }}"><i class="fas fa-cog"></i><span>Settings</span></a>
            @endcan
        </nav>
    </aside>

    <header class="main-header sc-topnav">
        <button class="sidebar-toggle" id="sidebarToggle" type="button" aria-controls="appSidebar" aria-expanded="true"><i class="fas fa-bars"></i></button>
        <button class="sc-command-trigger" id="commandTrigger" type="button"><i class="fas fa-search"></i><span>Cari fitur...</span><kbd>Ctrl K</kbd></button>
        <div class="sc-realtime-status" id="realtimeStatus" data-endpoint="{{ route('api.overview') }}">
            <span class="sc-live-dot"></span>
            <span><b>Live</b><small id="realtimeUpdated">Menghubungkan...</small></span>
            <button id="refreshRealtime" type="button" aria-label="Perbarui data"><i class="fas fa-sync-alt"></i></button>
        </div>
        <div class="account-nav ms-auto">
            @guest
                <a class="top-action" href="{{ route('login') }}"><i class="fas fa-shield-alt"></i><span>Admin Login</span></a>
            @else
                @can('admin')<a class="top-action" href="{{ route('admin.users.index') }}"><i class="fas fa-user-shield"></i><span>Admin Panel</span></a>@endcan
                <span class="account-identity"><i class="fas fa-user-circle"></i><span><b>{{ Auth::user()->name }}</b><small>{{ Auth::user()->role }}</small></span></span>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="logout-action" type="submit"><i class="fas fa-sign-out-alt"></i><span>Logout</span></button></form>
            @endguest
        </div>
    </header>

    <main class="content-wrapper">
        @hasSection('content_header')<section class="content-header"><div class="container-fluid">@yield('content_header')</div></section>@endif
        <section class="content"><div class="container-fluid">@yield('content')</div></section>
    </main>
</div>
<div class="sc-command-backdrop" id="commandPalette" hidden>
    <div class="sc-command-panel" role="dialog" aria-modal="true" aria-label="Pencarian fitur">
        <div class="sc-command-search"><i class="fas fa-search"></i><input id="commandSearch" type="search" placeholder="Ketik nama halaman atau fitur..." autocomplete="off"><button id="commandClose" type="button" aria-label="Tutup">&times;</button></div>
        <div class="sc-command-results" id="commandResults"></div>
        <div class="sc-command-help"><span><kbd>↑</kbd><kbd>↓</kbd> pilih</span><span><kbd>Enter</kbd> buka</span><span><kbd>Esc</kbd> tutup</span></div>
    </div>
</div>
<div class="sc-toast-stack" id="toastStack" aria-live="polite"></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios@1.11.0/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.0/dist/chart.umd.min.js"></script>
<script>
window.axios && (window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest');
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
if (window.axios && csrfToken) window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
document.getElementById('sidebarToggle')?.addEventListener('click', () => {
    document.body.classList.toggle('sidebar-collapsed');
    document.getElementById('sidebarToggle').setAttribute('aria-expanded', String(!document.body.classList.contains('sidebar-collapsed')));
});
</script>
<script src="{{ asset('js/supply-chain-runtime.js') }}"></script>
@yield('js')
</body>
</html>
