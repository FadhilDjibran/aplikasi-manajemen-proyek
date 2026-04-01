@extends('layouts.app')
@section('title', 'Laporan Neraca')

@section('content')
    <div class="card" style="max-width: 1200px; margin: 0 auto; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">

        <div
            style="padding: 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0;">
            <div>
                <h3 style="margin: 0; color: #1e293b; font-weight: 800;">Laporan Neraca</h3>
                <p style="margin: 0; color: #64748b; font-size: 0.9rem;">Perbandingan: Tahun {{ $tahunSekarang }} vs
                    {{ $tahunLalu }}</p>
            </div>

            <form method="GET" style="display: flex; gap: 10px;">
                <select name="tahun" class="form-control" style="width: 120px; border-radius: 6px;">
                    @for ($i = date('Y'); $i >= date('Y') - 5; $i--)
                        <option value="{{ $i }}" {{ $tahunSekarang == $i ? 'selected' : '' }}>{{ $i }}
                        </option>
                    @endfor
                </select>
                <button type="submit" class="btn btn-primary" style="border-radius: 6px;">Filter Tahun</button>
            </form>
        </div>

        <div style="padding: 2rem; overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-family: 'Arial', sans-serif; font-size: 0.85rem;">

                @php
                    function formatRp($angka)
                    {
                        if ($angka == 0) {
                            return '-';
                        }
                        if ($angka < 0) {
                            return '(' . number_format(abs($angka), 0, ',', '.') . ')';
                        }
                        return number_format($angka, 0, ',', '.');
                    }

                    function formatPersen($angka, $total)
                    {
                        if ($total == 0 || $angka == 0) {
                            return '0,00%';
                        }
                        $persen = ($angka / $total) * 100;
                        return ($persen < 0 ? '-' : '') . number_format(abs($persen), 2, ',', '.') . '%';
                    }
                @endphp

                <thead>
                    <tr style="border-bottom: 2px solid #cbd5e1;">
                        <th colspan="2" style="text-align: left; padding: 10px;">AKUN</th>
                        <th style="text-align: right; padding: 10px; width: 140px; border-bottom: 2px solid #000;">
                            {{ $tahunSekarang }}</th>
                        <th style="text-align: right; padding: 10px; width: 70px;">%</th>
                        <th style="text-align: right; padding: 10px; width: 140px; border-bottom: 2px solid #000;">
                            {{ $tahunLalu }}</th>
                        <th style="text-align: right; padding: 10px; width: 70px;">%</th>
                        <th style="text-align: right; padding: 10px; width: 140px;">SELISIH</th>
                    </tr>
                </thead>
                <tbody>

                    <tr>
                        <td colspan="7"
                            style="font-weight: bold; text-decoration: underline; padding: 15px 5px 5px 5px; text-align: center;">
                            ASET LANCAR</td>
                    </tr>
                    @foreach ($neracaSekarang['data']['aset_lancar'] as $no => $akun)
                        @php
                            $saldoSkrg = $akun['saldo'];
                            $saldoLalu = $neracaLalu['data']['aset_lancar'][$no]['saldo'] ?? 0;
                            $selisih = $saldoSkrg - $saldoLalu;
                        @endphp
                        <tr>
                            <td style="width: 60px; text-align: center; padding: 4px;">{{ $no }}</td>
                            <td style="padding: 4px;">{{ $akun['nama'] }}</td>
                            <td style="text-align: right; padding: 4px;">{{ formatRp($saldoSkrg) }}</td>
                            <td style="text-align: right; padding: 4px; color: #64748b;">
                                {{ formatPersen($saldoSkrg, $neracaSekarang['totalAset']) }}</td>
                            <td style="text-align: right; padding: 4px;">{{ formatRp($saldoLalu) }}</td>
                            <td style="text-align: right; padding: 4px; color: #64748b;">
                                {{ formatPersen($saldoLalu, $neracaLalu['totalAset']) }}</td>
                            <td style="text-align: right; padding: 4px;">{{ formatRp($selisih) }}</td>
                        </tr>
                    @endforeach
                    <tr style="background-color: #f1f5f9; font-weight: bold;">
                        <td colspan="2" style="text-align: right; padding: 8px;">TOTAL ASET LANCAR</td>
                        <td style="text-align: right; padding: 8px; border-top: 1px solid #000;">
                            {{ formatRp($neracaSekarang['totalAsetLancar']) }}</td>
                        <td style="text-align: right; padding: 8px;">
                            {{ formatPersen($neracaSekarang['totalAsetLancar'], $neracaSekarang['totalAset']) }}</td>
                        <td style="text-align: right; padding: 8px; border-top: 1px solid #000;">
                            {{ formatRp($neracaLalu['totalAsetLancar']) }}</td>
                        <td style="text-align: right; padding: 8px;">
                            {{ formatPersen($neracaLalu['totalAsetLancar'], $neracaLalu['totalAset']) }}</td>
                        <td style="text-align: right; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <td colspan="7"
                            style="font-weight: bold; text-decoration: underline; padding: 20px 5px 5px 5px; text-align: center;">
                            ASET TIDAK LANCAR</td>
                    </tr>
                    @foreach ($neracaSekarang['data']['aset_tidak_lancar'] as $no => $akun)
                        @php
                            $saldoSkrg = $akun['saldo'];
                            $saldoLalu = $neracaLalu['data']['aset_tidak_lancar'][$no]['saldo'] ?? 0;
                            $selisih = $saldoSkrg - $saldoLalu;
                        @endphp
                        <tr>
                            <td style="text-align: center; padding: 4px;">{{ $no }}</td>
                            <td style="padding: 4px;">{{ $akun['nama'] }}</td>
                            <td style="text-align: right; padding: 4px;">{{ formatRp($saldoSkrg) }}</td>
                            <td style="text-align: right; padding: 4px; color: #64748b;">
                                {{ formatPersen($saldoSkrg, $neracaSekarang['totalAset']) }}</td>
                            <td style="text-align: right; padding: 4px;">{{ formatRp($saldoLalu) }}</td>
                            <td style="text-align: right; padding: 4px; color: #64748b;">
                                {{ formatPersen($saldoLalu, $neracaLalu['totalAset']) }}</td>
                            <td style="text-align: right; padding: 4px;">{{ formatRp($selisih) }}</td>
                        </tr>
                    @endforeach
                    <tr style="background-color: #f1f5f9; font-weight: bold;">
                        <td colspan="2" style="text-align: right; padding: 8px;">TOTAL ASET TIDAK LANCAR</td>
                        <td style="text-align: right; padding: 8px; border-top: 1px solid #000;">
                            {{ formatRp($neracaSekarang['totalAsetTidakLancar']) }}</td>
                        <td style="text-align: right; padding: 8px;">
                            {{ formatPersen($neracaSekarang['totalAsetTidakLancar'], $neracaSekarang['totalAset']) }}</td>
                        <td style="text-align: right; padding: 8px; border-top: 1px solid #000;">
                            {{ formatRp($neracaLalu['totalAsetTidakLancar']) }}</td>
                        <td style="text-align: right; padding: 8px;">
                            {{ formatPersen($neracaLalu['totalAsetTidakLancar'], $neracaLalu['totalAset']) }}</td>
                        <td style="text-align: right; padding: 8px;"></td>
                    </tr>

                    <tr
                        style="background-color: #ffff00; font-weight: bold; border-top: 2px solid #000; border-bottom: 2px solid #000;">
                        <td colspan="2" style="text-align: center; padding: 12px 8px; font-size: 1rem;">TOTAL ASET</td>
                        <td style="text-align: right; padding: 12px 8px;">{{ formatRp($neracaSekarang['totalAset']) }}</td>
                        <td style="text-align: right; padding: 12px 8px;">100%</td>
                        <td style="text-align: right; padding: 12px 8px;">{{ formatRp($neracaLalu['totalAset']) }}</td>
                        <td style="text-align: right; padding: 12px 8px;">100%</td>
                        <td style="text-align: right; padding: 12px 8px;">
                            {{ formatRp($neracaSekarang['totalAset'] - $neracaLalu['totalAset']) }}</td>
                    </tr>

                    <tr>
                        <td colspan="7"
                            style="font-weight: bold; text-decoration: underline; padding: 25px 5px 5px 5px; text-align: center;">
                            HUTANG</td>
                    </tr>
                    @foreach ($neracaSekarang['data']['hutang'] as $no => $akun)
                        @php
                            $saldoSkrg = $akun['saldo'];
                            $saldoLalu = $neracaLalu['data']['hutang'][$no]['saldo'] ?? 0;
                            $selisih = $saldoSkrg - $saldoLalu;
                        @endphp
                        <tr>
                            <td style="text-align: center; padding: 4px;">{{ $no }}</td>
                            <td style="padding: 4px;">{{ $akun['nama'] }}</td>
                            <td style="text-align: right; padding: 4px;">{{ formatRp($saldoSkrg) }}</td>
                            <td style="text-align: right; padding: 4px; color: #64748b;">
                                {{ formatPersen($saldoSkrg, $neracaSekarang['totalPasiva']) }}</td>
                            <td style="text-align: right; padding: 4px;">{{ formatRp($saldoLalu) }}</td>
                            <td style="text-align: right; padding: 4px; color: #64748b;">
                                {{ formatPersen($saldoLalu, $neracaLalu['totalPasiva']) }}</td>
                            <td style="text-align: right; padding: 4px;">{{ formatRp($selisih) }}</td>
                        </tr>
                    @endforeach
                    <tr style="background-color: #f1f5f9; font-weight: bold;">
                        <td colspan="2" style="text-align: right; padding: 8px;">TOTAL HUTANG</td>
                        <td style="text-align: right; padding: 8px; border-top: 1px solid #000;">
                            {{ formatRp($neracaSekarang['totalHutang']) }}</td>
                        <td style="text-align: right; padding: 8px;">
                            {{ formatPersen($neracaSekarang['totalHutang'], $neracaSekarang['totalPasiva']) }}</td>
                        <td style="text-align: right; padding: 8px; border-top: 1px solid #000;">
                            {{ formatRp($neracaLalu['totalHutang']) }}</td>
                        <td style="text-align: right; padding: 8px;">
                            {{ formatPersen($neracaLalu['totalHutang'], $neracaLalu['totalPasiva']) }}</td>
                        <td style="text-align: right; padding: 8px;"></td>
                    </tr>

                    <tr>
                        <td colspan="7"
                            style="font-weight: bold; text-decoration: underline; padding: 20px 5px 5px 5px; text-align: center;">
                            EKUITAS</td>
                    </tr>
                    @foreach ($neracaSekarang['data']['ekuitas'] as $no => $akun)
                        @php
                            $saldoSkrg = $akun['saldo'];
                            $saldoLalu = $neracaLalu['data']['ekuitas'][$no]['saldo'] ?? 0;
                            $selisih = $saldoSkrg - $saldoLalu;
                        @endphp
                        <tr>
                            <td style="text-align: center; padding: 4px;">{{ $no }}</td>
                            <td style="padding: 4px;">{{ $akun['nama'] }}</td>
                            <td style="text-align: right; padding: 4px;">{{ formatRp($saldoSkrg) }}</td>
                            <td style="text-align: right; padding: 4px; color: #64748b;">
                                {{ formatPersen($saldoSkrg, $neracaSekarang['totalPasiva']) }}</td>
                            <td style="text-align: right; padding: 4px;">{{ formatRp($saldoLalu) }}</td>
                            <td style="text-align: right; padding: 4px; color: #64748b;">
                                {{ formatPersen($saldoLalu, $neracaLalu['totalPasiva']) }}</td>
                            <td style="text-align: right; padding: 4px;">{{ formatRp($selisih) }}</td>
                        </tr>
                    @endforeach
                    <tr style="background-color: #f1f5f9; font-weight: bold;">
                        <td colspan="2" style="text-align: right; padding: 8px;">TOTAL EKUITAS</td>
                        <td style="text-align: right; padding: 8px; border-top: 1px solid #000;">
                            {{ formatRp($neracaSekarang['totalEkuitas']) }}</td>
                        <td style="text-align: right; padding: 8px;">
                            {{ formatPersen($neracaSekarang['totalEkuitas'], $neracaSekarang['totalPasiva']) }}</td>
                        <td style="text-align: right; padding: 8px; border-top: 1px solid #000;">
                            {{ formatRp($neracaLalu['totalEkuitas']) }}</td>
                        <td style="text-align: right; padding: 8px;">
                            {{ formatPersen($neracaLalu['totalEkuitas'], $neracaLalu['totalPasiva']) }}</td>
                        <td style="text-align: right; padding: 8px;"></td>
                    </tr>

                    <tr
                        style="background-color: #ffff00; font-weight: bold; border-top: 2px solid #000; border-bottom: 2px solid #000;">
                        <td colspan="2" style="text-align: center; padding: 12px 8px; font-size: 1rem;">TOTAL HUTANG
                            DAN EKUITAS</td>
                        <td style="text-align: right; padding: 12px 8px;">{{ formatRp($neracaSekarang['totalPasiva']) }}
                        </td>
                        <td style="text-align: right; padding: 12px 8px;">100%</td>
                        <td style="text-align: right; padding: 12px 8px;">{{ formatRp($neracaLalu['totalPasiva']) }}</td>
                        <td style="text-align: right; padding: 12px 8px;">100%</td>
                        <td style="text-align: right; padding: 12px 8px;">
                            {{ formatRp($neracaSekarang['totalPasiva'] - $neracaLalu['totalPasiva']) }}</td>
                    </tr>

                </tbody>
            </table>
        </div>
    </div>
@endsection
