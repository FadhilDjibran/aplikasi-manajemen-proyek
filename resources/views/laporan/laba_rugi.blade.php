@extends('layouts.app')
@section('title', 'Laporan Laba Rugi')

@section('content')
    <div class="card"
        style="max-width: 1000px; min-width: 900px; margin: 0 auto; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">

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

            <form method="GET" style="display: flex; gap: 10px;">
                <select name="bulan" class="form-control" style="width: 160px; border-radius: 6px;">
                    <option value="all" {{ $bulan === 'all' ? 'selected' : '' }}>Setahun Penuh</option>

                    @for ($i = 1; $i <= 12; $i++)
                        <option value="{{ sprintf('%02d', $i) }}" {{ $bulan == sprintf('%02d', $i) ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                        </option>
                    @endfor
                </select>

                <select name="tahun" class="form-control" style="width: 100px; border-radius: 6px;">
                    @for ($i = date('Y'); $i >= date('Y') - 3; $i--)
                        <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>
                            {{ $i }}
                        </option>
                    @endfor
                </select>
                <button type="submit" class="btn btn-primary" style="border-radius: 6px;">Filter</button>
            </form>
        </div>

        <div style="padding: 2rem;">
            <table style="width: 100%; border-collapse: collapse; font-family: 'Arial', sans-serif; font-size: 0.9rem;">

                @php
                    function formatRp($angka)
                    {
                        return $angka == 0 ? '-' : number_format($angka, 0, ',', '.');
                    }
                    function formatPersen($angka, $total)
                    {
                        if ($total == 0 || $angka == 0) {
                            return '0,0%';
                        }
                        return number_format(($angka / $total) * 100, 1, ',', '') . '%';
                    }
                @endphp

                <tr>
                    <td colspan="4" style="font-weight: bold; text-decoration: underline; padding: 10px 5px;">PENDAPATAN
                    </td>
                </tr>
                @foreach ($laporan['pendapatan'] as $no => $akun)
                    <tr>
                        <td style="width: 80px; text-align: center; padding: 5px;">{{ $no }}</td>
                        <td style="padding: 5px;">{{ $akun['nama'] }}</td>
                        <td style="text-align: right; padding: 5px;">{{ formatRp($akun['saldo']) }}</td>
                        <td style="width: 80px; text-align: right; padding: 5px;">
                            {{ formatPersen($akun['saldo'], $totalPendapatan) }}</td>
                    </tr>
                @endforeach
                <tr style="background-color: #e2e8f0; font-weight: bold;">
                    <td colspan="2" style="text-align: center; padding: 8px;">PENJUALAN BERSIH</td>
                    <td style="text-align: right; padding: 8px;">{{ formatRp($totalPendapatan) }}</td>
                    <td style="text-align: right; padding: 8px;">100,0%</td>
                </tr>

                <tr>
                    <td colspan="4" style="font-weight: bold; text-decoration: underline; padding: 15px 5px 10px 5px;">
                        BEBAN POKOK PENJUALAN</td>
                </tr>
                @foreach ($laporan['beban_pokok'] as $no => $akun)
                    <tr>
                        <td style="text-align: center; padding: 5px;">{{ $no }}</td>
                        <td style="padding: 5px;">{{ $akun['nama'] }}</td>
                        <td style="text-align: right; padding: 5px;">{{ formatRp($akun['saldo']) }}</td>
                        <td style="text-align: right; padding: 5px;">{{ formatPersen($akun['saldo'], $totalPendapatan) }}
                        </td>
                    </tr>
                @endforeach
                <tr style="background-color: #e2e8f0; font-weight: bold;">
                    <td colspan="2" style="text-align: center; padding: 8px;">TOTAL BEBAN POKOK PENJUALAN</td>
                    <td style="text-align: right; padding: 8px;">{{ formatRp($totalBebanPokok) }}</td>
                    <td style="text-align: right; padding: 8px;">{{ formatPersen($totalBebanPokok, $totalPendapatan) }}
                    </td>
                </tr>

                <tr style="background-color: #ffff00; font-weight: bold;">
                    <td colspan="2" style="text-align: center; padding: 12px 8px; font-size: 1rem;">LABA (RUGI) KOTOR
                    </td>
                    <td style="text-align: right; padding: 12px 8px; font-size: 1rem;">{{ formatRp($labaKotor) }}</td>
                    <td style="text-align: right; padding: 12px 8px;">{{ formatPersen($labaKotor, $totalPendapatan) }}</td>
                </tr>

                <tr>
                    <td colspan="4" style="font-weight: bold; text-decoration: underline; padding: 15px 5px 10px 5px;">
                        BEBAN PEMASARAN</td>
                </tr>
                @foreach ($laporan['beban_pemasaran'] as $no => $akun)
                    <tr>
                        <td style="text-align: center; padding: 5px;">{{ $no }}</td>
                        <td style="padding: 5px;">{{ $akun['nama'] }}</td>
                        <td style="text-align: right; padding: 5px;">{{ formatRp($akun['saldo']) }}</td>
                        <td style="text-align: right; padding: 5px;">{{ formatPersen($akun['saldo'], $totalPendapatan) }}
                        </td>
                    </tr>
                @endforeach
                <tr style="background-color: #e2e8f0; font-weight: bold;">
                    <td colspan="2" style="text-align: center; padding: 8px;">TOTAL BEBAN PEMASARAN</td>
                    <td style="text-align: right; padding: 8px;">{{ formatRp($totalPemasaran) }}</td>
                    <td style="text-align: right; padding: 8px;">{{ formatPersen($totalPemasaran, $totalPendapatan) }}</td>
                </tr>

                <tr>
                    <td colspan="4" style="font-weight: bold; text-decoration: underline; padding: 15px 5px 10px 5px;">
                        BIAYA UMUM & ADMINISTRASI</td>
                </tr>
                @foreach ($laporan['biaya_umum'] as $no => $akun)
                    <tr>
                        <td style="text-align: center; padding: 5px;">{{ $no }}</td>
                        <td style="padding: 5px;">{{ $akun['nama'] }}</td>
                        <td style="text-align: right; padding: 5px;">{{ formatRp($akun['saldo']) }}</td>
                        <td style="text-align: right; padding: 5px;">{{ formatPersen($akun['saldo'], $totalPendapatan) }}
                        </td>
                    </tr>
                @endforeach
                <tr style="background-color: #e2e8f0; font-weight: bold;">
                    <td colspan="2" style="text-align: center; padding: 8px;">TOTAL BIAYA UMUM & ADMINISTRASI</td>
                    <td style="text-align: right; padding: 8px;">{{ formatRp($totalUmum) }}</td>
                    <td style="text-align: right; padding: 8px;">{{ formatPersen($totalUmum, $totalPendapatan) }}</td>
                </tr>

                <tr style="background-color: #ffff00; font-weight: bold;">
                    <td colspan="2" style="text-align: center; padding: 12px 8px; font-size: 1rem;">LABA (RUGI)
                        OPERASIONAL</td>
                    <td style="text-align: right; padding: 12px 8px; font-size: 1rem;">{{ formatRp($labaOperasional) }}
                    </td>
                    <td style="text-align: right; padding: 12px 8px;">
                        {{ formatPersen($labaOperasional, $totalPendapatan) }}</td>
                </tr>

                <tr>
                    <td colspan="4" style="font-weight: bold; text-decoration: underline; padding: 15px 5px 10px 5px;">
                        PENDAPATAN & BIAYA LUAR USAHA</td>
                </tr>
                @foreach ($laporan['pendapatan_biaya_luar'] as $no => $akun)
                    <tr>
                        <td style="text-align: center; padding: 5px;">{{ $no }}</td>
                        <td style="padding: 5px;">{{ $akun['nama'] }}</td>
                        <td style="text-align: right; padding: 5px;">{{ formatRp($akun['saldo']) }}</td>
                        <td style="text-align: right; padding: 5px;">{{ formatPersen($akun['saldo'], $totalPendapatan) }}
                        </td>
                    </tr>
                @endforeach
                <tr style="background-color: #e2e8f0; font-weight: bold;">
                    <td colspan="2" style="text-align: center; padding: 8px;">TOTAL PENDAPATAN & BIAYA LUAR USAHA</td>
                    <td style="text-align: right; padding: 8px;">{{ formatRp($totalLuarUsaha) }}</td>
                    <td style="text-align: right; padding: 8px;">{{ formatPersen($totalLuarUsaha, $totalPendapatan) }}</td>
                </tr>

                <tr style="background-color: #ffff00; font-weight: bold;">
                    <td colspan="2" style="text-align: center; padding: 12px 8px; font-size: 1rem;">LABA (RUGI) SEBELUM
                        PAJAK</td>
                    <td style="text-align: right; padding: 12px 8px; font-size: 1rem;">{{ formatRp($labaSebelumPajak) }}
                    </td>
                    <td style="text-align: right; padding: 12px 8px;">
                        {{ formatPersen($labaSebelumPajak, $totalPendapatan) }}</td>
                </tr>

                @foreach ($laporan['penyusutan_pajak'] as $no => $akun)
                    <tr>
                        <td style="text-align: center; padding: 5px; font-weight: bold;">{{ $no }}</td>
                        <td style="padding: 5px; font-weight: bold;">{{ $akun['nama'] }}</td>
                        <td style="text-align: right; padding: 5px;">{{ formatRp($akun['saldo']) }}</td>
                        <td style="text-align: right; padding: 5px;">{{ formatPersen($akun['saldo'], $totalPendapatan) }}
                        </td>
                    </tr>
                @endforeach

                <tr
                    style="background-color: #ffff00; font-weight: 800; border-top: 2px solid #000; border-bottom: 2px solid #000;">
                    <td colspan="2" style="text-align: center; padding: 15px 8px; font-size: 1.1rem;">LABA (RUGI) SESUDAH
                        PAJAK</td>
                    <td style="text-align: right; padding: 15px 8px; font-size: 1.1rem;">{{ formatRp($labaBersih) }}</td>
                    <td style="text-align: right; padding: 15px 8px;">{{ formatPersen($labaBersih, $totalPendapatan) }}
                    </td>
                </tr>

            </table>
        </div>
    </div>
@endsection
