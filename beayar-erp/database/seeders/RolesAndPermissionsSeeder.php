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

            // Image Management
            'view_images',
            'create_images',
            'edit_images',
            'delete_images',

            // Quotation Management
            'view_quotations',
            'create_quotations',
            'edit_quotations',
            'delete_quotations',

            // Challan Management
            'view_challans',
            'create_challans',
            'edit_challans',
            'delete_challans',

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

            // Feedback
            'view_feedback',
            'create_feedback',
            'edit_feedback',
            'delete_feedback',

            // Settings & Members
            'manage_settings',
            'manage_members',
            'manage_roles',
        ];

        $guardName = config('auth.defaults.guard');

        foreach ($permissions as $permissionName) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => $guardName]);
        }

        // Create Roles and Assign Permissions
        $roles = [
            'super_admin' => [], // Super admin bypasses checks usually, or we can give all
            'tenant_admin' => $permissions, // Owner gets everything
            'company_admin' => $permissions, // Admin gets everything
            'employee' => [
                'view_products', 'create_products', 'edit_products',
                'view_images', 'create_images', 'edit_images',
                'view_quotations', 'create_quotations', 'edit_quotations',
                'view_challans', 'create_challans', 'edit_challans',
                'view_customers', 'create_customers', 'edit_customers',
                'view_bills', 'create_bills', 'edit_bills',
                'view_feedback', 'create_feedback',
            ],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => $guardName]);
            if (!empty($rolePermissions)) {
                $role->syncPermissions($rolePermissions);
            }
        }
    }
}
