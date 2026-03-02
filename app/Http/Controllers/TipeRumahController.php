<?php

namespace App\Http\Controllers;

use App\Models\TipeRumah;
use Illuminate\Http\Request;

class TipeRumahController extends Controller
{
    public function index()
    {
        $projectId = session('active_project_id');

        // Ambil data berdasarkan project aktif
        $tipeRumah = TipeRumah::where('project_id', $projectId)
            ->orderBy('nama_tipe', 'asc')
            ->get();

        // PENTING: Memanggil file 'resources/views/tipe_rumah.blade.php'
        return view('tipe_rumah', compact('tipeRumah'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_tipe' => 'required|string|max:50',
        ]);

        TipeRumah::create([
            'project_id' => session('active_project_id'),
            'nama_tipe' => $request->nama_tipe,
        ]);

        // Redirect ke route 'tipe_rumah.index'
        return redirect()->route('tipe_rumah.index')
            ->with('success', 'Tipe rumah berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $tipe = TipeRumah::findOrFail($id);

        $request->validate([
            'nama_tipe' => 'required|string|max:50',
        ]);

        $tipe->update([
            'nama_tipe' => $request->nama_tipe
        ]);

        return redirect()->route('tipe_rumah.index')
            ->with('success', 'Nama tipe diperbarui');
    }

    public function destroy($id)
    {
        $tipe = TipeRumah::findOrFail($id);

         if($tipe->leads()->exists()) {
            return back()->with('error', 'Gagal hapus! Tipe ini sedang digunakan oleh data Leads.');
         }

        $tipe->delete();

        return redirect()->route('tipe_rumah.index')
            ->with('success', 'Tipe rumah dihapus');
    }
}
