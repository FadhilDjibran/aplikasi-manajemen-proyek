<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\FollowUp;
use App\Models\TipeRumah;
use App\Models\PicMarketing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $projectId = session('active_project_id');

        $query = Lead::with(['pic', 'tipeRumah'])->where('project_id', $projectId);

        $sumberLeads = Lead::where('project_id', $projectId)
            ->whereNotNull('sumber_lead')->where('sumber_lead', '!=', '')
            ->distinct()->pluck('sumber_lead');

        $kotaDomisilis = Lead::where('project_id', $projectId)
            ->whereNotNull('kota_domisili')->where('kota_domisili', '!=', '')
            ->distinct()->pluck('kota_domisili');

        $tipeRumahs = \App\Models\TipeRumah::where('project_id', $projectId)->get();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_lead', 'like', '%' . $search . '%')
                  ->orWhere('no_whatsapp', 'like', '%' . $search . '%')
                  ->orWhere('catatan', 'like', '%' . $search . '%')
                  ->orWhere('kota_domisili', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('status_filter')) {
            $query->where('status_lead', $request->status_filter);
        }

        if ($request->filled('tipe_filter')) {
            $query->where('id_tipe_rumah_minat', $request->tipe_filter);
        }

        if ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);

            if (count($dates) == 2) {
                $query->whereDate('tgl_masuk', '>=', $dates[0])
                      ->whereDate('tgl_masuk', '<=', $dates[1]);
            } elseif (count($dates) == 1) {
                $query->whereDate('tgl_masuk', $dates[0]);
            }
        }

        if ($request->filled('sumber_filter')) {
            $query->where('sumber_lead', $request->sumber_filter);
        }

        if ($request->filled('kota_filter')) {
            $query->where('kota_domisili', $request->kota_filter);
        }

        $query->orderBy('id_lead', 'desc');

        $leads = $query->paginate(15);

        return view('leads.index', compact('leads', 'sumberLeads', 'kotaDomisilis', 'tipeRumahs'));
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

        $sumberLead = $request->sumber_lead;
        if ($sumberLead === 'Lainnya') {
            $sumberLead = $request->sumber_lead_custom;
        }

        Lead::create([
            'id_lead'               => $newId,
            'project_id'            => Session::get('active_project_id'),
            'nama_lead'             => $request->nama_lead,
            'no_whatsapp'           => $request->no_whatsapp,
            'sumber_lead'           => $sumberLead,
            'id_tipe_rumah_minat'   => $request->id_tipe,
            'kota_domisili'         => $request->kota_domisili,
            'rencana_pembayaran'    => $request->rencana_pembayaran,
            'catatan'               => $request->catatan,
            'status_lead'           => 'Cold Lead',
            'tgl_masuk'             => $request->tgl_masuk ? $request->tgl_masuk : now(),
            'alamat'                => $request->alamat,
            'status_pekerjaan'      => $request->status_pekerjaan,
            'perkiraan_budget'      => $cleanBudget,
            'id_pic'                => $request->id_pic,
        ]);

        $pesanSukses = "Lead atas nama {$request->nama_lead} berhasil dibuat dengan status Cold Lead.";

        if ($request->action === 'save_and_new') {
            return redirect()->route('leads.create')->with('success', $pesanSukses);
        }

        return redirect()->route('leads.index')->with('success', $pesanSukses);
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

        if ($request->has('id_tipe')) {
            $data['id_tipe_rumah_minat'] = $request->id_tipe;
            unset($data['id_tipe']);
        }

        if ($request->has('sumber_lead')) {
            $sumberLead = $request->sumber_lead;
            if ($sumberLead === 'Lainnya') {
                $sumberLead = $request->sumber_lead_custom;
            }
            $data['sumber_lead'] = $sumberLead;
        }

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
        } else {
            $data['alasan_gagal'] = null;
            $data['catatan_gagal'] = null;
            $data['tgl_gagal'] = null;
        }

        if ($newStatus == 'Warm Lead' && $oldStatus != 'Warm Lead') {
            $data['follow_up_count'] = 0;
        }

        $lead->update($data);

        $pesan = "Data lead berhasil diperbarui.";

        if ($newStatus == 'Warm Lead' && $oldStatus != 'Warm Lead') {
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

                $pesan = "Status naik ke Warm Lead & Jadwal Follow Up baru berhasil dibuat.";
            } else {
                $pesan = "Status diperbarui menjadi Warm Lead (User bukan PIC Marketing).";
            }
        } elseif ($newStatus == 'Gagal Closing') {
            $pesan = "Lead ditandai sebagai Gagal Closing.";
        }

        if ($request->has('is_quick_update')) {
            return redirect()->back()->with('success', $pesan);
        }

        $queryParams = $request->except(['_token', '_method', 'status_lead', 'sumber_lead_custom', 'id_tipe']);
        return redirect()->route('leads.index', $queryParams)->with('success', $pesan);
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

    public function triggerUpdateStatus(Request $request)
    {
        $projectId = session('active_project_id');

        if (!$projectId) {
            return redirect()->back()->with('error', 'Gagal: Tidak ada proyek yang aktif saat ini.');
        }

        Artisan::call('leads:update-status', [
            'project_id' => $projectId
        ]);

        return redirect()->back()->with('success', 'Status lead pada proyek ini berhasil diperbarui!');
    }

    public function export(Request $request)
{
    $projectId = session('active_project_id');

    $query = Lead::with(['pic', 'tipeRumah'])->where('project_id', $projectId);


    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('nama_lead', 'like', "%{$search}%")
              ->orWhere('no_whatsapp', 'like', "%{$search}%")
              ->orWhere('catatan', 'like', "%{$search}%")
              ->orWhere('kota_domisili', 'like', "%{$search}%");
        });
    }

    if ($request->filled('status_filter')) {
        $query->where('status_lead', $request->status_filter);
    }

    if ($request->filled('tipe_filter')) {
        $query->where('id_tipe_rumah_minat', $request->tipe_filter);
    }

    if ($request->filled('sumber_filter')) {
        $query->where('sumber_lead', $request->sumber_filter);
    }

    if ($request->filled('kota_filter')) {
        $query->where('kota_domisili', $request->kota_filter);
    }

    if ($request->filled('date_range')) {
        $dates = explode(' to ', $request->date_range);
        if (count($dates) == 2) {
            $query->whereDate('tgl_masuk', '>=', $dates[0])
                  ->whereDate('tgl_masuk', '<=', $dates[1]);
        } elseif (count($dates) == 1) {
            $query->whereDate('tgl_masuk', $dates[0]);
        }
    }

    $leads = $query->orderBy('id_lead', 'desc')->get();

    $filename = "Laporan_Leads_" . date('Ymd_His') . ".csv";
    $headers = [
        "Content-type"        => "text/csv",
        "Content-Disposition" => "attachment; filename=$filename",
        "Pragma"              => "no-cache",
        "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
        "Expires"             => "0"
    ];

    $callback = function() use($leads) {
        $file = fopen('php://output', 'w');

        fputcsv($file, [
            'ID Lead',
            'Nama Lead',
            'No WhatsApp',
            'Kota',
            'Tipe Rumah',
            'Sumber Lead',
            'Status Lead',
            'Budget',
            'PIC Marketing',
            'Tanggal Masuk'
        ]);

        foreach ($leads as $row) {
            fputcsv($file, [
                $row->id_lead,
                $row->nama_lead,
                $row->no_whatsapp,
                $row->kota_domisili,
                $row->tipeRumah->nama_tipe ?? '-',
                $row->sumber_lead,
                $row->status_lead,
                $row->perkiraan_budget,
                $row->pic->nama_pic ?? 'Belum Ada',
                \Carbon\Carbon::parse($row->tgl_masuk)->format('Y-m-d')
            ]);
        }
        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}
}
