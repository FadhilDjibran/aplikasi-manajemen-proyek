<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Lead;
use Carbon\Carbon;

class UpdateLeadStatus extends Command
{
    protected $signature = 'leads:update-status {project_id?}';
    protected $description = 'Menurunkan status lead yang tidak di-follow up';

    public function handle()
    {
        $this->info('Memulai pengecekan status leads...');
        $projectId = $this->argument('project_id');
        $batasWarm = Carbon::now()->subDays(5)->format('Y-m-d');

        $warmQuery = Lead::where('status_lead', 'Warm Lead')
            ->whereHas('latestFollowUp', function ($q) use ($batasWarm) {
                $q->whereDate('tgl_follow_up', '<', $batasWarm);
            });

        if ($projectId) {
            $warmQuery->where('project_id', $projectId);
        }

        $warmLeads = $warmQuery->get();
        $warmCount = 0;

        foreach ($warmLeads as $lead) {
            $lead->update(['status_lead' => 'Cold Lead']);
            $warmCount++;
        }
        $this->info("Berhasil mengubah $warmCount Warm Lead menjadi Cold Lead.");

        $batasCold = Carbon::now()->subDays(30)->format('Y-m-d');
        $coldQuery = Lead::where('status_lead', 'Cold Lead')
            ->whereDate('updated_at', '<', $batasCold);

        if ($projectId) {
            $coldQuery->where('project_id', $projectId);
        }

        $coldLeads = $coldQuery->get();
        $coldCount = 0;

        foreach ($coldLeads as $lead) {
            $lead->update(['status_lead' => 'Tidak Prospek']);
            $coldCount++;
        }
        $this->info("Berhasil mengubah $coldCount Cold Lead menjadi Tidak Prospek.");
    }
}
