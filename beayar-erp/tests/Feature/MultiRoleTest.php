<?php

namespace Tests\Feature;

use App\Models\TenantCompany;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MultiRoleTest extends TestCase
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

    public function test_can_assign_multiple_roles()
    {
        $newMember = User::factory()->create();

        $response = $this->actingAs($this->user)
            ->post(route('company-members.store'), [
                'name' => 'New Member',
                'email' => 'new@member.com',
                'roles' => ['employee', 'company_admin'], // Multi-role
                'is_active' => 1,
            ]);

        $response->assertRedirect();
        
        $newMember = User::where('email', 'new@member.com')->first();
        
        // Check Pivot (should be company_admin as it's higher priority)
        $this->assertEquals('company_admin', $newMember->roleInCompany($this->company->id));
        
        // Check Spatie Roles
        setPermissionsTeamId($this->company->id);
        $this->assertTrue($newMember->hasRole('employee'));
        $this->assertTrue($newMember->hasRole('company_admin'));
    }

    public function test_can_update_multiple_roles()
    {
        $member = User::factory()->create();
        $this->company->members()->attach($member->id, ['role' => 'employee']);
        
        setPermissionsTeamId($this->company->id);
        $member->assignRole('employee');

        $response = $this->actingAs($this->user)
            ->put(route('company-members.update', $member->id), [
                'name' => 'Updated Name',
                'email' => $member->email,
                'roles' => ['employee', 'company_admin'],
                'is_active' => 1,
            ]);

        $response->assertRedirect();
        
        // Check Pivot
        $this->assertEquals('company_admin', $member->roleInCompany($this->company->id));

        // Check Spatie Roles
        setPermissionsTeamId($this->company->id);
        $this->assertTrue($member->hasRole('employee'));
        $this->assertTrue($member->hasRole('company_admin'));
    }
}
