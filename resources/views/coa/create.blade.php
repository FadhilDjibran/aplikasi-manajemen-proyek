@extends('layouts.app')
@section('title', 'Tambah Data Akun')

@section('content')
    <div class="mb-4" style="display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ route('coa.index') }}" class="btn"
            style="background: #f1f5f9; color: #475569; padding: 0.6rem 1.2rem; text-decoration: none; font-weight: 600; border-radius: 8px;">
            &larr; Kembali ke Daftar
        </a>
    </div>

    <div class="card"
        style="padding: 2.5rem; max-width: 1000px; margin: 0 auto; border: none; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
        @if (session('success'))
            <div
                style="background-color: #f0fdf4; color: #166534; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #bbf7d0;">
                <div style="display: flex; align-items: center; gap: 8px; font-weight: 600;">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            </div>
        @endif
        @if ($errors->any())
            <div
                style="background-color: #fef2f2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #fecaca;">
                <div style="display: flex; align-items: center; gap: 8px; font-weight: 600; margin-bottom: 0.5rem;">
                    <i class="fas fa-exclamation-circle"></i> Terdapat kesalahan pada input Anda:
                </div>
                <ul style="margin: 0; padding-left: 1.5rem; font-size: 0.85rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('coa.store') }}" method="POST">
            @csrf

            <h4 class="mb-4"
                style="border-bottom: 2px solid #f3f4f6; padding-bottom: 10px; color: #1e293b; font-weight: 700; font-size: 1.1rem;">
                Detail Identitas Akun
            </h4>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label class="form-label"
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block;">
                        Nomor Akun <span style="color:red">*</span>
                    </label>
                    <input type="text" name="no_akun" class="form-control" value="{{ old('no_akun') }}"
                        placeholder="Contoh: 1105"
                        style="border-radius: 6px; border: 1px solid #cbd5e1; padding: 0.5rem 0.75rem;" required>
                </div>

                <div class="form-group">
                    <label class="form-label"
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block;">
                        Kategori Akun <span style="color:red">*</span>
                    </label>
                    <input type="text" name="kategori_akun" class="form-control" value="{{ old('kategori_akun') }}"
                        placeholder="Contoh: ASET LANCAR"
                        style="border-radius: 6px; border: 1px solid #cbd5e1; padding: 0.5rem 0.75rem;" required>
                </div>

                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label"
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block;">
                        Nama Akun <span style="color:red">*</span>
                    </label>
                    <input type="text" name="nama_akun" class="form-control" value="{{ old('nama_akun') }}"
                        placeholder="Contoh: KAS CABANG"
                        style="border-radius: 6px; border: 1px solid #cbd5e1; padding: 0.5rem 0.75rem;" required>
                </div>
            </div>

            <h4 class="mb-4"
                style="border-bottom: 2px solid #f3f4f6; padding-bottom: 10px; margin-top: 2.5rem; color: #1e293b; font-weight: 700; font-size: 1.1rem;">
                Klasifikasi Akuntansi
            </h4>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label class="form-label"
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block;">
                        Posisi Normal <span style="color:red">*</span>
                    </label>
                    <select name="posisi_normal" class="form-control"
                        style="border-radius: 6px; border: 1px solid #cbd5e1; padding: 0.5rem 0.75rem; cursor: pointer;"
                        required>
                        <option value="">-- Pilih Posisi --</option>
                        <option value="Debit" {{ old('posisi_normal') == 'Debit' ? 'selected' : '' }}>Debit (D)</option>
                        <option value="Kredit" {{ old('posisi_normal') == 'Kredit' ? 'selected' : '' }}>Kredit (K)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label"
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block;">
                        Jenis Laporan <span style="color:red">*</span>
                    </label>
                    <select name="jenis_laporan" class="form-control"
                        style="border-radius: 6px; border: 1px solid #cbd5e1; padding: 0.5rem 0.75rem; cursor: pointer;"
                        required>
                        <option value="">-- Pilih Laporan --</option>
                        <option value="Neraca" {{ old('jenis_laporan') == 'Neraca' ? 'selected' : '' }}>Neraca (NRC)
                        </option>
                        <option value="Laba Rugi" {{ old('jenis_laporan') == 'Laba Rugi' ? 'selected' : '' }}>Laba Rugi
                            (LR)
                        </option>
                    </select>
                </div>
            </div>

            <div
                style="margin-top: 2.5rem; display: flex; justify-content: flex-end; gap: 1rem; border-top: 1px solid #f3f4f6; padding-top: 1.5rem;">

                <button type="submit" name="action" value="save_and_new" class="btn"
                    style="width: auto; max-width: fit-content; display: inline-flex; align-items: center; justify-content: center; background: #f1f5f9; color: #475569; padding: 0.8rem 1.5rem; font-weight: 600; border: 1px solid #cbd5e1; border-radius: 8px;">
                    <i class="fas fa-plus-circle" style="margin-right: 5px;"></i> Simpan & Tambah Lagi
                </button>

                <button type="submit" name="action" value="save_and_close" class="btn btn-primary"
                    style="width: auto; max-width: fit-content; display: inline-flex; align-items: center; justify-content: center; padding: 0.8rem 3rem; font-weight: 600; border-radius: 8px;">
                    <i class="fas fa-save" style="margin-right: 5px;"></i> Simpan Akun
                </button>

            </div>

        </form>
    </div>
@endsection
