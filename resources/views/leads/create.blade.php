@extends('layouts.app')
@section('title', 'Tambah Data Lead Baru')

@section('content')
    <div class="mb-4" style="display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ route('leads.index') }}" class="btn"
            style="background: #f1f5f9; color: #475569; padding: 0.6rem 1.2rem; text-decoration: none; font-weight: 600; border-radius: 8px;">
            &larr; Kembali ke Daftar
        </a>
    </div>

    <div class="card" style="padding: 2.5rem; max-width: 1000px; margin: 0 auto;">
        <form action="{{ route('leads.store') }}" method="POST">
            @csrf

            <h4 class="mb-4" style="border-bottom: 2px solid #f3f4f6; padding-bottom: 10px;">Data Identitas & Kontak</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label class="form-label">Tanggal Masuk</label>
                    <input type="date" name="tgl_masuk" class="form-control" value="{{ date('Y-m-d') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Nama Lengkap <span style="color:red">*</span></label>
                    <input type="text" name="nama_lead" class="form-control" placeholder="Nama lengkap calon buyer"
                        required>
                </div>
                <div class="form-group">
                    <label class="form-label">No. WhatsApp <span style="color:red">*</span></label>
                    <input type="text" name="no_whatsapp" class="form-control" placeholder="Contoh: 08123456789"
                        required>
                </div>
                <div class="form-group">
                    <label class="form-label">Kota Domisili</label>
                    <input type="text" name="kota_domisili" class="form-control" placeholder="Kota asal buyer">
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Alamat</label>
                    <textarea name="alamat" class="form-control" rows="2" placeholder="Alamat detail domisili"></textarea>
                </div>
            </div>

            <h4 class="mb-4" style="border-bottom: 2px solid #f3f4f6; padding-bottom: 10px; margin-top: 2rem;">Klasifikasi
                & Minat</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label class="form-label">Sumber Lead <span style="color:red">*</span></label>
                    <select name="sumber_lead" id="sumber_lead" class="form-control" required
                        onchange="toggleCustomSumber()">
                        <option value="">-- Pilih Sumber --</option>
                        <option value="Meta (FB / IG)">Meta (FB / IG)</option>
                        <option value="Tiktok">Tiktok</option>
                        <option value="Website">Website</option>
                        <option value="Walk In">Walk In</option>
                        <option value="Agen">Agen</option>
                        <option value="Brosur">Brosur</option>
                        <option value="Banner">Banner</option>
                        <option value="Banner">Freelance</option>
                        <option value="Banner">Referral</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                    <input type="text" name="sumber_lead_custom" id="sumber_lead_custom" class="form-control"
                        style="display: none; margin-top: 10px;" placeholder="Tuliskan sumber lead...">
                </div>
                <div class="form-group">
                    <label class="form-label">Tipe Rumah Minat</label>
                    <select name="id_tipe" class="form-control">
                        <option value="">-- Pilih Tipe Rumah --</option>
                        @foreach ($tipeRumah as $tipe)
                            <option value="{{ $tipe->id_tipe }}">{{ $tipe->nama_tipe }}</option>
                        @endforeach
                    </select>
                </div>
                @php
                    $myPic = $pics->where('user_id', Auth::id())->first();
                @endphp
                <div class="form-group">
                    <label class="form-label">PIC Marketing (Sales)</label>
                    <select name="id_pic" class="form-control"
                        {{ $myPic ? 'style=pointer-events:none;background-color:#f8fafc;' : '' }}>
                        @if (!$myPic)
                            <option value="">-- Pilih PIC --</option>
                        @endif

                        @foreach ($pics as $pic)
                            <option value="{{ $pic->id_pic }}"
                                {{ $myPic && $myPic->id_pic == $pic->id_pic ? 'selected' : '' }}>
                                {{ $pic->nama_pic }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <h4 class="mb-4" style="border-bottom: 2px solid #f3f4f6; padding-bottom: 10px; margin-top: 2rem;">Profil
                Pekerjaan & Finansial</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label class="form-label">Status Lead Awal</label>
                    <input type="text" class="form-control" value="Cold Lead" readonly
                        style="background-color: #f3f4f6; color: #6b7280; cursor: not-allowed;">
                    <small style="color: #6b7280; font-size: 0.8rem;">Lead baru otomatis masuk sebagai Cold Lead.</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Status Pekerjaan</label>
                    <input type="text" name="status_pekerjaan" class="form-control"
                        placeholder="PNS, Karyawan Swasta, Pengusaha, dll">
                </div>
                <div class="form-group">
                    <label class="form-label">Perkiraan Budget (Rp)</label>
                    <input type="text" class="form-control money-format" placeholder="Contoh: 500.000.000">
                    <input type="hidden" name="perkiraan_budget">
                </div>
                <div class="form-group">
                    <label class="form-label">Rencana Pembayaran</label>
                    <select name="rencana_pembayaran" class="form-control">
                        <option value="">-- Pilih Rencana Bayar --</option>
                        <option value="KPR">KPR</option>
                        <option value="Cash Bertahap">Cash Bertahap</option>
                        <option value="Cash Keras">Cash Keras</option>
                    </select>
                </div>
            </div>

            <div class="form-group" style="margin-top: 1.5rem;">
                <label class="form-label">Catatan Tambahan</label>
                <textarea name="catatan" class="form-control" rows="3"
                    placeholder="Kebutuhan khusus buyer, hasil chat awal, dll"></textarea>
            </div>

            <div style="margin-top: 2.5rem; text-align: right; border-top: 1px solid #f3f4f6; padding-top: 1.5rem;">
                <button type="submit" class="btn btn-primary" style="width: auto; padding: 0.8rem 3rem;">
                    Simpan Lead Baru
                </button>
            </div>
        </form>
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
                customInput.value = '';
            }
        }
    </script>
@endsection
