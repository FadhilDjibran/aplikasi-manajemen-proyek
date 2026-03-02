<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\TransaksiLead;
use Illuminate\Http\Request;

class HotProspekController extends Controller
{
    public function index(Request $request)
    {
        $projectId = session('active_project_id');
        $search = $request->search;

        $query = Lead::where('project_id', $projectId)
            ->where('status_lead', 'Hot Prospek')
            ->with(['pic', 'transaksi']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_lead', 'like', "%{$search}%")
                  ->orWhere('no_whatsapp', 'like', "%{$search}%");
            });
        }

        $hotLeads = $query->orderBy('updated_at', 'desc')->get();

        $pendingCount = $hotLeads->filter(function($lead) {
            return $lead->transaksi->isEmpty();
        })->count();

        $transactionHistory = TransaksiLead::with('lead')
            ->where('project_id', $projectId)
            ->orderBy('tgl_pembayaran', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('hot_prospek.index', compact('hotLeads', 'pendingCount', 'transactionHistory'));
    }

    public function storeTransaksi(Request $request)
    {
        $cleanNominal = str_replace('.', '', $request->nominal);
        $request->merge(['nominal' => $cleanNominal]);

        $request->validate([
            'id_lead' => 'required|exists:leads,id_lead',
            'jenis_pembayaran' => 'required',
            'nominal' => 'required|numeric|min:0',
            'tgl_pembayaran' => 'required|date',
            'keterangan' => 'nullable|string'
        ]);

        TransaksiLead::create([
            'project_id' => session('active_project_id'),
            'id_lead' => $request->id_lead,
            'jenis_pembayaran' => $request->jenis_pembayaran,
            'nominal' => $request->nominal,
            'tgl_pembayaran' => $request->tgl_pembayaran,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->back()->with('success', 'Data transaksi berhasil disimpan.');
    }
}
