<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin User
        $user = User::firstOrCreate(
            ['email' => 'admin@beayar.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Assign Admin Role
        $user->assignRole('super_admin');

        // Note: Admin doesn't necessarily need a "company" in the same way tenants do,
        // but for compatibility with some shared views/logic, we might give them a dummy one or handle nulls.
        // For now, let's leave them without a tenant context or create a system tenant.
    }
}
