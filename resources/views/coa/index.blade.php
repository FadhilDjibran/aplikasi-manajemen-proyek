@extends('layouts.app')
@section('title', 'Chart of Accounts')

@section('content')
    <div class="card"
        style="width: 100%; min-width:950px; max-width: 100%; border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">

        @if (session('success'))
            <div
                style="background-color: #f0fdf4; color: #166534; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #bbf7d0;">
                <div style="display: flex; align-items: center; gap: 8px; font-weight: 600;">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            </div>
        @endif

        <div
            style="padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; gap: 1rem;">

            <form action="{{ route('coa.index') }}" method="GET" id="filterForm"
                style="display: flex; align-items: center; gap: 8px; flex: 1;">

                <div style="position: relative; flex: 1; max-width: 300px;">
                    <i class="fas fa-search"
                        style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.9rem;"></i>
                    <input type="text" name="search" class="form-control" placeholder="Cari no. atau nama akun..."
                        value="{{ request('search') }}"
                        style="padding-left: 38px; border-radius: 8px; border: 1px solid #e2e8f0; height: 40px; width: 100%;">
                </div>

                <div style="display: flex; align-items: center; gap: 8px; position: relative;">
                    <div style="position: relative; display: flex; align-items: center;">
                        <select name="tahun" onchange="this.form.submit()"
                            style="background: #fff; border: 1px solid #e2e8f0; height: 40px; padding: 0 35px 0 15px; border-radius: 8px; font-weight: 600; color: #475569; appearance: none; cursor: pointer;">
                            @for ($i = date('Y'); $i >= date('Y') - 3; $i--)
                                <option value="{{ $i }}"
                                    {{ request('tahun', $tahun ?? date('Y')) == $i ? 'selected' : '' }}>
                                    Tahun {{ $i }}
                                </option>
                            @endfor
                        </select>
                        <i class="fas fa-calendar-alt"
                            style="position: absolute; right: 12px; color: #94a3b8; pointer-events: none;"></i>
                    </div>

                    <button type="button" id="btnFilter" class="btn"
                        style="background: #fff; border: 1px solid #e2e8f0; height: 40px; padding: 0 15px; border-radius: 8px; display: flex; align-items: center; gap: 8px; font-weight: 600; color: #475569; white-space: nowrap;">
                        <i class="fas fa-filter"
                            style="color: {{ request()->hasAny(['posisi_filter', 'laporan_filter']) ? '#2563eb' : '#94a3b8' }}"></i>
                        Filter
                        @php
                            $activeFilters = count(array_filter(request()->only(['posisi_filter', 'laporan_filter'])));
                        @endphp
                        @if ($activeFilters > 0)
                            <span
                                style="background: #2563eb; color: #fff; border-radius: 50%; width: 18px; height: 18px; font-size: 0.7rem; display: flex; align-items: center; justify-content: center;">{{ $activeFilters }}</span>
                        @endif
                    </button>

                    @if (request()->hasAny(['search', 'posisi_filter', 'laporan_filter']))
                        <a href="{{ route('coa.index') }}" class="btn-reset-filter" title="Bersihkan Semua Filter"
                            style="color: #ef4444; padding: 8px; border: 1px solid #fecaca; border-radius: 8px; background: #fef2f2;">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif

                    <div id="filterPanel"
                        style="display: none; position: absolute; top: 48px; left: 0; z-index: 100; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); padding: 1.5rem; min-width: 250px;">
                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div>
                                <label
                                    style="display: block; font-size: 0.75rem; font-weight: 700; color: #64748b; margin-bottom: 5px;">POSISI
                                    NORMAL</label>
                                <div style="position: relative;">
                                    <select name="posisi_filter" class="form-control"
                                        style="font-size: 0.85rem; border-radius: 6px; appearance: none; -webkit-appearance: none; padding-right: 30px; cursor: pointer;">
                                        <option value="">Semua Posisi</option>
                                        <option value="Debit" {{ request('posisi_filter') == 'Debit' ? 'selected' : '' }}>
                                            Debit (Debit)</option>
                                        <option value="Kredit"
                                            {{ request('posisi_filter') == 'Kredit' ? 'selected' : '' }}>Kredit (Kredit)
                                        </option>
                                    </select>
                                    <i class="fas fa-chevron-down"
                                        style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 0.7rem; color: #94a3b8; pointer-events: none;"></i>
                                </div>
                            </div>
                            <div>
                                <label
                                    style="display: block; font-size: 0.75rem; font-weight: 700; color: #64748b; margin-bottom: 5px;">JENIS
                                    LAPORAN</label>
                                <div style="position: relative;">
                                    <select name="laporan_filter" class="form-control"
                                        style="font-size: 0.85rem; border-radius: 6px; appearance: none; -webkit-appearance: none; padding-right: 30px; cursor: pointer;">
                                        <option value="">Semua Laporan</option>
                                        <option value="Neraca"
                                            {{ request('laporan_filter') == 'Neraca' ? 'selected' : '' }}>Neraca (NRC)
                                        </option>
                                        <option value="Laba Rugi"
                                            {{ request('laporan_filter') == 'Laba Rugi' ? 'selected' : '' }}>Laba Rugi (LR)
                                        </option>
                                    </select>
                                    <i class="fas fa-chevron-down"
                                        style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 0.7rem; color: #94a3b8; pointer-events: none;"></i>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary"
                                style="width: 100%; height: 38px; font-weight: 600; font-size: 0.85rem; display: flex; align-items: center; justify-content: center;">Terapkan
                                Filter</button>
                        </div>
                    </div>
                </div>
            </form>

            <div style="display: flex; gap: 8px;">
                <form action="{{ route('coa.rollover') }}" method="POST" style="margin: 0;"
                    onsubmit="return confirm('Proses ini akan menduplikasi struktur akun tahun {{ $tahun ?? date('Y') }} ke tahun {{ ($tahun ?? date('Y')) + 1 }}. Lanjutkan?');">
                    @csrf
                    <input type="hidden" name="tahun_asal" value="{{ $tahun ?? date('Y') }}">
                    <input type="hidden" name="tahun_tujuan" value="{{ ($tahun ?? date('Y')) + 1 }}">

                    <button type="submit" class="btn"
                        style="height: 40px; border-radius: 8px; font-weight: 600; background: #f59e0b; color: white; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 1rem; border: none; cursor: pointer;">
                        <i class="fas fa-copy"></i> Rollover
                    </button>
                </form>

                <a href="{{ route('coa.create') }}" class="btn btn-primary"
                    style="height: 40px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 1rem; white-space: nowrap;">
                    <i class="fas fa-plus"></i> Tambah Akun
                </a>
            </div>
        </div>

        <div class="table-container" style="padding: 0; margin: 0; overflow-x: auto;">
            <table class="custom-table" style="font-size: 0.85rem; width: 100%; border-collapse: collapse;">
                <thead
                    style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.75rem;">
                    <tr>
                        <th style="padding: 12px 10px; width: 40px; text-align: center;">NO</th>
                        <th style="padding: 12px 10px; text-align: left;">Kategori Akun</th>
                        <th style="padding: 12px 10px; text-align: left;">Nama Akun</th>
                        <th style="padding: 12px 5px; width: 50px; text-align: center;">Posisi</th>
                        <th style="padding: 12px 5px; width: 50px; text-align: center;">Jenis</th>
                        <th style="padding: 12px 10px; text-align: right; width: 110px;">Saldo Awal (Debit)</th>
                        <th style="padding: 12px 10px; text-align: right; width: 110px;">Saldo Awal (Kredit)</th>
                        <th style="padding: 12px 10px; text-align: right; width: 120px;">Saldo Akhir</th>
                        <th style="padding: 12px 10px; text-align: center; width: 90px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $sumDebit = 0;
                        $sumKredit = 0;
                        $sumAkhir = 0;
                    @endphp
                    @forelse($coas as $coa)
                        @php
                            $sumDebit += $coa->saldo_awal_debit;
                            $sumKredit += $coa->saldo_awal_kredit;
                            $sumAkhir += $coa->saldo_akhir;
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;">
                            <td
                                style="padding: 6px 10px; color: #64748b; font-weight: 700; font-size: 0.7rem; text-align: center;">
                                {{ $coa->no_akun }}</td>
                            <td style="padding: 6px 10px; color: #475569; font-size: 0.8rem;">{{ $coa->kategori_akun }}
                            </td>
                            <td style="padding: 6px 10px;">
                                <div style="font-weight: 700; color: #1e293b; font-size: 0.8rem;">{{ $coa->nama_akun }}
                                </div>
                            </td>
                            <td style="padding: 6px 5px; text-align: center;">
                                <span
                                    style="{{ $coa->posisi_normal == 'Debit' ? 'background: #eff6ff; color: #2563eb; border: 1px solid #dbeafe;' : 'background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5;' }} padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: 600;">
                                    {{ $coa->posisi_normal == 'Debit' ? 'D' : 'K' }}
                                </span>
                            </td>
                            <td style="padding: 6px 5px; text-align: center;">
                                <span
                                    style="background: #f1f5f9; color: #64748b; padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: 600; border: 1px solid #e2e8f0;">
                                    {{ $coa->jenis_laporan == 'Neraca' ? 'NRC' : 'LR' }}
                                </span>
                            </td>
                            <td style="padding: 6px 10px; text-align: right; font-size: 0.85rem; color: #64748b;">
                                {{ $coa->saldo_awal_debit != 0 ? number_format($coa->saldo_awal_debit, 2, ',', '.') : '-' }}
                            </td>
                            <td style="padding: 6px 10px; text-align: right; font-size: 0.85rem; color: #64748b;">
                                {{ $coa->saldo_awal_kredit != 0 ? number_format($coa->saldo_awal_kredit, 2, ',', '.') : '-' }}
                            </td>
                            <td
                                style="padding: 6px 10px; text-align: right; font-size: 0.9rem; font-weight: 700; color: {{ $coa->saldo_akhir < 0 ? '#ef4444' : '#0f172a' }};">
                                {{ (float) $coa->saldo_akhir != 0 ? number_format($coa->saldo_akhir, 2, ',', '.') : '-' }}
                            </td>
                            <td style="padding: 6px 10px; text-align: center;">
                                <div style="display: flex; gap: 4px; justify-content: center;">
                                    <a href="{{ route('coa.edit', $coa->id) }}" class="btn"
                                        style="color: #059669; background: #ecfdf5; padding: 4px 8px; font-size: 0.75rem; border: 1px solid #bbf7d0; border-radius: 4px;"><i
                                            class="fas fa-edit"></i></a>
                                    <form action="{{ route('coa.destroy', $coa->id) }}" method="POST"
                                        style="margin: 0;">
                                        @csrf @method('DELETE')
                                        <button type="submit" onclick="return confirm('Hapus akun?')" class="btn"
                                            style="color: #dc2626; background: #fef2f2; padding: 4px 8px; font-size: 0.75rem; border: 1px solid #fecaca; border-radius: 4px;"><i
                                                class="fas fa-trash-alt"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" style="padding: 3rem; text-align: center; color: #94a3b8;"><i
                                    class="fas fa-folder-open"
                                    style="font-size: 2rem; display: block; margin-bottom: 0.5rem; opacity: 0.3;"></i>
                                Belum ada data.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="background: #f8fafc; padding: 1.5rem; border-top: 1px solid #e2e8f0; border-radius: 0 0 12px 12px;">
            <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.5rem;">
                <div style="background: #fff; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <span
                        style="display: block; font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Total
                        Akun</span>
                    <span style="font-size: 1rem; font-weight: 800; color: #1e293b;">{{ count($coas) }} <small
                            style="font-weight: 500; font-size: 0.75rem;">Akun</small></span>
                </div>
                <div style="background: #fff; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <span
                        style="display: block; font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Total
                        Saldo Awal (Debit)</span>
                    <span style="font-size: 1rem; font-weight: 800; color: #2563eb;">Rp
                        {{ number_format($sumDebit, 2, ',', '.') }}</span>
                </div>
                <div style="background: #fff; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <span
                        style="display: block; font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Total
                        Saldo Awal (Kredit)</span>
                    <span style="font-size: 1rem; font-weight: 800; color: #ea580c;">Rp
                        {{ number_format($sumKredit, 2, ',', '.') }}</span>
                </div>
                <div
                    style="background: #fff; padding: 1rem; border-radius: 8px; border: 1px solid #2563eb; background: #eff6ff;">
                    <span
                        style="display: block; font-size: 0.7rem; font-weight: 700; color: #2563eb; text-transform: uppercase;">Total
                        Saldo Akhir</span>
                    <span style="font-size: 1rem; font-weight: 800; color: #1e293b;">Rp
                        {{ number_format($sumAkhir, 2, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnFilter = document.getElementById('btnFilter');
            const filterPanel = document.getElementById('filterPanel');

            if (btnFilter && filterPanel) {
                btnFilter.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    const isHidden = filterPanel.style.display === 'none' || filterPanel.style.display ===
                        '';
                    filterPanel.style.display = isHidden ? 'block' : 'none';
                });

                document.addEventListener('click', function(e) {
                    if (!filterPanel.contains(e.target) && e.target !== btnFilter && !btnFilter.contains(e
                            .target)) {
                        filterPanel.style.display = 'none';
                    }
                });
            }
        });
    </script>
@endsection
