@extends('layouts.app')
@section('title', 'Detail Profil Lead')

@section('content')
    <div class="mb-4" style="display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ route('leads.index') }}" class="btn"
            style="background: #f1f5f9; color: #475569; padding: 0.6rem 1.2rem; text-decoration: none; font-weight: 600; border-radius: 8px;">
            &larr; Kembali ke Daftar
        </a>
    </div>

    <div class="card" style="max-width: 900px; margin: 0 auto; padding: 2.5rem;">

        <div
            style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 1.5rem;">
            <div>
                <h2 style="font-size: 1.75rem; font-weight: 800; color: #1e293b; margin-bottom: 0.5rem;">
                    {{ $lead->nama_lead }}
                </h2>
                <p style="color: #64748b; font-size: 1rem;">
                    ID Lead: <strong style="color: #334155;">{{ $lead->id_lead }}</strong>
                    <span style="margin: 0 8px; color: #cbd5e1;">|</span>
                    Tgl Masuk: {{ \Carbon\Carbon::parse($lead->tgl_masuk)->translatedFormat('d F Y') }}
                </p>
            </div>
            <div>
                @php
                    $badgeStyle = match ($lead->status_lead) {
                        'Cold Lead' => 'background: #bae6fd; color: #0369a1; border: 1px solid #7dd3fc;',
                        'Warm Lead' => 'background: #fde68a; color: #92400e; border: 1px solid #f59e0b;',
                        'Hot Prospek' => 'background: #fecdd3; color: #be123c; border: 1px solid #fb7185;',
                        'Tidak Prospek' => 'background: #cbd5e1; color: #1e293b; border: 1px solid #94a3b8;',
                        'Gagal Closing' => 'background: #a61b1b; color: #ffffff; border: 1px solid #7f1d1d;',
                        default => 'background: #e2e8f0; color: #475569; border: 1px solid #cbd5e1;',
                    };
                @endphp

                <span class="badge"
                    style="{{ $badgeStyle }} font-size: 0.9rem; padding: 0.6rem 1.2rem; display: inline-flex; align-items: center; border-radius: 8px; font-weight: 700;">
                    <i class="fas fa-tag" style="margin-right: 8px; opacity: 0.7;"></i>
                    {{ $lead->status_lead }}
                </span>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 3rem;">

            <div>
                <h4
                    style="font-size: 0.8rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 1.2rem;">
                    Informasi Kontak
                </h4>

                <div class="mb-4">
                    <label style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.2rem; display: block;">Nomor
                        WhatsApp</label>
                    <div
                        style="font-size: 1.1rem; font-weight: 600; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                        <i class="fab fa-whatsapp" style="color: #25D366;"></i>
                        {{ $lead->no_whatsapp ?? '-' }}
                    </div>
                </div>

                <div class="mb-4">
                    <label style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.2rem; display: block;">Kota
                        Domisili</label>
                    <div style="font-size: 1rem; font-weight: 600; color: #334155;">{{ $lead->kota_domisili ?? '-' }}</div>
                </div>

                <div class="mb-4">
                    <label style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.2rem; display: block;">Alamat</label>
                    <div style="font-size: 1rem; color: #334155; line-height: 1.5;">{{ $lead->alamat ?? '-' }}</div>
                </div>

                <div class="mb-4">
                    <label style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.2rem; display: block;">Sumber
                        Lead</label>
                    <div style="font-size: 1rem; font-weight: 600; color: #334155;">{{ $lead->sumber_lead ?? '-' }}</div>
                </div>
            </div>

            <div>
                <h4
                    style="font-size: 0.8rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 1.2rem;">
                    Preferensi & Sales
                </h4>

                <div class="mb-4">
                    <label style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.2rem; display: block;">Minat Tipe
                        Rumah</label>
                    <div style="font-size: 1.2rem; font-weight: 700; color: #2563eb;">
                        {{ $lead->tipeRumah->nama_tipe ?? '-' }}
                    </div>
                </div>

                <div class="mb-4">
                    <label style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.2rem; display: block;">Rencana
                        Pembayaran</label>
                    <div style="font-size: 1rem; font-weight: 600; color: #334155;">{{ $lead->rencana_pembayaran ?? '-' }}
                    </div>
                </div>

                <div class="mb-4">
                    <label style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.2rem; display: block;">Perkiraan
                        Budget</label>
                    <div style="font-size: 1rem; font-weight: 600; color: #334155;">
                        @if ($lead->perkiraan_budget)
                            Rp {{ number_format($lead->perkiraan_budget, 0, ',', '.') }}
                        @else
                            -
                        @endif
                    </div>
                </div>

                <div class="mb-4">
                    <label style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.2rem; display: block;">Status
                        Pekerjaan</label>
                    <div style="font-size: 1rem; font-weight: 600; color: #334155;">{{ $lead->status_pekerjaan ?? '-' }}
                    </div>
                </div>

                <div class="mb-4">
                    <label style="font-size: 0.8rem; color: #64748b; margin-bottom: 0.2rem; display: block;">PIC
                        Marketing (Sales)</label>
                    <div style="font-size: 1rem; font-weight: 600; color: #334155; display: flex; align-items: center;">
                        <div
                            style="width: 28px; height: 28px; background: #f1f5f9; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 10px; font-size: 12px; color: #64748b; border: 1px solid #e2e8f0;">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        {{ $lead->picMarketing->nama_pic ?? 'Belum Ditentukan' }}
                    </div>
                </div>
            </div>
        </div>

        @if ($lead->catatan)
            <div
                style="margin-top: 2.5rem; background: #f8fafc; padding: 1.5rem; border-radius: 12px; border: 1px solid #e2e8f0;">
                <label
                    style="font-size: 0.8rem; color: #475569; font-weight: 700; display: block; margin-bottom: 0.5rem; text-transform: uppercase;">
                    <i class="fas fa-sticky-note" style="margin-right: 5px;"></i> Catatan Tambahan
                </label>
                <p style="font-size: 1rem; color: #334155; margin: 0; line-height: 1.6;">
                    {{ $lead->catatan }}
                </p>
            </div>
        @endif

        @if ($lead->status_lead === 'Gagal Closing' || $lead->alasan_gagal)
            <div
                style="margin-top: 1.5rem; background: #fef2f2; padding: 1.5rem; border-radius: 12px; border: 1px solid #fecaca;">
                <label
                    style="font-size: 0.8rem; color: #991b1b; font-weight: 700; display: block; margin-bottom: 1rem; text-transform: uppercase;">
                    <i class="fas fa-ban" style="margin-right: 5px;"></i> Riwayat Gagal Closing
                </label>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div>
                        <small
                            style="font-size: 0.75rem; color: #b91c1c; display: block; font-weight: 600; text-transform: uppercase;">Alasan
                            Utama</small>
                        <p style="font-size: 1rem; color: #7f1d1d; font-weight: 700; margin: 0;">
                            {{ $lead->alasan_gagal ?? 'Tidak disebutkan' }}
                        </p>
                    </div>
                    <div>
                        <small
                            style="font-size: 0.75rem; color: #b91c1c; display: block; font-weight: 600; text-transform: uppercase;">Tanggal
                            Gagal Closing</small>
                        <p style="font-size: 1rem; color: #7f1d1d; margin: 0;">
                            {{ $lead->tgl_gagal ? \Carbon\Carbon::parse($lead->tgl_gagal)->translatedFormat('d F Y') : '-' }}
                        </p>
                    </div>
                </div>

                @if ($lead->catatan_gagal)
                    <div style="margin-top: 1.2rem; padding-top: 1rem; border-top: 1px dashed #fca5a5;">
                        <small
                            style="font-size: 0.75rem; color: #b91c1c; display: block; font-weight: 600; text-transform: uppercase; margin-bottom: 0.3rem;">Catatan
                            Penolakan</small>
                        <p style="font-size: 0.95rem; color: #7f1d1d; margin: 0; line-height: 1.6; font-style: italic;">
                            "{{ $lead->catatan_gagal }}"
                        </p>
                    </div>
                @endif
            </div>
        @endif

        <div style="margin-top: 3rem; text-align: right; border-top: 1px solid #f1f5f9; padding-top: 1.5rem;">
            <a href="{{ route('leads.edit', $lead->id_lead) }}" class="btn btn-primary"
                style="padding: 0.75rem 2.5rem; font-weight: 600; border-radius: 8px;">
                <i class="fas fa-edit"></i> Edit Data Lead
            </a>
        </div>

    </div>
@endsection
