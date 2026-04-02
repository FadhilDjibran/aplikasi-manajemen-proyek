@extends('layouts.app')
@section('title', 'Approval Transaksi Masuk')

@section('content')
    <div class="mb-4" style="display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ route('keuangan.pending') }}" class="btn"
            style="background: #f1f5f9; color: #475569; padding: 0.6rem 1.2rem; text-decoration: none; font-weight: 600; border-radius: 8px;">
            &larr; Kembali ke Antrian
        </a>
    </div>

    <div class="card"
        style="width: 100%; padding: 2.5rem; min-width: 900px; max-width: 1000px; margin: 0 auto; border: none; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">

        <div style="background: #f0f9ff; border: 1px solid #bae6fd; padding: 15px; border-radius: 8px; margin-bottom: 2rem;">
            <h5 style="color: #0369a1; font-weight: 700; margin-bottom: 5px;">
                <i class="fas fa-info-circle"></i> Info Approval Transaksi Lead
            </h5>
            <p style="margin: 0; color: #0c4a6e; font-size: 0.9rem;">
                Approve transaksi <b>{{ $transaksiLead->jenis_pembayaran }}</b> dari Pelanggan
                <b>{{ $transaksiLead->lead->nama_lead ?? 'Unknown' }}</b>.
            </p>
        </div>

        @if ($errors->any())
            <div
                style="background-color: #fef2f2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #fecaca;">
                <ul style="margin: 0; padding-left: 1.5rem; font-size: 0.85rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('keuangan.process_approve', $transaksiLead->id_transaksi) }}" method="POST"
            enctype="multipart/form-data">
            @csrf

            <h4 class="mb-4"
                style="border-bottom: 2px solid #f3f4f6; padding-bottom: 10px; color: #1e293b; font-weight: 700; font-size: 1.1rem;">
                Informasi Penempatan Dana
            </h4>

            <div class="transaction-container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label class="form-label"
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block;">Tanggal Pembukuan
                        <span style="color:red">*</span></label>

                    <input type="date" name="tanggal" class="form-control date-trigger-coa"
                        value="{{ old('tanggal', date('Y-m-d')) }}"
                        style="border-radius: 6px; border: 1px solid #cbd5e1; padding: 0.5rem 0.75rem;" required>
                </div>

                <div class="form-group">
                    <label class="form-label"
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block;">Simpan Ke <span
                            style="color:red">*</span></label>
                    <select name="input" class="form-control"
                        style="border-radius: 6px; border: 1px solid #cbd5e1; padding: 0.5rem 0.75rem; cursor: pointer;"
                        required>
                        <option value="">-- Pilih Penempatan Dana --</option>
                        <option value="Kas Besar" {{ old('input') == 'Kas Besar' ? 'selected' : '' }}>Kas Besar</option>
                        <option value="Kas Kecil" {{ old('input') == 'Kas Kecil' ? 'selected' : '' }}>Kas Kecil</option>
                        <option value="Bank" {{ old('input') == 'Bank' ? 'selected' : '' }}>Bank</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label"
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block;">
                        Pilih Akun (CoA) <span style="color:red">*</span>
                    </label>

                    <select name="no_akun" id="select-coa" class="form-control coa-select-dynamic"
                        data-coa-url="{{ route('keuangan.get-coa') }}" required>
                        <option value="">-- Cari atau Pilih Akun --</option>

                        @php
                            // Ambil tahun dari input tanggal (atau hari ini)
                            $tanggalAwal = old('tanggal', date('Y-m-d'));
                            $tahunAwal = date('Y', strtotime($tanggalAwal));
                            $groupedCoa = isset($coa)
                                ? $coa->where('tahun', $tahunAwal)->groupBy('kategori_akun')
                                : collect();
                        @endphp

                        @foreach ($groupedCoa as $kategori => $akunList)
                            <optgroup label="{{ $kategori }}">
                                @foreach ($akunList as $coaItem)
                                    <option value="{{ $coaItem->no_akun }}"
                                        {{ old('no_akun', '1308') == $coaItem->no_akun ? 'selected' : '' }}>
                                        {{ $coaItem->no_akun }} - {{ $coaItem->nama_akun }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label"
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block;">Jenis
                        Penggunaan</label>
                    <input type="text" name="jenis_penggunaan" class="form-control"
                        value="{{ old('jenis_penggunaan', 'Pemasukan ' . $transaksiLead->jenis_pembayaran) }}"
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
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block;">Nominal Transaksi
                        (Rp)</label>
                    <div style="display: flex; align-items: stretch; width: 100%;">
                        <input type="text" class="form-control"
                            value="{{ number_format($transaksiLead->nominal, 0, ',', '.') }}" readonly
                            style="border-radius: 6px 0 0 6px; border: 1px solid #cbd5e1; border-right: none; padding: 0.5rem 0.75rem; color: #059669; font-weight: 600; flex: 1; background: #f8fafc; cursor: not-allowed;">
                        <span
                            style="background: #e2e8f0; border: 1px solid #cbd5e1; border-radius: 0 6px 6px 0; padding: 0.5rem 0.75rem; color: #64748b; font-weight: 600; display: flex; align-items: center;">,00</span>
                    </div>
                    <input type="hidden" name="mutasi_masuk" value="{{ $transaksiLead->nominal }}">
                </div>

                <div class="form-group">
                    <label class="form-label"
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block;">Upload Bukti
                        Transaksi (Opsional)</label>
                    <input type="file" name="bukti" class="form-control" accept="image/*,.pdf"
                        style="border-radius: 6px; border: 1px dashed #cbd5e1; padding: 0.5rem 0.75rem; background: #f8fafc;">
                </div>

                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label"
                        style="font-weight: 600; color: #475569; margin-bottom: 0.5rem; display: block;">Keterangan
                        Transaksi <span style="color:red">*</span></label>
                    <textarea name="keterangan" class="form-control" rows="3" required
                        style="border-radius: 6px; border: 1px solid #cbd5e1; padding: 0.5rem 0.75rem;">{{ old('keterangan', 'Pembayaran ' . $transaksiLead->jenis_pembayaran . ' dari Pelanggan: ' . ($transaksiLead->lead->nama_lead ?? '') . '. ' . $transaksiLead->keterangan) }}</textarea>
                </div>
            </div>

            <div
                style="margin-top: 2.5rem; display: flex; justify-content: flex-end; border-top: 1px solid #f3f4f6; padding-top: 1.5rem;">
                <button type="submit" class="btn btn-primary"
                    style="width: auto; max-width: fit-content; display: inline-flex; align-items: center; justify-content: center; padding: 0.8rem 3rem; font-weight: 600; border-radius: 8px;">
                    <i class="fas fa-check-double" style="margin-right: 5px;"></i> Setujui & Bukukan
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script src="{{ asset('js/dynamic-coa.js') }}"></script>
@endsection
