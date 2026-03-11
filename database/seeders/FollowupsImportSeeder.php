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
        fgetcsv($file, 2000, ';');

        $this->command->info('Mulai import data Follow Up...');

        $count = 0;
        while (($row = fgetcsv($file, 2000, ';')) !== false) {
            $idLead = trim($row[0]);

            if (empty($idLead)) continue;

            if (!Lead::where('id_lead', $idLead)->exists()) {
                continue;
            }

            $tglFollowUp = null;
            if (!empty(trim($row[2]))) {
                try {
                    $tglFollowUp = Carbon::createFromFormat('d/m/Y', trim($row[2]))->format('Y-m-d');
                } catch (\Exception $e) {
                    try {
                        $tglFollowUp = Carbon::parse(trim($row[2]))->format('Y-m-d');
                    } catch (\Exception $e2) {
                        $tglFollowUp = null;
                    }
                }
            }

            if (!$tglFollowUp) {
                $tglFollowUp = Carbon::now()->format('Y-m-d');
            }

            $jamFollowUp = null;
            if (!empty(trim($row[3]))) {
                try {
                    $jamFollowUp = date('H:i:s', strtotime(trim($row[3])));
                } catch (\Exception $e) {
                    $jamFollowUp = null;
                }
            }

            $channel = trim($row[4]);
            if (strtoupper($channel) == 'WA') $channel = 'Whatsapp';
            elseif (empty($channel)) $channel = null;

            $tglNext = null;
            if (!empty(trim($row[8]))) {
                try {
                    $tglNext = Carbon::createFromFormat('m/d/Y', trim($row[8]))->format('Y-m-d');
                } catch (\Exception $e) {
                    try {
                        $tglNext = Carbon::parse(trim($row[8]))->format('Y-m-d');
                    } catch (\Exception $e2) {
                        $tglNext = null;
                    }
                }
            }

            $tglSurvey = null;
            if (!empty(trim($row[11]))) {
                 try {
                    $tglSurvey = Carbon::createFromFormat('d/m/Y', trim($row[11]))->format('Y-m-d');
                } catch (\Exception $e) {
                    $tglSurvey = null;
                }
            }

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
                    'status_follow_up'         => !empty($row[9]) ? trim($row[9]) : 'Proses Follow Up',
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
