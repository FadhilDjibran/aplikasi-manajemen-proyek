<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
        public function run()
        {
            User::create([
                'name' => 'Super Admin Safira',
                'email' => 'superadmin@safira.com',
                'password' => Hash::make('pass123'),
                'role' => 'Super_Admin',
            ]);
        }
}
