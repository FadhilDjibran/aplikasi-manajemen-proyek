<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use App\Models\PicMarketing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

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
        } else {
            $users = $query->where(function ($q) use ($auth) {
                $q->where('role', 'Marketing')
                  ->where('project_id', $auth->project_id);
            })->orWhere(function ($q) use ($auth) {
                $q->where('role', 'Admin')
                  ->where('project_id', $auth->project_id);
            })->orWhere('id', $auth->id)
              ->get();
        }

        return view('users', compact('users', 'projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:4',
            'role' => 'required',
            'project_id' => 'nullable|exists:projects,id',
            'kpi_target' => 'nullable|numeric|min:0'
        ]);

        if (Auth::user()->role === 'Admin' && $request->role !== 'Marketing') {
            return redirect()->back()->with('error', 'Anda hanya dapat menambahkan user Marketing.');
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

        if ($auth->role === 'Admin') {
            if ($user->id !== $auth->id && $user->role !== 'Marketing') {
                return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengedit user ini.');
            }

            if ($request->role === 'Admin' && $user->id !== $auth->id) {
                return redirect()->back()->with('error', 'Anda tidak dapat mempromosikan user lain menjadi Admin.');
            }
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$id,
            'role' => 'required',
            'password' => 'nullable|string|min:4',
            'project_id' => 'nullable|exists:projects,id',
            'kpi_target' => 'nullable|numeric|min:0'
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'project_id' => $request->project_id,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        $this->syncToPicMarketing($user, $request->kpi_target);

        return redirect()->route('index')->with('success', 'Data user berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $auth = Auth::user();

        if ($user->id == $auth->id) {
            return redirect()->back()->with('error', 'Anda tidak bisa menghapus akun sendiri!');
        }

        if ($auth->role === 'Admin' && $user->role !== 'Marketing') {
            return redirect()->back()->with('error', 'Anda hanya dapat menghapus akun Marketing.');
        }

        PicMarketing::where('user_id', $user->id)->delete();
        $user->delete();

        return redirect()->route('index')->with('success', 'User berhasil dihapus!');
    }

    private function syncToPicMarketing($user, $targetKpi = 0)
    {
        if (in_array($user->role, ['Marketing', 'Admin'])) {
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
}
