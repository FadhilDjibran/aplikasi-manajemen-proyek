<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use App\Models\PicMarketing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;

class UserController extends Controller
{
    public function index()
    {
        if (!str_contains(url()->previous(), '/users')) {
            session(['user_mgmt_origin' => url()->previous()]);
        }

        $auth = Auth::user();
        $projects = Project::all();
        $query = User::with(['project', 'picMarketing']);

        if ($auth->role === 'Super_Admin') {
            $users = $query->get();
        } elseif (in_array($auth->role, ['Admin_Marketing', 'Admin_Keuangan'])) {
            $users = $query->where(function ($q) use ($auth) {
                $q->where('project_id', $auth->project_id)
                ->orWhere('id', $auth->id)
                ->orWhere(function ($subQ) {
                    $subQ->whereNull('project_id')
                        ->where(function ($roleQ) {
                            $roleQ->whereNull('role')
                                  ->orWhereNotIn('role', ['Super_Admin', 'Admin_Marketing', 'Admin_Keuangan']);
                        });
                });
            })->get();
        } elseif (in_array($auth->role, ['Marketing', 'Keuangan'])) {
            $users = $query->where('project_id', $auth->project_id)->get();
        } else {
            $users = $query->where('id', $auth->id)->get();
        }

        return view('users', compact('users', 'projects'));
    }

    public function store(Request $request)
    {
        $auth = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:4',
            'role' => 'required|in:Super_Admin,Admin_Marketing,Admin_Keuangan,Marketing,Keuangan',
            'project_id' => 'nullable|exists:projects,id',
            'kpi_target' => 'nullable|numeric|min:0'
        ]);

        if ($auth->role === 'Admin_Marketing' && !in_array($request->role, ['Marketing', 'Keuangan'])) {
            return redirect()->back()->with('error', 'Anda hanya dapat menambahkan user Marketing atau Keuangan.');
        }

        if ($auth->role === 'Admin_Keuangan' && $request->role !== 'Keuangan') {
            return redirect()->back()->with('error', 'Anda hanya dapat menambahkan user dengan role Keuangan.');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'project_id' => $request->project_id,
        ]);

        $this->syncToPicMarketing($user, $request->kpi_target);

        return redirect()->route('index')->with('success', 'User berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $auth = Auth::user();

        if ($auth->role === 'Super_Admin') {
        }
        elseif (in_array($auth->role, ['Admin_Marketing', 'Admin_Keuangan'])) {

            $allowedRoles = $auth->role === 'Admin_Marketing'
                            ? ['Marketing', 'Keuangan']
                            : ['Keuangan'];

            $isTargetValid = ($user->id === $auth->id) || in_array($user->role, $allowedRoles) || empty($user->role);

            if (!$isTargetValid) {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengedit user ini.');
            }

            $isPromoting = in_array($request->role, ['Admin_Marketing', 'Admin_Keuangan', 'Super_Admin']);
            if ($isPromoting && $user->id !== $auth->id) {
                return redirect()->back()->with('error', 'Anda tidak bisa memberikan hak akses Admin atau Super Admin kepada user lain.');
            }
        }
        else {
            if ($user->id !== $auth->id) {
                return redirect()->back()->with('error', 'Anda hanya dapat mengubah profil Anda sendiri.');
            }

            if ($request->has('role') && $request->role !== $user->role) {
                return redirect()->back()->with('error', 'Anda tidak diperbolehkan mengubah hak akses sendiri.');
            }
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$id,
            'role' => 'nullable|in:Super_Admin,Admin_Marketing,Admin_Keuangan,Marketing,Keuangan',
            'password' => 'nullable|string|min:4',
            'project_id' => 'nullable|exists:projects,id',
            'kpi_target' => 'nullable|numeric|min:0'
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if (in_array($auth->role, ['Admin_Marketing', 'Admin_Keuangan', 'Super_Admin'])) {
            $user->role = $request->role;
            $user->project_id = $request->project_id;
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        $this->syncToPicMarketing($user, $request->kpi_target);

        return redirect()->route('index')->with('success', 'Data user berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $auth = Auth::user();

        if ($user->id == $auth->id) {
            return redirect()->back()->with('error', 'Anda tidak bisa menghapus akun sendiri.');
        }

        if ($auth->role === 'Admin_Marketing' && !in_array($user->role, ['Marketing', 'Keuangan'])) {
            return redirect()->back()->with('error', 'Anda hanya dapat menghapus akun Marketing atau Keuangan.');
        }

        if ($auth->role === 'Admin_Keuangan' && $user->role !== 'Keuangan') {
            return redirect()->back()->with('error', 'Anda hanya dapat menghapus akun Keuangan.');
        }

        PicMarketing::where('user_id', $user->id)->delete();
        $user->delete();

        return redirect()->route('index')->with('success', 'User berhasil dihapus.');
    }

    private function syncToPicMarketing($user, $targetKpi = 0)
    {
        if (in_array($user->role, ['Marketing', 'Admin_Marketing'])) {
            PicMarketing::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nama_pic'   => $user->name,
                    'kpi_target' => $targetKpi ?? 0,
                ]
            );
        } else {
            PicMarketing::where('user_id', $user->id)->delete();
        }
    }

    public function triggerCleanupUnassigned()
    {
        Artisan::call('users:cleanup-unassigned');
        return redirect()->back()->with('success', 'User berhasil dihapus.');
    }
}
