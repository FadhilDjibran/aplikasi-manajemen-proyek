<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\Lead;
use App\Models\TipeRumah;

class LeadsImportSeeder extends Seeder
{
    public function run()
    {
        $csvFile = database_path('seeders/CSV Lead new.csv');

        if (!file_exists($csvFile)) {
            $this->command->error("File CSV tidak ditemukan di $csvFile");
            return;
        }

        $file = fopen($csvFile, 'r');

        fgetcsv($file, 2000, ';');
        fgetcsv($file, 2000, ';');

        $this->command->info('Mulai proses import data Leads...');

        $count = 0;
        while (($row = fgetcsv($file, 2000, ';')) !== false) {

            if (empty($row[0]) || trim($row[0]) == '' || empty($row[2]) || trim($row[2]) == '') {
                continue;
            }

            $rawDate = trim($row[1]);
            $tglMasuk = $this->parseIndonesianDate($rawDate);

            $statusLead = trim($row[7] ?? 'Cold Lead');
            $validStatuses = ['Cold Lead', 'Warm Lead', 'Hot Prospek', 'Tidak Prospek', 'Gagal Closing'];

            if (!in_array($statusLead, $validStatuses)) {
                $statusLead = 'Cold Lead';
            }

            $tipeId = null;
            $namaTipe = trim($row[5] ?? '');
            if (!empty($namaTipe)) {
                $tipe = TipeRumah::firstOrCreate(
                    ['nama_tipe' => $namaTipe],
                    ['project_id' => 1]
                );
                $tipeId = $tipe->id_tipe;
            }

            Lead::updateOrCreate(
                ['id_lead' => trim($row[0])],
                [
                    'project_id'          => 1,
                    'tgl_masuk'           => $tglMasuk,
                    'nama_lead'           => trim($row[2]),
                    'no_whatsapp'         => $this->cleanPhoneNumber($row[3] ?? ''),
                    'sumber_lead'         => !empty($row[4]) ? trim($row[4]) : null,
                    'id_tipe_rumah_minat' => $tipeId,
                    'status_lead'         => $statusLead,
                    'id_pic'              => 1,
                    'kota_domisili'       => !empty($row[12]) ? trim($row[12]) : null,
                    'alamat'              => !empty($row[13]) ? trim($row[13]) : null,
                    'status_pekerjaan'    => !empty($row[16]) ? trim($row[16]) : null,
                    'catatan'             => !empty($row[18]) ? trim($row[18]) : null,
                ]
            );
            $count++;
        }

        fclose($file);
        $this->command->info("Berhasil mengimpor $count data Lead.");
    }

    private function cleanPhoneNumber($number)
    {
        $cleaned = preg_replace('/[^0-9]/', '', $number);
        return $cleaned;
    }

    private function parseIndonesianDate($dateString)
    {
        if (empty($dateString)) return now();

        $months = [
            'Januari' => 'January', 'Februari' => 'February', 'Maret' => 'March',
            'April' => 'April', 'Mei' => 'May', 'Juni' => 'June',
            'Juli' => 'July', 'Agustus' => 'August', 'September' => 'September',
            'Oktober' => 'October', 'November' => 'November', 'Desember' => 'December'
        ];

        $translatedDate = str_replace(array_keys($months), array_values($months), $dateString);

        try {
            return Carbon::parse($translatedDate)->format('Y-m-d');
        } catch (\Exception $e) {
            return now();
        }
    }
}
