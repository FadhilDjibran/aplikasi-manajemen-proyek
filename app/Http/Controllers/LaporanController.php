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

        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));

        $query = \App\Models\Keuangan::where('project_id', $projectId)
            ->whereYear('tanggal', $tahun);

        if ($bulan !== 'all') {
            $query->whereMonth('tanggal', $bulan);
        }

        $transaksi = $query->get();

        $laporan = [
            'pendapatan' => [
                '4001' => ['nama' => 'PENJUALAN', 'saldo' => 0],
                '5000' => ['nama' => 'POTONGAN PENJUALAN', 'saldo' => 0],
            ],
            'beban_pokok' => [
                '5001' => ['nama' => 'HARGA POKOK TANAH', 'saldo' => 0],
                '5002' => ['nama' => 'HARGA POKOK BANGUNAN', 'saldo' => 0],
                '5003' => ['nama' => 'BY RETENSI BANGUNAN', 'saldo' => 0],
                '5004' => ['nama' => 'BY PENJUALAN LAIN-LAIN', 'saldo' => 0],
            ],
            'beban_pemasaran' => [
                '5100' => ['nama' => 'KOMISI PENJUALAN', 'saldo' => 0],
                '5101' => ['nama' => 'BY MARKETING', 'saldo' => 0],
                '5102' => ['nama' => 'BY ENTERTAINTMENT', 'saldo' => 0],
            ],
            'biaya_umum' => [
                '5201' => ['nama' => 'GAJI', 'saldo' => 0],
                '5202' => ['nama' => 'BONUS DAN TUNJANGAN', 'saldo' => 0],
                '5203' => ['nama' => 'BPJS', 'saldo' => 0],
                '5204' => ['nama' => 'PELATIHAN DAN SERTIFIKASI', 'saldo' => 0],
                '5205' => ['nama' => 'BY PENGIRIMAN DOKUMEN', 'saldo' => 0],
                '5206' => ['nama' => 'MAKAN DAN MINUM', 'saldo' => 0],
                '5207' => ['nama' => 'BEBAN UTILITAS', 'saldo' => 0],
                '5208' => ['nama' => 'BEBAN TRANSPORT', 'saldo' => 0],
                '5209' => ['nama' => 'BEBAN SEWA', 'saldo' => 0],
                '5210' => ['nama' => 'BY INSTALASI', 'saldo' => 0],
                '5211' => ['nama' => 'PARKIR', 'saldo' => 0],
                '5212' => ['nama' => 'ALAT TULIS KANTOR', 'saldo' => 0],
                '5213' => ['nama' => 'KEPERLUAN KANTOR LAIN', 'saldo' => 0],
                '5214' => ['nama' => 'PERJALANAN DINAS', 'saldo' => 0],
                '5215' => ['nama' => 'BEBAN PAJAK', 'saldo' => 0],
                '5216' => ['nama' => 'SUMBANGAN DAN IURAN', 'saldo' => 0],
                '5217' => ['nama' => 'BY TENAGA AHLI', 'saldo' => 0],
                '5218' => ['nama' => 'PEMELIHARAAN ASET', 'saldo' => 0],
                '5219' => ['nama' => 'ADMIN DAN TRANSFER', 'saldo' => 0],
            ],
            'pendapatan_biaya_luar' => [
                '4003' => ['nama' => 'PENDAPATAN LAIN-LAIN', 'saldo' => 0],
                '4004' => ['nama' => 'PENDAPATAN IURAN LINGKUNGAN', 'saldo' => 0],
                '4005' => ['nama' => 'JASA GIRO', 'saldo' => 0],
                '6001' => ['nama' => 'PAJAK GIRO', 'saldo' => 0],
                '6002' => ['nama' => 'PEMELIHARAAN FASUM', 'saldo' => 0],
                '6003' => ['nama' => 'BIAYA LAIN-LAIN', 'saldo' => 0],
            ],
            'penyusutan_pajak' => [
                '5301' => ['nama' => 'BEBAN PENYUSUTAN', 'saldo' => 0],
                '5302' => ['nama' => 'BEBAN AMORTISASI', 'saldo' => 0],
                '7000' => ['nama' => 'PAJAK PENGHASILAN', 'saldo' => 0],
            ],
        ];

        foreach ($transaksi as $trx) {
            $noAkun = $trx->no_akun;
            $group = null;
            $normalBalance = 'debit';

            if (array_key_exists($noAkun, $laporan['pendapatan'])) {
                $group = 'pendapatan';
                $normalBalance = 'kredit';
            } elseif (array_key_exists($noAkun, $laporan['beban_pokok'])) {
                $group = 'beban_pokok';
            } elseif (array_key_exists($noAkun, $laporan['beban_pemasaran'])) {
                $group = 'beban_pemasaran';
            } elseif (array_key_exists($noAkun, $laporan['biaya_umum'])) {
                $group = 'biaya_umum';
            } elseif (array_key_exists($noAkun, $laporan['pendapatan_biaya_luar'])) {
                $group = 'pendapatan_biaya_luar';
                $normalBalance = str_starts_with($noAkun, '4') ? 'kredit' : 'debit';
            } elseif (array_key_exists($noAkun, $laporan['penyusutan_pajak'])) {
                $group = 'penyusutan_pajak';
            }

            if (!$group) continue;

            $saldo = ($normalBalance === 'kredit')
                     ? ($trx->mutasi_masuk - $trx->mutasi_keluar)
                     : ($trx->mutasi_keluar - $trx->mutasi_masuk);

            $laporan[$group][$noAkun]['saldo'] += $saldo;
        }

        $totalPendapatan = array_sum(array_column($laporan['pendapatan'], 'saldo'));

        $totalBebanPokok = array_sum(array_column($laporan['beban_pokok'], 'saldo'));
        $labaKotor = $totalPendapatan - $totalBebanPokok;

        $totalPemasaran = array_sum(array_column($laporan['beban_pemasaran'], 'saldo'));
        $totalUmum = array_sum(array_column($laporan['biaya_umum'], 'saldo'));
        $labaOperasional = $labaKotor - ($totalPemasaran + $totalUmum);

        $totalLuarUsaha = array_sum(array_column($laporan['pendapatan_biaya_luar'], 'saldo'));

        $labaSebelumPajak = $labaOperasional + $totalLuarUsaha;

        $totalPenyusutanPajak = array_sum(array_column($laporan['penyusutan_pajak'], 'saldo'));
        $labaBersih = $labaSebelumPajak - $totalPenyusutanPajak;

        return view('laporan.laba_rugi', compact(
            'laporan', 'bulan', 'tahun',
            'totalPendapatan', 'totalBebanPokok', 'labaKotor',
            'totalPemasaran', 'totalUmum', 'labaOperasional',
            'totalLuarUsaha', 'labaSebelumPajak',
            'totalPenyusutanPajak', 'labaBersih'
        ));
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
                    $totalLabaRugiBerjalan += ($trx->mutasi_keluar - $trx->mutasi_masuk);
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
