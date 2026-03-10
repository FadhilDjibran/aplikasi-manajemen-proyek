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
        $failReasonStats = [];
        $statusStats = [];
        $cityStats = [];
        $typeStats = [];

        if ($isAdminOrSuper) {

            $sourceStats = Lead::where('project_id', $projectId)
                ->select('sumber_lead', DB::raw('count(*) as total'))
                ->whereNotNull('sumber_lead')->where('sumber_lead', '!=', '')
                ->groupBy('sumber_lead')
                ->pluck('total', 'sumber_lead')
                ->toArray();

            $failReasonStats = Lead::where('project_id', $projectId)
                ->where('status_lead', 'Gagal Closing')
                ->whereNotNull('alasan_gagal')
                ->select('alasan_gagal', DB::raw('count(*) as total'))
                ->groupBy('alasan_gagal')
                ->pluck('total', 'alasan_gagal')
                ->toArray();

            $statusStats = Lead::where('project_id', $projectId)
                ->select('status_lead', DB::raw('count(*) as total'))
                ->groupBy('status_lead')
                ->pluck('total', 'status_lead')
                ->toArray();

            $cityStats = Lead::where('project_id', $projectId)
                ->select('kota_domisili', DB::raw('count(*) as total'))
                ->whereNotNull('kota_domisili')->where('kota_domisili', '!=', '')
                ->groupBy('kota_domisili')
                ->pluck('total', 'kota_domisili')
                ->toArray();

            $typeStats = DB::table('leads')
                ->join('tipe_rumah', 'leads.id_tipe_rumah_minat', '=', 'tipe_rumah.id_tipe')
                ->where('leads.project_id', $projectId)
                ->where('tipe_rumah.project_id', $projectId)
                ->whereNotNull('leads.id_tipe_rumah_minat')
                ->select('tipe_rumah.nama_tipe', DB::raw('count(leads.id_lead) as total'))
                ->groupBy('tipe_rumah.nama_tipe')
                ->pluck('total', 'nama_tipe')
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

        return view('dashboard', compact(
            'stats',
            'priorities',
            'personalKpi',
            'allKpiData',
            'sourceStats',
            'failReasonStats',
            'statusStats',
            'cityStats',
            'typeStats',
            'pendingHotProspek'
        ));
    }
}
