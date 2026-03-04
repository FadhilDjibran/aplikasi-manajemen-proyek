@extends('layouts.app')
@section('title', 'Eksekusi Follow Up')

@section('content')

    <div class="mb-4" style="display: flex; justify-content: space-between; align-items: center;">
        <a href="javascript:history.back()" class="btn"
            style="background: #f1f5f9; color: #475569; padding: 0.6rem 1.2rem; text-decoration: none; font-weight: 600; border-radius: 8px;">
            &larr; Kembali
        </a>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 2.5fr; gap: 2rem;">

        <div class="card" style="padding: 1.5rem; height: fit-content;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1rem;">
                <h3 class="auth-title" style="margin: 0; font-size: 1.25rem;">{{ $lead->nama_lead }}</h3>

                @php
                    $badgeStyle = match ($lead->status_lead) {
                        'Cold Lead' => 'background: #bae6fd; color: #0369a1; border: 1px solid #7dd3fc;',
                        'Warm Lead' => 'background: #fde68a; color: #92400e; border: 1px solid #f59e0b;',
                        'Hot Prospek' => 'background: #fecdd3; color: #be123c; border: 1px solid #fb7185;',
                        'Deal' => 'background: #a7f3d0; color: #064e3b; border: 1px solid #34d399;',
                        'Tidak Prospek' => 'background: #cbd5e1; color: #1e293b; border: 1px solid #94a3b8;',
                        'Gagal Closing' => 'background: #a61b1b; color: #ffffff; border: 1px solid #7f1d1d;',
                        default => 'background: #e2e8f0; color: #475569; border: 1px solid #cbd5e1;',
                    };
                @endphp

                <span class="badge"
                    style="{{ $badgeStyle }} white-space: nowrap; font-size: 0.75rem; padding: 4px 10px; border-radius: 6px; font-weight: 700;">
                    {{ $lead->status_lead }}
                </span>
            </div>

            <hr style="margin: 1.5rem 0; border: 0; border-top: 1px solid #eee;">

            <div class="form-group mb-3">
                <label class="form-label">WhatsApp</label>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="fab fa-whatsapp" style="color: #25D366;"></i>
                    <strong>{{ $lead->no_whatsapp }}</strong>
                </div>
            </div>
            <div class="form-group mb-3">
                <label class="form-label">Minat Properti</label>
                <p><strong>{{ $lead->tipeRumah->nama_tipe ?? '-' }}</strong></p>
            </div>
            <div class="form-group">
                <label class="form-label">Domisili</label>
                <p><strong>{{ $lead->kota_domisili ?? '-' }}</strong></p>
            </div>

            <a href="{{ route('leads.edit', $lead->id_lead) }}" class="btn"
                style="margin-top: 1rem; background: #f8fafc; border: 1px solid #cbd5e1; font-size: 12px; width: 100%; text-align: center;">
                <i class="fas fa-edit"></i> Edit Profil Lead
            </a>
        </div>

        <div style="display: flex; flex-direction: column; gap: 2rem;">

            <div class="card" style="padding: 2rem; border-top: 4px solid #2563eb;">
                <h4 style="margin-bottom: 1.5rem; font-weight: 700;">
                    <i class="fas fa-pen-nib" style="margin-right: 8px; color: #2563eb;"></i>
                    Catat Hasil Interaksi Baru
                </h4>

                <form action="{{ route('followup.process', $lead->id_lead) }}" method="POST">
                    @csrf

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">

                        <div class="form-group">
                            <label class="form-label">Media Kontak</label>
                            <select name="channel_follow_up" class="form-control">
                                <option value="Whatsapp" selected>Whatsapp</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Status Follow Up</label>
                            <select name="status_fu" class="form-control">
                                @foreach (['Proses Follow Up', 'Sudah Dihubungi', 'Siap Survey / Pertimbangan', 'Tidak Respons / Stop Follow Up'] as $sfu)
                                    <option value="{{ $sfu }}"
                                        {{ ($activeFollowUp->status_follow_up ?? '') == $sfu ? 'selected' : '' }}>
                                        {{ $sfu }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group" style="grid-column: span 2;">
                            <label class="form-label">Hasil Pembicaraan Hari Ini <span style="color:red">*</span></label>
                            <textarea name="hasil" class="form-control" rows="3" required
                                placeholder="Tuliskan ringkasan obrolan Anda dengan Lead..."></textarea>
                        </div>

                        <div class="form-group" style="grid-column: span 2;">
                            <label class="form-label">Rencana Tindak Lanjut</label>
                            <input type="text" name="rencana" class="form-control"
                                placeholder="Contoh: Kirim pricelist via WA / Jadwalkan Survey">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Tanggal Follow Up Berikutnya <span style="color:red">*</span></label>
                            <input type="date" name="tgl_next" class="form-control"
                                value="{{ \Carbon\Carbon::now()->addWeeks(2)->format('Y-m-d') }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Jam (Estimasi)</label>
                            <input type="time" name="jam_next" class="form-control" value="09:00">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Jadwal Survey (Opsional)</label>
                            <input type="date" name="tgl_survey" class="form-control">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Catatan Internal</label>
                            <input type="text" name="catatan" class="form-control"
                                placeholder="Catatan khusus untuk sales">
                        </div>
                    </div>

                    <div style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: flex-end;">
                        <a href="{{ route('followup.index') }}" class="btn"
                            style="background: #f1f5f9; width: auto; color: #64748b;">Batal</a>
                        <button type="submit" class="btn btn-primary" style="width: auto; padding: 0.75rem 2rem;">
                            <i class="fas fa-save" style="margin-right: 8px;"></i> Simpan Riwayat
                        </button>
                    </div>
                </form>
            </div>

            <div class="card" style="padding: 1.5rem;">
                <h5 style="font-size: 1rem; font-weight: 700; color: #64748b; margin-bottom: 1rem;">
                    Riwayat Interaksi
                </h5>
                <table class="custom-table" style="font-size: 0.85rem;">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Hasil</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lead->followUps->sortByDesc('created_at') as $history)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($history->tgl_follow_up)->format('d M Y') }}</td>
                                <td>{{ Str::limit($history->hasil_follow_up, 60) }}</td>
                                <td>{{ $history->status_follow_up }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">Belum ada riwayat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
