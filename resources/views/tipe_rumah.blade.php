@extends('layouts.app')
@section('title', 'Tipe Rumah')

@section('content')
    <div class="card" style="width: 100%; max-width: 100%; border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">

        <div
            style="padding: 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9;">
            <div>
                <h3 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #1e293b;">Daftar Tipe Unit</h3>
                <p style="margin: 0; font-size: 0.875rem; color: #64748b;">Kelola kategori unit untuk proyek ini.</p>
            </div>
            <button onclick="openModal('create')" class="btn btn-primary"
                style="font-size: 0.8rem; padding: 0.4rem 0.8rem; width: auto; font-weight: 600;">
                <i class="fas fa-plus" style="font-size: 0.75rem; margin-right: 4px;"></i> Tambah Tipe
            </button>
        </div>

        <div class="table-container" style="padding: 0; width: 100%; overflow-x: auto;">
            <table class="custom-table"
                style="font-size: 0.9rem; width: 100%; border-collapse: collapse; min-width: 800px;">
                <thead
                    style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px;">
                    <tr>
                        <th style="padding: 16px 20px; width: 60px;">ID</th>
                        <th style="padding: 16px 20px;">Nama Tipe Rumah</th>
                        <th style="padding: 16px 20px; width: 180px; text-align: center;">Jumlah Peminat</th>
                        <th style="padding: 16px 20px; text-align: center; width: 160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tipeRumah as $item)
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;">
                            <td style="padding: 16px 20px; color: #94a3b8; font-weight: 500;">{{ $item->id_tipe }}</td>
                            <td style="padding: 16px 20px;">
                                <strong style="color: #1e293b; font-size: 1rem;">{{ $item->nama_tipe }}</strong>
                            </td>
                            <td style="padding: 16px 20px; text-align: center;">
                                <span
                                    style="background: #eff6ff; color: #1d4ed8; padding: 4px 12px; border-radius: 20px; font-weight: 600; font-size: 0.8rem; border: 1px solid #dbeafe;">
                                    {{ $item->leads->count() }} Leads
                                </span>
                            </td>
                            <td style="padding: 16px 20px; text-align: center;">
                                <div style="display: flex; gap: 8px; justify-content: center;">
                                    <button onclick='openModal("edit", @json($item))' class="btn"
                                        style="color: #059669; background: #ecfdf5; padding: 6px 12px; font-size: 0.85rem; border: 1px solid #bbf7d0; border-radius: 6px; transition: 0.2s;">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <form action="{{ route('tipe_rumah.destroy', $item->id_tipe) }}" method="POST"
                                        onsubmit="return confirm('Hapus tipe ini? Data leads terkait mungkin akan kehilangan referensi tipe.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn"
                                            style="color: #dc2626; background: #fef2f2; padding: 6px 12px; font-size: 0.85rem; border: 1px solid #fecaca; border-radius: 6px; transition: 0.2s;">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center" style="padding: 4rem; color: #94a3b8;">
                                <i class="fas fa-home"
                                    style="font-size: 2.5rem; margin-bottom: 1rem; opacity: 0.3;"></i><br>
                                <span style="font-size: 1rem;">Belum ada data tipe rumah.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="tipeModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div
            style="background: white; width: 100%; max-width: 450px; border-radius: 12px; padding: 2rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);">
            <h3 id="modalTitle" style="margin-top: 0; margin-bottom: 1.5rem; color: #1e293b; font-size: 1.25rem;">Tambah
                Tipe Rumah</h3>

            <form id="tipeForm" method="POST">
                @csrf
                <div id="methodField"></div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label
                        style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #475569; font-size: 0.9rem;">Nama
                        Tipe</label>
                    <input type="text" name="nama_tipe" id="nama_tipe" class="form-control" required
                        placeholder="Contoh: Tipe 36, Hook, Ruko 2LT" maxlength="50"
                        style="width: 100%; padding: 0.6rem; border: 1px solid #cbd5e1; border-radius: 6px;">
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeModal()" class="btn"
                        style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; padding: 0.6rem 1.2rem; border-radius: 6px;">Batal</button>
                    <button type="submit" class="btn btn-primary" style="padding: 0.6rem 1.2rem;">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        window.appRoutes = {
            store: "{{ route('tipe_rumah.store') }}",
            update: "{{ route('tipe_rumah.update', ':id') }}"
        };
    </script>

    <script src="{{ asset('js/tipe-rumah.js') }}"></script>

@endsection
