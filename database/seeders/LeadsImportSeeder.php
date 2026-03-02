<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\Lead;
use App\Models\TipeRumah;
use App\Models\PicMarketing;

class LeadsImportSeeder extends Seeder
{
    public function run()
    {
        $csvFile = database_path('seeders/CSV Lead.csv');

        if (!file_exists($csvFile)) {
            $this->command->error("File CSV tidak ditemukan di: $csvFile");
            return;
        }

        $file = fopen($csvFile, 'r');
        fgetcsv($file, 1000, ';'); // Skip Header 1
        fgetcsv($file, 1000, ';'); // Skip Header 2

        $this->command->info('Mulai proses import data...');

        while (($row = fgetcsv($file, 1000, ';')) !== false) {

            // Skip jika data ID atau Nama kosong
            if (empty($row[0]) || empty($row[2])) continue;

            // 1. Parsing Tanggal
            try {
                $tglMasuk = Carbon::parse($row[1])->format('Y-m-d');
            } catch (\Exception $e) {
                $tglMasuk = now();
            }

            // 2. Mapping Status (On Progress -> Cold Prospek)
            $statusLead = trim($row[7]);
            if ($statusLead == 'On Progress') $statusLead = 'Cold Prospek';
            if ($statusLead == 'Batal Booking') $statusLead = 'Tidak Deal';

            $validStatuses = ['Tidak Prospek', 'Cold Prospek', 'Hot Prospek', 'Deal', 'Tidak Deal'];
            if (!in_array($statusLead, $validStatuses)) {
                $statusLead = 'Cold Prospek';
            }

            // 3. Foreign Key: Tipe Rumah
            $tipeId = null;
            if (!empty($row[5])) {
                $tipe = TipeRumah::firstOrCreate(
                    ['nama_tipe' => trim($row[5])],
                    ['project_id' => 1]
                );
                $tipeId = $tipe->id_tipe;
            }

            // 4. Foreign Key: PIC Marketing
            $picId = null;
            if (!empty($row[10])) {
                $pic = PicMarketing::firstOrCreate(['nama_pic' => trim($row[10])]);
                $picId = $pic->id_pic;
            }

            // 5. Simpan Data Lead
            Lead::updateOrCreate(
                ['id_lead' => trim($row[0])],
                [
                    'project_id'          => 1,
                    'tgl_masuk'           => $tglMasuk,
                    'nama_lead'           => trim($row[2]),
                    'no_whatsapp'         => preg_replace('/[^0-9]/', '', $row[3]),

                    // Fix Sumber Lead (Null jika kosong)
                    'sumber_lead'         => !empty($row[4]) ? trim($row[4]) : null,

                    'id_tipe_rumah_minat' => $tipeId,
                    'status_lead'         => $statusLead,
                    'id_pic'              => $picId,
                    'kota_domisili'       => !empty($row[12]) ? trim($row[12]) : null,
                    'alamat'              => !empty($row[13]) ? trim($row[13]) : null,
                    'status_pekerjaan'    => !empty($row[16]) ? trim($row[16]) : null,

                    // PERUBAHAN: Set rencana pembayaran ke NULL (skip import)
                    'rencana_pembayaran'  => null,

                    'catatan'             => !empty($row[18]) ? trim($row[18]) : null,
                ]
            );
        }

        fclose($file);
        $this->command->info('Import selesai! Rencana pembayaran dikosongkan.');
    }
}
