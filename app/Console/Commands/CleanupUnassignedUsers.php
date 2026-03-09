<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

class CleanupUnassignedUsers extends Command
{
    protected $signature = 'users:cleanup-unassigned';

    protected $description = 'Menghapus otomatis user yang tidak diberi role selama 7 hari sejak mendaftar';

    public function handle()
    {
        $this->info('Memulai pengecekan user tanpa role...');

        $batasWaktu = Carbon::now()->subDays(7)->format('Y-m-d H:i:s');

        $abandonedUsers = User::where(function ($query) {
                $query->whereNull('role')
                      ->orWhere('role', '');
            })
            ->where('created_at', '<=', $batasWaktu)
            ->get();

        $hapusCount = 0;

        foreach ($abandonedUsers as $user) {
            $user->delete();
            $hapusCount++;
        }

        $this->info("Selesai! Berhasil menghapus $hapusCount user yang menggantung.");
    }
}
