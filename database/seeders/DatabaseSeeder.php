<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\TipeRumah;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;
    public function run(): void
{
    // 1. Buat Proyek
    $safira = Project::create(['nama_proyek' => 'Safira Regency']);

    // 2. Buat Tipe Rumah
    $t48 = TipeRumah::create([
        'project_id' => $safira->id,
        'nama_tipe' => 'Tipe 48'
    ]);

    $t58 = TipeRumah::create([
        'project_id' => $safira->id,
        'nama_tipe' => 'Tipe 58'
    ]);

    $t58premium = TipeRumah::create([
        'project_id' => $safira->id,
        'nama_tipe' => 'Tipe 58 Premium'
    ]);

    $t97 = TipeRumah::create([
        'project_id' => $safira->id,
        'nama_tipe' => 'Tipe 97'
    ]);

    $this->call(UserSeeder::class);
}
}
