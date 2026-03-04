<?php

namespace App\Http\Controllers;

use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\PicMarketing;
use Illuminate\Http\Request;

class FollowUpController extends Controller
{
    public function index(Request $request)
    {
        $projectId = session('active_project_id');
        $search = $request->search;

        $query = Lead::with(['latestFollowUp.pic'])
            ->where('project_id', $projectId)
            ->whereHas('followUps');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_lead', 'like', "%{$search}%")
                ->orWhere('id_lead', 'like', "%{$search}%")
                ->orWhere('no_whatsapp', 'like', "%{$search}%");
            });
        }

        $query->orderByDesc(
            FollowUp::select('created_at')
                ->whereColumn('follow_up.id_lead', 'leads.id_lead')
                ->orderBy('created_at', 'desc')
                ->take(1)
        );

        $activeLeads = (clone $query)
            ->whereIn('status_lead', ['Warm Lead'])
            ->paginate(10, ['*'], 'active_page')
            ->withQueryString();

        $inactiveLeads = (clone $query)
            ->whereIn('status_lead', ['Cold Lead', 'Tidak Prospek', 'Hot Prospek', 'Gagal Closing'])
            ->paginate(10, ['*'], 'inactive_page')
            ->withQueryString();

        return view('followup.index', compact('activeLeads', 'inactiveLeads'));
    }

    public function process(Request $request, $id_lead)
    {
        $projectId = session('active_project_id');
        $lead = Lead::findOrFail($id_lead);

        FollowUp::create([
            'id_lead' => $id_lead,
            'project_id' => $projectId,
            'id_pic' => $lead->id_pic,
            'tgl_follow_up' => now(),
            'jam_follow_up' => now()->format('H:i:s'),
            'channel_follow_up' => $request->channel_follow_up,
            'hasil_follow_up' => $request->hasil,
            'rencana_tindak_lanjut' => $request->rencana,
            'tgl_follow_up_berikutnya' => $request->tgl_next,
            'jam_follow_up_berikutnya' => $request->jam_next,
            'status_follow_up' => $request->status_fu,
            'tgl_survey' => $request->tgl_survey,
            'catatan' => $request->catatan,
        ]);

        if ($lead->status_lead == 'Warm Lead') {
            $lead->increment('follow_up_count');

            if ($lead->follow_up_count >= 5) {
                $lead->update([
                    'status_lead' => 'Cold Lead',
                    'follow_up_count' => 0
                ]);

                if ($lead->id_pic) {
                    PicMarketing::where('id_pic', $lead->id_pic)->increment('down_convert');
                }

                return redirect()->route('followup.index')->with('warning', 'Lead kembali ke Cold Lead.');
            }
        }

        return redirect()->route('followup.index')->with('success', 'Follow Up berhasil direkam.');
    }

    public function edit($id)
    {
        $followup = FollowUp::findOrFail($id);
        return view('followup.edit', compact('followup'));
    }

    public function update(Request $request, $id)
    {
        $followup = FollowUp::findOrFail($id);

        $request->validate([
            'tgl_follow_up_berikutnya' => 'required|date',
            'jam_follow_up_berikutnya' => 'nullable',
        ]);

        $followup->update([
            'tgl_follow_up_berikutnya' => $request->tgl_follow_up_berikutnya,
            'jam_follow_up_berikutnya' => $request->jam_follow_up_berikutnya,
        ]);

        return redirect()->route('followup.index')->with('success', 'Jadwal berhasil diatur ulang.');
    }
}
