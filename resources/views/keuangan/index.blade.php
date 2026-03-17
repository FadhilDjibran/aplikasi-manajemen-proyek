@extends('layouts.app')
@section('title', 'Database Keuangan')

@section('content')
    <div class="card" style="width: 100%; max-width: 100%; border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">

        @if (session('success'))
            <div
                style="background-color: #f0fdf4; color: #166534; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #bbf7d0;">
                <div style="display: flex; align-items: center; gap: 8px; font-weight: 600;">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            </div>
        @endif
        @if (isset($pendingApprovalsCount) && $pendingApprovalsCount > 0)
            <div
                style="background-color: #fffbeb; color: #b45309; padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #fde68a; display: flex; justify-content: space-between; align-items: center;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div
                        style="background: #f59e0b; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 5px rgba(245, 158, 11, 0.3);">
                        <i class="fas fa-bell" style="font-size: 1.1rem;"></i>
                    </div>
                    <div>
                        <strong style="display: block; font-size: 1.05rem; color: #92400e; margin-bottom: 2px;">Butuh
                            Approval</strong>
                        <span style="font-size: 0.9rem; color: #b45309;">Ada <b>{{ $pendingApprovalsCount }}</b> transaksi
                            booking leads yang perlu diapproval.</span>
                    </div>
                </div>
                <a href="{{ route('keuangan.pending') }}" class="btn"
                    style="max-width: 160px; background: #f59e0b; color: white; padding: 0.6rem 1.2rem; border-radius: 6px; text-decoration: none; font-weight: 600; font-size: 0.9rem; box-shadow: 0 2px 4px rgba(245, 158, 11, 0.2); white-space: nowrap; transition: 0.2s;">
                    Lihat Antrian &rarr;
                </a>
            </div>
        @endif

        <div
            style="padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; gap: 1rem;">

            <form action="{{ route('keuangan.index') }}" method="GET" id="filterForm"
                style="display: flex; align-items: center; gap: 8px; flex: 1;">

                <div style="position: relative; flex: 1; max-width: 300px;">
                    <i class="fas fa-search"
                        style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.9rem;"></i>
                    <input type="text" name="search" class="form-control" placeholder="Cari keterangan / no. akun..."
                        value="{{ request('search') }}"
                        style="padding-left: 38px; border-radius: 8px; border: 1px solid #e2e8f0; height: 40px; width: 100%;">
                </div>

                <div style="display: flex; align-items: center; gap: 8px; position: relative;">

                    <div style="position: relative; display: flex; align-items: center;">
                        <select name="tahun" onchange="this.form.submit()"
                            style="background: #fff; border: 1px solid #e2e8f0; height: 40px; padding: 0 35px 0 15px; border-radius: 8px; font-weight: 600; color: #475569; appearance: none; cursor: pointer;">
                            @for ($i = date('Y'); $i >= date('Y') - 3; $i--)
                                <option value="{{ $i }}"
                                    {{ request('tahun', date('Y')) == $i ? 'selected' : '' }}>
                                    Tahun {{ $i }}
                                </option>
                            @endfor
                        </select>
                        <i class="fas fa-calendar-alt"
                            style="position: absolute; right: 12px; color: #94a3b8; pointer-events: none;"></i>
                    </div>

                    @php
                        $activeFilters = 0;
                        if (request()->filled('input_filter')) {
                            $activeFilters++;
                        }
                        if (request()->filled('coa_filter')) {
                            $activeFilters++;
                        }
                    @endphp

                    <button type="button" id="btnFilter" class="btn"
                        style="background: #fff; border: 1px solid #e2e8f0; height: 40px; padding: 0 15px; border-radius: 8px; display: flex; align-items: center; gap: 8px; font-weight: 600; color: #475569; white-space: nowrap;">
                        <i class="fas fa-filter" style="color: {{ $activeFilters > 0 ? '#2563eb' : '#94a3b8' }}"></i>
                        Filter
                        @if ($activeFilters > 0)
                            <span
                                style="background: #2563eb; color: #fff; border-radius: 50%; width: 18px; height: 18px; font-size: 0.7rem; display: flex; align-items: center; justify-content: center;">
                                {{ $activeFilters }}
                            </span>
                        @endif
                    </button>

                    @if (request()->hasAny(['search', 'input_filter', 'coa_filter']))
                        <a href="{{ route('keuangan.index') }}" class="btn-reset-filter" title="Bersihkan Filter"
                            style="color: #ef4444; padding: 8px; border: 1px solid #fecaca; border-radius: 8px; background: #fef2f2;">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif

                    <div id="filterPanel"
                        style="display: none; position: absolute; top: 48px; left: 0; z-index: 100; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); padding: 1.5rem; min-width: 320px;">
                        <div style="display: flex; flex-direction: column; gap: 1rem;">

                            <div>
                                <label
                                    style="display: block; font-size: 0.75rem; font-weight: 700; color: #64748b; margin-bottom: 5px;">TIPE
                                    INPUT</label>
                                <div style="position: relative;">
                                    <select name="input_filter" class="form-control"
                                        style="font-size: 0.85rem; border-radius: 6px; appearance: none; -webkit-appearance: none; padding-right: 30px; cursor: pointer;">
                                        <option value="">Semua Input</option>
                                        <option value="Kas Besar"
                                            {{ request('input_filter') == 'Kas Besar' ? 'selected' : '' }}>Kas Besar
                                        </option>
                                        <option value="Kas Kecil"
                                            {{ request('input_filter') == 'Kas Kecil' ? 'selected' : '' }}>Kas Kecil
                                        </option>
                                        <option value="Bank" {{ request('input_filter') == 'Bank' ? 'selected' : '' }}>
                                            Bank</option>
                                        <option value="Jurnal" {{ request('input_filter') == 'Jurnal' ? 'selected' : '' }}>
                                            Jurnal Umum</option>
                                    </select>
                                    <i class="fas fa-chevron-down"
                                        style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); font-size: 0.7rem; color: #94a3b8; pointer-events: none;"></i>
                                </div>
                            </div>

                            <div class="form-group" style="margin-bottom: 0;">
                                <label
                                    style="display: block; font-size: 0.75rem; font-weight: 700; color: #64748b; margin-bottom: 5px;">PILIH
                                    AKUN (COA)</label>
                                <select name="coa_filter" id="select-coa" class="form-control"
                                    placeholder="-- Semua Akun --">
                                    <option value="">-- Semua Akun --</option>
                                    @php
                                        // Variabel $coa ini akan aman dari duplikat karena sudah difilter dari Controller
                                        $groupedCoa = isset($coa) ? $coa->groupBy('kategori_akun') : collect();
                                    @endphp

                                    @foreach ($groupedCoa as $kategori => $akunList)
                                        <optgroup label="{{ $kategori }}">
                                            @foreach ($akunList as $item)
                                                <option value="{{ $item->no_akun }}"
                                                    {{ request('coa_filter') == $item->no_akun ? 'selected' : '' }}>
                                                    {{ $item->no_akun }} - {{ $item->nama_akun }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary"
                                style="width: 100%; height: 38px; font-weight: 600; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; margin-top: 5px;">
                                Terapkan Filter
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <div style="display: flex; gap: 8px; flex-shrink: 0;">
                <a href="{{ route('keuangan.create') }}" class="btn btn-primary"
                    style="height: 40px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 0 1rem; white-space: nowrap;">
                    <i class="fas fa-plus"></i> Tambah Transaksi
                </a>
            </div>
        </div>

        <div class="table-container" style="padding: 0; margin: 0; overflow-x: auto;">
            <table class="custom-table" style="font-size: 0.85rem; width: 100%; border-collapse: collapse;">
                <thead
                    style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.75rem;">
                    <tr>
                        <th style="padding: 12px 15px; max-width: 80px;">Tanggal</th>
                        <th style="padding: 12px 15px; width: 100px; text-align: center;">Tipe Input</th>
                        <th style="padding: 12px 15px; min-width: 150px;">Akun (CoA)</th>
                        <th style="padding: 12px 15px; min-width: 200px;">Keterangan & Jenis</th>
                        <th style="padding: 12px 15px; text-align: center; width: 120px;">Mutasi Masuk</th>
                        <th style="padding: 12px 15px; text-align: center; width: 120px;">Mutasi Keluar</th>
                        <th style="padding: 12px 5px; text-align: center; width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($keuangan as $item)
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;">
                            <td style="padding: 8px 15px; color: #64748b;">
                                {{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}
                            </td>

                            <td style="padding: 8px 15px; text-align: center;">
                                @php
                                    $bgInput = match ($item->input) {
                                        'Kas Besar'
                                            => 'background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe;',
                                        'Kas Kecil'
                                            => 'background: #fef3c7; color: #92400e; border: 1px solid #fde68a;',
                                        'Bank' => 'background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0;',
                                        'Jurnal' => 'background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb;',
                                        default => 'background: #f1f5f9; color: #64748b;',
                                    };
                                @endphp
                                <span
                                    style="{{ $bgInput }} padding: 3px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 600; white-space: nowrap;">
                                    {{ $item->input }}
                                </span>
                            </td>

                            <td style="padding: 8px 15px;">
                                <div style="font-weight: 700; color: #1e293b; font-size: 0.85rem;">{{ $item->no_akun }}
                                </div>
                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                                    {{ $item->coa->nama_akun ?? 'Akun Dihapus' }}</div>
                            </td>

                            <td style="padding: 8px 15px;">
                                <div style="color: #334155; line-height: 1.4;">{{ $item->keterangan }}</div>
                                @if ($item->jenis_penggunaan)
                                    <span
                                        style="display: inline-block; margin-top: 4px; background: #f8fafc; color: #64748b; padding: 2px 6px; border-radius: 4px; font-size: 0.65rem; border: 1px solid #e2e8f0;">
                                        <i class="fas fa-tag" style="margin-right: 3px;"></i>
                                        {{ $item->jenis_penggunaan }}
                                    </span>
                                @endif
                            </td>

                            <td
                                style="padding: 8px 15px; text-align: right; font-size: 0.9rem; color: #059669; font-weight: 700;">
                                {{ (float) $item->mutasi_masuk != 0 ? number_format($item->mutasi_masuk, 2, ',', '.') : '-' }}
                            </td>

                            <td
                                style="padding: 8px 15px; text-align: right; font-size: 0.9rem; color: #dc2626; font-weight: 700;">
                                {{ (float) $item->mutasi_keluar != 0 ? number_format($item->mutasi_keluar, 2, ',', '.') : '-' }}
                            </td>

                            <td style="padding: 8px 15px; text-align: center; vertical-align: middle;">
                                <div style="display: flex; gap: 4px; justify-content: center; align-items: center;">

                                    @if ($item->bukti)
                                        <a href="{{ asset('storage/' . $item->bukti) }}" target="_blank" class="btn"
                                            style="color: #2563eb; background: #eff6ff; padding: 4px 8px; font-size: 0.75rem; border: 1px solid #dbeafe; border-radius: 4px;"
                                            title="Lihat Bukti">
                                            <i class="fas fa-paperclip"></i>
                                        </a>
                                    @else
                                        <span style="color: #cbd5e1; font-size: 0.8rem; padding: 4px 8px;">-</span>
                                    @endif

                                    <a href="{{ route('keuangan.edit', $item->id) }}" class="btn"
                                        style="color: #059669; background: #ecfdf5; padding: 4px 8px; font-size: 0.75rem; border: 1px solid #bbf7d0; border-radius: 4px;"
                                        title="Edit"><i class="fas fa-edit"></i></a>

                                    <form action="{{ route('keuangan.destroy', $item->id) }}" method="POST"
                                        style="margin: 0; display: inline-block;">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn"
                                            style="color: #dc2626; background: #fef2f2; padding: 4px 8px; font-size: 0.75rem; border: 1px solid #fecaca; border-radius: 4px;"
                                            title="Hapus"
                                            onclick="return confirm('Yakin ingin menghapus transaksi ini?')"><i
                                                class="fas fa-trash-alt"></i></button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center" style="padding: 3rem; color: #94a3b8;">
                                <i class="fas fa-receipt"
                                    style="font-size: 2rem; display: block; margin-bottom: 0.5rem; opacity: 0.3;"></i>
                                <span style="font-size: 0.9rem;">Belum ada transaksi keuangan.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="custom-pagination-wrapper" style="padding: 1rem 1.5rem; border-top: 1px solid #f1f5f9;">
            {{ $keuangan->withQueryString()->links() }}
        </div>
    </div>

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

                filterPanel.addEventListener('click', function(e) {
                    e.stopPropagation();
                });

                document.addEventListener('click', function(e) {
                    if (filterPanel.style.display === 'block') {
                        filterPanel.style.display = 'none';
                    }
                });
            }
        });
    </script>
@endsection
