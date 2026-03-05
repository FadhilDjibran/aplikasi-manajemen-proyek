@extends('layouts.plain')

@section('title', 'Login - Aplikasi Manajemen Proyek')

@section('content')

    <div class="login-container">
        <div class="auth-wrapper">
            <div class="card card-login">
                <div class="login-header">
                    <i class="fas fa-project-diagram"></i>
                    <h1 class="login-title">Aplikasi Manajemen Proyek</h1>
                </div>

                @error('email')
                    <div
                        style="background: #fee2e2; color: #991b1b; padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1.5rem; border: 1px solid #fecaca; font-size: 0.9rem; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>{{ $message }}</span>
                    </div>
                @enderror
                @if (session('success'))
                    <div
                        style="background: #dcfce7; color: #166534; padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1.5rem; border: 1px solid #bbf7d0; font-size: 0.9rem;">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                    </div>
                @endif
                <form action="{{ url('/login') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Alamat Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Masukkan Email Anda..."
                            required autocomplete="email" autofocus value="{{ old('email') }}"
                            style="@error('email') border-color: #dc2626; background-color: #fef2f2; @enderror">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kata Sandi</label>
                        <input type="password" name="password" class="form-control" placeholder="Masukkan Password Anda..."
                            required autocomplete="current-password"
                            style="@error('email') border-color: #dc2626; background-color: #fef2f2; @enderror">
                    </div>

                    <button type="submit" class="btn btn-primary">
                        Masuk <i class="fas fa-sign-in-alt"></i>
                    </button>
                    <div style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem;">
                        <span style="color: #64748b;">Belum punya akun?</span>
                        <a href="{{ url('/register') }}"
                            style="color: #2563eb; text-decoration: none; font-weight: 600;">Daftar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
