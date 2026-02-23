<?php

namespace Tests\Feature;

use App\Models\TenantCompany;
use App\Models\User;
use App\Models\Challan;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class ChallanPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected $owner;
    protected $employee;
    protected $company;

    protected function setUp(): void
    {
        parent::setUp();

        // Run Seeder
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

    public function test_permissions_are_seeded_correctly()
    {
        $this->assertDatabaseHas('permissions', ['name' => 'view_challans']);
        $this->assertDatabaseHas('permissions', ['name' => 'create_challans']);
        $this->assertDatabaseHas('permissions', ['name' => 'edit_challans']);
        $this->assertDatabaseHas('permissions', ['name' => 'delete_challans']);
    }

    public function test_owner_has_challan_permissions()
    {
        setPermissionsTeamId($this->company->id);

        $this->assertTrue($this->owner->can('view_challans'));
        $this->assertTrue($this->owner->can('create_challans'));
        $this->assertTrue($this->owner->can('edit_challans'));
        $this->assertTrue($this->owner->can('delete_challans'));
    }

    public function test_employee_has_limited_challan_permissions()
    {
        setPermissionsTeamId($this->company->id);

        // Employee should have view, create, edit
        $this->assertTrue($this->employee->can('view_challans'));
        $this->assertTrue($this->employee->can('create_challans'));
        $this->assertTrue($this->employee->can('edit_challans'));

        // Employee should NOT have delete (based on seeder)
        $this->assertFalse($this->employee->can('delete_challans'));
    }

    public function test_sidebar_visibility_for_challans()
    {
        // Owner should see Challans
        $response = $this->actingAs($this->owner)
            ->withSession(['tenant_id' => $this->company->id])
            ->get(route('tenant.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Challans');

        // Create a user WITHOUT any permissions/roles
        $noAccessUser = User::factory()->create();
        $this->company->members()->attach($noAccessUser->id, ['role' => 'employee', 'is_active' => true]);
        // Do NOT assign Spatie role

        $response = $this->actingAs($noAccessUser)
            ->withSession(['tenant_id' => $this->company->id])
            ->get(route('tenant.dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('Challans');
    }

    public function test_controller_authorization()
    {
        // Owner can access index
        $response = $this->actingAs($this->owner)
            ->withSession(['tenant_id' => $this->company->id])
            ->get(route('tenant.challans.index'));

        $response->assertStatus(200);

        // User without role cannot access
        $noAccessUser = User::factory()->create();
        $this->company->members()->attach($noAccessUser->id, ['role' => 'employee', 'is_active' => true]);

        $response = $this->actingAs($noAccessUser)
            ->withSession(['tenant_id' => $this->company->id])
            ->get(route('tenant.challans.index'));

        $response->assertStatus(403);
    }
}
