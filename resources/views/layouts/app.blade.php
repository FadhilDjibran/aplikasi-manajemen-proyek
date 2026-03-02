<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="{{ asset('favico.png') }}">
</head>

<body>
    <div class="layout-wrapper">

        <aside class="sidebar">
            <div class="sidebar-header text-center"
                style="padding: 20px 10px; border-bottom: 1px solid rgba(255,255,255,0.1);">

                @if (session('active_project_logo'))
                    <img src="{{ asset('storage/' . session('active_project_logo')) }}" alt="Logo Project"
                        style="max-width: 100%; height: 90px; object-fit: contain;">
                @else
                    <h4 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: white;">
                        <i class="fas fa-building" style="margin-right: 8px;"></i>
                        {{ session('active_project_name') ?? 'Projek' }}
                    </h4>
                @endif

            </div>

            <nav class="sidebar-nav">
                <a href="/dashboard" class="sidebar-link {{ request()->is('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-line" style="width: 24px;"></i> Dashboard Utama
                </a>

                <a href="/leads"
                    class="sidebar-link {{ request()->is('leads*') || request()->is('create') || request()->is('show') ? 'active' : '' }}">
                    <i class="fas fa-database" style="width: 24px;"></i> Database Leads
                </a>

                <a href="{{ route('hot_prospek.index') }}"
                    class="sidebar-link {{ request()->routeIs('hot_prospek.*') ? 'active' : '' }}">
                    <i class="fas fa-fire" style="width: 24px;"></i> Hot Prospek
                </a>

                <a href="/followup" class="sidebar-link {{ request()->is('followup*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt" style="width: 24px;"></i> Jadwal Follow Up
                </a>

                @if (auth()->check() && in_array(auth()->user()->role, ['Admin', 'Super_Admin']))
                    <a href="{{ route('index') }}" class="sidebar-link {{ request()->is('users*') ? 'active' : '' }}">
                        <i class="fas fa-users-cog" style="width: 24px;"></i> Manajemen User
                    </a>
                @endif

                @if (auth()->user()->role === 'Admin' || auth()->user()->role === 'Super_Admin')
                    <a href="{{ route('tipe_rumah.index') }}"
                        class="sidebar-link {{ request()->routeIs('tipe_rumah.*') ? 'active' : '' }}">
                        <i class="fas fa-home" style="width: 24px;"></i> <span>Edit Tipe Rumah</span>
                    </a>
                @endif

                @if (auth()->user()->role === 'Admin' || auth()->user()->role === 'Super_Admin')
                    <a href="{{ route('projects.edit', session('active_project_id')) }}"
                        class="sidebar-link {{ request()->routeIs('projects.edit') ? 'active' : '' }}">
                        <i class="fas fa-pen-to-square" style="width: 24px;"></i> <span>Edit Proyek</span>
                    </a>
                @endif
            </nav>

            @if (in_array(Auth::user()->role, ['Super_Admin']))
                <div style="padding: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1);">
                    <a href="{{ route('projects.exit') }}" class="sidebar-link sidebar-footer-link text-center"
                        style="font-size: 0.875rem;">
                        &larr; Keluar dari Proyek
                    </a>
                </div>
            @endif
        </aside>

        <div class="main-content">
            <header class="navbar">
                <div class="nav-brand">@yield('title')</div>

                <div class="nav-user">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <strong style="color: #ffffff; font-size: 0.95rem;">
                            {{ Auth::user()->name }}
                        </strong>
                        <span class="nav-badge" style="margin: 0;">{{ Auth::user()->role }}</span>
                    </div>

                    <div class="nav-separator"></div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-logout" title="Keluar Aplikasi">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </div>
            </header>

            <main class="container">
                @yield('content')
            </main>
        </div>
    </div>
</body>

</html>
