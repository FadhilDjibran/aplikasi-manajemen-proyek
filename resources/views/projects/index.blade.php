@extends('layouts.plain')

@section('title', 'Manajemen Proyek')

@section('content')
    <nav class="navbar mobile-responsive" style="padding: 2rem;">
        <div class="nav-brand" style="color: var(--accent); font-size: 1.5rem;">
            <i class="fas fa-layer-group"></i> Aplikasi Manajemen Proyek
        </div>

        <div class="nav-user">
            <div style="display: flex; align-items: center; gap: 0.75rem;">
                <strong style="color: #ffffff; font-size: 1rem;">
                    {{ Auth::user()->name }}
                </strong>
                <span class="nav-badge" style="margin: 0;">{{ Auth::user()->role }}</span>
            </div>

            <div class="nav-separator"></div>

            <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
                </button>
            </form>
        </div>
    </nav>

    <div class="container mobile-responsive" style="max-width: 1100px; padding-bottom: 5rem;">

        <div class="section-header mobile-header"
            style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">

            <div style="width: 100%;">
                <h2 style="margin: 0; font-weight: 700;">Proyek Anda</h2>
                <p style="margin: 5px 0 0 0; opacity: 0.8;">Pilih salah satu proyek untuk mulai mengelola.</p>
            </div>

            <div class="header-actions" style="display: flex; gap: 10px;">
                @if (Auth::check() && in_array(Auth::user()->role, ['Super_Admin']))
                    <a href="{{ route('index') }}" class="btn btn-primary"
                        style="width: auto; padding: 0.8rem 1.5rem; text-decoration: none; display: inline-flex; align-items: center; white-space: nowrap;">
                        <i class="fas fa-users-cog" style="margin-right: 8px;"></i> Kelola User
                    </a>

                    <a href="{{ route('projects.create') }}" class="btn btn-primary"
                        style="width: auto; padding: 0.8rem 1.5rem; text-decoration: none; display: inline-flex; align-items: center; white-space: nowrap;">
                        <i class="fas fa-plus-circle" style="margin-right: 8px;"></i> Tambah Proyek
                    </a>
                @endif
            </div>
        </div>

        <div class="project-grid"
            style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 2rem;">

            @forelse($projects as $project)
                <div class="card project-card" style="display: flex; flex-direction: column; height: 100%;">
                    <div class="logo-wrapper">
                        @if ($project->logo)
                            <img src="{{ asset('storage/' . $project->logo) }}" alt="{{ $project->nama_proyek }}">
                        @else
                            <div class="logo-placeholder">
                                {{ strtoupper(substr($project->nama_proyek, 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    <h3 style="margin-bottom: 0.5rem; font-size: 2rem; font-weight: 800; color: var(--bg-main);">
                        {{ $project->nama_proyek }}
                    </h3>

                    <p class="project-desc" style="flex-grow: 1;">
                        Akses CRM, Progress Pembangunan, dan Laporan Keuangan untuk
                        <strong style="color: var(--bg-main);">{{ $project->nama_proyek }}</strong>.
                    </p>

                    <a href="{{ route('projects.enter', $project->id) }}" class="btn btn-primary"
                        style="display: flex; justify-content: center; align-items: center; gap: 10px; margin-top: 1rem;">
                        Masuk <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            @empty
                <div class="card-empty"
                    style="grid-column: 1 / -1; background: var(--bg-main); border: 4px dashed var(--accent); padding: 3rem; text-align: center; border-radius: 12px;">
                    <i class="fas fa-folder-open"
                        style="font-size: 3rem; color: var(--accent); margin-bottom: 1.5rem; display: block;"></i>
                    <h3 class="empty-title" style="color: var(--accent);">Belum Ada Proyek</h3>
                    <p class="empty-subtitle" style="color: rgba(255,255,255,0.7);">Silahkan membuat proyek terlebih dahulu.
                    </p>
                </div>
            @endforelse

        </div>
    </div>
@endsection
