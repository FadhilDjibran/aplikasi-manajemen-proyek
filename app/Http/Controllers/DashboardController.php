<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\FollowUp;
use App\Models\PicMarketing;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $projectId = session('active_project_id');
        $isSuper = $user->role === 'Super_Admin';
        $isAdmin = $user->role === 'Admin';
        $isAdminOrSuper = $isSuper || $isAdmin;

        $stats = [
            'total' => Lead::where('project_id', $projectId)->count(),
            'cold'  => Lead::where('project_id', $projectId)->where('status_lead', 'Cold Lead')->count(),
            'warm'  => Lead::where('project_id', $projectId)->where('status_lead', 'Warm Lead')->count(),
            'hot'   => Lead::where('project_id', $projectId)->where('status_lead', 'Hot Prospek')->count(),
            'failed'=> Lead::where('project_id', $projectId)->where('status_lead', 'Gagal Closing')->count(),
            'tidak_prospek' => Lead::where('project_id', $projectId)->where('status_lead', 'Tidak Prospek')->count(),
        ];

        $sourceStats = [];
        if ($isAdminOrSuper) {
            $sourceStats = Lead::where('project_id', $projectId)
                ->select('sumber_lead', DB::raw('count(*) as total'))
                ->groupBy('sumber_lead')
                ->pluck('total', 'sumber_lead')
                ->toArray();
        }

        $failReasonStats = [];
        if ($isAdminOrSuper) {
            $failReasonStats = Lead::where('project_id', $projectId)
                ->where('status_lead', 'Gagal Closing')
                ->whereNotNull('alasan_gagal')
                ->select('alasan_gagal', DB::raw('count(*) as total'))
                ->groupBy('alasan_gagal')
                ->pluck('total', 'alasan_gagal')
                ->toArray();
        }

        $latestFollowUpIds = FollowUp::select(DB::raw('MAX(id_follow_up) as id'))
            ->groupBy('id_lead')
            ->pluck('id');

        $prioritiesQuery = FollowUp::with(['lead', 'pic'])
            ->whereIn('id_follow_up', $latestFollowUpIds)
            ->whereHas('lead', function ($q) use ($projectId) {
                $q->where('project_id', $projectId)
                  ->where('status_lead', 'Warm Lead');
            });

        if (!$isSuper) {
            $currentPic = PicMarketing::where('user_id', $user->id)->first();
            if ($currentPic) {
                $prioritiesQuery->where('id_pic', $currentPic->id_pic);
            } else {
                $prioritiesQuery->where('id_pic', 0);
            }
        }

        $priorities = $prioritiesQuery
            ->orderByRaw('ISNULL(tgl_follow_up_berikutnya), tgl_follow_up_berikutnya ASC')
            ->take(10)
            ->get();

        $personalKpi = null;
        $allKpiData = collect([]);

        if ($isAdminOrSuper) {
            $allKpiData = PicMarketing::whereHas('user', function ($q) use ($projectId) {
                $q->where('project_id', $projectId);
            })->get();
        } else {
            $personalKpi = PicMarketing::where('user_id', $user->id)->first();
        }

        $pendingHotProspek = Lead::where('project_id', $projectId)
            ->where('status_lead', 'Hot Prospek')
            ->doesntHave('transaksi')
            ->count();

        return view('dashboard', compact('stats', 'priorities', 'personalKpi', 'allKpiData', 'sourceStats', 'pendingHotProspek', 'failReasonStats'));
    }
}
