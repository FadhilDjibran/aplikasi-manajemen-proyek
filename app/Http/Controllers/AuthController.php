<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{

    public function showRegister()
    {
        return view('register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ], [
            'email.unique' => 'Email ini sudah terdaftar di sistem.',
            'password.min' => 'Password minimal harus 6 karakter.'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('login')->with('success', 'Akun berhasil dibuat! Silakan masuk.');
    }

    public function showLogin()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            $hasRole = !empty($user->role);
            $hasProject = !empty($user->project_id);
            $isSuperAdmin = ($user->role === 'Super_Admin');

            if (!$hasRole || (!$hasProject && !$isSuperAdmin)) {
                return redirect()->route('pending');
            }

            if ($hasProject) {
                session([
                    'active_project_id' => $user->project_id,
                    'active_project_name' => $user->project->nama_proyek ?? 'Proyek Tanpa Nama',
                    'active_project_logo' => $user->project->logo ?? null
                ]);
            }

            return redirect()->route('dashboard');
        }

        return back()->withErrors([
            'email' => 'Email atau password yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    public function showPending()
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        $hasRole = !empty($user->role);
        $hasProject = !empty($user->project_id);
        $isSuperAdmin = ($user->role === 'Super Admin');

        if ($hasRole && ($hasProject || $isSuperAdmin)) {
            return redirect()->route('dashboard');
        }

        return view('pending');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
