@extends('layouts.app')
@section('title', 'Laporan Laba Rugi')

@section('content')
    <div class="card"
        style="min-width: 960px; max-width: 1000px; margin: 0 auto; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">

        <div
            style="padding: 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0;">
            <div>
                <h3 style="margin: 0; color: #1e293b; font-weight: 800;">Laporan Laba Rugi</h3>
                <p style="margin: 0; color: #64748b; font-size: 0.9rem;">
                    Periode:
                    @if ($bulan === 'all')
                        Tahun {{ $tahun }}
                    @else
                        {{ date('F Y', strtotime($tahun . '-' . $bulan . '-01')) }}
                    @endif
                </p>
            </div>

            <div style="display: flex; gap: 10px; align-items: center;">
                <form method="GET" style="display: flex; gap: 10px; margin: 0;">
                    <select name="bulan" class="form-control" style="width: 160px; border-radius: 6px;">
                        <option value="all" {{ $bulan === 'all' ? 'selected' : '' }}>Setahun Penuh</option>
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ sprintf('%02d', $i) }}" {{ $bulan == sprintf('%02d', $i) ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                            </option>
                        @endfor
                    </select>

                    <select name="tahun" class="form-control" style="width: 100px; border-radius: 6px;">
                        @for ($i = date('Y') + 1; $i >= date('Y') - 3; $i--)
                            <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}
                            </option>
                        @endfor
                    </select>
                    <button type="submit" class="btn btn-primary" style="border-radius: 6px;">Filter</button>
                </form>

                <a href="{{ route('laporan.laba_rugi.excel', request()->query()) }}" class="btn btn-primary"
                    style="border-radius: 6px;">
                    <i class="fas fa-file-excel"></i> Ekspor Excel
                </a>
            </div>
        </div>

        <div style="padding: 2rem;">
            <table style="width: 100%; border-collapse: collapse; font-family: 'Arial', sans-serif; font-size: 0.85rem;">

                @php
                    function formatRp($angka)
                    {
                        $val = (float) $angka;
                        if ($val == 0) {
                            return '-';
                        }
                        if ($val < 0) {
                            return '(' . number_format(abs($val), 0, ',', '.') . ')';
                        }
                        return number_format($val, 0, ',', '.');
                    }

                    function formatPersen($angka, $total)
                    {
                        $val = (float) $angka;
                        $tot = (float) $total;

                        if (empty($tot) || $tot == 0 || empty($val) || $val == 0) {
                            return '0,0%';
                        }

                        $persen = ($val / $tot) * 100;
                        if ($persen < 0) {
                            return '(' . number_format(abs($persen), 1, ',', '.') . '%)';
                        }
                        return number_format($persen, 1, ',', '.') . '%';
                    }

                    if ($bulan === 'all') {
                        $judulSekarang = $tahun;
                        $judulLalu = $tahun - 1;
                    } else {
                        $judulSekarang = date('M Y', strtotime($tahun . '-' . $bulan . '-01'));
                        $waktuLalu = strtotime('-1 month', strtotime($tahun . '-' . $bulan . '-01'));
                        $judulLalu = date('M Y', $waktuLalu);
                    }

                    $basePersenLalu = $basePersenLalu ?? 0;
                @endphp

                <thead>
                    <tr style="border-bottom: 2px solid #cbd5e1;">
                        <th style="text-align: left; padding: 10px; width: 50px;">NO</th>
                        <th style="text-align: left; padding: 10px;">NAMA AKUN</th>
                        <th
                            style="text-align: right; padding: 10px; width: 140px; border-bottom: 2px solid #000; color: #0f172a;">
                            {{ $judulSekarang }}</th>
                        <th style="text-align: right; padding: 10px; width: 70px;">%</th>
                        <th
                            style="text-align: right; padding: 10px; width: 140px; border-bottom: 2px solid #000; color: #64748b;">
                            {{ $judulLalu }}</th>
                        <th style="text-align: right; padding: 10px; width: 70px; color: #64748b;">%</th>
                    </tr>
                </thead>
                <tbody>

                    <tr>
                        <td colspan="6"
                            style="font-weight: bold; text-decoration: underline; padding: 15px 5px 5px 5px;">PENDAPATAN
                        </td>
                    </tr>
                    @foreach ($laporan['pendapatan'] as $no => $akun)
                        <tr>
                            <td style="text-align: center; padding: 4px;">{{ $no }}</td>
                            <td style="padding: 4px;">{{ $akun['nama'] }}</td>
                            <td style="text-align: right; padding: 4px;">{{ formatRp($akun['saldo']) }}</td>
                            <td style="text-align: right; padding: 4px; color: #64748b;">
                                {{ formatPersen($akun['saldo'], $basePersen) }}</td>
                            <td style="text-align: right; padding: 4px; color: #475569;">
                                {{ formatRp($akun['saldo_lalu'] ?? 0) }}</td>
                            <td style="text-align: right; padding: 4px; color: #94a3b8;">
                                {{ formatPersen($akun['saldo_lalu'] ?? 0, $basePersenLalu) }}</td>
                        </tr>
                    @endforeach
                    <tr style="background-color: #f1f5f9; font-weight: bold;">
                        <td colspan="2" style="text-align: right; padding: 8px;">PENJUALAN BERSIH</td>
                        <td style="text-align: right; padding: 8px; border-top: 1px solid #000;">
                            {{ formatRp($totalPendapatan) }}</td>
                        <td style="text-align: right; padding: 8px;">{{ formatPersen($totalPendapatan, $totalPendapatan) }}
                        </td>
                        <td style="text-align: right; padding: 8px; border-top: 1px solid #000; color: #475569;">
                            {{ formatRp($totalPendapatanLalu ?? 0) }}</td>
                        <td style="text-align: right; padding: 8px; color: #94a3b8;">
                            {{ formatPersen($totalPendapatanLalu ?? 0, $totalPendapatanLalu ?? 0) }}</td>
                    </tr>

                    <tr>
                        <td colspan="6"
                            style="font-weight: bold; text-decoration: underline; padding: 20px 5px 5px 5px;">HARGA POKOK
                            PRODUKSI</td>
                    </tr>
                    @foreach ($laporan['beban_pokok'] as $no => $akun)
                        <tr>
                            <td style="text-align: center; padding: 4px;">{{ $no }}</td>
                            <td style="padding: 4px;">{{ $akun['nama'] }}</td>
                            <td style="text-align: right; padding: 4px;">{{ formatRp($akun['saldo']) }}</td>
                            <td style="text-align: right; padding: 4px; color: #64748b;">
                                {{ formatPersen($akun['saldo'], $basePersen) }}</td>
                            <td style="text-align: right; padding: 4px; color: #475569;">
                                {{ formatRp($akun['saldo_lalu'] ?? 0) }}</td>
                            <td style="text-align: right; padding: 4px; color: #94a3b8;">
                                {{ formatPersen($akun['saldo_lalu'] ?? 0, $basePersenLalu) }}</td>
                        </tr>
                    @endforeach
                    <tr style="background-color: #f1f5f9; font-weight: bold;">
                        <td colspan="2" style="text-align: right; padding: 8px;">TOTAL BEBAN POKOK PENJUALAN</td>
                        <td style="text-align: right; padding: 8px; border-top: 1px solid #000;">
                            {{ formatRp($totalBebanPokok) }}</td>
                        <td style="text-align: right; padding: 8px;">{{ formatPersen($totalBebanPokok, $totalPendapatan) }}
                        </td>
                        <td style="text-align: right; padding: 8px; border-top: 1px solid #000; color: #475569;">
                            {{ formatRp($totalBebanPokokLalu ?? 0) }}</td>
                        <td style="text-align: right; padding: 8px; color: #94a3b8;">
                            {{ formatPersen($totalBebanPokokLalu ?? 0, $totalPendapatanLalu ?? 0) }}</td>
                    </tr>

                    <tr
                        style="background-color: #ffff00; font-weight: bold; border-top: 2px solid #000; border-bottom: 2px solid #000;">
                        <td colspan="2" style="text-align: center; padding: 12px 8px; font-size: 1rem;">LABA (RUGI) KOTOR
                        </td>
                        <td style="text-align: right; padding: 12px 8px; font-size: 1rem;">{{ formatRp($labaKotor) }}</td>
                        <td style="text-align: right; padding: 12px 8px;"></td>
                        <td style="text-align: right; padding: 12px 8px; font-size: 1rem; color: #475569;">
                            {{ formatRp($labaKotorLalu ?? 0) }}</td>
                        <td style="text-align: right; padding: 12px 8px;"></td>
                    </tr>

                    <tr>
                        <td colspan="6"
                            style="font-weight: bold; text-decoration: underline; padding: 25px 5px 5px 5px;">BIAYA
                            PEMASARAN</td>
                    </tr>
                    @foreach ($laporan['beban_pemasaran'] as $no => $akun)
                        <tr>
                            <td style="text-align: center; padding: 4px;">{{ $no }}</td>
                            <td style="padding: 4px;">{{ $akun['nama'] }}</td>
                            <td style="text-align: right; padding: 4px;">{{ formatRp($akun['saldo']) }}</td>
                            <td style="text-align: right; padding: 4px; color: #64748b;">
                                {{ formatPersen($akun['saldo'], $basePersen) }}</td>
                            <td style="text-align: right; padding: 4px; color: #475569;">
                                {{ formatRp($akun['saldo_lalu'] ?? 0) }}</td>
                            <td style="text-align: right; padding: 4px; color: #94a3b8;">
                                {{ formatPersen($akun['saldo_lalu'] ?? 0, $basePersenLalu) }}</td>
                        </tr>
                    @endforeach
                    <tr style="background-color: #f1f5f9; font-weight: bold;">
                        <td colspan="2" style="text-align: right; padding: 8px;">TOTAL BIAYA PEMASARAN</td>
                        <td style="text-align: right; padding: 8px; border-top: 1px solid #000;">
                            {{ formatRp($totalPemasaran) }}</td>
                        <td style="text-align: right; padding: 8px;">{{ formatPersen($totalPemasaran, $basePersen) }}</td>
                        <td style="text-align: right; padding: 8px; border-top: 1px solid #000; color: #475569;">
                            {{ formatRp($totalPemasaranLalu ?? 0) }}</td>
                        <td style="text-align: right; padding: 8px; color: #94a3b8;">
                            {{ formatPersen($totalPemasaranLalu ?? 0, $basePersenLalu) }}</td>
                    </tr>

                    <tr>
                        <td colspan="6"
                            style="font-weight: bold; text-decoration: underline; padding: 25px 5px 5px 5px;">BIAYA UMUM &
                            ADMINISTRASI</td>
                    </tr>
                    @foreach ($laporan['biaya_umum'] as $no => $akun)
                        <tr>
                            <td style="text-align: center; padding: 4px;">{{ $no }}</td>
                            <td style="padding: 4px;">{{ $akun['nama'] }}</td>
                            <td style="text-align: right; padding: 4px;">{{ formatRp($akun['saldo']) }}</td>
                            <td style="text-align: right; padding: 4px; color: #64748b;">
                                {{ formatPersen($akun['saldo'], $basePersen) }}</td>
                            <td style="text-align: right; padding: 4px; color: #475569;">
                                {{ formatRp($akun['saldo_lalu'] ?? 0) }}</td>
                            <td style="text-align: right; padding: 4px; color: #94a3b8;">
                                {{ formatPersen($akun['saldo_lalu'] ?? 0, $basePersenLalu) }}</td>
                        </tr>
                    @endforeach
                    <tr style="background-color: #f1f5f9; font-weight: bold;">
                        <td colspan="2" style="text-align: right; padding: 8px;">TOTAL BIAYA UMUM & ADMINISTRASI</td>
                        <td style="text-align: right; padding: 8px; border-top: 1px solid #000;">{{ formatRp($totalUmum) }}
                        </td>
                        <td style="text-align: right; padding: 8px;">{{ formatPersen($totalUmum, $basePersen) }}</td>
                        <td style="text-align: right; padding: 8px; border-top: 1px solid #000; color: #475569;">
                            {{ formatRp($totalUmumLalu ?? 0) }}</td>
                        <td style="text-align: right; padding: 8px; color: #94a3b8;">
                            {{ formatPersen($totalUmumLalu ?? 0, $basePersenLalu) }}</td>
                    </tr>

                    <tr
                        style="background-color: #ffff00; font-weight: bold; border-top: 2px solid #000; border-bottom: 2px solid #000;">
                        <td colspan="2" style="text-align: center; padding: 12px 8px; font-size: 1rem;">LABA (RUGI)
                            OPERASIONAL</td>
                        <td style="text-align: right; padding: 12px 8px; font-size: 1rem;">
                            {{ formatRp($labaOperasional) }}
                        </td>
                        <td style="text-align: right; padding: 12px 8px;"></td>
                        <td style="text-align: right; padding: 12px 8px; font-size: 1rem; color: #475569;">
                            {{ formatRp($labaOperasionalLalu ?? 0) }}</td>
                        <td style="text-align: right; padding: 12px 8px;"></td>
                    </tr>

                    <tr>
                        <td colspan="6"
                            style="font-weight: bold; text-decoration: underline; padding: 25px 5px 5px 5px;">PENDAPATAN &
                            BIAYA (NON OPR)</td>
                    </tr>
                    @foreach ($laporan['pendapatan_biaya_luar'] as $no => $akun)
                        <tr>
                            <td style="text-align: center; padding: 4px;">{{ $no }}</td>
                            <td style="padding: 4px;">{{ $akun['nama'] }}</td>
                            <td style="text-align: right; padding: 4px;">{{ formatRp($akun['saldo']) }}</td>
                            <td style="text-align: right; padding: 4px; color: #64748b;">
                                {{ formatPersen($akun['saldo'], $basePersen) }}</td>
                            <td style="text-align: right; padding: 4px; color: #475569;">
                                {{ formatRp($akun['saldo_lalu'] ?? 0) }}</td>
                            <td style="text-align: right; padding: 4px; color: #94a3b8;">
                                {{ formatPersen($akun['saldo_lalu'] ?? 0, $basePersenLalu) }}</td>
                        </tr>
                    @endforeach
                    <tr style="background-color: #f1f5f9; font-weight: bold;">
                        <td colspan="2" style="text-align: right; padding: 8px;">TOTAL PENDAPATAN & BIAYA (NON OPR)
                        </td>
                        <td style="text-align: right; padding: 8px; border-top: 1px solid #000;">
                            {{ formatRp($totalLuarUsaha) }}</td>
                        <td style="text-align: right; padding: 8px;">{{ formatPersen($totalLuarUsaha, $basePersen) }}</td>
                        <td style="text-align: right; padding: 8px; border-top: 1px solid #000; color: #475569;">
                            {{ formatRp($totalLuarUsahaLalu ?? 0) }}</td>
                        <td style="text-align: right; padding: 8px; color: #94a3b8;">
                            {{ formatPersen($totalLuarUsahaLalu ?? 0, $basePersenLalu) }}</td>
                    </tr>

                    <tr
                        style="background-color: #ffff00; font-weight: bold; border-top: 2px solid #000; border-bottom: 2px solid #000;">
                        <td colspan="2" style="text-align: center; padding: 12px 8px; font-size: 1rem;">LABA BERSIH
                            SEBELUM PAJAK</td>
                        <td style="text-align: right; padding: 12px 8px; font-size: 1rem;">
                            {{ formatRp($labaSebelumPajak) }}</td>
                        <td style="text-align: right; padding: 12px 8px;"></td>
                        <td style="text-align: right; padding: 12px 8px; font-size: 1rem; color: #475569;">
                            {{ formatRp($labaSebelumPajakLalu ?? 0) }}</td>
                        <td style="text-align: right; padding: 12px 8px;"></td>
                    </tr>

                    @foreach ($laporan['penyusutan_pajak'] as $no => $akun)
                        <tr>
                            <td style="text-align: center; padding: 15px 4px 4px 4px;">{{ $no }}</td>
                            <td style="padding: 15px 4px 4px 4px;">{{ $akun['nama'] }}</td>
                            <td style="text-align: right; padding: 15px 4px 4px 4px;">{{ formatRp($akun['saldo']) }}</td>
                            <td style="text-align: right; padding: 15px 4px 4px 4px; color: #64748b;">
                                {{ formatPersen($akun['saldo'], $basePersen) }}</td>
                            <td style="text-align: right; padding: 15px 4px 4px 4px; color: #475569;">
                                {{ formatRp($akun['saldo_lalu'] ?? 0) }}</td>
                            <td style="text-align: right; padding: 15px 4px 4px 4px; color: #94a3b8;">
                                {{ formatPersen($akun['saldo_lalu'] ?? 0, $basePersenLalu) }}</td>
                        </tr>
                    @endforeach

                    <tr
                        style="background-color: #ffff00; font-weight: 800; border-top: 2px solid #000; border-bottom: 2px solid #000;">
                        <td colspan="2" style="text-align: center; padding: 15px 8px; font-size: 1.1rem;">LABA BERSIH
                            SETELAH PAJAK</td>
                        <td style="text-align: right; padding: 15px 8px; font-size: 1.1rem;">{{ formatRp($labaBersih) }}
                        </td>
                        <td style="text-align: right; padding: 15px 8px;"></td>
                        <td style="text-align: right; padding: 15px 8px; font-size: 1.1rem; color: #475569;">
                            {{ formatRp($labaBersihLalu ?? 0) }}</td>
                        <td style="text-align: right; padding: 15px 8px;"></td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>
@endsection
