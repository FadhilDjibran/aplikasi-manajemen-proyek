<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CoaSeeder2025 extends Seeder
{
    public function run()
    {
        $tahunTarget = 2025;
        $projectIdTarget = 1;
        $fileName = 'coa2025.csv';
        $file = database_path('seeders/' . $fileName);

        if (!file_exists($file)) {
            $this->command->error("File CSV tidak ditemukan di: {$file}");
            return;
        }

        $handle = fopen($file, "r");
        $row = 0;

        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            $row++;

            if ($row <= 2) {
                continue;
            }

            $noAkun = trim($data[0] ?? '');
            $namaAkun = trim($data[2] ?? '');

            if ($noAkun === '' || $namaAkun === '') {
                continue;
            }

            $kategori = trim($data[1] ?? '');
            $posisiRaw = strtoupper(trim($data[3] ?? ''));
            $jenisRaw = strtoupper(trim($data[4] ?? ''));

            $posisiNormal = ($posisiRaw === 'D') ? 'Debit' : 'Kredit';

            $jenisLaporan = ($jenisRaw === 'NRC') ? 'Neraca' : 'Laba Rugi';

            $saldoAwalDebit = $this->cleanNumber($data[5] ?? '0');
            $saldoAwalKredit = $this->cleanNumber($data[6] ?? '0');
            $saldoAkhir = $this->cleanNumber($data[7] ?? '0');

            DB::table('coa')->updateOrInsert(
                [
                    'project_id' => $projectIdTarget,
                    'tahun'      => $tahunTarget,
                    'no_akun'    => $noAkun
                ],
                [
                    'project_id'        => $projectIdTarget,
                    'tahun'             => $tahunTarget,
                    'kategori_akun'     => $kategori,
                    'nama_akun'         => $namaAkun,
                    'posisi_normal'     => $posisiNormal,
                    'jenis_laporan'     => $jenisLaporan,
                    'saldo_awal_debit'  => $saldoAwalDebit,
                    'saldo_awal_kredit' => $saldoAwalKredit,
                    'saldo_akhir'       => $saldoAkhir,
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]
            );
        }

        fclose($handle);
        $this->command->info('Data CoA 2025 berhasil diimpor!');
    }

    private function cleanNumber($value)
    {
        $value = preg_replace('/\s+/', '', $value);

        if ($value === '' || $value === '-') {
            return 0;
        }

        $value = str_replace('.', '', $value);

        return (float) $value;
    }
}
