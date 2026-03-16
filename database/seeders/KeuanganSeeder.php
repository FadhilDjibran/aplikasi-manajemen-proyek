<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KeuanganSeeder extends Seeder
{
    public function run()
    {
        $fileName  = 'KB2024.csv';
        $inputType = 'Kas Besar';
        $projectId = 1;

        $file = database_path('seeders/' . $fileName);

        if (!file_exists($file)) {
            $this->command->error("File CSV tidak ditemukan di: {$file}");
            return;
        }

        $handle = fopen($file, "r");
        $count = 0;

        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {

            $tanggalRaw = trim($data[6] ?? '');

            $tanggal = null;
            $formatTersedia = ['d/m/Y', 'd-M-y'];

            foreach ($formatTersedia as $format) {
                try {
                    $tanggal = \Carbon\Carbon::createFromFormat($format, $tanggalRaw)->format('Y-m-d');
                    break;
                } catch (\Exception $e) {
                    continue;
                }
            }

            if (!$tanggal) {
                continue;
            }

            $noAkun          = trim($data[7] ?? '');
            $kodeBukti       = trim($data[10] ?? '');
            $jenisPenggunaan = trim($data[11] ?? '');
            $keterangan      = trim($data[12] ?? '');

            $mutasiMasuk  = $this->cleanNumber($data[13] ?? '0');
            $mutasiKeluar = $this->cleanNumber($data[14] ?? '0');

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
    }

    private function cleanNumber($value)
    {
        $value = preg_replace('/\s+/', '', $value);
        if ($value === '' || $value === '-') return 0;

        $value = str_replace('.', '', $value);

        $value = str_replace(',', '.', $value);

        return (float) $value;
    }
}
