<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserCompany;
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
        $user->assignRole('admin');

        // Create Default Company
        $company = UserCompany::firstOrCreate(
            ['owner_id' => $user->id],
            [
                'name' => 'Beayar HQ',
                'email' => 'contact@beayar.com',
                'status' => 'active',
            ]
        );

        // Link Company to User
        $user->update([
            'current_user_company_id' => $company->id,
            'current_scope' => 'company'
        ]);

        // Create Tenant User
        $tenantUser = User::firstOrCreate(
            ['email' => 'tenant@beayar.com'],
            [
                'name' => 'Tenant User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $tenantUser->assignRole('tenant');

        $tenantCompany = UserCompany::firstOrCreate(
            ['owner_id' => $tenantUser->id],
            [
                'name' => 'Tenant Company',
                'email' => 'tenant@company.com',
                'status' => 'active',
            ]
        );

        $tenantUser->update([
            'current_user_company_id' => $tenantCompany->id,
            'current_scope' => 'company'
        ]);
    }
}
