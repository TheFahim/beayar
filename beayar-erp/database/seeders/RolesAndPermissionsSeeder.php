<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions (Global)
        $permissions = [
            // Product Management
            'view_products',
            'create_products',
            'edit_products',
            'delete_products',
            
            // Quotation Management
            'view_quotations',
            'create_quotations',
            'edit_quotations',
            'delete_quotations',
            
            // Customer Management
            'view_customers',
            'create_customers',
            'edit_customers',
            'delete_customers',
            
            // Billing & Finance
            'view_bills',
            'create_bills',
            'edit_bills',
            'view_finance',
            
            // Settings & Members
            'manage_settings',
            'manage_members',
            'manage_roles',
        ];

        $guardName = config('auth.defaults.guard');

        foreach ($permissions as $permissionName) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => $guardName]);
        }

        // Create Roles
        $roles = [
            'super_admin',  // Manages the entire application (tenants/customers)
            'tenant_admin', // The Owner who signed up (manages companies)
            'company_admin', // Manages a specific company (employees, data)
            'employee',     // Regular user within a company
        ];

        $guardName = config('auth.defaults.guard');

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => $guardName]);
        }
    }
}
