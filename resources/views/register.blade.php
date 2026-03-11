@extends('layouts.plain')

@section('title', 'Daftar Akun')

@section('content')

    <div class="login-container">
        <div class="auth-wrapper" style="width: 100%; max-width: 500px;">
            <div class="card card-login">
                <div class="login-header">
                    <i class="fas fa-user-plus"></i>
                    <h1 class="login-title">Daftar Akun Baru</h1>
                </div>

                @if ($errors->any())
                    <div
                        style="background: #fee2e2; color: #991b1b; padding: 0.75rem 1rem; border-radius: 6px; margin-bottom: 1.5rem; border: 1px solid #fecaca; font-size: 0.9rem; display: flex; flex-direction: column; gap: 8px;">
                        @foreach ($errors->all() as $error)
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $error }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                <form action="{{ url('/register') }}" method="POST">
                    @csrf

                    <div class="form-group">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control"
                            placeholder="Masukkan Nama Lengkap Anda..." required autocomplete="name" autofocus
                            value="{{ old('name') }}"
                            style="@error('name') border-color: #dc2626; background-color: #fef2f2; @enderror">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Alamat Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Masukkan Email Anda..."
                            required autocomplete="email" value="{{ old('email') }}"
                            style="@error('email') border-color: #dc2626; background-color: #fef2f2; @enderror">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kata Sandi</label>
                        <input type="password" name="password" class="form-control" placeholder="Buat Password Anda..."
                            required autocomplete="new-password"
                            style="@error('password') border-color: #dc2626; background-color: #fef2f2; @enderror">
                    </div>

                    <button type="submit" class="btn btn-primary" style="margin-top: 0.5rem;">
                        Daftar <i class="fas fa-user-check"></i>
                    </button>

                    <div style="text-align: center; margin-top: 1.5rem; font-size: 0.9rem;">
                        <span style="color: #64748b;">Sudah punya akun?</span>
                        <a href="{{ url('/') }}"
                            style="color: #2563eb; text-decoration: none; font-weight: 600;">Masuk</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
