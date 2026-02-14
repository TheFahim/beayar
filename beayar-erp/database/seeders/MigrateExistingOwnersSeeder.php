<?php

namespace Database\Seeders;

use App\Models\TenantCompany;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MigrateExistingOwnersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = TenantCompany::all();

        foreach ($companies as $company) {
            if ($company->owner_id) {
                // Check if already exists to avoid duplication if run multiple times
                $exists = DB::table('company_members')
                    ->where('tenant_company_id', $company->id)
                    ->where('user_id', $company->owner_id)
                    ->exists();

                if (! $exists) {
                    DB::table('company_members')->insert([
                        'tenant_company_id' => $company->id,
                        'user_id' => $company->owner_id,
                        'role' => 'company_admin', // Owners are also company admins in their context
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Also assign Spatie Role 'tenant_admin' globally to the owner
                    $user = \App\Models\User::find($company->owner_id);
                    if ($user && ! $user->hasRole('tenant_admin')) {
                        $user->assignRole('tenant_admin');
                    }
                }
            }
        }
    }
}
