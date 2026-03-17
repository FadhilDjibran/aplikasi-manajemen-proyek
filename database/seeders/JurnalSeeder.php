<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class JurnalSeeder extends Seeder
{
    public function run()
    {
        $fileName  = 'jurnal2025.csv';
        $inputType = 'Jurnal';
        $projectId = 1;

        $file = database_path('seeders/' . $fileName);

        if (!file_exists($file)) {
            $this->command->error("File CSV Jurnal tidak ditemukan di: {$file}");
            return;
        }

        $validCoas = DB::table('coa')
            ->where('project_id', $projectId)
            ->select('no_akun', 'tahun')
            ->get()
            ->mapToGroups(function ($item) {
                return [$item->tahun => $item->no_akun];
            })
            ->toArray();

        $handle = fopen($file, "r");

        $count = 0;
        $skippedKosong = 0;
        $skippedTidakValid = 0;
        $missingCoas = [];

        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {

            $tanggalRaw = trim($data[5] ?? '');

            $tanggal = null;
            $formatTersedia = ['d/m/Y', 'd-M-y', 'd F Y', 'j F Y', 'Y-m-d'];

            foreach ($formatTersedia as $format) {
                try {
                    $tanggal = Carbon::createFromFormat($format, $tanggalRaw)->format('Y-m-d');
                    break;
                } catch (\Exception $e) {
                    continue;
                }
            }

            if (!$tanggal) {
                try {
                    $tanggal = Carbon::parse($tanggalRaw)->format('Y-m-d');
                } catch (\Exception $e) {
                    $tanggal = null;
                }
            }

            if (!$tanggal || $tanggalRaw == 'TANGGAL' || $tanggalRaw == 'A') {
                continue;
            }

            $noAkun = trim($data[6] ?? '');

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

            $kodeBukti       = trim($data[9] ?? '');
            $jenisPenggunaan = trim($data[10] ?? '');
            $keterangan      = trim($data[11] ?? '');

            $mutasiMasuk  = $this->cleanNumber($data[12] ?? '0');
            $mutasiKeluar = $this->cleanNumber($data[13] ?? '0');

            DB::table('keuangan')->insert([
                'project_id'       => $projectId,
                'tanggal'          => $tanggal,
                'input'            => $inputType,
                'no_akun'          => $noAkun,
                'keterangan'       => $keterangan,
                'bukti'            => $kodeBukti,
                'jenis_penggunaan' => $jenisPenggunaan,
                'mutasi_masuk'     => $mutasiMasuk,
                'mutasi_keluar'    => $mutasiKeluar,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            $count++;
        }

        fclose($handle);

        $this->command->info("Selesai! Berhasil mengimpor {$count} transaksi {$inputType} dari file {$fileName}.");

        if ($skippedKosong > 0 || $skippedTidakValid > 0) {
            $this->command->warn("Rincian baris yang dilewati:");

            if ($skippedKosong > 0) {
                $this->command->line("- <fg=cyan>{$skippedKosong} baris dilewati karena kolom No Akun (CoA) kosong.</>");
            }

            if ($skippedTidakValid > 0) {
                $this->command->line("- <fg=red>{$skippedTidakValid} baris dilewati karena No Akun tidak terdaftar di Database.</>");
            }

            if (!empty($missingCoas)) {
                $this->command->error("\nDaftar No Akun (CoA) yang perlu Anda tambahkan ke Master Data CoA:");
                foreach ($missingCoas as $tahun => $coas) {
                    $uniqueCoas = array_unique($coas);
                    $this->command->line("<fg=yellow>Tahun {$tahun}: " . implode(', ', $uniqueCoas) . "</>");
                }
            }
        }
    }

    private function cleanNumber($value)
    {
        $value = preg_replace('/\s+/', '', $value);
        if ($value === '' || $value === '-' || $value === 'MASUK' || $value === 'KELUAR' || $value === 'DEBET' || $value === 'KREDIT') return 0;

        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);

        return (float) $value;
    }
}
