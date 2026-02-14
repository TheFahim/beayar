<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Models\TenantCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiTenancyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed plans
        Plan::create([
            'name' => 'Free',
            'slug' => 'free',
            'base_price' => 0,
            'billing_cycle' => 'monthly',
        ]);
    }

    public function test_registration_flow_creates_tenant_structure()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('onboarding.plan'));

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);

        // At this point, Tenant and Company should NOT exist yet
        $this->assertNull($user->tenant);
        $this->assertEmpty($user->ownedCompanies);
    }

    public function test_user_can_create_new_company()
    {
        $user = User::factory()->create();
        $tenant = Tenant::create(['user_id' => $user->id, 'name' => 'Tenant']);
        $plan = Plan::first();
        \App\Models\Subscription::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'price' => 0,
        ]);

        // Create initial company to pass onboarding middleware
        TenantCompany::create([
            'tenant_id' => $tenant->id,
            'owner_id' => $user->id,
            'name' => 'Initial Company',
            'status' => 'active',
        ]);

        // Simulate login
        $this->actingAs($user);

        // Ensure user has tenant relation set up in test (factory might not do it)
        // The controller uses $user->tenant which relies on the relationship.

        $response = $this->post(route('tenant.user-companies.store'), [
            'name' => 'New Workspace',
        ]);

        $response->assertRedirect(route('tenant.user-companies.index'));

        $company = TenantCompany::where('name', 'New Workspace')->first();
        $this->assertNotNull($company);
        $this->assertEquals($tenant->id, $company->tenant_id);
        $this->assertEquals('company_admin', $user->roleInCompany($company->id));
    }

    public function test_switching_company_context()
    {
        $user = User::factory()->create();
        $tenant = Tenant::create(['user_id' => $user->id, 'name' => 'Tenant']);
        $plan = Plan::first();
        \App\Models\Subscription::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'price' => 0,
        ]);

        $company1 = TenantCompany::create([
            'tenant_id' => $tenant->id,
            'owner_id' => $user->id,
            'name' => 'Company 1',
            'status' => 'active',
        ]);
        $company1->members()->attach($user->id, ['role' => 'company_admin', 'is_active' => true]);

        $company2 = TenantCompany::create([
            'tenant_id' => $tenant->id,
            'owner_id' => $user->id,
            'name' => 'Company 2',
            'status' => 'active',
        ]);
        $company2->members()->attach($user->id, ['role' => 'company_admin', 'is_active' => true]);

        $this->actingAs($user);

        // Switch to Company 2
        $response = $this->post(route('companies.switch', $company2->id));

        $response->assertRedirect();
        $this->assertEquals($company2->id, session('tenant_id'));
        $this->assertEquals($company2->id, $user->fresh()->current_tenant_company_id);
    }

    public function test_access_control_middleware()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $tenant = Tenant::create(['user_id' => $user->id, 'name' => 'Tenant']);
        $otherTenant = Tenant::create(['user_id' => $otherUser->id, 'name' => 'Other Tenant']);

        $plan = Plan::first();
        \App\Models\Subscription::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'price' => 0,
        ]);

        // Company owned by OTHER user
        $company1 = TenantCompany::create([
            'tenant_id' => $otherTenant->id,
            'owner_id' => $otherUser->id,
            'name' => 'Company 1',
            'status' => 'active',
        ]);
        // User is NOT a member of Company 1

        $this->actingAs($user);

        // Try to switch to a company they don't belong to
        $response = $this->post(route('companies.switch', $company1->id));
        $response->assertStatus(403);
    }
}
