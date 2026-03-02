<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\FollowUp;
use App\Models\Lead;

class FollowupsImportSeeder extends Seeder
{
    public function run()
    {
        $csvFile = database_path('seeders/CSV Followup.csv');

        if (!file_exists($csvFile)) {
            $this->command->error("File CSV tidak ditemukan.");
            return;
        }

        $file = fopen($csvFile, 'r');
        fgetcsv($file, 1000, ';'); // Lewati Header

        $this->command->info('Mulai import data beserta Tanggal Follow Up Berikutnya...');

        while (($row = fgetcsv($file, 1000, ';')) !== false) {
            $idLead = trim($row[0]);
            if (empty($idLead)) continue;

            // Pastikan Lead-nya ada
            if (!Lead::where('id_lead', $idLead)->exists()) continue;

            // 1. Parsing TANGGAL FOLLOW UP (Format Excel: DD/MM/YYYY)
            $tglFollowUp = null;
            if (!empty(trim($row[2]))) {
                try {
                    $tglFollowUp = Carbon::createFromFormat('d/m/Y', trim($row[2]))->format('Y-m-d');
                } catch (\Exception $e) {
                    $tglFollowUp = null;
                }
            }

            // Jika tanggal utama kosong/gagal, jadikan hari ini
            if (!$tglFollowUp) {
                $tglFollowUp = Carbon::now()->format('Y-m-d');
            }

            // 2. Parsing TANGGAL FU BERIKUTNYA (Format Excel: MM/DD/YYYY)
            // Ini akan memproses data "12/8/2025" menjadi 8 Desember 2025
            $tglNext = null;
            if (!empty(trim($row[8]))) {
                try {
                    $tglNext = Carbon::createFromFormat('m/d/Y', trim($row[8]))->format('Y-m-d');
                } catch (\Exception $e) {
                    // Fallback jika ada format campuran
                    try {
                        $tglNext = Carbon::parse(trim($row[8]))->format('Y-m-d');
                    } catch (\Exception $e2) {
                        $tglNext = null;
                    }
                }
            }

            // 3. Parsing JAM
            $jamFollowUp = null;
            if (!empty(trim($row[3]))) {
                try {
                    $jamFollowUp = Carbon::parse(trim($row[3]))->format('H:i:s');
                } catch (\Exception $e) {
                    $jamFollowUp = null;
                }
            }

            // 4. Mapping Channel
            $channel = trim($row[4]);
            if (strtoupper($channel) == 'WA') $channel = 'Whatsapp';
            elseif (empty($channel)) $channel = null;

            // 5. Simpan Data ke Database
            FollowUp::updateOrCreate(
                [
                    'id_lead'       => $idLead,
                    'tgl_follow_up' => $tglFollowUp,
                ],
                [
                    'jam_follow_up'            => $jamFollowUp,
                    'channel_follow_up'        => $channel,
                    'hasil_follow_up'          => !empty($row[5]) ? trim($row[5]) : null,
                    'status_minat_follow_up'   => !empty($row[6]) ? trim($row[6]) : 'Mulai Tertarik',
                    'rencana_tindak_lanjut'    => !empty($row[7]) ? trim($row[7]) : null,

                    // MENGIMPOR KEMBALI TANGGAL NEXT FU
                    'tgl_follow_up_berikutnya' => $tglNext,

                    'status_follow_up'         => !empty($row[9]) ? trim($row[9]) : 'Proses Follow Up',
                    'tgl_survey'               => null,
                    'catatan'                  => !empty($row[13]) ? trim($row[13]) : null,
                ]
            );
        }

        fclose($file);
        $this->command->info('Import selesai! Tanggal FU Berikutnya berhasil diproses.');
    }
}
