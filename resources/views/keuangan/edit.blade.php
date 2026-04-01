@extends('layouts.app')
@section('title', 'Edit Transaksi Keuangan')

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

        <form action="{{ route('keuangan.update', $item->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <h4 class="mb-4"
                style="border-bottom: 2px solid #f3f4f6; padding-bottom: 10px; color: #1e293b; font-weight: 700; font-size: 1.1rem;">
                Edit Informasi Transaksi
            </h4>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">

                <div class="form-group">
                    <label class="form-label"
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block;">
                        Tanggal Transaksi <span style="color:red">*</span>
                    </label>
                    <input type="date" name="tanggal" class="form-control"
                        value="{{ old('tanggal', date('Y-m-d', strtotime($item->tanggal))) }}"
                        style="border-radius: 6px; border: 1px solid #cbd5e1; padding: 0.5rem 0.75rem;" required>
                </div>

                <div class="form-group">
                    <label class="form-label"
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block;">
                        Tipe Input <span style="color:red">*</span>
                    </label>
                    <select name="input" class="form-control"
                        style="border-radius: 6px; border: 1px solid #cbd5e1; padding: 0.5rem 0.75rem; cursor: pointer;"
                        required>
                        <option value="Kas Besar" {{ old('input', $item->input) == 'Kas Besar' ? 'selected' : '' }}>Kas
                            Besar</option>
                        <option value="Kas Kecil" {{ old('input', $item->input) == 'Kas Kecil' ? 'selected' : '' }}>Kas
                            Kecil</option>
                        <option value="Bank" {{ old('input', $item->input) == 'Bank' ? 'selected' : '' }}>Bank</option>
                        <option value="Jurnal" {{ old('input', $item->input) == 'Jurnal' ? 'selected' : '' }}>Jurnal Umum
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label"
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block;">
                        Pilih Akun (CoA) <span style="color:red">*</span>
                    </label>

                    <select name="no_akun" id="select-coa" class="form-control" required>
                        <option value="">-- Pilih Akun --</option>

                        @php
                            $groupedCoa = isset($coa) ? $coa->unique('no_akun')->groupBy('kategori_akun') : collect();
                        @endphp

                        @foreach ($groupedCoa as $kategori => $akunList)
                            <optgroup label="{{ $kategori }}">
                                @foreach ($akunList as $coaItem)
                                    <option value="{{ $coaItem->no_akun }}"
                                        {{ old('no_akun', $item->no_akun) == $coaItem->no_akun ? 'selected' : '' }}>
                                        {{ $coaItem->no_akun }} - {{ $coaItem->nama_akun }}
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
                        value="{{ old('jenis_penggunaan', $item->jenis_penggunaan) }}"
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
                            value="{{ old('mutasi_masuk', $item->mutasi_masuk != 0 ? number_format($item->mutasi_masuk, 2, ',', '.') : '') }}"
                            placeholder="0,00"
                            style="border-radius: 6px; border: 1px solid #cbd5e1; padding: 0.5rem 0.75rem; color: #059669; font-weight: 600; flex: 1;">

                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label"
                        style="font-weight: 700; color: #475569; margin-bottom: 0.5rem; display: block;">
                        Mutasi Keluar (Rp)
                    </label>
                    <div style="display: flex; align-items: stretch; width: 100%;">
                        <input type="text" name="mutasi_keluar" class="form-control money-format"
                            value="{{ old('mutasi_keluar', $item->mutasi_keluar != 0 ? number_format($item->mutasi_keluar, 2, ',', '.') : '') }}"
                            placeholder="0,00"
                            style="border-radius: 6px; border: 1px solid #cbd5e1; padding: 0.5rem 0.75rem; color: #dc2626; font-weight: 600; flex: 1;">

                    </div>
                </div>

                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label"
                        style="font-weight: 700; color: #475569; margin-bottom: 0.5rem; display: block;">
                        Keterangan Transaksi <span style="color:red">*</span>
                    </label>
                    <textarea name="keterangan" class="form-control" rows="3"
                        style="border-radius: 6px; border: 1px solid #cbd5e1; padding: 0.5rem 0.75rem;" required>{{ old('keterangan', $item->keterangan) }}</textarea>
                </div>

                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label"
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block;">
                        Ganti Bukti Transaksi (Opsional)
                    </label>

                    @if ($item->bukti)
                        <div
                            style="margin-bottom: 10px; display: flex; align-items: center; gap: 10px; background: #f0f9ff; padding: 10px; border-radius: 6px; border: 1px solid #bae6fd;">
                            <i class="fas fa-file-alt" style="color: #0284c7;"></i>
                            <span style="font-size: 0.85rem; color: #0369a1;">File saat ini:
                                <a href="{{ asset('storage/' . $item->bukti) }}" target="_blank"
                                    style="font-weight: 600; text-decoration: underline; color: #0284c7;">Lihat Bukti</a>
                            </span>
                        </div>
                    @endif

                    <input type="file" name="bukti" class="form-control" accept="image/*,.pdf"
                        style="border-radius: 6px; border: 1px dashed #cbd5e1; padding: 0.5rem 0.75rem; background: #f8fafc;">
                    <small style="color: #94a3b8; font-size: 0.8rem; margin-top: 5px; display: block;">Kosongkan jika tidak
                        ingin mengganti bukti transaksi.</small>
                </div>

            </div>

            <div
                style="margin-top: 2.5rem; display: flex; justify-content: flex-end; gap: 1rem; border-top: 1px solid #f3f4f6; padding-top: 1.5rem;">
                <button type="submit" class="btn btn-primary"
                    style="width: auto; padding: 0.8rem 4rem; font-weight: 600; border-radius: 8px;">
                    <i class="fas fa-save" style="margin-right: 5px;"></i> Simpan Perubahan
                </button>
            </div>

        </form>
    </div>

    <script src="{{ asset('js/money-format.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var el = document.getElementById('select-coa');
            if (el) {
                new TomSelect(el, {
                    create: false,
                    sortField: {
                        field: "text",
                        direction: "asc"
                    },
                    maxOptions: 1000
                });
            }
        });
    </script>
@endsection
