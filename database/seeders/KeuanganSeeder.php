<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Coa;

class KeuanganSeeder extends Seeder
{
    public function run()
    {
        $fileName  = 'KK2026.csv';
        $inputType = 'Kas Kecil';
        $projectId = 1;

        $file = database_path('seeders/' . $fileName);

        if (!file_exists($file)) {
            $this->command->error("File CSV tidak ditemukan di: {$file}");
            return;
        }

        $this->command->info("Memulai import data baru dari {$fileName}...");

        $validCoas = DB::table('coa')
            ->where('project_id', $projectId)
            ->select('no_akun', 'tahun')
            ->get()
            ->mapToGroups(function ($item) {
                return [$item->tahun => $item->no_akun];
            })
            ->toArray();

        $handle = fopen($file, "r");

        $countInsert = 0;
        $skippedKosong = 0;
        $skippedTidakValid = 0;
        $missingCoas = [];

        $successRecords = [];

        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {

            $tanggalRaw = trim($data[6] ?? '');
            $tanggal = null;
            $formatTersedia = ['d/m/Y', 'd-M-y', 'd F Y', 'j F Y', 'Y-m-d'];

            foreach ($formatTersedia as $format) {
                try {
                    $tanggal = Carbon::createFromFormat($format, $tanggalRaw)->format('Y-m-d');
                    break;
                } catch (\Exception $e) { continue; }
            }
            if (!$tanggal) {
                try { $tanggal = Carbon::parse($tanggalRaw)->format('Y-m-d'); }
                catch (\Exception $e) { $tanggal = null; }
            }

            if (!$tanggal || $tanggalRaw == 'TANGGAL' || $tanggalRaw == 'A') { continue; }

            $noAkun = trim($data[7] ?? '');
            if (empty($noAkun)) {
                $skippedKosong++;
                continue;
            }

            $tahunTransaksi = Carbon::parse($tanggal)->year;

            if (!isset($validCoas[$tahunTransaksi]) || !in_array($noAkun, $validCoas[$tahunTransaksi])) {
                $skippedTidakValid++;
                $missingCoas[$tahunTransaksi][] = $noAkun;
                continue;
            }

            $mutasiMasuk  = $this->cleanNumber($data[13] ?? '0');
            $mutasiKeluar = $this->cleanNumber($data[14] ?? '0');

            DB::table('keuangan')->insert([
                'project_id'       => $projectId,
                'tanggal'          => $tanggal,
                'input'            => $inputType,
                'no_akun'          => $noAkun,
                'keterangan'       => trim($data[12] ?? ''),
                'bukti'            => trim($data[10] ?? ''),
                'jenis_penggunaan' => trim($data[11] ?? ''),
                'mutasi_masuk'     => $mutasiMasuk,
                'mutasi_keluar'    => $mutasiKeluar,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            $successRecords[] = (object) [
                'tanggal'       => $tanggal,
                'input'         => $inputType,
                'no_akun'       => $noAkun,
                'mutasi_masuk'  => $mutasiMasuk,
                'mutasi_keluar' => $mutasiKeluar,
            ];

            $countInsert++;
        }

        fclose($handle);

        $this->command->info("Selesai mengolah file.");
        $this->command->line("<fg=green>Berhasil import: {$countInsert} transaksi.</>");

        if ($skippedKosong > 0) $this->command->warn("Dilewati (Akun Kosong): {$skippedKosong}");
        if ($skippedTidakValid > 0) $this->command->error("Dilewati (Akun Tidak Terdaftar): {$skippedTidakValid}");

        if ($countInsert > 0) {
            $this->command->newLine();
            if ($this->command->confirm("Apakah Anda ingin melanjutkan dengan memperbarui (Adjust) Saldo Akhir CoA untuk {$countInsert} data ini?", true)) {

                $this->command->info("Memperbarui saldo... Mohon tunggu.");

                $bar = $this->command->getOutput()->createProgressBar(count($successRecords));
                $bar->start();

                foreach ($successRecords as $record) {
                    $this->adjustBalances($record, $projectId, 'apply');
                    $bar->advance();
                }

                $bar->finish();
                $this->command->newLine();
                $this->command->info("Saldo berhasil diperbarui!");
            } else {
                $this->command->warn("Proses adjust saldo dibatalkan. Data tetap tersimpan namun saldo CoA tidak berubah.");
            }
        }
    }

    private function cleanNumber($value)
    {
        $value = preg_replace('/\s+/', '', $value);
        if ($value === '' || $value === '-' || $value === 'MASUK' || $value === 'KELUAR') return 0;
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);
        return (float) $value;
    }

    private function adjustBalances($transaksi, $projectId, $mode = 'apply')
    {
        $tahun = date('Y', strtotime($transaksi->tanggal));
        $masuk = (float) $transaksi->mutasi_masuk;
        $keluar = (float) $transaksi->mutasi_keluar;

        if ($mode === 'reverse') {
            $masuk = -$masuk;
            $keluar = -$keluar;
        }

        if ($transaksi->input !== 'Jurnal') {
            $dompet = Coa::where('project_id', $projectId)
                ->where('tahun', $tahun)
                ->where('nama_akun', 'LIKE', '%' . $transaksi->input . '%')
                ->first();

            if ($dompet && $dompet->jenis_laporan !== 'Laba Rugi') {
                $dompet->saldo_akhir += ($masuk - $keluar);
                $dompet->save();
            }
        }

        $lawan = Coa::where('project_id', $projectId)
            ->where('tahun', $tahun)
            ->where('no_akun', $transaksi->no_akun)
            ->first();

        if ($lawan && $lawan->jenis_laporan !== 'Laba Rugi') {

            if ($transaksi->input === 'Jurnal') {
                $lawan->saldo_akhir += ($masuk - $keluar);
            } else {
                $lawan->saldo_akhir += ($keluar - $masuk);
            }

            $lawan->save();
        }
    }
}
