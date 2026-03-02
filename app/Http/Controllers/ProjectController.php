<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{

    public function index()
    {
        $projects = Project::orderBy('id', 'asc')->get();
        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_proyek' => 'required|string|max:255',
            'logo'        => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        $data = [
            'nama_proyek' => $request->nama_proyek,
        ];

        if ($request->hasFile('logo')) {
            // Simpan di folder: storage/app/public/logos
            $path = $request->file('logo')->store('logos', 'public');
            $data['logo'] = $path;
        }

        Project::create($data);

        return redirect()->route('projects.index')->with('success', 'Proyek berhasil dibuat!');
    }

    public function edit($id)
    {
        $project = Project::findOrFail($id);
        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_proyek' => 'required|string|max:255',
            'logo'        => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        $project = Project::findOrFail($id);

        $data = [
            'nama_proyek' => $request->nama_proyek,
        ];

        if ($request->hasFile('logo')) {
            if ($project->logo && Storage::disk('public')->exists($project->logo)) {
                Storage::disk('public')->delete($project->logo);
            }

            $path = $request->file('logo')->store('logos', 'public');
            $data['logo'] = $path;
        }

        $project->update($data);

        if (session('active_project_id') == $project->id) {
            session([
                'active_project_name' => $project->nama_proyek,
                'active_project_logo' => $project->logo
            ]);
        }

        return back()->with('success', 'Proyek berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);

        if ($project->logo && Storage::disk('public')->exists($project->logo)) {
            Storage::disk('public')->delete($project->logo);
        }

        if (session('active_project_id') == $project->id) {
            session()->forget(['active_project_id', 'active_project_name', 'active_project_logo']);
        }

        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Proyek berhasil dihapus.');
    }

    public function enterProject($id)
    {
        $project = Project::findOrFail($id);

        session([
            'active_project_id'   => $project->id,
            'active_project_name' => $project->nama_proyek,
            'active_project_logo' => $project->logo,
        ]);

        return redirect()->route('dashboard');
    }

    public function exitProject()
    {
        session()->forget(['active_project_id', 'active_project_name', 'active_project_logo']);

        return redirect()->route('projects.index');
    }
}
