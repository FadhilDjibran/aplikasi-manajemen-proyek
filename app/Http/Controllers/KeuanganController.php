<?php

namespace App\Http\Controllers;

use App\Models\Keuangan;
use App\Models\TransaksiLead;
use App\Models\Coa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class KeuanganController extends Controller
{
    public function index(Request $request)
    {
        $projectId = session('active_project_id');

        if (!$projectId) {
            return redirect()->route('projects.index')->with('error', 'Silakan pilih proyek aktif terlebih dahulu.');
        }

        $tahun = $request->input('tahun', date('Y'));

        $pendingApprovalsCount = TransaksiLead::where('project_id', $projectId)
            ->where('status_keuangan', 'pending')
            ->count();

        $coa = Coa::where('project_id', $projectId)
            ->where('tahun', $tahun)
            ->orderBy('no_akun', 'asc')
            ->get();

        $query = Keuangan::with('coa')
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

        $coa = Coa::where('project_id', $projectId)
                    ->orderBy('no_akun', 'asc')
                    ->get();

        return view('keuangan.create', compact('coa'));
    }

    public function store(Request $request)
    {
        $projectId = session('active_project_id');

        $cleanMasuk = parse_money($request->mutasi_masuk);
        $cleanKeluar = parse_money($request->mutasi_keluar);

        $request->merge([
            'mutasi_masuk'  => $cleanMasuk,
            'mutasi_keluar' => $cleanKeluar,
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

        if (!in_array(Auth::user()?->role, ['Super_Admin', 'Admin_Keuangan'])) {
            if (in_array($request->input, ['Kas Besar', 'Bank'])) {
                return redirect()->back()->withInput()->with('error', 'Anda tidak memiliki izin untuk menginput Kas Besar atau Bank.');
            }
        }

        DB::beginTransaction();
        try {
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

            DB::commit();

            if ($request->action === 'save_and_new') {
                return redirect()->route('keuangan.create')
                    ->withInput($request->only(['input', 'no_akun', 'tanggal']))
                    ->with('success', 'Transaksi berhasil disimpan & Saldo telah disesuaikan.');
            }

            return redirect()->route('keuangan.index')
                ->with('success', 'Transaksi keuangan berhasil dicatat & Saldo diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
    }

    public function edit($id)
    {
        $item = Keuangan::findOrFail($id);
        $projectId = session('active_project_id');

        $coa = Coa::where('project_id', $projectId)
                    ->orderBy('no_akun', 'asc')
                    ->get();

        return view('keuangan.edit', compact('item', 'coa'));
    }

    public function update(Request $request, $id)
    {
        $transaksi = Keuangan::findOrFail($id);

        $cleanMasuk = parse_money($request->mutasi_masuk);
        $cleanKeluar = parse_money($request->mutasi_keluar);

        $request->merge([
            'mutasi_masuk'  => $cleanMasuk,
            'mutasi_keluar' => $cleanKeluar,
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

        if (!in_array(Auth::user()?->role, ['Super_Admin', 'Admin_Keuangan'])) {
            if (in_array($request->input, ['Kas Besar', 'Bank']) || in_array($transaksi->input, ['Kas Besar', 'Bank'])) {
                return redirect()->back()->withInput()->with('error', 'Anda tidak memiliki izin untuk mengedit transaksi terkait Kas Besar atau Bank.');
            }
        }

        DB::beginTransaction();
        try {
            $this->adjustBalances($transaksi, 'reverse');

            if ($request->hasFile('bukti')) {
                $validated['bukti'] = $request->file('bukti')->store('bukti_transaksi', 'public');
            }

            $transaksi->update($validated);

            $this->adjustBalances($transaksi, 'apply');

            DB::commit();
            return redirect()->route('keuangan.index')->with('success', 'Transaksi diperbarui dan saldo telah disesuaikan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui transaksi: ' . $e->getMessage());
        }
    }

    public function createJurnal()
    {

        $projectId = session('active_project_id');

        $coa = Coa::where('project_id', $projectId)
                    ->orderBy('no_akun', 'asc')
                    ->get();

        return view('keuangan.create_jurnal', compact('coa'));
    }

    public function storeJurnal(Request $request)
    {
        $projectId = session('active_project_id');

        $request->validate([
            'bukti'                    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'tanggal_array'            => 'required|array|min:2',
            'tanggal_array.*'          => 'required|date',
            'no_akun_array'            => 'required|array|min:2',
            'no_akun_array.*'          => 'required|exists:coa,no_akun',
            'mutasi_debit_array'       => 'required|array',
            'mutasi_kredit_array'      => 'required|array',
            'keterangan_array'         => 'required|array',
        ]);

        $debitArray = array_map('parse_money', $request->mutasi_debit_array);
        $kreditArray = array_map('parse_money', $request->mutasi_kredit_array);

        $totalDebit = array_sum($debitArray);
        $totalKredit = array_sum($kreditArray);

        if (round($totalDebit, 2) !== round($totalKredit, 2)) {
            return back()->withInput()->with('error', 'GAGAL: Total Debit dan Kredit tidak Balance!');
        }
        if (round($totalDebit, 2) == 0 && round($totalKredit, 2) == 0) {
            return back()->withInput()->with('error', 'GAGAL: Nominal Jurnal tidak boleh 0!');
        }

        $buktiPath = null;
        if ($request->hasFile('bukti')) {
            $buktiPath = $request->file('bukti')->store('bukti_transaksi', 'public');
        }

        DB::transaction(function () use ($request, $projectId, $debitArray, $kreditArray, $buktiPath) {
            $newJurnalRef = DB::table('keuangan')->max('jurnal_ref') + 1;

            foreach ($request->no_akun_array as $index => $akun) {
                if ($debitArray[$index] == 0 && $kreditArray[$index] == 0) {
                    continue;
                }

                $transaksi = Keuangan::create([
                    'jurnal_ref'       => $newJurnalRef,
                    'project_id'       => $projectId,
                    'tanggal'          => $request->tanggal_array[$index],
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
            ->where('jurnal_ref', $ref->jurnal_ref)
            ->get();

        $coa = Coa::where('project_id', $projectId)
            ->orderBy('no_akun', 'asc')
            ->get();


        return view('keuangan.edit_jurnal', compact('ref', 'jurnalRows', 'coa'));
    }

    public function updateJurnal(Request $request, $id)
    {
        $ref = Keuangan::findOrFail($id);
        $projectId = session('active_project_id');

        $request->validate([
            'bukti'               => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'tanggal_array'       => 'required|array|min:1',
            'tanggal_array.*'     => 'required|date',
            'no_akun_array'       => 'required|array|min:1',
            'no_akun_array.*'     => 'required|exists:coa,no_akun',
            'mutasi_debit_array'  => 'required|array',
            'mutasi_kredit_array' => 'required|array',
        ]);

        $debitArray = array_map('parse_money', $request->mutasi_debit_array);
        $kreditArray = array_map('parse_money', $request->mutasi_kredit_array);

        $totalDebit = array_sum($debitArray);
        $totalKredit = array_sum($kreditArray);

        if (round($totalDebit, 2) !== round($totalKredit, 2)) {
            return back()->withInput()->with('error', 'GAGAL: Total Debit dan Kredit Form tidak Balance!');
        }

        $buktiPath = $ref->bukti;
        if ($request->hasFile('bukti')) {
            $buktiPath = $request->file('bukti')->store('bukti_transaksi', 'public');
        }

        $targetRef = $ref->jurnal_ref;

        DB::beginTransaction();
        try {
            $oldJurnalRows = Keuangan::where('project_id', $projectId)
                ->where('jurnal_ref', $targetRef)
                ->get();

            foreach ($oldJurnalRows as $oldRow) {
                $this->adjustBalances($oldRow, 'reverse');
                $oldRow->delete();
            }

            foreach ($request->no_akun_array as $index => $akun) {
                if ($debitArray[$index] == 0 && $kreditArray[$index] == 0) continue;

                $transaksi = Keuangan::create([
                    'jurnal_ref'       => $targetRef,
                    'project_id'       => $projectId,
                    'tanggal'          => $request->tanggal_array[$index],
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

            DB::commit();
            return redirect()->route('keuangan.index')->with('success', 'Jurnal berhasil diperbarui dan saldo disesuaikan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui jurnal: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $transaksi = Keuangan::findOrFail($id);

        if ($transaksi->input === 'Jurnal' && !is_null($transaksi->jurnal_ref)) {

            $paketJurnal = Keuangan::where('project_id', $transaksi->project_id)
                ->where('jurnal_ref', $transaksi->jurnal_ref)
                ->get();

            foreach ($paketJurnal as $item) {
                $this->adjustBalances($item, 'reverse');
                $item->delete();
            }

            return redirect()->route('keuangan.index')->with('success', 'Satu paket Jurnal Umum (Ref: #' . $transaksi->jurnal_ref . ') berhasil dihapus dan saldo telah disesuaikan.');
        }

        $this->adjustBalances($transaksi, 'reverse');
        $transaksi->delete();

        return redirect()->route('keuangan.index')->with('success', 'Transaksi berhasil dihapus.');
    }

    private function adjustBalances($transaksi, $mode = 'apply')
    {
        $projectId = session('active_project_id');
        $tahun = date('Y', strtotime($transaksi->tanggal));

        $masuk = (float) $transaksi->mutasi_masuk;
        $keluar = (float) $transaksi->mutasi_keluar;

        if ($mode === 'reverse') {
            $masuk = -$masuk;
            $keluar = -$keluar;
        }

        if ($transaksi->input !== 'Jurnal') {
            $dompet = Coa::where('project_id', $projectId)
                ->where('tahun', $tahun)
                ->where('nama_akun', 'LIKE', '%' . $transaksi->input . '%')
                ->first();

            if ($dompet && $dompet->jenis_laporan !== 'Laba Rugi') {
                $dompet->saldo_akhir += ($masuk - $keluar);
                $dompet->save();
            }
        }

        $lawan = Coa::where('project_id', $projectId)
            ->where('tahun', $tahun)
            ->where('no_akun', $transaksi->no_akun)
            ->first();

        if ($lawan && $lawan->jenis_laporan !== 'Laba Rugi') {

            if ($transaksi->input === 'Jurnal') {
                $lawan->saldo_akhir += ($masuk - $keluar);

            } else {
                $lawan->saldo_akhir += ($keluar - $masuk);
            }

            $lawan->save();
        }
    }

    public function pendingApprovals()
    {
        $projectId = session('active_project_id');

        $pendingTransactions = TransaksiLead::with('lead')
            ->where('project_id', $projectId)
            ->where('status_keuangan', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('keuangan.pending', compact('pendingTransactions'));
    }

    public function approveForm($id)
    {
        $transaksiLead = TransaksiLead::with('lead')->findOrFail($id);
        $projectId = session('active_project_id');

        $coa = Coa::where('project_id', $projectId)
                ->orderBy('no_akun', 'asc')
                ->get();

        return view('keuangan.approve', compact('transaksiLead', 'coa'));
    }

    public function processApprove(Request $request, $id)
    {
        $transaksiLead = TransaksiLead::findOrFail($id);
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

        $transaksiKeuangan = Keuangan::create([
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

    public function getSaldoRealtime(Request $request)
    {
        $projectId = session('active_project_id');
        $tanggal = $request->tanggal;
        $tipeInput = $request->tipe_input;

        if (!$projectId || !$tanggal || !$tipeInput) {
            return response()->json(['saldo' => 0]);
        }

        $tahun = date('Y', strtotime($tanggal));

        $noAkun = '';
        if ($tipeInput === 'Kas Besar') $noAkun = '1101';
        elseif ($tipeInput === 'Kas Kecil') $noAkun = '1102';
        elseif ($tipeInput === 'Bank') $noAkun = '1103';

        if (!$noAkun) {
            return response()->json(['saldo' => 0]);
        }

        $coa = Coa::where('project_id', $projectId)
            ->where('tahun', $tahun)
            ->where('no_akun', $noAkun)
            ->first();

        return response()->json([
            'saldo' => $coa ? (float)$coa->saldo_akhir : 0
        ]);
    }

    public function getCoaByDate(Request $request)
    {
        $projectId = session('active_project_id');
        $tanggal = $request->tanggal;

        if (!$projectId || !$tanggal) {
            return response()->json([]);
        }

        $tahun = date('Y', strtotime($tanggal));

        $coas = Coa::where('project_id', $projectId)
            ->where('tahun', $tahun)
            ->orderBy('no_akun', 'asc')
            ->get()
            ->groupBy('kategori_akun');

        return response()->json($coas);
    }
}

