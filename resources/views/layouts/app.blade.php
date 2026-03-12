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

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <div class="layout-wrapper">

        <aside class="sidebar" id="sidebar">
            <button class="close-sidebar-btn" onclick="toggleSidebar()">
                <i class="fas fa-times"></i>
            </button>

            <div class="sidebar-header text-center"
                style="padding: 20px 10px; border-bottom: 1px solid rgba(255,255,255,0.1); position: relative;">

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

            <nav class="sidebar-nav" style="padding-top: 15px;">

                <a href="/dashboard" class="sidebar-link {{ request()->is('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-chart-line" style="width: 24px;"></i> Dashboard
                </a>

                @php
                    $isCrmActive =
                        request()->is('leads*') ||
                        request()->is('create') ||
                        request()->is('show') ||
                        request()->is('followup*') ||
                        request()->routeIs('hot_prospek.*');
                @endphp
                <div class="sidebar-dropdown">
                    <a href="javascript:void(0)" class="sidebar-link dropdown-toggle {{ $isCrmActive ? 'active' : '' }}"
                        onclick="toggleDropdown('crmMenu', this)">
                        <i class="fas fa-handshake" style="width: 24px;"></i> CRM
                        <i class="fas fa-chevron-down dropdown-icon"
                            style="margin-left: auto; transition: transform 0.3s; {{ $isCrmActive ? 'transform: rotate(180deg);' : '' }}"></i>
                    </a>
                    <div id="crmMenu" class="dropdown-content"
                        style="display: {{ $isCrmActive ? 'block' : 'none' }}; border-radius: 0 0 8px 8px; margin-bottom: 5px;">
                        <a href="/leads"
                            class="sidebar-link sub-link {{ request()->is('leads*') || request()->is('create') || request()->is('show') ? 'active' : '' }}"
                            style="padding-left: 3.5rem; font-size: 0.9rem;">
                            <i class="fas fa-database" style="width: 20px; font-size: 0.85rem;"></i> Database Leads
                        </a>
                        <a href="/followup"
                            class="sidebar-link sub-link {{ request()->is('followup*') ? 'active' : '' }}"
                            style="padding-left: 3.5rem; font-size: 0.9rem;">
                            <i class="fas fa-calendar-alt" style="width: 20px; font-size: 0.85rem;"></i> Jadwal Follow
                            Up
                        </a>
                        <a href="{{ route('hot_prospek.index') }}"
                            class="sidebar-link sub-link {{ request()->routeIs('hot_prospek.*') ? 'active' : '' }}"
                            style="padding-left: 3.5rem; font-size: 0.9rem;">
                            <i class="fas fa-fire" style="width: 20px; font-size: 0.85rem;"></i> Hot Prospek
                        </a>
                    </div>
                </div>

                @php
                    $isKeuanganActive = request()->routeIs('coa.*'); // Nanti bisa ditambah || request()->routeIs('jurnal.*') dll
                @endphp
                <div class="sidebar-dropdown">
                    <a href="javascript:void(0)"
                        class="sidebar-link dropdown-toggle {{ $isKeuanganActive ? 'active' : '' }}"
                        onclick="toggleDropdown('keuanganMenu', this)">
                        <i class="fas fa-wallet" style="width: 24px;"></i> Keuangan
                        <i class="fas fa-chevron-down dropdown-icon"
                            style="margin-left: auto; transition: transform 0.3s; {{ $isKeuanganActive ? 'transform: rotate(180deg);' : '' }}"></i>
                    </a>
                    <div id="keuanganMenu" class="dropdown-content"
                        style="display: {{ $isKeuanganActive ? 'block' : 'none' }};  border-radius: 0 0 8px 8px; margin-bottom: 5px;">
                        <a href="{{ route('coa.index') }}"
                            class="sidebar-link sub-link {{ request()->routeIs('coa.*') ? 'active' : '' }}"
                            style="padding-left: 3.5rem; font-size: 0.9rem;">
                            <i class="fas fa-book" style="width: 20px; font-size: 0.85rem;"></i> Chart of Accounts
                        </a>
                    </div>
                </div>

                @php
                    $isSettingsActive =
                        request()->routeIs('tipe_rumah.*') ||
                        request()->is('users*') ||
                        request()->routeIs('projects.edit');
                @endphp
                <div class="sidebar-dropdown">
                    <a href="javascript:void(0)"
                        class="sidebar-link dropdown-toggle {{ $isSettingsActive ? 'active' : '' }}"
                        onclick="toggleDropdown('settingsMenu', this)">
                        <i class="fas fa-cog" style="width: 24px;"></i> Pengaturan
                        <i class="fas fa-chevron-down dropdown-icon"
                            style="margin-left: auto; transition: transform 0.3s; {{ $isSettingsActive ? 'transform: rotate(180deg);' : '' }}"></i>
                    </a>
                    <div id="settingsMenu" class="dropdown-content"
                        style="display: {{ $isSettingsActive ? 'block' : 'none' }};  border-radius: 0 0 8px 8px; margin-bottom: 5px;">

                        @if (auth()->user()->role === 'Admin' || auth()->user()->role === 'Super_Admin')
                            <a href="{{ route('tipe_rumah.index') }}"
                                class="sidebar-link sub-link {{ request()->routeIs('tipe_rumah.*') ? 'active' : '' }}"
                                style="padding-left: 3.5rem; font-size: 0.9rem;">
                                <i class="fas fa-home" style="width: 20px; font-size: 0.85rem;"></i> Tipe Rumah
                            </a>
                        @endif

                        @if (auth()->check())
                            <a href="{{ route('index') }}"
                                class="sidebar-link sub-link {{ request()->is('users*') ? 'active' : '' }}"
                                style="padding-left: 3.5rem; font-size: 0.9rem;">
                                <i class="fas fa-users-cog" style="width: 20px; font-size: 0.85rem;"></i> Manajemen User
                            </a>
                        @endif

                        @if (auth()->user()->role === 'Super_Admin')
                            <a href="{{ route('projects.edit', session('active_project_id')) }}"
                                class="sidebar-link sub-link {{ request()->routeIs('projects.edit') ? 'active' : '' }}"
                                style="padding-left: 3.5rem; font-size: 0.9rem;">
                                <i class="fas fa-pen-to-square" style="width: 20px; font-size: 0.85rem;"></i> Edit
                                Proyek
                            </a>
                        @endif
                    </div>
                </div>

            </nav>

            @if (in_array(Auth::user()->role, ['Super_Admin']))
                <div style="padding: 1.5rem; border-top: 1px solid rgba(255,255,255,0.1); margin-top: auto;">
                    <a href="{{ route('projects.exit') }}" class="sidebar-link sidebar-footer-link text-center"
                        style="font-size: 0.875rem;">
                        <i class="fas fa-sign-out-alt" style="margin-right: 5px;"></i> Keluar Proyek
                    </a>
                </div>
            @endif
        </aside>
        <div class="main-content">
            <header class="navbar">
                <div class="nav-left">
                    <button class="mobile-toggle" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div class="nav-brand">@yield('title')</div>
                </div>

                <div class="nav-user">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <strong style="color: var(--text-on-main); font-size: 0.95rem;">
                            {{ Auth::user()->name }}
                        </strong>
                        <span class="nav-badge" style="margin: 0;">{{ Auth::user()->role }}</span>
                    </div>

                    <div class="nav-separator"></div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-logout" title="Keluar Aplikasi">
                            <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
                        </button>
                    </form>
                </div>
            </header>

            <main class="container">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const body = document.body;

            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');

            if (sidebar.classList.contains('active')) {
                body.classList.add('no-scroll');
            } else {
                body.classList.remove('no-scroll');
            }
        }
    </script>
    <script>
        function toggleDropdown(menuId, element) {
            const menu = document.getElementById(menuId);
            const icon = element.querySelector('.dropdown-icon');

            const isClosed = menu.style.display === "none" || menu.style.display === "";

            document.querySelectorAll('.dropdown-content').forEach(el => el.style.display = 'none');
            document.querySelectorAll('.dropdown-icon').forEach(el => el.style.transform = 'rotate(0deg)');

            if (isClosed) {
                menu.style.display = "block";
                icon.style.transform = "rotate(180deg)";
            } else {
                menu.style.display = "none";
                icon.style.transform = "rotate(0deg)";
            }
        }
    </script>
    @stack('scripts')
</body>

</html>
