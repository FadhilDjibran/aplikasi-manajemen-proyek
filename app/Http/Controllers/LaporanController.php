<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function labaRugi(Request $request)
    {
        $projectId = session('active_project_id');
        if (!$projectId) {
            return redirect()->route('projects.index')->with('error', 'Pilih proyek terlebih dahulu.');
        }

        $bulan = $request->input('bulan', 'all');
        $tahun = $request->input('tahun', date('Y'));

        if ($bulan === 'all') {
            $prevBulan = 'all';
            $prevTahun = $tahun - 1;
        } else {
            $waktuLalu = strtotime('-1 month', strtotime($tahun . '-' . $bulan . '-01'));
            $prevBulan = date('m', $waktuLalu);
            $prevTahun = date('Y', $waktuLalu);
        }

        $hitungPeriode = function ($b, $t) use ($projectId) {
            $lap = [
                'pendapatan' => [
                    '4001' => ['nama' => 'PENJUALAN', 'saldo' => 0],
                    '4002' => ['nama' => 'POTONGAN PENJUALAN', 'saldo' => 0],
                ],
                'beban_pokok' => [
                    '5001' => ['nama' => 'BY MATERIAL', 'saldo' => 0],
                    '5002' => ['nama' => 'BY TENAGA KERJA', 'saldo' => 0],
                    '5003' => ['nama' => 'BY OVERHEAD', 'saldo' => 0],
                    '5004' => ['nama' => 'BY PERENCANAAN DAN IZIN', 'saldo' => 0],
                    '5005' => ['nama' => 'BY PENJUALAN LAIN-LAIN', 'saldo' => 0],
                    '5006' => ['nama' => 'HARGA POKOK TANAH', 'saldo' => 0],
                    '5007' => ['nama' => 'BY FASUM', 'saldo' => 0],
                ],
                'beban_pemasaran' => [
                    '5201' => ['nama' => 'KOMISI PENJUALAN', 'saldo' => 0],
                    '5202' => ['nama' => 'BY MARKETING', 'saldo' => 0],
                    '5203' => ['nama' => 'BY ENTERTAINTMENT', 'saldo' => 0],
                ],
                'biaya_umum' => [
                    '5101' => ['nama' => 'GAJI', 'saldo' => 0],
                    '5102' => ['nama' => 'BONUS, LEMBUR', 'saldo' => 0],
                    '5104' => ['nama' => 'TUNJANGAN', 'saldo' => 0],
                    '5106' => ['nama' => 'MAKAN DAN MINUM', 'saldo' => 0],
                    '5107' => ['nama' => 'TELEPON, PULSA, WIFI, LISTRIK DAN AIR', 'saldo' => 0],
                    '5108' => ['nama' => 'BBM, TOL, TRANSPORT', 'saldo' => 0],
                    '5109' => ['nama' => 'PARKIR', 'saldo' => 0],
                    '5111' => ['nama' => 'PEMELIHARAAN ASET', 'saldo' => 0],
                    '5113' => ['nama' => 'ALAT TULIS KANTOR', 'saldo' => 0],
                    '5114' => ['nama' => 'KEPERLUAN KANTOR LAIN', 'saldo' => 0],
                    '5115' => ['nama' => 'PERJALANAN DINAS', 'saldo' => 0],
                    '5116' => ['nama' => 'BEBAN PAJAK', 'saldo' => 0],
                    '5117' => ['nama' => 'SUMBANGAN DAN IURAN', 'saldo' => 0],
                    '5119' => ['nama' => 'PELATIHAN PEGAWAI', 'saldo' => 0],
                    '5120' => ['nama' => 'BEBAN PENYUSUTAN', 'saldo' => 0],
                ],
                'pendapatan_biaya_luar' => [
                    '6001' => ['nama' => 'PENDAPATAN LAIN-LAIN', 'saldo' => 0],
                    '6002' => ['nama' => 'JASA GIRO', 'saldo' => 0],
                    '6003' => ['nama' => 'PAJAK JASA GIRO', 'saldo' => 0],
                    '6004' => ['nama' => 'BY TRANSFER', 'saldo' => 0],
                    '6005' => ['nama' => 'ADMIN BANK', 'saldo' => 0],
                    '6199' => ['nama' => 'BIAYA LAIN-LAIN', 'saldo' => 0],
                ],
                'penyusutan_pajak' => [
                    '7100' => ['nama' => 'PAJAK PENGHASILAN', 'saldo' => 0],
                ],
            ];

            $query = \App\Models\Keuangan::where('project_id', $projectId)->whereYear('tanggal', $t);
            if ($b !== 'all') {
                $query->whereMonth('tanggal', $b);
            }
            $transaksi = $query->get();

            foreach ($transaksi as $trx) {
                $noAkun = $trx->no_akun;
                $group = null;
                $normalBalance = 'debit';

                if (array_key_exists($noAkun, $lap['pendapatan'])) {
                    $group = 'pendapatan'; $normalBalance = 'kredit';
                } elseif (array_key_exists($noAkun, $lap['beban_pokok'])) {
                    $group = 'beban_pokok';
                } elseif (array_key_exists($noAkun, $lap['beban_pemasaran'])) {
                    $group = 'beban_pemasaran';
                } elseif (array_key_exists($noAkun, $lap['biaya_umum'])) {
                    $group = 'biaya_umum';
                } elseif (array_key_exists($noAkun, $lap['pendapatan_biaya_luar'])) {
                    $group = 'pendapatan_biaya_luar'; $normalBalance = 'kredit';
                } elseif (array_key_exists($noAkun, $lap['penyusutan_pajak'])) {
                    $group = 'penyusutan_pajak';
                }

                if (!$group) continue;

                $debit = ($trx->input === 'Jurnal') ? $trx->mutasi_masuk : $trx->mutasi_keluar;
                $kredit = ($trx->input === 'Jurnal') ? $trx->mutasi_keluar : $trx->mutasi_masuk;

                $saldoMutasi = ($normalBalance === 'kredit') ? ($kredit - $debit) : ($debit - $kredit);
                $lap[$group][$noAkun]['saldo'] += $saldoMutasi;
            }

            $totPendapatan = array_sum(array_column($lap['pendapatan'], 'saldo'));
            $totBebanPokok = array_sum(array_column($lap['beban_pokok'], 'saldo'));
            $labaKtr = $totPendapatan - $totBebanPokok;

            $totPemasaran = array_sum(array_column($lap['beban_pemasaran'], 'saldo'));
            $totUmum = array_sum(array_column($lap['biaya_umum'], 'saldo'));
            $labaOpr = $labaKtr - ($totPemasaran + $totUmum);

            $totPendapatanLuar = 0; $totBiayaLuar = 0;
            foreach ($lap['pendapatan_biaya_luar'] as $noAkun => $akun) {
                if (in_array($noAkun, ['6001', '6002'])) {
                    $totPendapatanLuar += $akun['saldo'];
                } else {
                    $totBiayaLuar += $akun['saldo'];
                }
            }

            $totLuarUsaha = $totPendapatanLuar + $totBiayaLuar;
            $labaSblmPajak = $labaOpr + $totLuarUsaha;
            $totPenyusutan = array_sum(array_column($lap['penyusutan_pajak'], 'saldo'));
            $labaBrsh = $labaSblmPajak - $totPenyusutan;

            $basePrsn = ($totPendapatan > 0) ? $totPendapatan : (($totPemasaran + $totUmum) - abs($totBiayaLuar));

            return [
                'laporan' => $lap,
                'totals' => [
                    'Pendapatan' => $totPendapatan, 'BebanPokok' => $totBebanPokok, 'LabaKotor' => $labaKtr,
                    'Pemasaran' => $totPemasaran, 'Umum' => $totUmum, 'LabaOperasional' => $labaOpr,
                    'LuarUsaha' => $totLuarUsaha, 'LabaSebelumPajak' => $labaSblmPajak,
                    'PenyusutanPajak' => $totPenyusutan, 'LabaBersih' => $labaBrsh, 'BasePersen' => $basePrsn
                ]
            ];
        };

        $dataSekarang = $hitungPeriode($bulan, $tahun);
        $dataLalu = $hitungPeriode($prevBulan, $prevTahun);

        $laporanUtama = $dataSekarang['laporan'];

        foreach ($laporanUtama as $group => $akunList) {
            foreach ($akunList as $noAkun => $akunData) {
                $laporanUtama[$group][$noAkun]['saldo_lalu'] = $dataLalu['laporan'][$group][$noAkun]['saldo'];
            }
        }

        return view('laporan.laba_rugi', [
            'bulan' => $bulan, 'tahun' => $tahun,
            'laporan' => $laporanUtama,

            'totalPendapatan' => $dataSekarang['totals']['Pendapatan'],
            'totalBebanPokok' => $dataSekarang['totals']['BebanPokok'],
            'labaKotor' => $dataSekarang['totals']['LabaKotor'],
            'totalPemasaran' => $dataSekarang['totals']['Pemasaran'],
            'totalUmum' => $dataSekarang['totals']['Umum'],
            'labaOperasional' => $dataSekarang['totals']['LabaOperasional'],
            'totalLuarUsaha' => $dataSekarang['totals']['LuarUsaha'],
            'labaSebelumPajak' => $dataSekarang['totals']['LabaSebelumPajak'],
            'totalPenyusutanPajak' => $dataSekarang['totals']['PenyusutanPajak'],
            'labaBersih' => $dataSekarang['totals']['LabaBersih'],
            'basePersen' => $dataSekarang['totals']['BasePersen'],

            'totalPendapatanLalu' => $dataLalu['totals']['Pendapatan'],
            'totalBebanPokokLalu' => $dataLalu['totals']['BebanPokok'],
            'labaKotorLalu' => $dataLalu['totals']['LabaKotor'],
            'totalPemasaranLalu' => $dataLalu['totals']['Pemasaran'],
            'totalUmumLalu' => $dataLalu['totals']['Umum'],
            'labaOperasionalLalu' => $dataLalu['totals']['LabaOperasional'],
            'totalLuarUsahaLalu' => $dataLalu['totals']['LuarUsaha'],
            'labaSebelumPajakLalu' => $dataLalu['totals']['LabaSebelumPajak'],
            'totalPenyusutanPajakLalu' => $dataLalu['totals']['PenyusutanPajak'],
            'labaBersihLalu' => $dataLalu['totals']['LabaBersih'],
            'basePersenLalu' => $dataLalu['totals']['BasePersen'],
        ]);
    }

    public function neraca(Request $request)
    {
        $projectId = session('active_project_id');
        if (!$projectId) {
            return redirect()->route('projects.index')->with('error', 'Pilih proyek terlebih dahulu.');
        }

        $tahunSekarang = $request->input('tahun', date('Y'));
        $tahunLalu = $tahunSekarang - 1;

        $coaSekarang = \App\Models\Coa::where('project_id', $projectId)->where('tahun', $tahunSekarang)->get();
        $coaLalu = \App\Models\Coa::where('project_id', $projectId)->where('tahun', $tahunLalu)->get();

        $transaksiSekarang = \App\Models\Keuangan::where('project_id', $projectId)
            ->whereYear('tanggal', $tahunSekarang)->get();

        $transaksiLalu = \App\Models\Keuangan::where('project_id', $projectId)
            ->whereYear('tanggal', $tahunLalu)->get();

        $template = [
            'aset_lancar' => [
                '1199' => ['nama' => 'AYAT SILANG'],
                '1101' => ['nama' => 'KAS BESAR'],
                '1102' => ['nama' => 'KAS KECIL'],
                '1103' => ['nama' => 'BANK BSI'],
                '1104' => ['nama' => 'DANA UMAT'],
                '1201' => ['nama' => 'PIUTANG USAHA'],
                '1202' => ['nama' => 'PIUTANG KARYAWAN'],
                '1301' => ['nama' => 'KAVLING UTK DIJUAL'],
                '1302' => ['nama' => 'DLM PROSES-MATERIAL'],
                '1303' => ['nama' => 'DLM PROSES-TENAGA KERJA LANGSUNG'],
                '1304' => ['nama' => 'DLM PROSES-BY OVERHEAD KONSTRUKSI'],
                '1305' => ['nama' => 'DLM PROSES-PERENCANAAN DAN IZIN'],
                '1306' => ['nama' => 'DLM PROSES-BY FASUM'],
                '1307' => ['nama' => 'PERSEDIAAN MATERIAL'],
                '1308' => ['nama' => 'BIAYA DIBAYAR DIMUKA'],
                '1399' => ['nama' => 'UANG MUKA LAIN-LAIN'],
                '1401' => ['nama' => 'PPN MASUKAN'],
                '1402' => ['nama' => 'UM PPH NON FINAL'],
                '1403' => ['nama' => 'UM PPH 4(2)'],
            ],
            'aset_tidak_lancar' => [
                '1501' => ['nama' => 'TANAH'],
                '1502' => ['nama' => 'BANGUNAN'],
                '1503' => ['nama' => 'MESIN & PERALATAN'],
                '1504' => ['nama' => 'KENDARAAN'],
                '1505' => ['nama' => 'INVENTARIS KANTOR'],
                '1601' => ['nama' => 'AKM PENYUSUTAN BANGUNAN', 'is_contra' => true],
                '1602' => ['nama' => 'AKM PENYUSUTAN MESIN & PERALATAN', 'is_contra' => true],
                '1603' => ['nama' => 'AKM PENYUSUTAN KENDARAAN', 'is_contra' => true],
                '1604' => ['nama' => 'AKM PENYUSUTAN INVENTARIS KANTOR', 'is_contra' => true],
            ],
            'hutang' => [
                '2001' => ['nama' => 'UANG MUKA PENJUALAN'],
                '2002' => ['nama' => 'HUTANG USAHA'],
                '2003' => ['nama' => 'HUTANG BIAYA'],
                '2004' => ['nama' => 'HUTANG PIHAK 3'],
                '2005' => ['nama' => 'HUTANG PEMILIK'],
                '2101' => ['nama' => 'PPN KELUARAN'],
                '2102' => ['nama' => 'HUTANG PPH NON FINAL'],
                '2103' => ['nama' => 'HUTANG PPH 4 (2)'],
                '2099' => ['nama' => 'HUTANG LAIN-LAIN'],
            ],
            'ekuitas' => [
                '3001' => ['nama' => 'MODAL DISETOR'],
                '3002' => ['nama' => 'SALDO LABA'],
                '3003' => ['nama' => 'TAMBAHAN MODAL DISETOR'],
                '3004' => ['nama' => 'LABA TAHUN LALU'],
                '3005' => ['nama' => 'SELISIH REVALUASI'],
                '3006' => ['nama' => 'DEVIDEN / PRIVE', 'is_contra' => true],
                '3007' => ['nama' => 'LABA TAHUN BERJALAN'],
            ],
        ];

       $processData = function ($transaksiData, $coaData) use ($template) {
            $laporan = [];

            foreach ($template as $groupKey => $akunList) {
                foreach ($akunList as $noAkun => $detail) {
                    $laporan[$groupKey][$noAkun] = ['nama' => $detail['nama'], 'saldo' => 0];
                }
            }

            $totalLabaRugiBerjalan = 0;
            foreach ($transaksiData as $trx) {
                if ($trx->coa && $trx->coa->jenis_laporan === 'Laba Rugi') {

                    $debit = 0;
                    $kredit = 0;

                    if ($trx->input === 'Jurnal') {
                        $debit = $trx->mutasi_masuk;
                        $kredit = $trx->mutasi_keluar;
                    } else {
                        $debit = $trx->mutasi_keluar;
                        $kredit = $trx->mutasi_masuk;
                    }

                    $totalLabaRugiBerjalan += ($kredit - $debit);
                }
            }

            foreach ($coaData as $akun) {
                $noAkun = $akun->no_akun;
                $group = null;

                if (array_key_exists($noAkun, $laporan['aset_lancar'])) { $group = 'aset_lancar'; }
                elseif (array_key_exists($noAkun, $laporan['aset_tidak_lancar'])) { $group = 'aset_tidak_lancar'; }
                elseif (array_key_exists($noAkun, $laporan['hutang'])) { $group = 'hutang'; }
                elseif (array_key_exists($noAkun, $laporan['ekuitas'])) { $group = 'ekuitas'; }

                if (!$group) continue;

                $saldo = (float) $akun->saldo_akhir;

                if ($noAkun == '3007' && $saldo == 0) {
                    $saldo = $totalLabaRugiBerjalan * -1;
                }

                if ($group === 'hutang' || $group === 'ekuitas') {
                    $saldo = $saldo * -1;
                }

                $laporan[$group][$noAkun]['saldo'] = $saldo;
            }

            $totalAsetLancar = array_sum(array_column($laporan['aset_lancar'], 'saldo'));
            $totalAsetTidakLancar = array_sum(array_column($laporan['aset_tidak_lancar'], 'saldo'));
            $totalAset = $totalAsetLancar + $totalAsetTidakLancar;

            $totalHutang = array_sum(array_column($laporan['hutang'], 'saldo'));
            $totalEkuitas = array_sum(array_column($laporan['ekuitas'], 'saldo'));
            $totalPasiva = $totalHutang + $totalEkuitas;

            return [
                'data' => $laporan,
                'totalAsetLancar' => $totalAsetLancar,
                'totalAsetTidakLancar' => $totalAsetTidakLancar,
                'totalAset' => $totalAset,
                'totalHutang' => $totalHutang,
                'totalEkuitas' => $totalEkuitas,
                'totalPasiva' => $totalPasiva
            ];
        };
        $neracaSekarang = $processData($transaksiSekarang, $coaSekarang);
        $neracaLalu = $processData($transaksiLalu, $coaLalu);

        return view('laporan.neraca', compact('neracaSekarang', 'neracaLalu', 'tahunSekarang', 'tahunLalu'));
    }
}
