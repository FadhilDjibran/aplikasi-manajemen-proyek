@extends('layouts.app')
@section('title', 'Antrean Approval Transaksi')

@section('content')
    <div class="card"
        style="max-width: 1200px; margin: 0 auto; border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden;">

        <div
            style="padding: 1.5rem; border-bottom: 1px solid #e5e7eb; display: flex; justify-content: space-between; align-items: center; background: white;">
            <div>
                <h3 style="font-size: 1.2rem; font-weight: 800; color: #1e293b; margin-bottom: 0.25rem;">
                    <i class="fas fa-clipboard-check" style="color: #f59e0b; margin-right: 5px;"></i> Antrian Approval
                    Transaksi
                </h3>
                <p style="font-size: 0.875rem; color: #64748b; margin: 0;">
                    Daftar transaksi booking leads yang perlu diapproval.
                </p>
            </div>
        </div>

        @if (session('success'))
            <div
                style="margin: 1rem 1.5rem; background-color: #f0fdf4; color: #166534; padding: 1rem; border-radius: 8px; border: 1px solid #bbf7d0; display: flex; align-items: center; gap: 8px; font-weight: 600;">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <div class="table-container" style="background: white;">
            <table class="custom-table" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 2px solid #f1f5f9; color: #475569; font-size: 0.9rem;">
                        <th style="padding: 15px; text-align: left;">Lead / Pelanggan</th>
                        <th style="padding: 15px; text-align: left;">Jenis</th>
                        <th style="padding: 15px; text-align: right;">Nominal Closing</th>
                        <th style="padding: 15px; text-align: center;">Tgl Input</th>
                        <th style="padding: 15px; text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingTransactions as $trx)
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 15px;">
                                <strong style="color: #1e293b;">{{ $trx->lead->nama_lead ?? 'Unknown' }}</strong>
                            </td>
                            <td style="padding: 15px;">
                                <span
                                    style="background: #f0f9ff; color: #0369a1; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                                    {{ $trx->jenis_pembayaran }}
                                </span>
                            </td>
                            <td style="padding: 15px; text-align: right; font-weight: 700; color: #15803d;">
                                Rp {{ number_format($trx->nominal, 0, ',', '.') }}
                            </td>
                            <td style="padding: 15px; text-align: center; color: #64748b; font-size: 0.9rem;">
                                {{ \Carbon\Carbon::parse($trx->tgl_pembayaran)->format('d M Y') }}
                            </td>
                            <td style="padding: 15px; text-align: center;">
                                <a href="{{ route('keuangan.approve_form', $trx->id_transaksi) }}" class="btn"
                                    style="background: #f59e0b; color: white; padding: 6px 16px; font-size: 0.85rem; font-weight: 600; border-radius: 6px; text-decoration: none; box-shadow: 0 2px 4px rgba(245, 158, 11, 0.2);">
                                    Proses <i class="fas fa-arrow-right" style="margin-left: 4px;"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center" style="padding: 3rem; color: #94a3b8;">
                                <i class="fas fa-inbox"
                                    style="font-size: 2rem; color: #cbd5e1; margin-bottom: 10px; display: block;"></i>
                                Antrian kosong.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
