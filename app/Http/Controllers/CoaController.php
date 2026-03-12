<?php

namespace App\Http\Controllers;

use App\Models\Coa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CoaController extends Controller
{
    public function index(Request $request)
    {
        $projectId = session('active_project_id');

        if (!$projectId) {
            return redirect()->route('home')->with('error', 'Silakan pilih proyek aktif terlebih dahulu.');
        }

        $query = Coa::where('project_id', $projectId);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_akun', 'like', "%{$search}%")
                  ->orWhere('nama_akun', 'like', "%{$search}%");
            });
        }

        if ($request->filled('posisi_filter')) {
            $query->where('posisi_normal', $request->posisi_filter);
        }

        if ($request->filled('laporan_filter')) {
            $query->where('jenis_laporan', $request->laporan_filter);
        }

        $coas = $query->orderBy('no_akun', 'asc')->get();

        return view('coa.index', compact('coas'));
    }

    public function store(Request $request)
    {
        $projectId = session('active_project_id');

        if (!$projectId) {
            return redirect()->back()->withInput()->with('error', 'Pilih proyek aktif terlebih dahulu.');
        }

        $validated = $request->validate([
            'no_akun' => [
                'required',
                'max:20',
                Rule::unique('coa', 'no_akun')->where(function ($query) use ($projectId) {
                    return $query->where('project_id', $projectId);
                })
            ],
            'kategori_akun' => 'required|string|max:255',
            'nama_akun'     => 'required|string|max:255',
            'posisi_normal' => 'required|in:Debit,Kredit',
            'jenis_laporan' => 'required|in:Neraca,Laba Rugi',
        ]);

        $validated['project_id'] = $projectId;

        Coa::create($validated);

        if ($request->action == 'save_and_new') {
            return redirect()->route('coa.create')->with('success', 'Data CoA berhasil ditambahkan! Silakan input data berikutnya.');
        }

        return redirect()->route('coa.index')->with('success', 'Data CoA berhasil ditambahkan!');
    }

    public function create()
    {
        return view('coa.create');
    }

    public function edit($id)
    {
        $coa = Coa::findOrFail($id);
        return view('coa.edit', compact('coa'));
    }

    public function update(Request $request, $id)
    {
        $coa = Coa::findOrFail($id);
        $projectId = session('active_project_id');

        $validated = $request->validate([
            'no_akun' => [
                'required',
                'max:20',
                \Illuminate\Validation\Rule::unique('coa', 'no_akun')
                    ->where('project_id', $projectId)
                    ->ignore($id)
            ],
            'kategori_akun'     => 'required|string|max:255',
            'nama_akun'         => 'required|string|max:255',
            'posisi_normal'     => 'required|in:Debit,Kredit',
            'jenis_laporan'     => 'required|in:Neraca,Laba Rugi',
            'saldo_awal_debit'  => 'nullable|numeric',
            'saldo_awal_kredit' => 'nullable|numeric',
        ]);

        $debit = $request->saldo_awal_debit ?? 0;
        $kredit = $request->saldo_awal_kredit ?? 0;

        if ($validated['posisi_normal'] === 'Debit') {
            $validated['saldo_akhir'] = $debit - $kredit;
        } else {
            $validated['saldo_akhir'] = $kredit - $debit;
        }

        $coa->update($validated);

        return redirect()->route('coa.index')->with('success', 'Data Akun dan Saldo berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $coa = Coa::findOrFail($id);
        $coa->delete();
        return redirect()->route('coa.index')->with('success', 'Data CoA berhasil dihapus!');
    }

}
