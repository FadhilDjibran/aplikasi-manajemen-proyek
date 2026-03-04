@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
    @php
        $user = auth()->user();
        $isSuper = $user->role === 'Super_Admin';
        $isAdmin = $user->role === 'Admin';
        $isAdminOrSuper = $isSuper || $isAdmin;

        $overdueItems = $priorities->where('tgl_follow_up_berikutnya', '<', now()->format('Y-m-d'));
        $overdueCount = $overdueItems->count();

        $overduePicNames = collect([]);
        if ($isSuper && $overdueCount > 0) {
            $overduePicNames = $overdueItems->map(fn($item) => $item->pic->nama_pic ?? 'Tanpa PIC')->unique()->values();
        }
    @endphp

    @if ($overdueCount > 0)
        <div class="alert-overdue" style="display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 1rem;">
                <div class="alert-icon-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div>
                    <h4 style="margin: 0; font-size: 1rem; font-weight: 700;">
                        Perhatian! Ada {{ $overdueCount }} Jadwal Terlewat
                    </h4>
                    <p style="margin: 0; font-size: 0.875rem;">
                        Segera cek tabel Follow Up di bawah.
                    </p>
                </div>
            </div>

            @if ($isSuper && $overduePicNames->isNotEmpty())
                <div class="alert-pic-list" style="text-align: left; margin: 0; min-width: max-content;">
                    <small
                        style="color: #7f1d1d; font-weight: 700; display: block; font-size: 0.7rem; text-transform: uppercase;">
                        PIC Bersangkutan:
                    </small>
                    <span style="color: #b91c1c; font-weight: 600; font-size: 0.9rem;">
                        {{ $overduePicNames->implode(', ') }}
                    </span>
                </div>
            @endif
        </div>
    @endif

    @if (isset($pendingHotProspek) && $pendingHotProspek > 0)
        <div class="alert-pending">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div class="alert-icon-warning">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div>
                    <h4 style="margin: 0; color: #9a3412; font-size: 0.9rem; font-weight: 700;">Menunggu input data</h4>
                    <p style="margin: 0; color: #c2410c; font-size: 0.8rem; opacity: 0.9;">
                        Ada <strong>{{ $pendingHotProspek }} Lead Hot Prospek</strong> yang belum input transaksi.
                    </p>
                </div>
            </div>

            <a href="{{ route('hot_prospek.index') }}" class="btn btn-alert-action">
                Kelola <i class="fas fa-arrow-right" style="margin-left: 6px; font-size: 0.75rem;"></i>
            </a>
        </div>
    @endif

    <div class="stats-grid">
        <div class="stat-card stat-total">
            <p class="stat-title">Total Leads</p>
            <h3 class="stat-value">{{ $stats['total'] }}</h3>
        </div>
        <div class="stat-card stat-tidak-prospek">
            <p class="stat-title">Tidak Prospek</p>
            <h3 class="stat-value">{{ $stats['tidak_prospek'] ?? 0 }}</h3>
        </div>
        <div class="stat-card stat-cold">
            <p class="stat-title">Cold Lead</p>
            <h3 class="stat-value">{{ $stats['cold'] }}</h3>
        </div>
        <div class="stat-card stat-warm">
            <p class="stat-title">Warm Lead</p>
            <h3 class="stat-value">{{ $stats['warm'] }}</h3>
        </div>
        <div class="stat-card stat-hot">
            <p class="stat-title">Hot Prospek</p>
            <h3 class="stat-value">{{ $stats['hot'] }}</h3>
        </div>
        <div class="stat-card stat-gagal">
            <p class="stat-title">Gagal Closing</p>
            <h3 class="stat-value">{{ $stats['failed'] }}</h3>
        </div>
    </div>

    @if ($isAdminOrSuper)
        <div class="chart-grid">
            <div class="chart-card">
                <h4 class="chart-title">Statistik Sumber Lead</h4>
                <div class="chart-container">
                    <canvas id="sourceChart"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <h4 class="chart-title">Analisa Gagal Closing</h4>
                <div class="chart-container">
                    <canvas id="failReasonChart"></canvas>
                </div>
            </div>
        </div>
    @endif

    @if ($isSuper)
        <div class="dashboard-card">
            <div class="dashboard-card-header">
                Performa Tim Marketing
            </div>
            <div class="table-container" style="border: none;">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>Nama Marketing</th>
                            <th style="text-align: center;">Target (KPI)</th>
                            <th style="text-align: center;">Up Convert</th>
                            <th style="text-align: center;">Down Convert</th>
                            <th style="text-align: center;">Pencapaian</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($allKpiData as $kpi)
                            @php
                                $percent = $kpi->kpi_target > 0 ? ($kpi->up_convert / $kpi->kpi_target) * 100 : 0;
                                $badgeClass =
                                    $percent >= 100
                                        ? 'badge-kpi-success'
                                        : ($percent >= 50
                                            ? 'badge-kpi-warning'
                                            : 'badge-kpi-danger');
                            @endphp
                            <tr>
                                <td style="font-weight: 600;">{{ $kpi->nama_pic }}</td>
                                <td style="text-align: center;">{{ number_format($kpi->kpi_target) }}</td>
                                <td style="text-align: center; color: #166534; font-weight: 700;">
                                    {{ number_format($kpi->up_convert) }}</td>
                                <td style="text-align: center; color: #991b1b;">{{ number_format($kpi->down_convert) }}
                                </td>
                                <td style="text-align: center;">
                                    <span class="badge {{ $badgeClass }}">{{ number_format($percent, 1) }}%</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="dashboard-card">
        <div class="dashboard-card-header">
            <div>
                <i class="fas fa-clock" style="margin-right: 8px; color: #f59e0b;"></i>
                Follow Up Terdekat {{ $isSuper ? '(Global)' : '' }}
            </div>
            <span style="font-weight: 400; color: #64748b; font-size: 0.875rem;">Hari Ini:
                {{ now()->translatedFormat('d F Y') }}</span>
        </div>

        <div class="table-container" style="border: none;">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th style="width: 140px;">Jadwal</th>
                        <th>Nama Lead & Kontak</th>
                        @if ($isSuper)
                            <th>PIC</th>
                        @endif
                        <th>Status Lead</th>
                        <th>Catatan Terakhir</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($priorities as $fu)
                        @php
                            $targetDate = $fu->tgl_follow_up_berikutnya;
                            $isHMinus1 = $targetDate && \Carbon\Carbon::parse($targetDate)->isTomorrow();
                            $isToday = $targetDate && \Carbon\Carbon::parse($targetDate)->isToday();
                            $isOverdue = $targetDate && $targetDate < date('Y-m-d');

                            $dateColor = $isOverdue
                                ? '#dc2626'
                                : ($isToday
                                    ? '#ea580c'
                                    : ($isHMinus1
                                        ? '#b91c1c'
                                        : '#2563eb'));
                        @endphp

                        <tr style="{{ $isHMinus1 ? 'background: #fef2f2;' : '' }}">

                            <td style="padding: 1rem;">
                                <strong style="color: {{ $dateColor }}; font-size: 0.9rem;">
                                    @if ($isHMinus1)
                                        <i class="fas fa-bell"
                                            style="color: #ef4444; animation: swing 1s infinite; margin-right:4px;"></i>
                                    @endif
                                    {{ $targetDate ? \Carbon\Carbon::parse($targetDate)->translatedFormat('d M') : '-' }}
                                </strong>
                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 4px;">
                                    <i class="far fa-clock"></i>
                                    {{ $fu->jam_follow_up_berikutnya ? \Carbon\Carbon::parse($fu->jam_follow_up_berikutnya)->format('H:i') : '--:--' }}
                                </div>
                                @if ($isOverdue)
                                    <div
                                        style="font-size: 0.65rem; color: #dc2626; font-weight: 800; text-transform: uppercase; margin-top: 4px;">
                                        Terlewat!</div>
                                @endif
                            </td>

                            <td style="padding: 1rem;">
                                <strong>{{ $fu->lead->nama_lead }}</strong><br>
                                <small style="color: #64748b;">
                                    <i class="fab fa-whatsapp" style="color: #22c55e;"></i> {{ $fu->lead->no_whatsapp }}
                                </small>
                            </td>

                            @if ($isSuper)
                                <td style="padding: 1rem;">
                                    <span class="badge"
                                        style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;">{{ $fu->pic->nama_pic ?? '-' }}</span>
                                </td>
                            @endif

                            <td style="padding: 1rem;">
                                <form action="{{ route('leads.update', $fu->lead->id_lead) }}" method="POST">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="is_quick_update" value="1">
                                    <div style="position: relative; display: inline-block;">
                                        @php
                                            $slug = Str::slug($fu->lead->status_lead);
                                        @endphp

                                        <select name="status_lead" onchange="this.form.submit()"
                                            class="select-status status-{{ $slug }}">
                                            @foreach (['Cold Lead', 'Warm Lead', 'Hot Prospek', 'Deal', 'Tidak Prospek', 'Gagal Closing'] as $status)
                                                <option value="{{ $status }}"
                                                    {{ $fu->lead->status_lead == $status ? 'selected' : '' }}
                                                    style="background: white; color: #1e293b;">
                                                    {{ $status }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <i class="fas fa-chevron-down select-icon"></i>
                                    </div>
                                </form>
                            </td>

                            <td style="padding: 1rem;">
                                <p style="font-size: 0.8rem; color: #475569; line-height: 1.4; margin: 0;">
                                    {{ Str::limit($fu->hasil_follow_up, 60) }}</p>
                            </td>

                            <td style="text-align: center; padding: 1rem;">
                                <div style="display: flex; gap: 4px; justify-content: center; align-items: center;">
                                    <a href="{{ route('followup.followup_execute', $fu->id_lead) }}" class="btn"
                                        style="color: #b45309; background: #ffedd5; font-weight: 600; padding: 5px 12px; font-size: 0.75rem; width: auto; display: inline-block; border: 1px solid #fed7aa; text-decoration: none; border-radius: 4px;">
                                        Eksekusi
                                    </a>

                                    <a href="{{ route('followup.edit', $fu->id_follow_up) }}" class="btn"
                                        style="color: #166534; background: #dcfce7; font-weight: 600; padding: 5px 12px; font-size: 0.75rem; width: auto; display: inline-block; border: 1px solid #bbf7d0; text-decoration: none; border-radius: 4px;">
                                        Jadwal
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $isSuper ? 6 : 5 }}" class="text-center"
                                style="padding: 3rem; color: #94a3b8;">
                                <i class="fas fa-check-circle"
                                    style="font-size: 2rem; color: #cbd5e1; margin-bottom: 10px;"></i><br>
                                Tidak ada jadwal urgent saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @if ($isAdminOrSuper)
        <script>
            window.dashboardData = {
                sourceStats: @json($sourceStats ?? []),
                failStats: @json($failReasonStats ?? [])
            };
        </script>
        <script src="{{ asset('js/dashboard.js') }}"></script>
    @endif
@endsection
