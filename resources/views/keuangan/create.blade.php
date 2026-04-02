@extends('layouts.app')
@section('title', 'Tambah Transaksi Keuangan')

@section('content')
    <div class="mb-4" style="display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ url()->previous() }}" class="btn"
            style="background: #f1f5f9; color: #475569; padding: 0.6rem 1.2rem; text-decoration: none; font-weight: 600; border-radius: 8px;">
            &larr; Kembali ke Daftar
        </a>
    </div>

    <div class="card"
        style="width: 100%; padding: 2.5rem; min-width: 900px; max-width: 1000px; margin: 0 auto; border: none; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
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

        <form action="{{ route('keuangan.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <h4 class="mb-4"
                style="border-bottom: 2px solid #f3f4f6; padding-bottom: 10px; color: #1e293b; font-weight: 700; font-size: 1.1rem;">
                Informasi Transaksi
            </h4>

            <div class="transaction-container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">

                <div class="form-group">
                    <label class="form-label"
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block;">
                        Tanggal Transaksi <span style="color:red">*</span>
                    </label>
                    <input type="date" name="tanggal" id="tanggal_transaksi" class="form-control date-trigger-coa"
                        value="{{ old('tanggal', date('Y-m-d')) }}"
                        style="border-radius: 6px; border: 1px solid #cbd5e1; padding: 0.5rem 0.75rem;" required>
                </div>

                <div class="form-group">
                    <label class="form-label"
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block;">
                        Tipe Input <span style="color:red">*</span>
                    </label>
                    <select name="input" id="tipe_input" class="form-control"
                        data-saldo-url="{{ route('keuangan.get-saldo') }}"
                        style="border-radius: 6px; border: 1px solid #cbd5e1; padding: 0.5rem 0.75rem; cursor: pointer;"
                        required>
                        <option value="">-- Pilih Tipe Input --</option>
                        @if (in_array(auth()->user()->role, ['Super_Admin', 'Admin_Keuangan']))
                            <option value="Bank" {{ old('input') == 'Bank' ? 'selected' : '' }}>Bank</option>
                        @endif
                        @if (in_array(auth()->user()->role, ['Super_Admin', 'Admin_Keuangan']))
                            <option value="Kas Besar" {{ old('input') == 'Kas Besar' ? 'selected' : '' }}>Kas Besar</option>
                        @endif
                        <option value="Kas Kecil" {{ old('input') == 'Kas Kecil' ? 'selected' : '' }}>Kas Kecil</option>
                    </select>

                    <div id="saldo-container"
                        style="display: none; margin-top: 0.75rem; padding: 0.75rem; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 6px;">
                        <span style="font-size: 0.85rem; color: #475569; font-weight: 600;">Saldo Saat Ini: </span>
                        <span id="saldo-value" style="font-size: 1rem; color: #0f172a; font-weight: 800; float: right;">
                            Rp 0,00
                        </span>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label
                        style="display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 5px;">
                        PILIH AKUN (COA) <span style="color:red">*</span>
                    </label>

                    <select name="no_akun" id="select-coa" class="form-control coa-select-dynamic"
                        data-coa-url="{{ route('keuangan.get-coa') }}" required>
                        <option value="">-- Cari atau Pilih Akun --</option>

                        @php
                            // Ambil tahun dari input tanggal lama (jika ada error), atau tahun saat ini
                            $tanggalAwal = old('tanggal', date('Y-m-d'));
                            $tahunAwal = date('Y', strtotime($tanggalAwal));
                            $groupedCoa = isset($coa)
                                ? $coa->where('tahun', $tahunAwal)->groupBy('kategori_akun')
                                : collect();
                        @endphp

                        @foreach ($groupedCoa as $kategori => $akunList)
                            <optgroup label="{{ $kategori }}">
                                @foreach ($akunList as $item)
                                    <option value="{{ $item->no_akun }}"
                                        {{ old('no_akun') == $item->no_akun ? 'selected' : '' }}>
                                        {{ $item->no_akun }} - {{ $item->nama_akun }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label"
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block;">
                        Jenis Penggunaan
                    </label>
                    <input type="text" name="jenis_penggunaan" class="form-control"
                        value="{{ old('jenis_penggunaan') }}" placeholder="Contoh: Operasional, Pemasaran, dll"
                        style="border-radius: 6px; border: 1px solid #cbd5e1; padding: 0.5rem 0.75rem;">
                </div>
            </div>
            <h4 class="mb-4"
                style="border-bottom: 2px solid #f3f4f6; padding-bottom: 10px; margin-top: 2.5rem; color: #1e293b; font-weight: 700; font-size: 1.1rem;">
                Detail Nominal & Keterangan
            </h4>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">

                <div class="form-group">
                    <label class="form-label"
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block;">
                        Mutasi Masuk (Rp)
                    </label>
                    <div style="display: flex; align-items: stretch; width: 100%;">
                        <input type="text" name="mutasi_masuk" class="form-control money-format"
                            value="{{ old('mutasi_masuk') }}" placeholder="0,00"
                            style="border-radius: 6px; border: 1px solid #cbd5e1; padding: 0.5rem 0.75rem; color: #059669; font-weight: 600; flex: 1;">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label"
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block;">
                        Mutasi Keluar (Rp)
                    </label>
                    <div style="display: flex; align-items: stretch; width: 100%;">
                        <input type="text" name="mutasi_keluar" class="form-control money-format"
                            value="{{ old('mutasi_keluar') }}" placeholder="0,00"
                            style="border-radius: 6px; border: 1px solid #cbd5e1; padding: 0.5rem 0.75rem; color: #dc2626; font-weight: 600; flex: 1;">
                    </div>
                </div>

                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label"
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block;">
                        Keterangan Transaksi <span style="color:red">*</span>
                    </label>
                    <textarea name="keterangan" class="form-control" rows="3" placeholder="Tuliskan rincian transaksi..."
                        style="border-radius: 6px; border: 1px solid #cbd5e1; padding: 0.5rem 0.75rem;" required>{{ old('keterangan') }}</textarea>
                </div>

                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label"
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block;">
                        Upload Bukti Transaksi (Opsional)
                    </label>
                    <input type="file" name="bukti" class="form-control" accept="image/*,.pdf"
                        style="border-radius: 6px; border: 1px dashed #cbd5e1; padding: 0.5rem 0.75rem; background: #f8fafc;">
                    <small style="color: #94a3b8; font-size: 0.8rem; margin-top: 5px; display: block;">Format yang
                        diizinkan: JPG, PNG, PDF. Maksimal ukuran 2MB.</small>
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
                    <i class="fas fa-save" style="margin-right: 5px;"></i> Simpan Transaksi
                </button>

            </div>

        </form>
    </div>

    <script src="{{ asset('js/money-format.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script src="{{ asset('js/dynamic-coa.js') }}"></script>
    <script src="{{ asset('js/fetch-saldo.js') }}"></script>

@endsection
