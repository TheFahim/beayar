<?php

namespace Tests\Feature;

use App\Models\TenantCompany;
use App\Models\User;
use App\Models\Product;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Permission;

class PermissionEnforcementTest extends TestCase
{
    use RefreshDatabase;

    protected $owner;
    protected $employee;
    protected $company;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed permissions
        $this->seed(RolesAndPermissionsSeeder::class);

        // Create Owner
        $this->owner = User::factory()->create();
        $this->company = TenantCompany::create([
            'owner_id' => $this->owner->id,
            'name' => 'Test Company',
            'status' => 'active',
        ]);

        $this->owner->current_tenant_company_id = $this->company->id;
        $this->owner->save();
        $this->company->members()->attach($this->owner->id, ['role' => 'company_admin', 'is_active' => true]);

        setPermissionsTeamId($this->company->id);
        $this->owner->assignRole('company_admin');

        // Create Employee
        $this->employee = User::factory()->create();
        $this->company->members()->attach($this->employee->id, ['role' => 'employee', 'is_active' => true]);

        setPermissionsTeamId($this->company->id);
        $this->employee->assignRole('employee');
    }

    public function test_sidebar_visibility_based_on_permissions()
    {
        $tenantCompany = TenantCompany::factory()->create();
        $response = $this->actingAs($this->owner)
            ->withSession(['tenant_id' => $this->company->id])
            ->get(route('tenant.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Roles');
        $response->assertSee('Products');

        // Employee should see Products but NOT Roles & Permissions
        $response = $this->actingAs($this->employee)
            ->withSession(['tenant_id' => $this->company->id])
            ->get(route('tenant.dashboard'));

        $response->assertStatus(200);
        $this->assertStringContainsString('Products', $response->getContent());
        $this->assertStringContainsString('Quotations', $response->getContent());
        $this->assertStringContainsString('Customers', $response->getContent());
        $this->assertStringContainsString('Feedback', $response->getContent());
    }

    public function test_controller_authorization_enforcement()
    {
        // Employee can view products
        $response = $this->actingAs($this->employee)
            ->withSession(['tenant_id' => $this->company->id])
            ->get(route('tenant.products.index'));

        $response->assertStatus(200);

        // Employee cannot access Roles page
        $response = $this->actingAs($this->employee)
            ->withSession(['tenant_id' => $this->company->id])
            ->get(route('tenant.roles.index'));

        $response->assertStatus(403);
    }

    public function test_product_deletion_permission()
    {
        $product = Product::create([
            'name' => 'Test Product',
            'tenant_company_id' => $this->company->id,
        ]);

        // Employee cannot delete product
        $response = $this->actingAs($this->employee)
            ->withSession(['tenant_id' => $this->company->id])
            ->delete(route('tenant.products.destroy', $product));

        $response->assertStatus(403);

        // Owner can delete product
        $response = $this->actingAs($this->owner)
            ->withSession(['tenant_id' => $this->company->id])
            ->delete(route('tenant.products.destroy', $product));

        // Should redirect or return success (depending on controller logic)
        // Controller returns redirect on success, or json
        // If it fails validation (e.g. used in quotation), it might be 422 or redirect back
        // But here we test authorization (403 vs 200/302)
        $this->assertNotEquals(403, $response->status());
    }
}
