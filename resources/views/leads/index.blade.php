@extends('layouts.app')
@section('title', 'Database Leads')

@section('content')
    <div class="card" style="width: 100%; max-width: 100%; border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <div
            style="padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; gap: 1rem;">

            <form action="{{ route('leads.index') }}" method="GET" id="filterForm"
                style="display: flex; align-items: center; gap: 8px; flex: 1;">

                <div style="position: relative; flex: 1; max-width: 300px;">
                    <i class="fas fa-search"
                        style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.9rem;"></i>
                    <input type="text" name="search" class="form-control"
                        placeholder="Cari nama, nomor, atau catatan..." value="{{ request('search') }}"
                        style="padding-left: 38px; border-radius: 8px; border: 1px solid #e2e8f0; height: 40px; width: 100%;">
                </div>

                <div style="display: flex; align-items: center; gap: 8px; position: relative;">

                    <button type="button" id="btnFilter" class="btn"
                        style="background: #fff; border: 1px solid #e2e8f0; height: 40px; padding: 0 15px; border-radius: 8px; display: flex; align-items: center; gap: 8px; font-weight: 600; color: #475569; white-space: nowrap;">
                        <i class="fas fa-filter"
                            style="color: {{ request()->hasAny(['status_filter', 'sumber_filter', 'kota_filter', 'date_range']) ? '#2563eb' : '#94a3b8' }}"></i>
                        Filter
                        @php
                            $activeFilters = count(
                                array_filter(
                                    request()->only([
                                        'status_filter',
                                        'sumber_filter',
                                        'kota_filter',
                                        'date_range',
                                        'tipe_filter',
                                    ]),
                                ),
                            );
                        @endphp
                        @if ($activeFilters > 0)
                            <span
                                style="background: #2563eb; color: #fff; border-radius: 50%; width: 18px; height: 18px; font-size: 0.7rem; display: flex; align-items: center; justify-content: center;">{{ $activeFilters }}</span>
                        @endif
                    </button>

                    @if (request()->hasAny(['search', 'status_filter', 'sumber_filter', 'kota_filter', 'date_range', 'tipe_filter']))
                        <a href="{{ route('leads.index') }}" class="btn-reset-filter" title="Bersihkan Semua Filter">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif

                    <div id="filterPanel"
                        style="display: none; position: absolute; top: 48px; left: 0; z-index: 100; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); padding: 1.5rem; min-width: 280px;">

                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <div>
                                    <label
                                        style="display: block; font-size: 0.75rem; font-weight: 700; color: #64748b; margin-bottom: 5px;">STATUS</label>
                                    <div style="position: relative;">
                                        <select name="status_filter" class="form-control"
                                            style="font-size: 0.85rem; border-radius: 6px; appearance: none; -webkit-appearance: none; padding-right: 30px; cursor: pointer;">
                                            <option value="">Semua Status</option>
                                            @foreach (['Cold Lead', 'Warm Lead', 'Hot Prospek', 'Tidak Prospek', 'Gagal Closing'] as $st)
                                                <option value="{{ $st }}"
                                                    {{ request('status_filter') == $st ? 'selected' : '' }}>
                                                    {{ $st }}</option>
                                            @endforeach
                                        </select>
                                        <i class="fas fa-chevron-down"
                                            style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 0.7rem; color: #94a3b8; pointer-events: none;"></i>
                                    </div>
                                </div>

                                <div>
                                    <label
                                        style="display: block; font-size: 0.75rem; font-weight: 700; color: #64748b; margin-bottom: 5px;">SUMBER</label>
                                    <div style="position: relative;">
                                        <select name="sumber_filter" class="form-control"
                                            style="font-size: 0.85rem; border-radius: 6px; appearance: none; -webkit-appearance: none; padding-right: 30px; cursor: pointer;">
                                            <option value="">Semua Sumber</option>
                                            @foreach ($sumberLeads as $sumber)
                                                <option value="{{ $sumber }}"
                                                    {{ request('sumber_filter') == $sumber ? 'selected' : '' }}>
                                                    {{ $sumber }}</option>
                                            @endforeach
                                        </select>
                                        <i class="fas fa-chevron-down"
                                            style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 0.7rem; color: #94a3b8; pointer-events: none;"></i>
                                    </div>
                                </div>

                                <div>
                                    <label
                                        style="display: block; font-size: 0.75rem; font-weight: 700; color: #64748b; margin-bottom: 5px;">KOTA</label>
                                    <div style="position: relative;">
                                        <select name="kota_filter" class="form-control"
                                            style="font-size: 0.85rem; border-radius: 6px; appearance: none; -webkit-appearance: none; padding-right: 30px; cursor: pointer;">
                                            <option value="">Semua Kota</option>
                                            @foreach ($kotaDomisilis as $kota)
                                                <option value="{{ $kota }}"
                                                    {{ request('kota_filter') == $kota ? 'selected' : '' }}>
                                                    {{ $kota }}</option>
                                            @endforeach
                                        </select>
                                        <i class="fas fa-chevron-down"
                                            style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 0.7rem; color: #94a3b8; pointer-events: none;"></i>
                                    </div>
                                </div>

                                <div>
                                    <label
                                        style="display: block; font-size: 0.75rem; font-weight: 700; color: #64748b; margin-bottom: 5px;">TIPE
                                        RUMAH</label>
                                    <div style="position: relative;">
                                        <select name="tipe_filter" class="form-control"
                                            style="font-size: 0.85rem; border-radius: 6px; appearance: none; -webkit-appearance: none; padding-right: 30px; cursor: pointer;">
                                            <option value="">Semua Tipe</option>
                                            @foreach ($tipeRumahs as $tipe)
                                                <option value="{{ $tipe->id_tipe }}"
                                                    {{ request('tipe_filter') == $tipe->id_tipe ? 'selected' : '' }}>
                                                    {{ $tipe->nama_tipe }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <i class="fas fa-chevron-down"
                                            style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 0.7rem; color: #94a3b8; pointer-events: none;"></i>
                                    </div>
                                </div>

                            </div>

                            <div>
                                <label
                                    style="display: block; font-size: 0.75rem; font-weight: 700; color: #64748b; margin-bottom: 5px;">RENTANG
                                    TANGGAL</label>
                                <input type="text" name="date_range" id="date_range" value="{{ request('date_range') }}"
                                    placeholder="Pilih tanggal..." readonly
                                    style="width: 100%; border-radius: 6px; border: 1px solid #e2e8f0; height: 38px; padding: 0 10px; font-size: 0.85rem;">
                            </div>

                            <button type="submit" class="btn btn-primary"
                                style="width: 100%; height: 38px; font-weight: 600; font-size: 0.85rem; display: flex; align-items: center; justify-content: center;">
                                Terapkan Filter
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <a href="{{ route('leads.create') }}" class="btn btn-primary"
                style="height: 40px; max-width: 145px; width: 100%; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 1rem; white-space: nowrap; flex-shrink: 0;">
                <i class="fas fa-plus"></i> Tambah Lead
            </a>
        </div>

        <div class="table-container" style="padding: 0; margin: 0;">
            <table class="custom-table" style="font-size: 0.85rem; width: 100%; border-collapse: collapse;">
                <thead
                    style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.75rem;">
                    <tr>
                        <th style="padding: 12px 15px; width: 50px;">ID</th>
                        <th style="padding: 12px 15px; min-width: 200px;">Nama Lead & Kontak</th>
                        <th style="padding: 12px 15px; width: 120px;">PIC</th>
                        <th style="padding: 12px 15px; width: 150px;">Sumber</th>
                        <th style="padding: 12px 15px; text-align: center; width: 160px;">Status</th>
                        <th style="padding: 12px 15px; width: 100px;">Tgl Masuk</th>
                        <th style="padding: 12px 15px;">Catatan</th>
                        <th style="padding: 12px 15px; text-align: center; width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leads as $lead)
                        @php
                            $isWarmHot = in_array($lead->status_lead, ['Warm Lead', 'Hot Prospek']);
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;">
                            <td style="padding: 6px 10px; color: #94a3b8; font-size: 0.8rem;">{{ $lead->id_lead }}</td>

                            <td style="padding: 6px 10px;">
                                <div
                                    style="font-weight: 700; color: #1e293b; font-size: 0.85rem; margin-bottom: 2px; line-height: 1.2;">
                                    {{ $lead->nama_lead }}
                                    @if ($lead->kota_domisili)
                                        <span
                                            style="font-size: 0.7rem; color: #94a3b8; font-weight: normal;">({{ $lead->kota_domisili }})</span>
                                    @endif
                                </div>

                                <div
                                    style="display: flex; align-items: center; gap: 4px 8px; flex-wrap: wrap; line-height: 1;">
                                    <a target="_blank"
                                        style="text-decoration: none; color: #64748b; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 4px; padding-top: 2px;">
                                        <i class="fab fa-whatsapp" style="color: #22c55e;"></i> {{ $lead->no_whatsapp }}
                                    </a>

                                    @if ($lead->tipeRumah)
                                        <span
                                            style="background: #f8fafc; color: #475569; padding: 2px 6px; border-radius: 4px; font-size: 0.65rem; border: 1px solid #e2e8f0; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; margin-top: 2px;">
                                            <i class="fas fa-home" style="color: #94a3b8; font-size: 0.6rem;"></i>
                                            {{ $lead->tipeRumah->nama_tipe }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td style="padding: 6px 10px;">
                                <div
                                    style="display: flex; align-items: center; gap: 4px; color: #475569; font-size: 0.8rem;">
                                    <i class="fas fa-user-circle" style="color: #cbd5e1;"></i>
                                    <span style="font-weight: 500;">
                                        {{ $lead->pic->nama_pic ?? '-' }}
                                    </span>
                                </div>
                            </td>

                            <td style="padding: 6px 10px; width: 1%; white-space: nowrap;">
                                <span
                                    style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 10px; border: 1px solid #e2e8f0; color: #64748b; font-weight: 600; white-space: nowrap;">
                                    {{ $lead->sumber_lead ?: '-' }}
                                </span>
                            </td>

                            <td
                                style="padding: 6px 10px 6px 2px; text-align: left; white-space: nowrap; vertical-align: middle; width: 1%;">
                                @php
                                    $badgeStyle = match ($lead->status_lead) {
                                        'Cold Lead'
                                            => 'background: #bae6fd; color: #0369a1; border: 1px solid #7dd3fc;',
                                        'Warm Lead'
                                            => 'background: #fde68a; color: #92400e; border: 1px solid #f59e0b;',
                                        'Hot Prospek'
                                            => 'background: #fecdd3; color: #be123c; border: 1px solid #fb7185;',
                                        'Tidak Prospek'
                                            => 'background: #cbd5e1; color: #1e293b; border: 1px solid #94a3b8;',
                                        'Gagal Closing'
                                            => 'background: #a61b1b; color: #ffffff; border: 1px solid #7f1d1d;',
                                        default => 'background: #e2e8f0; color: #475569; border: 1px solid #cbd5e1;',
                                    };
                                @endphp

                                <form action="{{ route('leads.update', $lead->id_lead) }}" method="POST"
                                    style="margin: 0; width: auto; display: inline-block;">
                                    @csrf @method('PUT')

                                    @foreach (request()->query() as $key => $value)
                                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                    @endforeach

                                    <div
                                        style="position: relative; display: inline-block; white-space: nowrap; min-width: 110px;">
                                        <select name="status_lead" onchange="this.form.submit()"
                                            style="{{ $badgeStyle }} cursor: pointer; border-radius: 6px; padding: 2px 16px 2px 6px; font-size: 0.7rem; font-weight: 700; appearance: none; outline: none; width: 100%; text-align: center;">
                                            @foreach (['Cold Lead', 'Warm Lead', 'Hot Prospek', 'Tidak Prospek', 'Gagal Closing'] as $status)
                                                <option value="{{ $status }}"
                                                    {{ $lead->status_lead == $status ? 'selected' : '' }}
                                                    style="background: white; color: #334155;">
                                                    {{ $status }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <i class="fas fa-chevron-down"
                                            style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); font-size: 0.6rem; pointer-events: none; opacity: 0.6;"></i>
                                    </div>
                                </form>
                            </td>

                            <td
                                style="padding: 6px 10px; color: #64748b; white-space: nowrap; vertical-align: middle; font-size: 0.8rem;">
                                {{ \Carbon\Carbon::parse($lead->tgl_masuk)->format('d M Y') }}
                            </td>

                            <td style="padding: 6px 10px; max-width: 150px;">
                                <p style="font-size: 0.75rem; color: #64748b; margin: 0; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                    title="{{ $lead->catatan }}">
                                    {{ $lead->catatan ? $lead->catatan : '-' }}
                                </p>
                            </td>

                            <td style="padding: 6px 10px; text-align: center; vertical-align: middle;">
                                <div style="display: flex; gap: 4px; justify-content: center;">
                                    @php
                                        $isWarmHot = in_array($lead->status_lead, [
                                            'Warm Prospek',
                                            'Hot Prospek',
                                            'Warm Lead',
                                        ]);
                                        $isMyLead = $lead->pic && $lead->pic->user_id === auth()->id();
                                        $isSuperAdmin = auth()->user()->role === 'Super_Admin';
                                        $canExecute = $isWarmHot && ($isSuperAdmin || $isMyLead);
                                    @endphp

                                    @if ($canExecute)
                                        <a href="{{ route('followup.followup_execute', $lead->id_lead) }}" class="btn"
                                            style="color: #ea580c; background: #fff7ed; padding: 4px 8px; font-size: 0.75rem; border: 1px solid #ffedd5; border-radius: 4px;"
                                            title="Eksekusi">
                                            <i class="fas fa-play"></i>
                                        </a>
                                    @elseif($isWarmHot)
                                        <button class="btn" disabled
                                            style="color: #9ca3af; background: #f3f4f6; padding: 4px 8px; font-size: 0.75rem; border: 1px solid #e5e7eb; border-radius: 4px; cursor: not-allowed;"
                                            title="Hanya PIC atau Super Admin yang dapat mengeksekusi">
                                            <i class="fas fa-play"></i>
                                        </button>
                                    @endif
                                    <a href="{{ route('leads.show', $lead->id_lead) }}" class="btn"
                                        style="color: #2563eb; background: #eff6ff; padding: 4px 8px; font-size: 0.75rem; border: 1px solid #dbeafe; border-radius: 4px;"
                                        title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('leads.edit', $lead->id_lead) }}" class="btn"
                                        style="color: #059669; background: #ecfdf5; padding: 4px 8px; font-size: 0.75rem; border: 1px solid #bbf7d0; border-radius: 4px;"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center" style="padding: 3rem; color: #94a3b8;">
                                <i class="fas fa-inbox"
                                    style="font-size: 2rem; display: block; margin-bottom: 0.5rem; opacity: 0.3;"></i>
                                <span style="font-size: 0.9rem;">Tidak ada data lead ditemukan.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="custom-pagination-wrapper">
            {{ $leads->withQueryString()->links() }}
        </div>
    </div>

    <div style="margin-top: 1rem; margin-bottom: 2rem; display: flex; justify-content: flex-end; gap: 12px;">

        <form action="{{ route('leads.export') }}" method="GET" style="margin: 0;">
            @foreach (request()->query() as $key => $val)
                <input type="hidden" name="{{ $key }}" value="{{ $val }}">
            @endforeach
            <button type="submit" class="btn btn-primary"
                style="display: flex; align-items: center; gap: 8px; font-weight: 600; padding: 8px 16px; border-radius: 6px;">
                <i class="fas fa-file-excel"></i> Export Spreadsheet
            </button>
        </form>

        <form action="{{ route('leads.trigger_update') }}" method="POST" style="margin: 0;">
            @csrf
            <button type="submit" class="btn btn-primary"
                style="display: flex; align-items: center; gap: 8px; font-weight: 600; padding: 8px 16px; border-radius: 6px;"
                onclick="return confirm('Proses ini akan mengecek tanggal follow up dan menurunkan status lead yang tidak aktif. Lanjutkan?')">
                <i class="fas fa-sync-alt"></i> Update Status
            </button>
        </form>

    </div>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnFilter = document.getElementById('btnFilter');
            const filterPanel = document.getElementById('filterPanel');

            if (!btnFilter || !filterPanel) return;

            btnFilter.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                const isHidden = filterPanel.style.display === 'none' || filterPanel.style.display === '';
                filterPanel.style.display = isHidden ? 'block' : 'none';
            });

            document.addEventListener('click', function(e) {
                const isFlatpickr = e.target.closest('.flatpickr-calendar');
                const isInsidePanel = filterPanel.contains(e.target);

                if (!isInsidePanel && !isFlatpickr) {
                    filterPanel.style.display = 'none';
                }
            });

            flatpickr("#date_range", {
                mode: "range",
                dateFormat: "Y-m-d"
            });
        });
    </script>
@endsection
