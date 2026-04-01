@extends('layouts.app')
@section('title', 'Edit Lead')

@section('content')
    <div class="mb-4" style="display: flex; justify-content: space-between; align-items: center;">
        <a href="javascript:history.back()" class="btn"
            style="background: #f1f5f9; color: #475569; padding: 0.6rem 1.2rem; text-decoration: none; font-weight: 600; border-radius: 8px;">
            &larr; Kembali ke Daftar
        </a>
    </div>

    <div class="card" style="padding: 2.5rem; max-width: 1000px; margin: 0 auto;">

        <form action="{{ route('leads.update', $lead->id_lead) }}" method="POST" id="update-form">
            @csrf
            @method('PUT')

            <h4 class="mb-4" style="border-bottom: 2px solid #f3f4f6; padding-bottom: 10px;">
                Data Identitas & Kontak (ID: {{ $lead->id_lead }})
            </h4>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label class="form-label">Tanggal Masuk</label>
                    <input type="date" name="tgl_masuk" class="form-control"
                        value="{{ $lead->tgl_masuk ? \Carbon\Carbon::parse($lead->tgl_masuk)->format('Y-m-d') : '' }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Lengkap <span style="color:red">*</span></label>
                    <input type="text" name="nama_lead" class="form-control" value="{{ $lead->nama_lead }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">No. WhatsApp <span style="color:red">*</span></label>
                    <input type="text" name="no_whatsapp" class="form-control" value="{{ $lead->no_whatsapp }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Kota Domisili</label>
                    <input type="text" name="kota_domisili" class="form-control" value="{{ $lead->kota_domisili }}">
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-control" rows="2">{{ $lead->alamat }}</textarea>
                </div>
            </div>

            <h4 class="mb-4" style="border-bottom: 2px solid #f3f4f6; padding-bottom: 10px; margin-top: 2rem;">
                Klasifikasi & Minat
            </h4>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                @php
                    $standardSources = [
                        'Meta (FB / IG)',
                        'Tiktok',
                        'Website',
                        'Walk In',
                        'Agen',
                        'Brosur',
                        'Banner',
                        'Freelance',
                        'Referral',
                    ];
                    $isCustomSource = !in_array($lead->sumber_lead, $standardSources) && !empty($lead->sumber_lead);
                @endphp

                <div class="form-group">
                    <label class="form-label">Sumber Lead <span style="color:red">*</span></label>

                    <select name="sumber_lead" id="sumber_lead" class="form-control" required
                        onchange="toggleCustomSumber()">
                        <option value="">-- Pilih Sumber --</option>

                        @foreach ($standardSources as $sumber)
                            <option value="{{ $sumber }}" {{ $lead->sumber_lead == $sumber ? 'selected' : '' }}>
                                {{ $sumber }}
                            </option>
                        @endforeach

                        <option value="Lainnya" {{ $isCustomSource ? 'selected' : '' }}>Lainnya (Tulis Sendiri)</option>
                    </select>

                    <input type="text" name="sumber_lead_custom" id="sumber_lead_custom" class="form-control"
                        style="display: {{ $isCustomSource ? 'block' : 'none' }}; margin-top: 10px;"
                        placeholder="Tuliskan sumber lead..." value="{{ $isCustomSource ? $lead->sumber_lead : '' }}"
                        {{ $isCustomSource ? 'required' : '' }}>
                </div>
                <div class="form-group">
                    <label class="form-label">Tipe Rumah Minat</label>
                    <select name="id_tipe" class="form-control">
                        <option value="">-- Pilih Tipe Rumah --</option>
                        @foreach ($tipeRumah as $tipe)
                            <option value="{{ $tipe->id_tipe }}"
                                {{ $lead->id_tipe_rumah_minat == $tipe->id_tipe ? 'selected' : '' }}>
                                {{ $tipe->nama_tipe }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">PIC Marketing (Sales)</label>
                    <select name="id_pic" class="form-control">
                        <option value="">-- Pilih PIC --</option>
                        @foreach ($pics as $pic)
                            <option value="{{ $pic->id_pic }}" {{ $lead->id_pic == $pic->id_pic ? 'selected' : '' }}>
                                {{ $pic->nama_pic }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <h4 class="mb-4" style="border-bottom: 2px solid #f3f4f6; padding-bottom: 10px; margin-top: 2rem;">
                Status & Rencana Finansial
            </h4>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label class="form-label">Status Lead <span style="color:red">*</span></label>
                    <select name="status_lead" id="status_lead" class="form-control" required
                        onchange="toggleGagalClosing()">
                        @foreach (['Tidak Prospek', 'Cold Lead', 'Warm Lead', 'Hot Prospek', 'Gagal Closing'] as $status)
                            <option value="{{ $status }}" {{ $lead->status_lead == $status ? 'selected' : '' }}>
                                {{ $status }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Status Pekerjaan</label>
                    <input type="text" name="status_pekerjaan" class="form-control"
                        value="{{ $lead->status_pekerjaan }}">
                </div>

                <div id="form-gagal-closing"
                    style="display: {{ $lead->status_lead == 'Gagal Closing' ? 'block' : 'none' }}; grid-column: span 2; background: #fef2f2; padding: 1.5rem; border-radius: 8px; border: 1px solid #fecaca;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div class="form-group">
                            <label class="form-label" style="color: #991b1b;">Alasan Gagal <span
                                    style="color:red">*</span></label>
                            <select name="alasan_gagal" id="alasan_gagal" class="form-control"
                                {{ $lead->status_lead == 'Gagal Closing' ? 'required' : '' }}>

                                <option value="" disabled {{ is_null($lead->alasan_gagal) ? 'selected' : '' }}>--
                                    Pilih Alasan --</option>

                                <option value="BI Checking Ditolak"
                                    {{ $lead->alasan_gagal == 'BI Checking Ditolak' ? 'selected' : '' }}>
                                    Gagal BI Checking
                                </option>

                                <option value="Harga Terlalu Tinggi"
                                    {{ $lead->alasan_gagal == 'Harga Terlalu Tinggi' ? 'selected' : '' }}>
                                    Harga Terlalu Tinggi
                                </option>

                                <option value="Lokasi Tidak Cocok"
                                    {{ $lead->alasan_gagal == 'Lokasi Tidak Cocok' ? 'selected' : '' }}>
                                    Lokasi Tidak Cocok
                                </option>

                                <option value="Beli di Kompetitor"
                                    {{ $lead->alasan_gagal == 'Beli di Kompetitor' ? 'selected' : '' }}>
                                    Sudah beli di kompetitor
                                </option>

                                <option value="Uang Muka Belum Cukup"
                                    {{ $lead->alasan_gagal == 'Uang Muka Belum Cukup' ? 'selected' : '' }}>
                                    Urusan Pribadi
                                </option>

                                <option value="Batal Sepihak"
                                    {{ $lead->alasan_gagal == 'Batal Sepihak' ? 'selected' : '' }}>
                                    Mengundurkan Diri
                                </option>

                                <option value="Lainnya" {{ $lead->alasan_gagal == 'Lainnya' ? 'selected' : '' }}>
                                    Lainnya
                                </option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label" style="color: #991b1b;">Catatan Gagal (Opsional)</label>
                            <input type="text" name="catatan_gagal" class="form-control"
                                value="{{ $lead->catatan_gagal }}" placeholder="Penjelasan singkat...">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; color: #475569;">Perkiraan Budget (Rp)</label>
                    <div style="display: flex; align-items: stretch; width: 100%;">
                        <input type="text" class="form-control money-format"
                            value="{{ old('perkiraan_budget') ? number_format((float) old('perkiraan_budget'), 2, ',', '.') : ($lead->perkiraan_budget != 0 ? number_format($lead->perkiraan_budget, 2, ',', '.') : '') }}"
                            placeholder="0,00"
                            style="border-radius: 6px; border: 1px solid #cbd5e1; padding: 0.5rem 0.75rem; font-weight: 600; flex: 1;">

                        <input type="hidden" name="perkiraan_budget"
                            value="{{ old('perkiraan_budget', $lead->perkiraan_budget) }}">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Rencana Pembayaran</label>
                    <select name="rencana_pembayaran" class="form-control">
                        <option value="">-- Pilih Rencana Bayar --</option>
                        @foreach (['KPR', 'Cash Bertahap', 'Cash Keras'] as $rencana)
                            <option value="{{ $rencana }}"
                                {{ $lead->rencana_pembayaran == $rencana ? 'selected' : '' }}>
                                {{ $rencana }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-top: 1.5rem;">
                <label class="form-label">Catatan Tambahan</label>
                <textarea name="catatan" class="form-control" rows="3">{{ $lead->catatan }}</textarea>
            </div>
        </form>

        <div
            style="margin-top: 2.5rem; border-top: 1px solid #f3f4f6; padding-top: 1.5rem; display: flex; justify-content: flex-end; align-items: center; gap: 1rem;">

            <form action="{{ route('leads.destroy', $lead->id_lead) }}" method="POST"
                onsubmit="return confirm('Apakah Anda yakin ingin menghapus lead ini secara permanen?')"
                style="margin: 0;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn"
                    style="background-color: #ef4444; color: white; width: auto; padding: 0.8rem 2.5rem; margin: 0; font-weight: 600; border-radius: 8px;">
                    Hapus Lead
                </button>
            </form>

            <button type="submit" form="update-form" class="btn btn-primary"
                style="width: auto; padding: 0.8rem 2.5rem; margin: 0; font-weight: 600; border-radius: 8px;">
                Simpan Perubahan
            </button>

        </div>
    </div>

    <script src="{{ asset('js/money-format.js') }}"></script>
    <script>
        function toggleCustomSumber() {
            var selectBox = document.getElementById('sumber_lead');
            var customInput = document.getElementById('sumber_lead_custom');

            if (selectBox.value === 'Lainnya') {
                customInput.style.display = 'block';
                customInput.required = true;
            } else {
                customInput.style.display = 'none';
                customInput.required = false;
            }
        }

        function toggleGagalClosing() {
            var statusBox = document.getElementById('status_lead');
            var gagalForm = document.getElementById('form-gagal-closing');
            var alasanInput = document.getElementById('alasan_gagal');

            if (statusBox.value === 'Gagal Closing') {
                gagalForm.style.display = 'block';
                alasanInput.required = true;
            } else {
                gagalForm.style.display = 'none';
                alasanInput.required = false;
                alasanInput.value = '';
            }
        }
    </script>
@endsection
