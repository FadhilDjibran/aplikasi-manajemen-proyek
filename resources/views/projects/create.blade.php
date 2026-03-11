@extends('layouts.plain')
@section('title', 'Tambah Proyek')

@section('content')
    <nav class="navbar" style="padding: 1.5rem 2rem; margin-bottom: 2rem;">
        <div class="nav-brand" style="color: var(--accent); font-size: 1.5rem; font-weight: 800;">
            <i class="fas fa-plus-circle"></i> Tambah Proyek
        </div>
        <a href="{{ route('projects.index') }}" class="btn-logout"
            style="border: 1px solid var(--accent); padding: 5px 15px; border-radius: 6px; color: white; text-decoration: none; display: flex; align-items: center; gap: 8px;">
            <i class="fas fa-arrow-left"></i>
            <span>Kembali</span>
        </a>
    </nav>
    <div class="container" style="max-width: 500px; width: 100%; margin: 3rem auto; padding: 0 0rem;">
        <div class="card"
            style="border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); overflow: hidden;">
            <div class="card-header" style="background: white; border-bottom: 1px solid #f1f5f9; padding: 1.5rem;">
                <h4 style="margin: 0; font-weight: 700; color: var(--bg-main); font-size: 1.25rem;">
                    <i class="fas fa-plus-circle" style="color: var(--bg-main); margin-right: 8px;"></i> Buat Proyek
                    Baru
                </h4>
            </div>

            <div class="card-body" style="padding: 1.5rem;">

                <form action="{{ route('projects.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="form-group mb-4">
                        <label
                            style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block; font-size: 0.9rem;">
                            Nama Proyek <span style="color: red;">*</span>
                        </label>
                        <input type="text" name="nama_proyek"
                            class="form-control @error('nama_proyek') is-invalid @enderror"
                            placeholder="Masukkan nama proyek disini..." value="{{ old('nama_proyek') }}" required
                            style="padding: 0.6rem 0.8rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                        @error('nama_proyek')
                            <small class="text-danger" style="margin-top: 5px; display: block;">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group mb-5">
                        <label
                            style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block; font-size: 0.9rem;">
                            Logo Proyek (Opsional)
                        </label>
                        <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror"
                            accept="image/*" style="padding: 0.6rem; border-radius: 6px; border: 1px solid #cbd5e1;">

                        <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 5px;">
                            <small style="color: #94a3b8; font-size: 0.8rem;">Format: JPG, PNG, SVG. Maks 2MB.</small>
                        </div>

                        @error('logo')
                            <div class="text-danger small" style="margin-top: 5px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div
                        style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9;">
                        <button type="submit" class="btn btn-primary"
                            style="padding: 0.6rem 1.5rem; font-weight: 600; border-radius: 6px; box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.5);">
                            <i class="fas fa-save" style="margin-right: 6px;"></i> Simpan Proyek
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
@endsection
