@extends('layouts.plain')

@section('title', 'Menunggu Persetujuan')

@section('content')
    <div class="login-container">
        <div class="auth-wrapper" style="width: 100%; max-width: 500px;">
            <div class="card card-login" style="text-align: center; padding: 3rem 2rem;">
                <i class="fas fa-user-clock" style="font-size: 4rem; color: #f59e0b; margin-bottom: 1.5rem;"></i>

                <h2 style="font-size: 1.5rem; font-weight: 700; color: #1e293b; margin-bottom: 1rem;">
                    Akun Menunggu Persetujuan
                </h2>

                <p style="color: #64748b; font-size: 0.95rem; line-height: 1.6; margin-bottom: 2rem;">
                    Halo <strong>{{ auth()->user()->name }}</strong>, akun Anda berhasil dibuat namun belum memiliki hak
                    akses di sistem ini.
                    Silakan hubungi Admin.
                </p>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary"
                        style="background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; width: auto; padding: 0.6rem 1.5rem; transition: all 0.3s;">
                        <i class="fas fa-sign-out-alt"></i> Keluar
                    </button>
                </form>

            </div>
        </div>
    </div>
@endsection
