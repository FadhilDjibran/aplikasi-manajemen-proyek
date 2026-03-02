@extends('layouts.app')
@section('title', 'Database Leads')

@section('content')
    <div class="card" style="width: 100%; max-width: 100%; border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
        <div
            style="padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; border-bottom: 1px solid #f1f5f9;">
            <form action="{{ route('leads.index') }}" method="GET"
                style="display: flex; flex-wrap: wrap; gap: 10px; width: 100%; max-width: 700px; align-items: center;">

                <div style="position: relative; flex: 1; min-width: 200px;">
                    <i class="fas fa-search"
                        style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.9rem;"></i>
                    <input type="text" name="search" class="form-control" placeholder="Cari nama, WA, atau catatan..."
                        value="{{ request('search') }}"
                        style="padding-left: 38px; border-radius: 8px; border: 1px solid #e2e8f0; height: 42px; width: 100%;">
                </div>

                <div style="flex: 0 0 auto; min-width: 160px;">
                    <select name="status_filter" class="form-control" onchange="this.form.submit()"
                        style="cursor: pointer; border-radius: 8px; border: 1px solid #e2e8f0; height: 42px; width: 100%; padding-left: 10px; padding-right: 30px; background-color: #fff; font-size: 0.85rem;">
                        <option value="">Semua Status</option>
                        @foreach (['Cold Lead', 'Warm Lead', 'Hot Prospek', 'Tidak Prospek', 'Gagal Closing'] as $st)
                            <option value="{{ $st }}" {{ request('status_filter') == $st ? 'selected' : '' }}>
                                {{ $st }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @if (request('search') || request('status_filter'))
                    <a href="{{ route('leads.index') }}" class="btn" title="Reset Filter"
                        style="background: #fee2e2; color: #ef4444; border: 1px solid #fecaca; height: 42px; width: 42px; display: flex; align-items: center; justify-content: center; border-radius: 8px; text-decoration: none; transition: 0.2s;">
                        <i class="fas fa-times"></i>
                    </a>
                @endif

                <button type="submit" style="display: none;"></button>
            </form>

            <a href="{{ route('leads.create') }}" class="btn btn-primary"
                style="width: auto; font-size: 0.9rem; padding: 0.5rem 1.2rem; font-weight: 600;">
                <i class="fas fa-plus" style="margin-right: 5px;"></i> Tambah Lead
            </a>
        </div>

        <div class="table-container" style="padding: 0; margin: 0;">
            <table class="custom-table" style="font-size: 0.85rem; width: 100%; border-collapse: collapse;">
                <thead
                    style="background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.75rem;">
                    <tr>
                        <th style="padding: 12px 15px; width: 50px;">ID</th>
                        <th style="padding: 12px 15px; min-width: 200px;">Nama Lead & Kontak</th>
                        <th style="padding: 12px 15px; width: 120px;">PIC</th>
                        <th style="padding: 12px 15px; width: 150px;">Sumber</th>
                        <th style="padding: 12px 15px; text-align: center; width: 160px;">Status</th>
                        <th style="padding: 12px 15px; width: 100px;">Tgl Masuk</th>
                        <th style="padding: 12px 15px;">Catatan</th>
                        <th style="padding: 12px 15px; text-align: center; width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leads as $lead)
                        @php
                            $isWarmHot = in_array($lead->status_lead, ['Warm Lead', 'Hot Prospek']);
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;">
                            <td style="padding: 10px 15px; color: #94a3b8;">{{ $lead->id_lead }}</td>
                            <td style="padding: 10px 15px;">
                                <div style="font-weight: 700; color: #1e293b; font-size: 0.9rem; margin-bottom: 2px;">
                                    {{ $lead->nama_lead }}</div>
                                <a href="https://wa.me/{{ $lead->no_whatsapp }}" target="_blank"
                                    style="text-decoration: none; color: #64748b; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 4px;">
                                    <i class="fab fa-whatsapp" style="color: #22c55e;"></i> {{ $lead->no_whatsapp }}
                                </a>
                            </td>
                            <td style="padding: 10px 15px;">
                                <div style="display: flex; align-items: center; gap: 6px; color: #475569;">
                                    <i class="fas fa-user-circle" style="color: #cbd5e1; font-size: 0.9rem;"></i>
                                    <span style="font-weight: 500;">
                                        {{ $lead->pic->nama_pic ?? '-' }}
                                    </span>
                                </div>
                            </td>
                            <td style="padding: 10px 15px;">
                                <span
                                    style="background: #f1f5f9; padding: 3px 8px; border-radius: 4px; font-size: 0.75rem; border: 1px solid #e2e8f0; color: #64748b; font-weight: 600;">
                                    {{ $lead->sumber_lead }}
                                </span>
                            </td>
                            <td
                                style="padding: 10px 15px; text-align: center; white-space: nowrap; vertical-align: middle;">
                                @php
                                    $badgeStyle = match ($lead->status_lead) {
                                        'Cold Lead'
                                            => 'background: #bae6fd; color: #0369a1; border: 1px solid #7dd3fc;',
                                        'Warm Lead'
                                            => 'background: #fde68a; color: #92400e; border: 1px solid #f59e0b;',
                                        'Hot Prospek'
                                            => 'background: #fecdd3; color: #be123c; border: 1px solid #fb7185;',
                                        'Tidak Prospek'
                                            => 'background: #cbd5e1; color: #1e293b; border: 1px solid #94a3b8;',
                                        'Gagal Closing'
                                            => 'background: #a61b1b; color: #ffffff; border: 1px solid #7f1d1d;',
                                        default => 'background: #e2e8f0; color: #475569; border: 1px solid #cbd5e1;',
                                    };
                                @endphp
                                <form action="{{ route('leads.update', $lead->id_lead) }}" method="POST">
                                    @csrf @method('PUT')
                                    <div
                                        style="position: relative; display: inline-block; width: 100%; white-space: nowrap; vertical-align: middle;">
                                        <select name="status_lead" onchange="this.form.submit()"
                                            style="{{ $badgeStyle }} cursor: pointer; border-radius: 6px; padding: 4px 20px 4px 8px; font-size: 0.75rem; font-weight: 700; appearance: none; -webkit-appearance: none; outline: none; width: 100%; text-align: center;">
                                            @foreach (['Cold Lead', 'Warm Lead', 'Hot Prospek', 'Tidak Prospek', 'Gagal Closing'] as $status)
                                                <option value="{{ $status }}"
                                                    {{ $lead->status_lead == $status ? 'selected' : '' }}
                                                    style="background: white; color: #334155;">
                                                    {{ $status }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <i class="fas fa-chevron-down"
                                            style="position: absolute; right: 8px; top: 50%; transform: translateY(-50%); font-size: 0.6rem; pointer-events: none; opacity: 0.6;"></i>
                                    </div>
                                </form>
                            </td>
                            <td style="padding: 10px 15px; color: #64748b; white-space: nowrap; vertical-align: middle;">
                                {{ \Carbon\Carbon::parse($lead->tgl_masuk)->format('d M Y') }}
                            </td>
                            <td style="padding: 10px 15px;">
                                <p style="font-size: 0.8rem; color: #64748b; margin: 0; line-height: 1.4;">
                                    {{ $lead->catatan ? Str::limit($lead->catatan, 30) : '-' }}
                                </p>
                            </td>
                            <td style="padding: 10px 15px; text-align: center;">
                                <div style="display: flex; gap: 6px; justify-content: center;">
                                    @php
                                        $isWarmHot = in_array($lead->status_lead, [
                                            'Warm Prospek',
                                            'Hot Prospek',
                                            'Warm Lead',
                                        ]);
                                        $isMyLead = $lead->pic && $lead->pic->user_id === auth()->id();
                                        $isSuperAdmin = auth()->user()->role === 'Super_Admin';
                                        $canExecute = $isWarmHot && ($isSuperAdmin || $isMyLead);
                                    @endphp
                                    @if ($canExecute)
                                        <a href="{{ route('followup.followup_execute', $lead->id_lead) }}" class="btn"
                                            style="color: #ea580c; background: #fff7ed; padding: 6px 10px; font-size: 0.8rem; border: 1px solid #ffedd5; border-radius: 6px; transition: 0.2s;"
                                            title="Eksekusi">
                                            <i class="fas fa-play"></i>
                                        </a>
                                    @elseif($isWarmHot)
                                        <button class="btn" disabled
                                            style="color: #9ca3af; background: #f3f4f6; padding: 6px 10px; font-size: 0.8rem; border: 1px solid #e5e7eb; border-radius: 6px; cursor: not-allowed;"
                                            title="Hanya PIC atau Super Admin yang dapat mengeksekusi">
                                            <i class="fas fa-play"></i>
                                        </button>
                                    @endif
                                    <a href="{{ route('leads.show', $lead->id_lead) }}" class="btn"
                                        style="color: #2563eb; background: #eff6ff; padding: 6px 10px; font-size: 0.8rem; border: 1px solid #dbeafe; border-radius: 6px; transition: 0.2s;"
                                        title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('leads.edit', $lead->id_lead) }}" class="btn"
                                        style="color: #059669; background: #ecfdf5; padding: 6px 10px; font-size: 0.8rem; border: 1px solid #bbf7d0; border-radius: 6px; transition: 0.2s;"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center" style="padding: 4rem; color: #94a3b8;">
                                <i class="fas fa-inbox"
                                    style="font-size: 2.5rem; display: block; margin-bottom: 1rem; opacity: 0.3;"></i>
                                <span style="font-size: 1rem;">Tidak ada data lead ditemukan.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="padding: 1.5rem; border-top: 1px solid #f1f5f9;">
            {{ $leads->withQueryString()->links() }}
        </div>
    </div>
@endsection
