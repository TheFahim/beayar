<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin
        Admin::firstOrCreate(
            ['email' => 'admin@beayar.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('Password123!'),
                'role' => 'super_admin',
            ]
        );
    }
}
