<?php

namespace App\Http\Controllers;

use App\Models\Keuangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KeuanganController extends Controller
{
    public function index(Request $request)
    {
        $projectId = session('active_project_id');

        if (!$projectId) {
            return redirect()->route('projects.index')->with('error', 'Silakan pilih proyek aktif terlebih dahulu.');
        }

        $tahun = $request->input('tahun', date('Y'));

        $pendingApprovalsCount = \App\Models\TransaksiLead::where('project_id', $projectId)
            ->where('status_keuangan', 'pending')
            ->count();

        $coa = \App\Models\Coa::where('project_id', $projectId)
            ->where('tahun', $tahun)
            ->orderBy('no_akun', 'asc')
            ->get();

        $query = \App\Models\Keuangan::with('coa')
            ->where('project_id', $projectId)
            ->whereYear('tanggal', $tahun);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('keterangan', 'like', "%{$search}%")
                  ->orWhere('no_akun', 'like', "%{$search}%")
                  ->orWhereHas('coa', function($qCoa) use ($search) {
                      $qCoa->where('nama_akun', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('input_filter')) {
            $query->where('input', $request->input_filter);
        }

        if ($request->filled('coa_filter')) {
            $query->where('no_akun', $request->coa_filter);
        }

        $keuangan = $query->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->paginate(50);

        return view('keuangan.index', compact('keuangan', 'pendingApprovalsCount', 'coa', 'tahun'));
    }

    public function create()
    {
        $projectId = session('active_project_id');

        $coa = \App\Models\Coa::where('project_id', $projectId)
                    ->orderBy('no_akun', 'asc')
                    ->get();

        return view('keuangan.create', compact('coa'));
    }

    public function store(Request $request)
    {
        $projectId = session('active_project_id');

        $request->merge([
            'mutasi_masuk' => $request->mutasi_masuk ? str_replace('.', '', $request->mutasi_masuk) : 0,
            'mutasi_keluar' => $request->mutasi_keluar ? str_replace('.', '', $request->mutasi_keluar) : 0,
        ]);

        $validated = $request->validate([
            'tanggal'          => 'required|date',
            'input'            => 'required|in:Kas Besar,Kas Kecil,Bank',
            'no_akun'          => 'required|exists:coa,no_akun',
            'jenis_penggunaan' => 'nullable|string|max:255',
            'mutasi_masuk'     => 'required|numeric',
            'mutasi_keluar'    => 'required|numeric',
            'keterangan'       => 'required|string',
            'bukti'            => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $buktiPath = null;
        if ($request->hasFile('bukti')) {
            $buktiPath = $request->file('bukti')->store('bukti_transaksi', 'public');
        }

        $transaksi = Keuangan::create([
            'project_id'       => $projectId,
            'tanggal'          => $validated['tanggal'],
            'input'            => $validated['input'],
            'no_akun'          => $validated['no_akun'],
            'jenis_penggunaan' => $validated['jenis_penggunaan'],
            'keterangan'       => $validated['keterangan'],
            'mutasi_masuk'     => $validated['mutasi_masuk'],
            'mutasi_keluar'    => $validated['mutasi_keluar'],
            'bukti'            => $buktiPath,
        ]);

        $this->adjustBalances($transaksi, 'apply');

        if ($request->action === 'save_and_new') {
            return redirect()->route('keuangan.create')
                ->withInput($request->only(['input', 'no_akun']))
                ->with('success', 'Transaksi berhasil disimpan & Saldo telah disesuaikan.');
        }

        return redirect()->route('keuangan.index')
            ->with('success', 'Transaksi keuangan berhasil dicatat & Saldo diperbarui.');
    }

    public function createJurnal()
    {
        $projectId = session('active_project_id');

        $coa = \App\Models\Coa::where('project_id', $projectId)
                    ->orderBy('no_akun', 'asc')
                    ->get();

        return view('keuangan.create_jurnal', compact('coa'));
    }

    public function storeJurnal(Request $request)
    {
        $projectId = session('active_project_id');

        $request->validate([
            'tanggal'                  => 'required|date',
            'bukti'                    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'no_akun_array'            => 'required|array|min:2',
            'no_akun_array.*'          => 'required|exists:coa,no_akun',
            'mutasi_debit_array'       => 'required|array',
            'mutasi_kredit_array'      => 'required|array',
            'keterangan_array'         => 'required|array',
        ]);

        $debitArray = array_map(function($val) {
            return (float) str_replace('.', '', $val);
        }, $request->mutasi_debit_array);

        $kreditArray = array_map(function($val) {
            return (float) str_replace('.', '', $val);
        }, $request->mutasi_kredit_array);

        $totalDebit = array_sum($debitArray);
        $totalKredit = array_sum($kreditArray);

        if (round($totalDebit, 2) !== round($totalKredit, 2)) {
            return back()->withInput()->with('error', 'GAGAL: Total Debit dan Kredit tidak Balance!');
        }
        if ($totalDebit == 0 && $totalKredit == 0) {
            return back()->withInput()->with('error', 'GAGAL: Nominal Jurnal tidak boleh 0!');
        }

        $buktiPath = null;
        if ($request->hasFile('bukti')) {
            $buktiPath = $request->file('bukti')->store('bukti_transaksi', 'public');
        }

        DB::transaction(function () use ($request, $projectId, $debitArray, $kreditArray, $buktiPath) {
            foreach ($request->no_akun_array as $index => $akun) {
                if ($debitArray[$index] == 0 && $kreditArray[$index] == 0) {
                    continue;
                }

                $transaksi = Keuangan::create([
                    'project_id'       => $projectId,
                    'tanggal'          => $request->tanggal,
                    'input'            => 'Jurnal',
                    'no_akun'          => $akun,
                    'jenis_penggunaan' => $request->jenis_penggunaan_array[$index] ?? null,
                    'keterangan'       => $request->keterangan_array[$index] ?? '-',
                    'mutasi_masuk'     => $debitArray[$index],
                    'mutasi_keluar'    => $kreditArray[$index],
                    'bukti'            => $buktiPath,
                ]);

                $this->adjustBalances($transaksi, 'apply');
            }
        });

        return redirect()->route('keuangan.index')->with('success', 'Jurnal Umum berhasil dicatat dan sudah dipastikan Balance!');
    }

    public function editJurnal($id)
    {
        $ref = Keuangan::findOrFail($id);
        $projectId = session('active_project_id');

        if ($ref->input !== 'Jurnal') {
            return redirect()->route('keuangan.index')->with('error', 'Transaksi ini bukan Jurnal Umum.');
        }

        $jurnalRows = Keuangan::where('project_id', $projectId)
            ->where('tanggal', $ref->tanggal)
            ->where('input', 'Jurnal')
            ->where('keterangan', $ref->keterangan)
            ->get();

        $coa = \App\Models\Coa::where('project_id', $projectId)
            ->orderBy('no_akun', 'asc')
            ->get();

        return view('keuangan.edit_jurnal', compact('ref', 'jurnalRows', 'coa'));
    }

    public function updateJurnal(Request $request, $id)
    {
        $ref = Keuangan::findOrFail($id);
        $projectId = session('active_project_id');

        $request->validate([
            'tanggal'                  => 'required|date',
            'bukti'                    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'no_akun_array'            => 'required|array|min:2',
            'no_akun_array.*'          => 'required|exists:coa,no_akun',
            'mutasi_debit_array'       => 'required|array',
            'mutasi_kredit_array'      => 'required|array',
            'keterangan_array'         => 'required|array',
        ]);

        $debitArray = array_map(function($val) { return (float) str_replace('.', '', $val); }, $request->mutasi_debit_array);
        $kreditArray = array_map(function($val) { return (float) str_replace('.', '', $val); }, $request->mutasi_kredit_array);

        $totalDebit = array_sum($debitArray);
        $totalKredit = array_sum($kreditArray);

        if (round($totalDebit, 2) !== round($totalKredit, 2)) {
            return back()->withInput()->with('error', 'GAGAL: Total Debit dan Kredit tidak Balance!');
        }

        $buktiPath = $ref->bukti;
        if ($request->hasFile('bukti')) {
            $buktiPath = $request->file('bukti')->store('bukti_transaksi', 'public');
        }

        DB::transaction(function () use ($request, $projectId, $debitArray, $kreditArray, $buktiPath, $ref) {

            $oldJurnalRows = Keuangan::where('project_id', $projectId)
                ->where('tanggal', $ref->tanggal)
                ->where('input', 'Jurnal')
                ->where('keterangan', $request->keterangan_lama)
                ->get();

            foreach ($oldJurnalRows as $oldRow) {
                $this->adjustBalances($oldRow, 'reverse');
                $oldRow->delete();
            }

            foreach ($request->no_akun_array as $index => $akun) {
                if ($debitArray[$index] == 0 && $kreditArray[$index] == 0) continue;

                $transaksi = Keuangan::create([
                    'project_id'       => $projectId,
                    'tanggal'          => $request->tanggal,
                    'input'            => 'Jurnal',
                    'no_akun'          => $akun,
                    'jenis_penggunaan' => $request->jenis_penggunaan_array[$index] ?? null,
                    'keterangan'       => $request->keterangan_array[$index] ?? '-',
                    'mutasi_masuk'     => $debitArray[$index],
                    'mutasi_keluar'    => $kreditArray[$index],
                    'bukti'            => $buktiPath,
                ]);

                $this->adjustBalances($transaksi, 'apply');
            }
        });

        return redirect()->route('keuangan.index')->with('success', 'Jurnal Umum berhasil diperbarui dan saldo telah diseimbangkan!');
    }

    public function show(string $id)
    {
    }

    public function edit($id)
    {
        $item = Keuangan::findOrFail($id);
        $projectId = session('active_project_id');

        $coa = \App\Models\Coa::where('project_id', $projectId)
                    ->orderBy('no_akun', 'asc')
                    ->get();

        return view('keuangan.edit', compact('item', 'coa'));
    }

    public function update(Request $request, $id)
    {
        $transaksi = Keuangan::findOrFail($id);
        $projectId = session('active_project_id');

        $cleanMasuk = str_replace('.', '', $request->mutasi_masuk);
        $cleanKeluar = str_replace('.', '', $request->mutasi_keluar);

        $request->merge([
            'mutasi_masuk'  => $cleanMasuk ?: 0,
            'mutasi_keluar' => $cleanKeluar ?: 0,
        ]);

        $validated = $request->validate([
            'tanggal'          => 'required|date',
            'input'            => 'required|in:Kas Besar,Kas Kecil,Bank,Jurnal',
            'no_akun'          => 'required|exists:coa,no_akun',
            'mutasi_masuk'     => 'nullable|numeric',
            'mutasi_keluar'    => 'nullable|numeric',
            'keterangan'       => 'required|string',
            'jenis_penggunaan' => 'nullable|string',
            'bukti'            => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $this->adjustBalances($transaksi, 'reverse');

        $mutasiMasuk = $request->mutasi_masuk ?? 0;
        $mutasiKeluar = $request->mutasi_keluar ?? 0;

        if ($request->hasFile('bukti')) {
            $validated['bukti'] = $request->file('bukti')->store('bukti_transaksi', 'public');
        }

        $transaksi->update(array_merge($validated, [
            'mutasi_masuk' => $mutasiMasuk,
            'mutasi_keluar' => $mutasiKeluar,
        ]));

        $this->adjustBalances($transaksi, 'apply');

        return redirect()->route('keuangan.index')->with('success', 'Transaksi diperbarui dan saldo telah disesuaikan!');
    }

    public function destroy($id)
    {
        $transaksi = Keuangan::findOrFail($id);

        if ($transaksi->input === 'Jurnal') {
            $paketJurnal = Keuangan::where('project_id', $transaksi->project_id)
                ->where('tanggal', $transaksi->tanggal)
                ->where('input', 'Jurnal')
                ->where('keterangan', $transaksi->keterangan)
                ->get();

            foreach ($paketJurnal as $item) {
                $this->adjustBalances($item, 'reverse');
                $item->delete();
            }

            return redirect()->route('keuangan.index')->with('success', 'Satu paket Jurnal Umum berhasil dihapus dan saldo diseimbangkan.');
        }

        $this->adjustBalances($transaksi, 'reverse');
        $transaksi->delete();

        return redirect()->route('keuangan.index')->with('success', 'Transaksi berhasil dihapus.');
    }

    private function adjustBalances($transaksi, $mode = 'apply')
    {
        $projectId = session('active_project_id');
        $masuk = $transaksi->mutasi_masuk;
        $keluar = $transaksi->mutasi_keluar;

        if ($mode === 'reverse') {
            $masuk = -$transaksi->mutasi_masuk;
            $keluar = -$transaksi->mutasi_keluar;
        }

        if ($transaksi->input !== 'Jurnal') {
            $dompet = \App\Models\Coa::where('project_id', $projectId)
                ->where('nama_akun', 'LIKE', '%' . $transaksi->input . '%')
                ->first();

            if ($dompet) {
                $dompet->saldo_akhir += $masuk;
                $dompet->saldo_akhir -= $keluar;
                $dompet->save();
            }
        }

        $lawan = \App\Models\Coa::where('project_id', $projectId)
            ->where('no_akun', $transaksi->no_akun)
            ->first();

        if ($lawan) {
            if ($masuk != 0) {
                $lawan->saldo_akhir += ($lawan->posisi_normal == 'Kredit') ? $masuk : -$masuk;
            }
            if ($keluar != 0) {
                $lawan->saldo_akhir += ($lawan->posisi_normal == 'Debit') ? $keluar : -$keluar;
            }
            $lawan->save();
        }
    }

    public function pendingApprovals()
    {
        $projectId = session('active_project_id');

        $pendingTransactions = \App\Models\TransaksiLead::with('lead')
            ->where('project_id', $projectId)
            ->where('status_keuangan', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('keuangan.pending', compact('pendingTransactions'));
    }

    public function approveForm($id)
    {
        $transaksiLead = \App\Models\TransaksiLead::with('lead')->findOrFail($id);
        $projectId = session('active_project_id');

        $coa = \App\Models\Coa::where('project_id', $projectId)
                ->orderBy('no_akun', 'asc')
                ->get();

        return view('keuangan.approve', compact('transaksiLead', 'coa'));
    }

    public function processApprove(Request $request, $id)
    {
        $transaksiLead = \App\Models\TransaksiLead::findOrFail($id);
        $projectId = session('active_project_id');

        $validated = $request->validate([
            'tanggal'          => 'required|date',
            'input'            => 'required|in:Kas Besar,Kas Kecil,Bank',
            'no_akun'          => 'required|exists:coa,no_akun',
            'jenis_penggunaan' => 'nullable|string|max:255',
            'mutasi_masuk'     => 'required|numeric',
            'keterangan'       => 'required|string',
            'bukti'            => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $buktiPath = null;
        if ($request->hasFile('bukti')) {
            $buktiPath = $request->file('bukti')->store('bukti_transaksi', 'public');
        }

        $transaksiKeuangan = \App\Models\Keuangan::create([
            'project_id'       => $projectId,
            'tanggal'          => $validated['tanggal'],
            'input'            => $validated['input'],
            'no_akun'          => $validated['no_akun'],
            'jenis_penggunaan' => $validated['jenis_penggunaan'],
            'keterangan'       => $validated['keterangan'],
            'mutasi_masuk'     => $validated['mutasi_masuk'],
            'mutasi_keluar'    => 0,
            'bukti'            => $buktiPath,
        ]);

        $this->adjustBalances($transaksiKeuangan, 'apply');

        $transaksiLead->update([
            'status_keuangan' => 'approved'
        ]);

        return redirect()->route('keuangan.pending')
            ->with('success', 'Transaksi Lead berhasil disetujui dan disimpan ke Keuangan!');
    }
}
