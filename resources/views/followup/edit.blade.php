@extends('layouts.app')
@section('title', 'Reschedule Follow Up')

@section('content')
    <div class="mb-4">
        <a href="{{ route('followup.index') }}" class="btn" style="text-decoration: none; color: #64748b;">
            &larr; Kembali ke Jadwal
        </a>
    </div>

    <div class="card" style="max-width: 600px; margin: 0 auto; padding: 2rem;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <h4 style="margin-bottom: 0.5rem; font-weight: 700;">Atur Ulang Jadwal</h4>
            <p style="color: #64748b; font-size: 0.9rem;">
                Menentukan waktu tindak lanjut berikutnya untuk Lead:<br>
                <strong style="color: #0f172a; font-size: 1.1rem;">{{ $followup->lead->nama_lead }}</strong>
            </p>
        </div>

        <form action="{{ route('followup.update', $followup->id_follow_up) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; font-size: 0.9rem;">Tanggal Berikutnya</label>
                    <input type="date" name="tgl_follow_up_berikutnya" class="form-control"
                        value="{{ $followup->tgl_follow_up_berikutnya }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-weight: 600; font-size: 0.9rem;">Jam Estimasi</label>
                    <input type="time" name="jam_follow_up_berikutnya" class="form-control"
                        value="{{ $followup->jam_follow_up_berikutnya ?? '09:00' }}">
                </div>
            </div>

            <div style="margin-top: 2rem; display: flex; justify-content: flex-end;">
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-save" style="margin-right: 8px;"></i> Simpan Jadwal Baru
                </button>
            </div>
        </form>
    </div>
@endsection
