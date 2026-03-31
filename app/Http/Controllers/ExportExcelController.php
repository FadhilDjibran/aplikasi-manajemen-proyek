<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Keuangan;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ExportExcelController extends Controller
{
    public function exportLabaRugi(Request $request)
    {
        $projectId = session('active_project_id');
        if (!$projectId) return redirect()->back()->with('error', 'Pilih proyek terlebih dahulu.');

        $project = \App\Models\Project::find($projectId);
        $nama = $project ? $project->nama_proyek : 'TIDAK DITEMUKAN';
        $HeaderNamaProyek = "PERUMAHAN " . strtoupper($nama);

        $bulan = $request->input('bulan', 'all');
        $tahun = $request->input('tahun', date('Y'));

        $data = $this->getLabaRugiData($projectId, $bulan, $tahun);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(45);
        $sheet->getColumnDimension('C')->setWidth(18);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(10);

        $sheet->setCellValue('A1', $HeaderNamaProyek);
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'LAPORAN LABA RUGI');
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A2')->getFont()->setSize(12)->setBold(true);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $teksPeriode = ($bulan === 'all') ? "Tahun $tahun" : date('F Y', strtotime("$tahun-$bulan-01"));
        $sheet->setCellValue('A3', 'Periode: ' . $teksPeriode);
        $sheet->mergeCells('A3:F3');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        if ($bulan === 'all') {
            $judulSekarang = (string) $tahun;
            $judulLalu = (string) ($tahun - 1);
        } else {
            $judulSekarang = date('M Y', strtotime("$tahun-$bulan-01"));
            $waktuLalu = strtotime("-1 month", strtotime("$tahun-$bulan-01"));
            $judulLalu = date('M Y', $waktuLalu);
        }

        $currentRow = 5;
        $sheet->setCellValue('A'.$currentRow, 'NO');
        $sheet->setCellValue('B'.$currentRow, 'NAMA AKUN');
        $sheet->setCellValueExplicit('C'.$currentRow, $judulSekarang, DataType::TYPE_STRING);
        $sheet->setCellValue('D'.$currentRow, '%');
        $sheet->setCellValueExplicit('E'.$currentRow, $judulLalu, DataType::TYPE_STRING);
        $sheet->setCellValue('F'.$currentRow, '%');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['bottom' => ['borderStyle' => Border::BORDER_THICK]]
        ];
        $sheet->getStyle("A$currentRow:F$currentRow")->applyFromArray($headerStyle);
        $currentRow++;

        $subtotalStyle = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']], 'font' => ['bold' => true]];
        $totalStyle = ['fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']], 'font' => ['bold' => true]];

        $sheet->setCellValue('B'.$currentRow, 'PENDAPATAN')->getStyle('B'.$currentRow)->getFont()->setBold(true)->setUnderline(true);
        $currentRow++;
        foreach ($data['laporan']['pendapatan'] as $no => $akun) {
            $this->writeRow($sheet, $currentRow, $no, $akun, $data['basePersen'], $data['basePersenLalu']);
            $currentRow++;
        }

        $sheet->setCellValue('B'.$currentRow, 'PENJUALAN BERSIH');
        $sheet->setCellValue('C'.$currentRow, $data['totals']['Pendapatan']);
        $sheet->setCellValue('D'.$currentRow, 1);
        $sheet->setCellValue('E'.$currentRow, $data['totals']['PendapatanLalu']);
        $sheet->setCellValue('F'.$currentRow, 1);
        $sheet->getStyle("A$currentRow:F$currentRow")->applyFromArray($subtotalStyle);
        $currentRow += 2;

        $sheet->setCellValue('B'.$currentRow, 'HARGA POKOK PRODUKSI')->getStyle('B'.$currentRow)->getFont()->setBold(true)->setUnderline(true);
        $currentRow++;
        foreach ($data['laporan']['beban_pokok'] as $no => $akun) {
            $this->writeRow($sheet, $currentRow, $no, $akun, $data['totals']['Pendapatan'], $data['totals']['PendapatanLalu']);
            $currentRow++;
        }

        $sheet->setCellValue('B'.$currentRow, 'TOTAL BEBAN POKOK PENJUALAN');
        $sheet->setCellValue('C'.$currentRow, $data['totals']['BebanPokok']);
        $sheet->setCellValue('D'.$currentRow, $data['totals']['Pendapatan'] > 0 ? $data['totals']['BebanPokok'] / $data['totals']['Pendapatan'] : 0);
        $sheet->setCellValue('E'.$currentRow, $data['totals']['BebanPokokLalu']);
        $sheet->setCellValue('F'.$currentRow, $data['totals']['PendapatanLalu'] > 0 ? $data['totals']['BebanPokokLalu'] / $data['totals']['PendapatanLalu'] : 0);
        $sheet->getStyle("A$currentRow:F$currentRow")->applyFromArray($subtotalStyle);
        $currentRow++;

        $sheet->setCellValue('B'.$currentRow, 'LABA (RUGI) KOTOR');
        $sheet->setCellValue('C'.$currentRow, $data['totals']['LabaKotor']);
        $sheet->setCellValue('E'.$currentRow, $data['totals']['LabaKotorLalu']);
        $sheet->getStyle("A$currentRow:F$currentRow")->applyFromArray($totalStyle);
        $sheet->getStyle("A$currentRow:F$currentRow")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A$currentRow:F$currentRow")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $currentRow += 2;


        $sheet->setCellValue('B'.$currentRow, 'BIAYA PEMASARAN')->getStyle('B'.$currentRow)->getFont()->setBold(true)->setUnderline(true);
        $currentRow++;
        foreach ($data['laporan']['beban_pemasaran'] as $no => $akun) {
            $this->writeRow($sheet, $currentRow, $no, $akun, $data['basePersen'], $data['basePersenLalu']);
            $currentRow++;
        }

        $sheet->setCellValue('B'.$currentRow, 'TOTAL BIAYA PEMASARAN');
        $sheet->setCellValue('C'.$currentRow, $data['totals']['Pemasaran']);
        $sheet->setCellValue('D'.$currentRow, $data['basePersen'] > 0 ? $data['totals']['Pemasaran'] / $data['basePersen'] : 0);
        $sheet->setCellValue('E'.$currentRow, $data['totals']['PemasaranLalu']);
        $sheet->setCellValue('F'.$currentRow, $data['basePersenLalu'] > 0 ? $data['totals']['PemasaranLalu'] / $data['basePersenLalu'] : 0);
        $sheet->getStyle("A$currentRow:F$currentRow")->applyFromArray($subtotalStyle);
        $currentRow += 2;


        $sheet->setCellValue('B'.$currentRow, 'BIAYA UMUM & ADMINISTRASI')->getStyle('B'.$currentRow)->getFont()->setBold(true)->setUnderline(true);
        $currentRow++;
        foreach ($data['laporan']['biaya_umum'] as $no => $akun) {
            $this->writeRow($sheet, $currentRow, $no, $akun, $data['basePersen'], $data['basePersenLalu']);
            $currentRow++;
        }

        $sheet->setCellValue('B'.$currentRow, 'TOTAL BIAYA UMUM & ADMINISTRASI');
        $sheet->setCellValue('C'.$currentRow, $data['totals']['Umum']);
        $sheet->setCellValue('D'.$currentRow, $data['basePersen'] > 0 ? $data['totals']['Umum'] / $data['basePersen'] : 0);
        $sheet->setCellValue('E'.$currentRow, $data['totals']['UmumLalu']);
        $sheet->setCellValue('F'.$currentRow, $data['basePersenLalu'] > 0 ? $data['totals']['UmumLalu'] / $data['basePersenLalu'] : 0);
        $sheet->getStyle("A$currentRow:F$currentRow")->applyFromArray($subtotalStyle);
        $currentRow++;

        $sheet->setCellValue('B'.$currentRow, 'LABA (RUGI) OPERASIONAL');
        $sheet->setCellValue('C'.$currentRow, $data['totals']['LabaOperasional']);
        $sheet->setCellValue('E'.$currentRow, $data['totals']['LabaOperasionalLalu']);
        $sheet->getStyle("A$currentRow:F$currentRow")->applyFromArray($totalStyle);
        $sheet->getStyle("A$currentRow:F$currentRow")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A$currentRow:F$currentRow")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $currentRow += 2;


        $sheet->setCellValue('B'.$currentRow, 'PENDAPATAN & BIAYA (NON OPR)')->getStyle('B'.$currentRow)->getFont()->setBold(true)->setUnderline(true);
        $currentRow++;
        foreach ($data['laporan']['pendapatan_biaya_luar'] as $no => $akun) {
            $this->writeRow($sheet, $currentRow, $no, $akun, $data['basePersen'], $data['basePersenLalu']);
            $currentRow++;
        }

        $sheet->setCellValue('B'.$currentRow, 'TOTAL PENDAPATAN & BIAYA (NON OPR)');
        $sheet->setCellValue('C'.$currentRow, $data['totals']['LuarUsaha']);
        $sheet->setCellValue('D'.$currentRow, $data['basePersen'] > 0 ? $data['totals']['LuarUsaha'] / $data['basePersen'] : 0);
        $sheet->setCellValue('E'.$currentRow, $data['totals']['LuarUsahaLalu']);
        $sheet->setCellValue('F'.$currentRow, $data['basePersenLalu'] > 0 ? $data['totals']['LuarUsahaLalu'] / $data['basePersenLalu'] : 0);
        $sheet->getStyle("A$currentRow:F$currentRow")->applyFromArray($subtotalStyle);
        $currentRow++;

        $sheet->setCellValue('B'.$currentRow, 'LABA BERSIH SEBELUM PAJAK');
        $sheet->setCellValue('C'.$currentRow, $data['totals']['LabaSebelumPajak']);
        $sheet->setCellValue('E'.$currentRow, $data['totals']['LabaSebelumPajakLalu']);
        $sheet->getStyle("A$currentRow:F$currentRow")->applyFromArray($totalStyle);
        $sheet->getStyle("A$currentRow:F$currentRow")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A$currentRow:F$currentRow")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
        $currentRow += 2;


        foreach ($data['laporan']['penyusutan_pajak'] as $no => $akun) {
            $this->writeRow($sheet, $currentRow, $no, $akun, $data['basePersen'], $data['basePersenLalu']);
            $currentRow++;
        }

        $sheet->setCellValue('B'.$currentRow, 'LABA BERSIH SETELAH PAJAK');
        $sheet->setCellValue('C'.$currentRow, $data['totals']['LabaBersih']);
        $sheet->setCellValue('E'.$currentRow, $data['totals']['LabaBersihLalu']);
        $sheet->getStyle("A$currentRow:F$currentRow")->applyFromArray($totalStyle);
        $sheet->getRowDimension($currentRow)->setRowHeight(30);
        $sheet->getStyle('B'.$currentRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);


        $sheet->getStyle("C5:C$currentRow")->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("E5:E$currentRow")->getNumberFormat()->setFormatCode('#,##0');

        $sheet->getStyle("D5:D$currentRow")->getNumberFormat()->setFormatCode('0.0%');
        $sheet->getStyle("F5:F$currentRow")->getNumberFormat()->setFormatCode('0.0%');

        $filename = "Laba_Rugi_" . $bulan . "_" . $tahun . ".xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private function writeRow($sheet, $row, $no, $akun, $base, $baseLalu)
    {
        $sheet->setCellValueExplicit('A'.$row, (string)$no, DataType::TYPE_STRING);
        $sheet->setCellValue('B'.$row, $akun['nama']);

        $saldo = is_numeric($akun['saldo']) ? $akun['saldo'] : 0;
        $saldoLalu = isset($akun['saldo_lalu']) && is_numeric($akun['saldo_lalu']) ? $akun['saldo_lalu'] : 0;

        $sheet->setCellValue('C'.$row, $saldo);
        $sheet->setCellValue('D'.$row, $base > 0 ? $saldo / $base : 0);
        $sheet->setCellValue('E'.$row, $saldoLalu);
        $sheet->setCellValue('F'.$row, $baseLalu > 0 ? $saldoLalu / $baseLalu : 0);
    }

    private function getLabaRugiData($projectId, $bulan, $tahun)
    {
        if ($bulan === 'all') {
            $prevB = 'all'; $prevT = $tahun - 1;
        } else {
            $waktu = strtotime("-1 month", strtotime("$tahun-$bulan-01"));
            $prevB = date('m', $waktu); $prevT = date('Y', $waktu);
        }

        $hitung = function ($b, $t) use ($projectId) {
            $lap = [
                'pendapatan' => ['4001' => ['nama' => 'PENJUALAN', 'saldo' => 0], '4002' => ['nama' => 'POTONGAN PENJUALAN', 'saldo' => 0]],
                'beban_pokok' => ['5001' => ['nama' => 'BY MATERIAL', 'saldo' => 0], '5002' => ['nama' => 'BY TENAGA KERJA', 'saldo' => 0], '5003' => ['nama' => 'BY OVERHEAD', 'saldo' => 0], '5004' => ['nama' => 'BY PERENCANAAN DAN IZIN', 'saldo' => 0], '5005' => ['nama' => 'BY PENJUALAN LAIN-LAIN', 'saldo' => 0], '5006' => ['nama' => 'HARGA POKOK TANAH', 'saldo' => 0], '5007' => ['nama' => 'BY FASUM', 'saldo' => 0]],
                'beban_pemasaran' => ['5201' => ['nama' => 'KOMISI PENJUALAN', 'saldo' => 0], '5202' => ['nama' => 'BY MARKETING', 'saldo' => 0], '5203' => ['nama' => 'BY ENTERTAINTMENT', 'saldo' => 0]],
                'biaya_umum' => ['5101' => ['nama' => 'GAJI', 'saldo' => 0], '5102' => ['nama' => 'BONUS, LEMBUR', 'saldo' => 0], '5104' => ['nama' => 'TUNJANGAN', 'saldo' => 0], '5106' => ['nama' => 'MAKAN DAN MINUM', 'saldo' => 0], '5107' => ['nama' => 'TELEPON, PULSA, WIFI, LISTRIK DAN AIR', 'saldo' => 0], '5108' => ['nama' => 'BBM, TOL, TRANSPORT', 'saldo' => 0], '5109' => ['nama' => 'PARKIR', 'saldo' => 0], '5111' => ['nama' => 'PEMELIHARAAN ASET', 'saldo' => 0], '5112' => ['nama' => 'SEWA OPERASIONAL', 'saldo' => 0], '5113' => ['nama' => 'ALAT TULIS KANTOR', 'saldo' => 0], '5114' => ['nama' => 'KEPERLUAN KANTOR LAIN', 'saldo' => 0], '5115' => ['nama' => 'PERJALANAN DINAS', 'saldo' => 0], '5116' => ['nama' => 'BEBAN PAJAK', 'saldo' => 0], '5117' => ['nama' => 'SUMBANGAN DAN IURAN', 'saldo' => 0], '5119' => ['nama' => 'PELATIHAN PEGAWAI', 'saldo' => 0], '5120' => ['nama' => 'BEBAN PENYUSUTAN', 'saldo' => 0]],
                'pendapatan_biaya_luar' => ['6001' => ['nama' => 'PENDAPATAN LAIN-LAIN', 'saldo' => 0], '6002' => ['nama' => 'JASA GIRO', 'saldo' => 0], '6003' => ['nama' => 'PAJAK JASA GIRO', 'saldo' => 0], '6004' => ['nama' => 'BY TRANSFER', 'saldo' => 0], '6005' => ['nama' => 'ADMIN BANK', 'saldo' => 0], '6199' => ['nama' => 'BIAYA LAIN-LAIN', 'saldo' => 0]],
                'penyusutan_pajak' => ['7100' => ['nama' => 'PAJAK PENGHASILAN', 'saldo' => 0]],
            ];

            $query = Keuangan::where('project_id', $projectId)->whereYear('tanggal', $t);
            if ($b !== 'all') $query->whereMonth('tanggal', $b);
            $transaksi = $query->get();

            foreach ($transaksi as $trx) {
                $no = $trx->no_akun; $grp = null; $nb = 'debit';
                if (isset($lap['pendapatan'][$no])) { $grp = 'pendapatan'; $nb = 'kredit'; }
                elseif (isset($lap['beban_pokok'][$no])) { $grp = 'beban_pokok'; }
                elseif (isset($lap['beban_pemasaran'][$no])) { $grp = 'beban_pemasaran'; }
                elseif (isset($lap['biaya_umum'][$no])) { $grp = 'biaya_umum'; }
                elseif (isset($lap['pendapatan_biaya_luar'][$no])) { $grp = 'pendapatan_biaya_luar'; if(in_array($no,['6001','6002'])) $nb = 'kredit'; }
                elseif (isset($lap['penyusutan_pajak'][$no])) { $grp = 'penyusutan_pajak'; }

                if (!$grp) continue;
                $d = ($trx->input === 'Jurnal') ? $trx->mutasi_masuk : $trx->mutasi_keluar;
                $k = ($trx->input === 'Jurnal') ? $trx->mutasi_keluar : $trx->mutasi_masuk;
                $lap[$grp][$no]['saldo'] += ($nb === 'kredit') ? ($k - $d) : ($d - $k);
            }

            $totPnd = array_sum(array_column($lap['pendapatan'], 'saldo'));
            $totHPP = array_sum(array_column($lap['beban_pokok'], 'saldo'));
            $totPemasaran = array_sum(array_column($lap['beban_pemasaran'], 'saldo'));
            $totUmum = array_sum(array_column($lap['biaya_umum'], 'saldo'));
            $totOpr = $totPemasaran + $totUmum;
            $totLuar = 0;
            foreach ($lap['pendapatan_biaya_luar'] as $k => $v) { $totLuar += (in_array($k,['6001','6002']) ? $v['saldo'] : -$v['saldo']); }

            $labaKtr = $totPnd - $totHPP;
            $labaOpr = $labaKtr - $totOpr;
            $labaSebelumPajak = $labaOpr + $totLuar;
            $labaBersih = $labaSebelumPajak - array_sum(array_column($lap['penyusutan_pajak'], 'saldo'));

            return [
                'laporan' => $lap,
                'totals' => [
                    'Pendapatan' => $totPnd, 'BebanPokok' => $totHPP, 'LabaKotor' => $labaKtr,
                    'Pemasaran' => $totPemasaran, 'Umum' => $totUmum,
                    'LabaOperasional' => $labaOpr, 'LuarUsaha' => $totLuar,
                    'LabaSebelumPajak' => $labaSebelumPajak, 'LabaBersih' => $labaBersih
                ]
            ];
        };

        $cur = $hitung($bulan, $tahun);
        $pre = $hitung($prevB, $prevT);

        foreach ($cur['laporan'] as $g => $list) {
            foreach ($list as $no => $val) { $cur['laporan'][$g][$no]['saldo_lalu'] = $pre['laporan'][$g][$no]['saldo']; }
        }

        return [
            'bulan' => $bulan, 'tahun' => $tahun, 'laporan' => $cur['laporan'],
            'basePersen' => $cur['totals']['Pendapatan'], 'basePersenLalu' => $pre['totals']['Pendapatan'],
            'totals' => [
                'Pendapatan' => $cur['totals']['Pendapatan'], 'PendapatanLalu' => $pre['totals']['Pendapatan'],
                'BebanPokok' => $cur['totals']['BebanPokok'], 'BebanPokokLalu' => $pre['totals']['BebanPokok'],
                'LabaKotor' => $cur['totals']['LabaKotor'], 'LabaKotorLalu' => $pre['totals']['LabaKotor'],
                'Pemasaran' => $cur['totals']['Pemasaran'], 'PemasaranLalu' => $pre['totals']['Pemasaran'],
                'Umum' => $cur['totals']['Umum'], 'UmumLalu' => $pre['totals']['Umum'],
                'LabaOperasional' => $cur['totals']['LabaOperasional'], 'LabaOperasionalLalu' => $pre['totals']['LabaOperasional'],
                'LuarUsaha' => $cur['totals']['LuarUsaha'], 'LuarUsahaLalu' => $pre['totals']['LuarUsaha'],
                'LabaSebelumPajak' => $cur['totals']['LabaSebelumPajak'], 'LabaSebelumPajakLalu' => $pre['totals']['LabaSebelumPajak'],
                'LabaBersih' => $cur['totals']['LabaBersih'], 'LabaBersihLalu' => $pre['totals']['LabaBersih']
            ]
        ];
    }
}
