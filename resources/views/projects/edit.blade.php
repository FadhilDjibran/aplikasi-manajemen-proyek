@php
    $layout = session('active_project_id') ? 'layouts.app' : 'layouts.plain';
@endphp

@extends($layout)

@section('title', 'Edit Proyek')

@section('content')
    @if (!session('active_project_id'))
        <nav class="navbar" style="padding: 1.5rem 2rem; margin-bottom: 2rem;">
            <div class="nav-brand" style="color: var(--accent); font-size: 1.5rem; font-weight: 800;">
                <i class="fas fa-edit"></i> Edit Proyek
            </div>
            <a href="{{ route('projects.index') }}" class="btn-logout"
                style="border: 1px solid var(--accent); padding: 5px 15px; border-radius: 6px; color: white; text-decoration: none; display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </nav>
    @endif

    {{-- CARD 1: EDIT FORM --}}
    {{-- PERBAIKAN DI SINI: max-width: 50% dan margin: 0 auto --}}
    <div class="card"
        style="width: 100%; max-width: 70%; min-width: 320px; margin: 0 auto; border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06); overflow: hidden; margin-bottom: 2rem;">

        <div class="card-header" style="background: white; border-bottom: 1px solid #f1f5f9; padding: 1.5rem;">
            <h4 style="margin: 0; font-weight: 700; color: #1e293b; font-size: 1.25rem;">
                <i class="fas fa-pen-to-square" style="color: #1e293b; margin-right: 8px;"></i> Edit Proyek
            </h4>
        </div>

        <div class="card-body" style="padding: 1.5rem;">

            <form action="{{ route('projects.update', $project->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group mb-4">
                    <label
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block; font-size: 0.9rem;">
                        Nama Proyek <span style="color: red;">*</span>
                    </label>
                    <input type="text" name="nama_proyek" class="form-control @error('nama_proyek') is-invalid @enderror"
                        placeholder="Masukkan nama proyek disini..." value="{{ old('nama_proyek', $project->nama_proyek) }}"
                        required style="padding: 0.6rem 0.8rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                    @error('nama_proyek')
                        <small class="text-danger" style="margin-top: 5px; display: block;">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group mb-5">
                    <label
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block; font-size: 0.9rem;">
                        Logo Proyek (Opsional)
                    </label>

                    @if ($project->logo)
                        <div
                            style="margin-bottom: 10px; padding: 10px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 6px; display: flex; align-items: center; gap: 10px; width: fit-content;">
                            <img src="{{ asset('storage/' . $project->logo) }}" alt="Logo Lama"
                                style="height: 40px; width: auto; border-radius: 4px;">
                            <span style="font-size: 0.8rem; color: #64748b;">Logo saat ini</span>
                        </div>
                    @endif

                    <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror"
                        accept="image/*" style="padding: 0.6rem; border-radius: 6px; border: 1px solid #cbd5e1;">

                    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 5px;">
                        <small style="color: #94a3b8; font-size: 0.8rem;">Biarkan kosong jika tidak ingin mengubah
                            logo.</small>
                    </div>

                    @error('logo')
                        <div class="text-danger small" style="margin-top: 5px;">{{ $message }}</div>
                    @enderror
                </div>

                <div
                    style="display: flex; justify-content: flex-end; align-items: center; border-top: 1px solid #f1f5f9; padding-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary"
                        style="padding: 0.6rem 1.5rem; font-weight: 600; border-radius: 6px; box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.5);">
                        <i class="fas fa-save" style="margin-right: 6px;"></i> Update Proyek
                    </button>
                </div>
            </form>

        </div>
    </div>


    {{-- CARD 2: DANGER ZONE (KHUSUS SUPER ADMIN) --}}
    @if (Auth::user()->role === 'Super_Admin')
        {{-- PERBAIKAN DI SINI JUGA: max-width: 50% dan margin: 2rem auto --}}
        <div class="card"
            style="width: 100%; max-width: 90%; min-width: 320px; margin: 2rem auto; border: 1px solid #fecaca; border-radius: 12px; overflow: hidden;">

            <div class="card-header" style="background: #fef2f2; border-bottom: 1px solid #fecaca; padding: 1rem 1.5rem;">
                <h4
                    style="margin: 0; font-weight: 700; color: #991b1b; font-size: 1rem; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-exclamation-triangle"></i> Hapus Proyek
            </div>

            <div class="card-body"
                style="padding: 1.5rem; display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap;">

                <div style="flex: 1;">
                    <strong style="color: #7f1d1d; display: block; margin-bottom: 5px;">Yakin menghapus Proyek Ini?</strong>
                    <p style="margin: 0; font-size: 0.875rem; color: #991b1b;">
                        Menghapus proyek <strong>{{ $project->nama_proyek }}</strong> akan menghapus seluruh data terkait.
                    </p>
                </div>

                <form action="{{ route('projects.destroy', $project->id) }}" method="POST"
                    onsubmit="return confirm('PERINGATAN KERAS!\n\nAnda (Super Admin) akan menghapus proyek: {{ $project->nama_proyek }}\n\nMenghapus proyek ini akan MENGHAPUS SEMUA DATA terkait.\nTindakan ini TIDAK DAPAT DIBATALKAN.\n\nKetik OK jika Anda benar-benar yakin.')">
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="btn"
                        style="background: #dc2626; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 6px; font-weight: 600; transition: 0.2s; white-space: nowrap;">
                        <i class="fas fa-trash-alt" style="margin-right: 6px;"></i> Hapus Proyek
                    </button>
                </form>

            </div>
        </div>
    @endif

@endsection
