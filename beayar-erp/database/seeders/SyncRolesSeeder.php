<?php

namespace Database\Seeders;

use App\Models\TenantCompany;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class SyncRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = TenantCompany::all();

        foreach ($companies as $company) {
            $this->command->info("Syncing roles for company: {$company->name} (ID: {$company->id})");

            // Set Spatie Team Context
            setPermissionsTeamId($company->id);

            // Sync Owner
            if ($company->owner) {
                // Owner gets tenant_admin or company_admin?
                // Seeder defines tenant_admin as owner.
                // But pivot might say company_admin.
                // Let's give them tenant_admin as they are the owner.
                if (!$company->owner->hasRole('tenant_admin')) {
                     $company->owner->assignRole('tenant_admin');
                }
            }

            // Sync Members
            foreach ($company->members as $member) {
                $pivotRole = $member->pivot->role; // e.g. 'company_admin', 'employee'
                
                if (!$member->hasRole($pivotRole)) {
                    // Check if role exists in DB (it should from RolesAndPermissionsSeeder)
                    // Note: RolesAndPermissionsSeeder created global roles (team_id=null) if not changed?
                    // Wait, Spatie finds roles by name and guard.
                    // If we setPermissionsTeamId, it looks for roles with that team_id OR global roles if configured?
                    // Spatie default behavior: team_id must match.
                    // RolesAndPermissionsSeeder created roles without team_id (global).
                    // If team_permission is enabled, global roles might act as templates or need to be duplicated per team?
                    // Spatie documentation says: "If you use teams, you must assign roles/permissions to a specific team."
                    // But if roles are global, can they be assigned to a user-team pair?
                    // Yes, model_has_roles has team_id.
                    // So we assign global role 'employee' to User X for Team Y.
                    
                    $member->assignRole($pivotRole);
                }
            }
        }
    }
}
