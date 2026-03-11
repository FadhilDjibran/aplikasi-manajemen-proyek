@extends('layouts.app')
@section('title', 'Manajemen Hot Prospek')

@section('content')

    @if ($pendingCount > 0)
        <div
            style="margin-bottom: 1.5rem; padding: 1rem; background: #fff7ed; border: 1px solid #ffedd5; border-radius: 8px; display: flex; align-items: center; gap: 12px;">
            <div
                style="background: #f97316; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                <i class="fas fa-exclamation"></i>
            </div>
            <div>
                <strong style="color: #9a3412; font-size: 0.95rem;">Perhatian!</strong>
                <span style="color: #c2410c; font-size: 0.9rem;">Ada <b>{{ $pendingCount }}</b> leads berstatus Hot Prospek
                    yang belum
                    memiliki data transaksi Booking. Segera input datanya.</span>
            </div>
        </div>
    @endif

    <div class="card"
        style="max-width: 1400px; margin: 0 auto 2rem auto; border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden;">

        <div
            style="padding: 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; background: white;">
            <div>
                <h3 style="font-size: 1.2rem; font-weight: 800; color: #1e293b; margin-bottom: 0.25rem;">
                    🔥 Daftar Hot Prospek
                </h3>
                <p style="font-size: 0.875rem; color: #64748b; margin: 0;">
                    Input transaksi atau ubah menjadi gagal closing.
                </p>
            </div>
            <form action="{{ route('hot_prospek.index') }}" method="GET" style="display: flex; gap: 0.5rem;">
                <input type="text" name="search" class="form-control" placeholder="Cari lead..."
                    value="{{ request('search') }}" style="padding-left: 1rem; font-size: 0.9rem;">
                <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem;">Cari</button>
            </form>
        </div>

        <div class="table-container" style="background: white;">
            <table class="custom-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #ffffff; border-bottom: 2px solid #f1f5f9;">
                        <th style="width: 250px; padding: 15px;">Lead & Kontak</th>
                        <th style="width: 150px; padding: 15px;">PIC Marketing</th>
                        <th style="padding: 15px;">Status Transaksi Terakhir</th>
                        <th style="width: 160px; text-align: center; padding: 15px;">Status Lead</th>
                        <th style="width: 130px; text-align: center; padding: 15px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($hotLeads as $lead)
                        @php
                            $latestTr = $lead->transaksi->sortByDesc('tgl_pembayaran')->first();
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 15px;">
                                <strong style="font-size: 0.95rem; color: #1e293b;">{{ $lead->nama_lead }}</strong><br>
                                <div style="margin-top: 4px;">
                                    <small
                                        style="color: #64748b; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-weight: 600;">ID:
                                        {{ $lead->id_lead }}</small>
                                </div>
                                <small style="color: #64748b; display: block; margin-top: 4px;">
                                    <i class="fab fa-whatsapp" style="color: #22c55e;"></i> {{ $lead->no_whatsapp }}
                                </small>
                            </td>
                            <td style="padding: 15px;">
                                <div style="font-size: 0.85rem; color: #334155; font-weight: 600;">
                                    <i class="fas fa-user-circle" style="color: #cbd5e1; margin-right: 5px;"></i>
                                    {{ $lead->pic->nama_pic ?? '-' }}
                                </div>
                            </td>
                            <td style="padding: 10px 15px;">
                                @if (!$latestTr)
                                    <div
                                        style="width: 100%; background: #fee2e2; border: 1px dashed #f87171; border-radius: 8px; padding: 10px; text-align: center; color: #991b1b;">
                                        <i class="fas fa-times-circle" style="margin-bottom: 4px; font-size: 1.2rem;"></i>
                                        <div style="font-size: 0.8rem; font-weight: 700;">BELUM BOOKING</div>
                                    </div>
                                @else
                                    <div
                                        style="width: 100%; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 8px; padding: 12px; display: flex; flex-direction: column; gap: 6px;">
                                        <span
                                            style="font-size: 0.75rem; color: #15803d; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                                            <i class="fas fa-check-circle" style="margin-right: 4px;"></i>
                                            {{ $latestTr->jenis_pembayaran }}
                                        </span>
                                        <div style="font-weight: 600; font-size: 1.1rem; color: #166534; line-height: 1;">
                                            Rp {{ number_format($latestTr->nominal, 0, ',', '.') }}
                                        </div>
                                        <small style="font-size: 0.75rem; color: #64748b;">
                                            <i class="far fa-calendar-alt" style="margin-right: 4px;"></i>
                                            {{ \Carbon\Carbon::parse($latestTr->tgl_pembayaran)->format('d M Y') }}
                                        </small>
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: center; padding: 15px;">
                                <form id="form-status-{{ $lead->id_lead }}"
                                    action="{{ route('leads.update', $lead->id_lead) }}" method="POST">
                                    @csrf @method('PUT')
                                    <div style="position: relative; width: 100%;">
                                        <select name="status_lead"
                                            onchange="checkStatus(this, '{{ $lead->id_lead }}', '{{ $lead->nama_lead }}')"
                                            data-original-value="Hot Prospek"
                                            style="background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; cursor: pointer; border-radius: 6px; padding: 6px 8px; font-size: 0.75rem; font-weight: 700; width: 100%; outline: none; appearance: none;">

                                            <option value="Hot Prospek" selected>Hot Prospek</option>
                                            <option value="Gagal Closing" style="background: white; color: #334155;">Gagal
                                                Closing</option>

                                        </select>
                                        <i class="fas fa-chevron-down"
                                            style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); font-size: 0.6rem; pointer-events: none; opacity: 0.6;"></i>
                                    </div>
                                </form>
                            </td>
                            <td style="text-align: center; padding: 15px;">
                                <button onclick="openTransaksiModal('{{ $lead->id_lead }}', '{{ $lead->nama_lead }}')"
                                    class="btn"
                                    style="background: #3b82f6; color: white; padding: 8px 16px; font-size: 0.8rem; font-weight: 600; border-radius: 6px; border: none; cursor: pointer; transition: 0.2s; box-shadow: 0 2px 5px rgba(59, 130, 246, 0.3);">
                                    <i class="fas fa-plus" style="margin-right: 4px;"></i> Input
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center" style="padding: 3rem; color: #94a3b8;">
                                Belum ada data Hot Prospek saat ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card"
        style="max-width: 1400px; margin: 0 auto; border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden;">
        <div style="padding: 1.2rem 1.5rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
            <h4 style="font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0;">
                <i class="fas fa-history" style="color: #64748b; margin-right: 8px;"></i> Riwayat Transaksi Masuk
            </h4>
        </div>
        <div class="table-container" style="background: white;">
            <table class="custom-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #ffffff; border-bottom: 2px solid #f1f5f9; font-size: 0.85rem; color: #64748b;">
                        <th style="padding: 15px; text-align: left;">ID Transaksi</th>
                        <th style="padding: 15px; text-align: left;">Nama Lead</th>
                        <th style="padding: 15px; text-align: left;">Jenis Pembayaran</th>
                        <th style="padding: 15px; text-align: left;">Nominal</th>
                        <th style="padding: 15px; text-align: left;">Tgl Bayar</th>
                        <th style="padding: 15px; text-align: left;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactionHistory as $trx)
                        <tr style="border-bottom: 1px solid #f1f5f9; font-size: 0.9rem;">
                            <td style="padding: 15px; color: #64748b;">#{{ $trx->id_transaksi }}</td>
                            <td style="padding: 15px; font-weight: 600; color: #1e293b;">
                                {{ $trx->lead->nama_lead ?? 'Lead Terhapus' }}
                            </td>
                            <td style="padding: 15px;">
                                <span
                                    style="background: #f0f9ff; color: #0369a1; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; border: 1px solid #bae6fd;">
                                    {{ $trx->jenis_pembayaran }}
                                </span>
                            </td>
                            <td style="padding: 15px; font-size: 0.95rem; font-weight: 600; color: #15803d;">
                                Rp {{ number_format($trx->nominal, 0, ',', '.') }}
                            </td>
                            <td style="padding: 15px; color: #475569;">
                                {{ \Carbon\Carbon::parse($trx->tgl_pembayaran)->format('d/m/Y') }}
                            </td>
                            <td style="padding: 15px; color: #64748b; font-size: 0.85rem; max-width: 250px;">
                                {{ $trx->keterangan ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center" style="padding: 3rem; color: #cbd5e1;">
                                Belum ada riwayat transaksi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="transaksiModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div
            style="background: white; width: 100%; max-width: 450px; border-radius: 12px; padding: 2rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
            <h3 style="margin-top: 0; margin-bottom: 0.5rem; color: #1e293b;">Input Transaksi</h3>
            <p style="font-size: 0.9rem; color: #64748b; margin-bottom: 1.5rem;">Untuk Lead: <strong id="modalLeadName"
                    style="color: #3b82f6;"></strong></p>

            <form action="{{ route('hot_prospek.store_transaksi') }}" method="POST">
                @csrf
                <input type="hidden" name="id_lead" id="modalLeadId">

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label
                        style="display: block; margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 600; color: #475569;">Jenis
                        Pembayaran</label>
                    <select name="jenis_pembayaran" class="form-control" required
                        style="width: 100%; padding: 0.6rem; border: 1px solid #cbd5e1; border-radius: 6px;">
                        <option value="Booking">Booking Fee</option>
                        <option value="DP">Down Payment (DP)</option>
                        <option value="Lunas">Pelunasan</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label
                        style="display: block; margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 600; color: #475569;">
                        Nominal (Rp)
                    </label>
                    <div style="position: relative;">
                        <span
                            style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-weight: bold;">Rp</span>
                        <input type="text" name="nominal" id="inputNominal" class="form-control" required
                            placeholder="0"
                            style="width: 100%; padding: 0.6rem 0.6rem 0.6rem 35px; border: 1px solid #cbd5e1; border-radius: 6px;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label
                        style="display: block; margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 600; color: #475569;">Tanggal
                        Pembayaran</label>
                    <input type="date" name="tgl_pembayaran" class="form-control" required
                        value="{{ date('Y-m-d') }}"
                        style="width: 100%; padding: 0.6rem; border: 1px solid #cbd5e1; border-radius: 6px;">
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label
                        style="display: block; margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 600; color: #475569;">Keterangan
                        <small style="font-weight: normal; color: #94a3b8;">(Opsional)</small></label>
                    <textarea name="keterangan" class="form-control" rows="2"
                        style="width: 100%; padding: 0.6rem; border: 1px solid #cbd5e1; border-radius: 6px;"
                        placeholder="Catatan tambahan..."></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeTransaksiModal()" class="btn"
                        style="background: #f1f5f9; color: #475569; padding: 0.6rem 1.2rem; border-radius: 6px; border: 1px solid #e2e8f0; cursor: pointer;">Batal</button>
                    <button type="submit" class="btn btn-primary"
                        style="padding: 0.6rem 1.2rem; cursor: pointer;">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div id="gagalModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
        <div
            style="background: white; width: 100%; max-width: 450px; border-radius: 12px; padding: 2rem; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">

            <h3 style="margin-top: 0; color: #dc2626;">🚫 Gagal Closing</h3>
            <p style="font-size: 0.9rem; color: #64748b; margin-bottom: 1.5rem;">
                Mengapa lead <strong id="gagalLeadName" style="color: #1e293b;"></strong> batal transaksi?
            </p>

            <form id="gagalForm" method="POST">
                @csrf @method('PUT')

                <input type="hidden" name="status_lead" value="Gagal Closing">

                <div class="form-group" style="margin-bottom: 1rem;">
                    <label
                        style="display: block; margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 600; color: #475569;">Alasan
                        Utama</label>
                    <select name="alasan_gagal" class="form-control" required
                        style="width: 100%; padding: 0.6rem; border: 1px solid #cbd5e1; border-radius: 6px;">
                        <option value="" disabled selected>-- Pilih Alasan --</option>
                        <option value="BI Checking Ditolak">Gagal BI Checking</option>
                        <option value="Harga Terlalu Tinggi">Harga Terlalu Tinggi</option>
                        <option value="Lokasi Tidak Cocok">Lokasi Tidak Cocok</option>
                        <option value="Beli di Kompetitor">Sudah beli di kompetitor</option>
                        <option value="Uang Muka Belum Cukup">Urusan Pribadi</option>
                        <option value="Batal Sepihak">Mengundurkan Diri</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label
                        style="display: block; margin-bottom: 0.5rem; font-size: 0.85rem; font-weight: 600; color: #475569;">Catatan
                        Tambahan</label>
                    <textarea name="catatan_gagal" rows="3" class="form-control"
                        style="width: 100%; padding: 0.6rem; border: 1px solid #cbd5e1; border-radius: 6px;"
                        placeholder="Ceritakan detail penolakan..."></textarea>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeGagalModal()" class="btn"
                        style="background: #f1f5f9; color: #475569; padding: 0.6rem 1.2rem; border-radius: 6px; border: 1px solid #e2e8f0; cursor: pointer;">
                        Batal
                    </button>
                    <button type="submit" class="btn"
                        style="background: #dc2626; color: white; padding: 0.6rem 1.2rem; border-radius: 6px; border: none; cursor: pointer;">
                        Simpan Status
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        window.appRoutes = {
            updateLead: "{{ route('leads.update', ':id') }}"
        };
    </script>
    <script src="{{ asset('js/hot-prospek.js') }}"></script>
@endsection
