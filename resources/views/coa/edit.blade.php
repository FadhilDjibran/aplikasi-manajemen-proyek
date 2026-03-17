@extends('layouts.app')
@section('title', 'Edit Data Akun (CoA)')

@section('content')
    <div class="mb-4" style="display: flex; justify-content: space-between; align-items: center;">
        <a href="javascript:history.back()" class="btn"
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

        <form action="{{ route('coa.update', $coa->id) }}" method="POST" id="update-form">
            @csrf
            @method('PUT')

            <h4 class="mb-4"
                style="border-bottom: 2px solid #f3f4f6; padding-bottom: 10px; color: #1e293b; font-weight: 700; font-size: 1.1rem;">
                Detail Identitas Akun (ID: {{ $coa->id }})
            </h4>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label class="form-label"
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block;">
                        Nomor Akun <span style="color:red">*</span>
                    </label>
                    <input type="text" name="no_akun" class="form-control" value="{{ old('no_akun', $coa->no_akun) }}"
                        placeholder="Contoh: 1105"
                        style="border-radius: 6px; border: 1px solid #cbd5e1; padding: 0.5rem 0.75rem;" required>
                </div>

                <div class="form-group">
                    <label class="form-label"
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block;">
                        Kategori Akun <span style="color:red">*</span>
                    </label>
                    <input type="text" name="kategori_akun" class="form-control"
                        value="{{ old('kategori_akun', $coa->kategori_akun) }}" placeholder="Contoh: ASET LANCAR"
                        style="border-radius: 6px; border: 1px solid #cbd5e1; padding: 0.5rem 0.75rem;" required>
                </div>

                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label"
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block;">
                        Nama Akun <span style="color:red">*</span>
                    </label>
                    <input type="text" name="nama_akun" class="form-control"
                        value="{{ old('nama_akun', $coa->nama_akun) }}" placeholder="Contoh: KAS CABANG"
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
                        <option value="Debit" {{ old('posisi_normal', $coa->posisi_normal) == 'Debit' ? 'selected' : '' }}>
                            Debit (D)</option>
                        <option value="Kredit"
                            {{ old('posisi_normal', $coa->posisi_normal) == 'Kredit' ? 'selected' : '' }}>Kredit (K)
                        </option>
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
                        <option value="Neraca"
                            {{ old('jenis_laporan', $coa->jenis_laporan) == 'Neraca' ? 'selected' : '' }}>Neraca (NRC)
                        </option>
                        <option value="Laba Rugi"
                            {{ old('jenis_laporan', $coa->jenis_laporan) == 'Laba Rugi' ? 'selected' : '' }}>Laba Rugi (LR)
                        </option>
                    </select>
                </div>
            </div>

            <h4 class="mb-4"
                style="border-bottom: 2px solid #f3f4f6; padding-bottom: 10px; margin-top: 2.5rem; color: #1e293b; font-weight: 700; font-size: 1.1rem;">
                Setup Saldo Awal
            </h4>

            @php
                $debitVal = (float) old('saldo_awal_debit', $coa->saldo_awal_debit);
                $kreditVal = (float) old('saldo_awal_kredit', $coa->saldo_awal_kredit);

                $displayDebit = $debitVal != 0 ? number_format($debitVal, 0, '', '') : '';
                $displayKredit = $kreditVal != 0 ? number_format($kreditVal, 0, '', '') : '';
            @endphp

            <div
                style="background: #f8fafc; padding: 1.5rem; border-radius: 8px; border: 1px solid #e2e8f0; display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label"
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block;">
                        Saldo Awal Debit (Rp)
                    </label>
                    <div style="display: flex; align-items: stretch; width: 100%;">
                        <input type="text" class="form-control money-format" value="{{ $displayDebit }}" placeholder="0"
                            style="border-radius: 6px 0 0 6px; border: 1px solid #cbd5e1; border-right: none; padding: 0.5rem 0.75rem; color: #059669; font-weight: 600; flex: 1;">

                        <span
                            style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 0 6px 6px 0; padding: 0.5rem 0.75rem; color: #94a3b8; font-weight: 600; display: flex; align-items: center;">
                            ,00
                        </span>

                        <input type="hidden" name="saldo_awal_debit" value="{{ $debitVal }}">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label"
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block;">
                        Saldo Awal Kredit (Rp)
                    </label>
                    <div style="display: flex; align-items: stretch; width: 100%;">
                        <input type="text" class="form-control money-format" value="{{ $displayKredit }}"
                            placeholder="0"
                            style="border-radius: 6px 0 0 6px; border: 1px solid #cbd5e1; border-right: none; padding: 0.5rem 0.75rem; color: #dc2626; font-weight: 600; flex: 1;">

                        <span
                            style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 0 6px 6px 0; padding: 0.5rem 0.75rem; color: #94a3b8; font-weight: 600; display: flex; align-items: center;">
                            ,00
                        </span>

                        <input type="hidden" name="saldo_awal_kredit" value="{{ $kreditVal }}">
                    </div>
                </div>

            </div>

            <p style="font-size: 0.8rem; color: #94a3b8; margin-top: 10px;">
                <i class="fas fa-info-circle"></i> Saldo Akhir akan dihitung otomatis oleh sistem berdasarkan Saldo Awal
                dan Transaksi yang berjalan.
            </p>

        </form>

        <div
            style="margin-top: 2.5rem; border-top: 1px solid #f3f4f6; padding-top: 1.5rem; display: flex; justify-content: flex-end; align-items: center; gap: 1rem;">
            <form action="{{ route('coa.destroy', $coa->id) }}" method="POST"
                onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun ini secara permanen? Pastikan tidak ada transaksi yang terhubung.')"
                style="margin: 0;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn"
                    style="background-color: #ef4444; color: white; width: auto; padding: 0.8rem 2.5rem; margin: 0; font-weight: 600; border-radius: 8px;">
                    <i class="fas fa-trash-alt" style="margin-right: 5px;"></i> Hapus Akun
                </button>
            </form>

            <button type="submit" form="update-form" class="btn btn-primary"
                style="width: auto; padding: 0.8rem 2.5rem; margin: 0; font-weight: 600; border-radius: 8px;">
                <i class="fas fa-save" style="margin-right: 5px;"></i> Simpan Perubahan
            </button>
        </div>
    </div>
    <script src="{{ asset('js/money-format.js') }}"></script>
@endsection
