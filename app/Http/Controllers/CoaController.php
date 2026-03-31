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
            return redirect()->route('projects.index')->with('error', 'Silakan pilih proyek aktif terlebih dahulu.');
        }

        $tahun = $request->input('tahun', date('Y'));

        $query = Coa::where('project_id', $projectId)
                    ->where('tahun', $tahun);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_akun', 'like', "%{$search}%")
                  ->orWhere('nama_akun', 'like', "%{$search}%")
                  ->orWhere('kategori_akun', 'like', "%{$search}%");
            });
        }

        if ($request->filled('posisi_filter')) {
            $query->where('posisi_normal', $request->posisi_filter);
        }

        if ($request->filled('laporan_filter')) {
            $query->where('jenis_laporan', $request->laporan_filter);
        }

        $coas = $query->orderBy('no_akun', 'asc')->get();

        return view('coa.index', compact('coas', 'tahun'));
    }

    public function store(Request $request)
    {
        $projectId = session('active_project_id');
        $tahun = $request->input('tahun', date('Y'));

        if (!$projectId) {
            return redirect()->back()->withInput()->with('error', 'Pilih proyek aktif terlebih dahulu.');
        }

        $request->merge([
            'saldo_awal_debit' => str_replace('.', '', $request->saldo_awal_debit ?? 0),
            'saldo_awal_kredit' => str_replace('.', '', $request->saldo_awal_kredit ?? 0),
        ]);

        $validated = $request->validate([
            'no_akun' => [
                'required',
                'max:20',
                Rule::unique('coa', 'no_akun')->where(function ($query) use ($projectId, $tahun) {
                    return $query->where('project_id', $projectId)->where('tahun', $tahun);
                })
            ],
            'tahun'         => 'required|digits:4',
            'kategori_akun' => 'required|string|max:255',
            'nama_akun'     => 'required|string|max:255',
            'posisi_normal' => 'required|in:Debit,Kredit',
            'jenis_laporan' => 'required|in:Neraca,Laba Rugi',
            'saldo_awal_debit'  => 'nullable|numeric',
            'saldo_awal_kredit' => 'nullable|numeric',
        ]);

        $validated['project_id'] = $projectId;

        $debit = $validated['saldo_awal_debit'] ?? 0;
        $kredit = $validated['saldo_awal_kredit'] ?? 0;

        $validated['saldo_akhir'] = $debit - $kredit;

        Coa::create($validated);

        if ($request->action == 'save_and_new') {
            return redirect()->route('coa.create')->with('success', 'Data CoA berhasil ditambahkan!');
        }

        return redirect()->route('coa.index', ['tahun' => $tahun])->with('success', 'Data CoA berhasil ditambahkan!');
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
        $tahun = $coa->tahun;

        $request->merge([
            'saldo_awal_debit' => str_replace('.', '', $request->saldo_awal_debit ?: 0),
            'saldo_awal_kredit' => str_replace('.', '', $request->saldo_awal_kredit ?: 0),
        ]);

        $validated = $request->validate([
            'no_akun' => [
                'required',
                'max:20',
                Rule::unique('coa', 'no_akun')
                    ->where('project_id', $projectId)
                    ->where('tahun', $tahun)
                    ->ignore($id)
            ],
            'kategori_akun'     => 'required|string|max:255',
            'nama_akun'         => 'required|string|max:255',
            'posisi_normal'     => 'required|in:Debit,Kredit',
            'jenis_laporan'     => 'required|in:Neraca,Laba Rugi',
            'saldo_awal_debit'  => 'nullable|numeric',
            'saldo_awal_kredit' => 'nullable|numeric',
        ]);

        $debit = $validated['saldo_awal_debit'] ?? 0;
        $kredit = $validated['saldo_awal_kredit'] ?? 0;

        $validated['saldo_akhir'] = $debit - $kredit;

        $coa->update($validated);

        return redirect()->route('coa.index', ['tahun' => $tahun])->with('success', 'Data Akun dan Saldo berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $coa = Coa::findOrFail($id);
        $tahun = $coa->tahun;
        $coa->delete();

        return redirect()->route('coa.index', ['tahun' => $tahun])->with('success', 'Data CoA berhasil dihapus!');
    }

    public function rollover(Request $request)
    {
        $projectId = session('active_project_id');

        if (!$projectId) {
            return redirect()->route('projects.index')->with('error', 'Pilih proyek aktif terlebih dahulu.');
        }

        $tahunAsal = $request->input('tahun_asal');
        $tahunTujuan = $request->input('tahun_tujuan');

        if ($tahunTujuan <= $tahunAsal) {
            return redirect()->back()->with('error', 'Tahun tujuan harus lebih besar dari tahun asal.');
        }

        $coaAsal = Coa::where('project_id', $projectId)
                      ->where('tahun', $tahunAsal)
                      ->get();

        if ($coaAsal->isEmpty()) {
            return redirect()->back()->with('error', "Tidak ada data CoA pada tahun {$tahunAsal}.");
        }

        foreach ($coaAsal as $coa) {
            Coa::firstOrCreate(
                [
                    'project_id' => $projectId,
                    'tahun'      => $tahunTujuan,
                    'no_akun'    => $coa->no_akun,
                ],
                [
                    'kategori_akun'     => $coa->kategori_akun,
                    'nama_akun'         => $coa->nama_akun,
                    'posisi_normal'     => $coa->posisi_normal,
                    'jenis_laporan'     => $coa->jenis_laporan,
                    'saldo_awal_debit'  => 0,
                    'saldo_awal_kredit' => 0,
                    'saldo_akhir'       => 0,
                ]
            );
        }

        return redirect()->route('coa.index', ['tahun' => $tahunTujuan])
                         ->with('success', "Struktur CoA tahun {$tahunAsal} berhasil disalin ke tahun {$tahunTujuan}.");
    }
}
