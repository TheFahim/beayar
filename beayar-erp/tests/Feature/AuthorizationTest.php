<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\TenantCompany;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $company;

    protected function setUp(): void
    {
        parent::setUp();

        // Run Seeder
        $this->seed(RolesAndPermissionsSeeder::class);

        // Create User and Company
        $this->user = User::factory()->create();
        $this->company = TenantCompany::create([
            'owner_id' => $this->user->id,
            'name' => 'Test Company',
            'status' => 'active',
        ]);

        $this->user->current_tenant_company_id = $this->company->id;
        $this->user->save();

        // Attach to company members
        $this->company->members()->attach($this->user->id, [
            'role' => 'company_admin',
            'is_active' => true,
        ]);

        // Assign Spatie Role
        setPermissionsTeamId($this->company->id);
        $this->user->assignRole('company_admin');
    }

    public function test_user_has_correct_permissions()
    {
        // Company Admin should have view_products
        $this->assertTrue($this->user->can('view_products'));
        $this->assertTrue($this->user->can('create_quotations'));
    }

    public function test_product_policy_allows_view()
    {
        $product = Product::factory()->create([
            'tenant_company_id' => $this->company->id,
        ]);

        $this->assertTrue($this->user->can('view', $product));
    }

    public function test_product_policy_denies_cross_tenant_access()
    {
        $otherCompany = TenantCompany::create([
            'owner_id' => User::factory()->create()->id,
            'name' => 'Other Company',
            'status' => 'active',
        ]);

        $product = Product::factory()->create([
            'tenant_company_id' => $otherCompany->id,
        ]);

        $this->assertFalse($this->user->can('view', $product));
    }

    public function test_employee_role_permissions()
    {
        $employee = User::factory()->create();
        $this->company->members()->attach($employee->id, [
            'role' => 'employee',
            'is_active' => true,
        ]);

        setPermissionsTeamId($this->company->id);
        $employee->assignRole('employee');
        
        // Employee should view products
        $this->assertTrue($employee->can('view_products'));
        
        // Employee should NOT delete products (as per seeder definition for 'employee' role)
        // Wait, let's check seeder definition again.
        // 'employee' => ['view_products', 'create_products', 'edit_products', ...]
        // 'delete_products' is NOT in the list.
        
        $this->assertFalse($employee->can('delete_products'));
    }
}
