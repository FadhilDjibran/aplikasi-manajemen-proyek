<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\FollowUp;
use App\Models\TipeRumah;
use App\Models\PicMarketing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::with('pic')
            ->where('project_id', session('active_project_id'));

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function($q) use ($search) {
                $q->where('nama_lead', 'like', '%' . $search . '%')
                ->orWhere('no_whatsapp', 'like', '%' . $search . '%')
                ->orWhere('catatan', 'like', '%' . $search . '%')
                ->orWhere('sumber_lead', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('status_filter')) {
            $query->where('status_lead', $request->status_filter);
        }

        $leads = $query->orderBy('created_at', 'desc')
                    ->paginate(10);

        return view('leads.index', compact('leads'));
    }

    public function create()
    {
        $projectId = session('active_project_id');
        $tipeRumah = TipeRumah::where('project_id', $projectId)->get();

        $pics = PicMarketing::whereHas('user', function($query) use ($projectId) {
            $query->where('project_id', $projectId);
        })->get();

        if ($pics->isEmpty()) {
            session()->flash('warning', 'Belum ada PIC Marketing di proyek ini. Harap input data PIC terlebih dahulu.');
        }

        return view('leads.create', compact('tipeRumah', 'pics'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lead'         => 'required|string|max:100',
            'no_whatsapp'       => 'required|string|max:20',
            'sumber_lead'       => 'required',
            'perkiraan_budget'  => 'nullable|string',
        ]);

        $lastLead = Lead::select('id_lead')
            ->orderByRaw('CAST(SUBSTRING(id_lead, 2) AS UNSIGNED) DESC')
            ->first();
        $number = $lastLead ? ((int)substr($lastLead->id_lead, 1)) + 1 : 1;
        $newId = 'L' . str_pad($number, 3, '0', STR_PAD_LEFT);

        $cleanBudget = $request->perkiraan_budget
            ? str_replace('.', '', $request->perkiraan_budget)
            : null;

        Lead::create([
            'id_lead'               => $newId,
            'project_id'            => Session::get('active_project_id'),
            'nama_lead'             => $request->nama_lead,
            'no_whatsapp'           => $request->no_whatsapp,
            'sumber_lead'           => $request->sumber_lead,
            'id_tipe_rumah_minat'   => $request->id_tipe,
            'kota_domisili'         => $request->kota_domisili,
            'rencana_pembayaran'    => $request->rencana_pembayaran,
            'catatan'               => $request->catatan,
            'status_lead'           => 'Cold Lead',
            'tgl_masuk'             => now(),
            'alamat'                => $request->alamat,
            'status_pekerjaan'      => $request->status_pekerjaan,
            'perkiraan_budget'      => $cleanBudget,
            'id_pic'                => $request->id_pic,
        ]);

        return redirect()->route('leads.index')
            ->with('success', "Lead {$request->nama_lead} berhasil dibuat dengan status Cold Lead.");
    }

    public function edit($id)
    {
        $lead = Lead::findOrFail($id);
        $projectId = session('active_project_id');
        $tipeRumah = TipeRumah::where('project_id', $projectId)->get();

        $pics = PicMarketing::whereHas('user', function($query) use ($projectId) {
            $query->where('project_id', $projectId);
        })->get();

        return view('leads.edit', compact('lead', 'tipeRumah', 'pics'));
    }

    public function update(Request $request, $id)
    {
        $lead = Lead::findOrFail($id);
        $oldStatus = $lead->status_lead;
        $newStatus = $request->status_lead;

        $request->validate([
            'status_lead' => 'required',
            'alasan_gagal' => 'nullable|string',
            'catatan_gagal' => 'nullable|string',
        ]);

        $data = $request->all();

        if ($request->has('perkiraan_budget')) {
            $data['perkiraan_budget'] = $request->perkiraan_budget
                ? str_replace('.', '', $request->perkiraan_budget)
                : null;
        }

        if ($newStatus == 'Gagal Closing') {
            if (empty($request->alasan_gagal)) {
                return redirect()->back()->with('error', 'Harap pilih alasan gagal closing!');
            }

            $data['alasan_gagal'] = $request->alasan_gagal;
            $data['catatan_gagal'] = $request->catatan_gagal;
            $data['tgl_gagal'] = now();
        }
        else {
            $data['alasan_gagal'] = null;
            $data['catatan_gagal'] = null;
            $data['tgl_gagal'] = null;
        }

        if ($newStatus == 'Warm Lead' && $oldStatus != 'Warm Lead') {
            $data['follow_up_count'] = 0;
        }

        $lead->update($data);

        if ($lead->id_pic) {
            if (in_array($oldStatus, ['Warm Lead', 'Hot Prospek']) &&
                in_array($newStatus, ['Cold Lead', 'Gagal Closing'])) {

                PicMarketing::where('id_pic', $lead->id_pic)->increment('down_convert');
            }

            elseif ($oldStatus == 'Warm Lead' && $newStatus == 'Hot Prospek') {

                PicMarketing::where('id_pic', $lead->id_pic)->increment('up_convert');
            }
        }

        if ($newStatus == 'Warm Lead' && $oldStatus != 'Warm Lead') {
            $existingFollowUp = \App\Models\FollowUp::where('id_lead', $id)
                ->where('status_follow_up', 'Proses Follow Up')
                ->exists();

            if (!$existingFollowUp) {
                $userId = Auth::id();
                $activePic = \App\Models\PicMarketing::where('user_id', $userId)->first();

                if ($activePic) {
                    $currentPicId = $activePic->id_pic;

                    $lead->update(['id_pic' => $currentPicId]);

                    \App\Models\FollowUp::create([
                        'id_lead'                  => $lead->id_lead,
                        'project_id'               => $lead->project_id,
                        'id_pic'                   => $currentPicId,
                        'tgl_follow_up'            => now(),
                        'hasil_follow_up'          => 'Status naik menjadi Warm Lead.',
                        'tgl_follow_up_berikutnya' => now()->addDays(14),
                        'jam_follow_up_berikutnya' => '09:00:00',
                        'status_follow_up'         => 'Proses Follow Up'
                    ]);

                    $pesan = "Status naik ke Warm Lead & Jadwal dibuat.";
                } else {
                    $pesan = "Status diperbarui (User bukan PIC Marketing).";
                }
            } else {
                $pesan = "Status diperbarui.";
            }
        } elseif ($newStatus == 'Gagal Closing') {
            $pesan = "Lead ditandai sebagai Gagal Closing.";
        } else {
            $pesan = "Data lead berhasil diperbarui.";
        }

        return redirect()->route('leads.index')->with('success', $pesan);
    }

    public function destroy($id)
    {
        $lead = Lead::findOrFail($id);
        $lead->delete();
        return redirect()->route('leads.index')->with('success', 'Lead berhasil dihapus.');
    }

    public function show($id)
    {
        $lead = Lead::with(['tipeRumah'])->findOrFail($id);
        $activeFollowUp = FollowUp::where('id_lead', $id)->latest()->first();

        return view('followup.execute', compact('lead', 'activeFollowUp'));
    }

    public function detail($id)
    {
        $lead = Lead::with(['tipeRumah', 'picMarketing'])->findOrFail($id);
        return view('leads.show', compact('lead'));
    }
}
