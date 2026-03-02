<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use Carbon\Carbon;

class UpdateLeadStatus extends Command
{
    protected $signature = 'leads:update-status';

    protected $description = 'Otomatis menurunkan status lead berdasarkan ketidakaktifan follow up';

    public function handle()
    {
        $this->info('Memulai pengecekan status leads...');
        $batasWarm = Carbon::now()->subDays(5)->format('Y-m-d');

        $warmLeads = Lead::where('status_lead', 'Warm Lead')
            ->where(function ($query) use ($batasWarm) {
                $query->whereHas('latestFollowUp', function ($q) use ($batasWarm) {
                    $q->whereDate('tgl_follow_up', '<', $batasWarm);
                })
                ->orWhere(function ($q) use ($batasWarm) {
                    $q->doesntHave('followUps')
                      ->whereDate('tgl_masuk', '<', $batasWarm);
                });
            })
            ->update(['status_lead' => 'Cold Lead']);

        $this->info("Berhasil mengubah $warmLeads Warm Lead menjadi Cold Lead.");

        $batasCold = Carbon::now()->subDays(30)->format('Y-m-d');

    $coldLeads = Lead::where('status_lead', 'Cold Lead')
        ->whereDate('updated_at', '<', $batasCold)
        ->update(['status_lead' => 'Tidak Prospek']);

    $this->info("Berhasil mengubah $coldLeads Cold Lead menjadi Tidak Prospek.");
        }
}
