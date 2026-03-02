@extends('layouts.app')
@section('title', 'Jadwal Follow Up')

@section('content')
    <div class="card"
        style="max-width: 1400px; margin: 0 auto; border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">

        <div
            style="padding: 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <form action="{{ route('followup.index') }}" method="GET" style="display: flex; gap: 0.5rem; margin: 0;">
                    <div style="position: relative;">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama, WA, atau ID..."
                            value="{{ request('search') }}"
                            style="padding-left: 2.2rem; min-width: 280px; font-size: 0.9rem;">
                        <i class="fas fa-search"
                            style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.875rem;"></i>
                    </div>
                    <button type="submit" class="btn btn-primary"
                        style="padding: 0.5rem 1.2rem; width: auto;">Cari</button>
                    @if (request('search'))
                        <a href="{{ route('followup.index') }}" class="btn"
                            style="background: #fef2f2; color: #ef4444; border: 1px solid #fca5a5; padding: 0.5rem 1rem; width: auto; text-decoration: none; border-radius: 6px; font-weight: 600;">Reset</a>
                    @endif
                </form>
            </div>
        </div>

        @php

            $activeLeads = $followups->filter(fn($l) => in_array($l->status_lead, ['Warm Lead']));

            $inactiveLeads = $followups->filter(
                fn($l) => in_array($l->status_lead, ['Cold Lead', 'Tidak Prospek', 'Hot Prospek', 'Gagal Closing']),
            );

            $currentUserPicId = \App\Models\PicMarketing::where('user_id', auth()->id())->value('id_pic');
            $isAdminOrSuper = in_array(auth()->user()->role, ['Admin', 'Super_Admin']);
        @endphp

        <div
            style="padding: 1.2rem 1.5rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-fire" style="color: #ef4444;"></i>
            <h4
                style="font-size: 0.9rem; font-weight: 800; color: #1e293b; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">
                Follow Up Aktif <span style="color: #64748b; font-weight: normal;">({{ $activeLeads->count() }})</span>
            </h4>
        </div>

        <div class="table-container" style="background: white;">
            <table class="custom-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #ffffff; border-bottom: 2px solid #f1f5f9;">
                        <th style="width: 160px; padding: 15px;">Jadwal</th>
                        <th style="width: 250px; padding: 15px;">Lead & Kontak</th>
                        <th style="width: 150px; padding: 15px;">PIC</th>
                        <th style="padding: 15px;">Hasil Interaksi Terakhir</th>
                        <th style="width: 170px; text-align: center; padding: 15px;">Status</th>
                        <th style="width: 180px; text-align: center; padding: 15px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activeLeads as $lead)
                        @php
                            $lastFu = $lead->latestFollowUp;
                            $targetDate = $lastFu->tgl_follow_up_berikutnya ?? null;
                            $targetTime = $lastFu->jam_follow_up_berikutnya ?? null;
                            $isOverdue = $targetDate && $targetDate < date('Y-m-d');
                            $isToday = $targetDate && $targetDate == date('Y-m-d');
                            $picName = $lastFu && $lastFu->pic ? $lastFu->pic->nama_pic : '-';

                            // Logika akses tombol
                            $currentOwnerId = $lead->id_pic;
                            $canAccessButton = $isAdminOrSuper || $currentOwnerId == $currentUserPicId;

                            // Styling Status Dropdown
                            $badgeStyle = match ($lead->status_lead) {
                                'Cold Lead' => 'background: #bae6fd; color: #0369a1; border: 1px solid #7dd3fc;',
                                'Warm Lead' => 'background: #fde68a; color: #92400e; border: 1px solid #f59e0b;',
                                'Hot Prospek' => 'background: #fecdd3; color: #be123c; border: 1px solid #fb7185;',
                                'Tidak Prospek' => 'background: #cbd5e1; color: #1e293b; border: 1px solid #94a3b8;',
                                'Gagal Closing' => 'background: #a61b1b; color: #ffffff; border: 1px solid #7f1d1d;',
                                default => 'background: #e2e8f0; color: #475569; border: 1px solid #cbd5e1;',
                            };
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 15px;">
                                <div style="display: flex; flex-direction: column;">
                                    <strong
                                        style="font-size: 0.95rem; {{ $isOverdue ? 'color: #dc2626' : ($isToday ? 'color: #ea580c' : 'color: #2563eb') }}">
                                        {{ $targetDate ? \Carbon\Carbon::parse($targetDate)->translatedFormat('d M Y') : '-' }}
                                    </strong>
                                    <small
                                        style="color: #64748b; margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                                        <i class="fas fa-clock"></i>
                                        {{ $targetTime ? \Carbon\Carbon::parse($targetTime)->format('H:i') : '(Jam -)' }}
                                    </small>
                                    @if ($isOverdue)
                                        <span
                                            style="font-size: 9px; color: #dc2626; font-weight: 800; margin-top: 4px; text-transform: uppercase;">⚠️
                                            TERLEWAT</span>
                                    @endif
                                </div>
                            </td>
                            <td style="padding: 15px;">
                                <strong style="font-size: 0.95rem; color: #1e293b;">{{ $lead->nama_lead }}</strong><br>
                                <div style="margin-top: 4px;">
                                    <small
                                        style="color: #64748b; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-weight: 600;">ID:
                                        {{ $lead->id_lead }}</small>
                                </div>
                                <small style="color: #64748b; display: block; margin-top: 4px;"><i class="fab fa-whatsapp"
                                        style="color: #22c55e;"></i> {{ $lead->no_whatsapp }}</small>
                            </td>
                            <td style="padding: 15px;">
                                <div style="font-size: 0.85rem; color: #334155; font-weight: 600;">
                                    <i class="fas fa-user-circle" style="color: #cbd5e1; margin-right: 5px;"></i>
                                    {{ $picName }}
                                </div>
                            </td>
                            <td style="padding: 15px;">
                                <p style="font-size: 0.875rem; color: #4b5563; line-height: 1.5; margin: 0;">
                                    {{ Str::limit($lastFu->hasil_follow_up ?? 'Belum ada catatan interaksi.', 100) }}
                                </p>
                            </td>
                            <td style="text-align: center; padding: 15px;">
                                <form action="{{ route('leads.update', $lead->id_lead) }}" method="POST">
                                    @csrf @method('PUT')
                                    <div style="position: relative; display: inline-block; width: 100%;">
                                        <select name="status_lead" onchange="this.form.submit()"
                                            style="{{ $badgeStyle }} cursor: pointer; border-radius: 6px; padding: 6px 25px 6px 12px; font-size: 0.75rem; font-weight: 700; appearance: none; -webkit-appearance: none; outline: none; width: 100%;">
                                            @foreach (['Tidak Prospek', 'Cold Lead', 'Warm Lead', 'Hot Prospek', 'Gagal Closing'] as $status)
                                                <option value="{{ $status }}"
                                                    {{ $lead->status_lead == $status ? 'selected' : '' }}
                                                    style="background: white; color: #334155;">
                                                    {{ $status }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <i class="fas fa-chevron-down"
                                            style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 0.6rem; pointer-events: none; opacity: 0.6;"></i>
                                    </div>
                                </form>
                            </td>
                            <td style="text-align: center; padding: 15px;">
                                <div style="display: flex; gap: 4px; justify-content: center; align-items: center;">
                                    @if ($canAccessButton)
                                        <a href="{{ route('followup.followup_execute', $lead->id_lead) }}" class="btn"
                                            style="color: #166534; background: #dcfce7; font-weight: 600; padding: 5px 12px; font-size: 0.75rem; width: auto; display: inline-block; border: 1px solid #bbf7d0; text-decoration: none; border-radius: 4px;">
                                            Eksekusi
                                        </a>
                                        @if ($lastFu)
                                            <a href="{{ route('followup.edit', $lastFu->id_follow_up) }}" class="btn"
                                                style="color: #b45309; background: #ffedd5; font-weight: 600; padding: 5px 12px; font-size: 0.75rem; width: auto; display: inline-block; border: 1px solid #fed7aa; text-decoration: none; border-radius: 4px;">
                                                Jadwal
                                            </a>
                                        @endif
                                    @else
                                        <span
                                            style="font-size: 0.75rem; color: #94a3b8; font-style: italic; background: #f1f5f9; padding: 4px 8px; border-radius: 4px;">
                                            <i class="fas fa-lock"></i>
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center" style="padding: 4rem; color: #94a3b8;">Tidak ada tugas
                                aktif dalam daftar ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div onclick="toggleInactive()"
            style="padding: 1.2rem 1.5rem; background: #f1f5f9; cursor: pointer; display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; margin-top: 1rem;">
            <div style="display: flex; align-items: center; gap: 10px; color: #64748b;">
                <i class="fas fa-archive"></i>
                <h4
                    style="font-size: 0.85rem; font-weight: 700; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">
                    Follow Up Tidak Aktif<span
                        style="font-weight: normal; background: #cbd5e1; padding: 2px 8px; border-radius: 10px; color: #475569; margin-left: 5px;">{{ $inactiveLeads->count() }}</span>
                </h4>
            </div>
            <i id="chevronIcon" class="fas fa-chevron-down" style="color: #94a3b8; transition: 0.3s;"></i>
        </div>

        <div id="inactiveSection" style="display: none; background: #fafafa;">
            <div class="table-container" style="opacity: 0.9;">
                <table class="custom-table" style="width: 100%;">
                    <tbody>
                        @forelse($inactiveLeads as $lead)
                            @php
                                $lastFu = $lead->latestFollowUp;
                                $picName = $lastFu && $lastFu->pic ? $lastFu->pic->nama_pic : '-';
                                $badgeStyle = match ($lead->status_lead) {
                                    'Cold Lead' => 'background: #bae6fd; color: #0369a1; border: 1px solid #7dd3fc;',
                                    'Warm Lead' => 'background: #fde68a; color: #92400e; border: 1px solid #f59e0b;',
                                    'Hot Prospek' => 'background: #fecdd3; color: #be123c; border: 1px solid #fb7185;',
                                    'Tidak Prospek'
                                        => 'background: #cbd5e1; color: #1e293b; border: 1px solid #94a3b8;',
                                    'Gagal Closing'
                                        => 'background: #a61b1b; color: #ffffff; border: 1px solid #7f1d1d;',
                                    default => 'background: #e2e8f0; color: #475569; border: 1px solid #cbd5e1;',
                                };
                            @endphp
                            <tr style="background: #ffffff; border-bottom: 1px solid #f1f5f9;">
                                <td style="width: 160px; padding: 12px; color: #94a3b8; font-size: 0.85rem;">
                                    <i class="fas fa-calendar-alt"></i>
                                    {{ $lastFu && $lastFu->tgl_follow_up_berikutnya ? \Carbon\Carbon::parse($lastFu->tgl_follow_up_berikutnya)->format('d/m/Y') : '-' }}
                                </td>
                                <td style="width: 250px; padding: 12px;">
                                    <strong style="color: #64748b; font-size: 0.9rem;">{{ $lead->nama_lead }}</strong>
                                </td>
                                <td style="width: 150px; padding: 12px;">
                                    <small style="color: #64748b;">{{ $picName }}</small>
                                </td>
                                <td style="padding: 12px;">
                                    <p style="font-size: 0.8rem; color: #94a3b8; margin: 0;">
                                        {{ Str::limit($lastFu->hasil_follow_up ?? 'Tidak ada riwayat interaksi.', 80) }}
                                    </p>
                                </td>
                                <td style="width: 170px; text-align: center; padding: 12px;">
                                    <form action="{{ route('leads.update', $lead->id_lead) }}" method="POST">
                                        @csrf @method('PUT')
                                        <div style="position: relative; display: inline-block; width: 100%;">
                                            <select name="status_lead" onchange="this.form.submit()"
                                                style="{{ $badgeStyle }} cursor: pointer; border-radius: 6px; padding: 4px 25px 4px 10px; font-size: 0.75rem; font-weight: 700; appearance: none; -webkit-appearance: none; outline: none; width: 100%;">
                                                @foreach (['Cold Lead', 'Warm Lead', 'Hot Prospek', 'Deal', 'Tidak Prospek', 'Gagal Closing'] as $status)
                                                    <option value="{{ $status }}"
                                                        {{ $lead->status_lead == $status ? 'selected' : '' }}>
                                                        {{ $status }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <i class="fas fa-chevron-down"
                                                style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 0.6rem; pointer-events: none; opacity: 0.6;"></i>
                                        </div>
                                    </form>
                                </td>
                                <td style="width: 180px; text-align: center; padding: 12px;">
                                    <a href="{{ route('followup.followup_execute', $lead->id_lead) }}" class="btn"
                                        title="Lihat Riwayat"
                                        style="color: #64748b; background: #f8fafc; border: 1px solid #e2e8f0; padding: 6px 10px; font-size: 0.8rem; text-decoration: none; border-radius: 6px; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-history"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center" style="padding: 2rem; color: #cbd5e1;">Tidak ada
                                    data dalam arsip.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function toggleInactive() {
            const section = document.getElementById('inactiveSection');
            const icon = document.getElementById('chevronIcon');

            if (section.style.display === 'none') {
                section.style.display = 'block';
                icon.style.transform = 'rotate(180deg)';
                icon.style.color = '#1e293b';
            } else {
                section.style.display = 'none';
                icon.style.transform = 'rotate(0deg)';
                icon.style.color = '#94a3b8';
            }
        }
    </script>
@endsection
