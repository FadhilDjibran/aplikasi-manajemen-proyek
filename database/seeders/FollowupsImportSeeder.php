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
        $csvFile = database_path('seeders/CSV Followup new.csv');

        if (!file_exists($csvFile)) {
            $this->command->error("File CSV tidak ditemukan di: $csvFile");
            return;
        }

        $file = fopen($csvFile, 'r');
        fgetcsv($file, 2000, ';'); // Lewati Header baris pertama

        $this->command->info('Mulai import data Follow Up...');

        $count = 0;
        while (($row = fgetcsv($file, 2000, ';')) !== false) {
            $idLead = trim($row[0]);

            // Lewati jika ID Lead kosong
            if (empty($idLead)) continue;

            // Pastikan Lead-nya ada di database leads
            if (!Lead::where('id_lead', $idLead)->exists()) {
                // Opsional: Buka komen di bawah jika ingin melihat lead mana yang di-skip
                // $this->command->warn("Lead ID $idLead tidak ditemukan, skip followup.");
                continue;
            }

            // 1. Parsing TANGGAL FOLLOW UP (Format CSV: DD/MM/YYYY)
            $tglFollowUp = null;
            if (!empty(trim($row[2]))) {
                try {
                    $tglFollowUp = Carbon::createFromFormat('d/m/Y', trim($row[2]))->format('Y-m-d');
                } catch (\Exception $e) {
                    // Fallback jika format meleset
                    try {
                        $tglFollowUp = Carbon::parse(trim($row[2]))->format('Y-m-d');
                    } catch (\Exception $e2) {
                        $tglFollowUp = null;
                    }
                }
            }

            // Jika tanggal utama kosong/gagal, defaultkan hari ini
            if (!$tglFollowUp) {
                $tglFollowUp = Carbon::now()->format('Y-m-d');
            }

            // 2. Parsing JAM (Format CSV: 4:00:00 PM)
            $jamFollowUp = null;
            if (!empty(trim($row[3]))) {
                try {
                    // strtotime aman untuk mengubah '4:00:00 PM' ke format '16:00:00'
                    $jamFollowUp = date('H:i:s', strtotime(trim($row[3])));
                } catch (\Exception $e) {
                    $jamFollowUp = null;
                }
            }

            // 3. Mapping Channel
            $channel = trim($row[4]);
            if (strtoupper($channel) == 'WA') $channel = 'Whatsapp';
            elseif (empty($channel)) $channel = null;

            // 4. Parsing TANGGAL FU BERIKUTNYA (Format CSV: MM/DD/YYYY)
            $tglNext = null;
            if (!empty(trim($row[8]))) {
                try {
                    // Spesifik ke format US sesuai CSV (contoh: 12/8/2025)
                    $tglNext = Carbon::createFromFormat('m/d/Y', trim($row[8]))->format('Y-m-d');
                } catch (\Exception $e) {
                    try {
                        $tglNext = Carbon::parse(trim($row[8]))->format('Y-m-d');
                    } catch (\Exception $e2) {
                        $tglNext = null;
                    }
                }
            }

            // 5. Tanggal Survey
            $tglSurvey = null;
            if (!empty(trim($row[11]))) {
                 try {
                     // Asumsi format sama dengan tgl followup utama
                    $tglSurvey = Carbon::createFromFormat('d/m/Y', trim($row[11]))->format('Y-m-d');
                } catch (\Exception $e) {
                    $tglSurvey = null;
                }
            }

            // 6. Simpan Data ke Database
            FollowUp::updateOrCreate(
                [
                    'id_lead'       => $idLead,
                    'tgl_follow_up' => $tglFollowUp,
                ],
                [
                    'project_id'               => 1,
                    'id_pic'                   => 1,
                    'jam_follow_up'            => $jamFollowUp,
                    'channel_follow_up'        => $channel,
                    'hasil_follow_up'          => !empty($row[5]) ? trim($row[5]) : null,
                    'rencana_tindak_lanjut'    => !empty($row[7]) ? trim($row[7]) : null,
                    'tgl_follow_up_berikutnya' => $tglNext,
                    'status_follow_up'         => !empty($row[9]) ? trim($row[9]) : 'Proses Follow Up', // Default Selesai
                    'tgl_survey'               => $tglSurvey,
                    'catatan'                  => !empty($row[13]) ? trim($row[13]) : null,
                ]
            );
            $count++;
        }

        fclose($file);
        $this->command->info("Import selesai! Berhasil memproses $count data Follow Up.");
    }
}
