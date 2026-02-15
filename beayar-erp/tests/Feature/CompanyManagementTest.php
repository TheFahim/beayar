<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Models\TenantCompany;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_create_company_if_limit_reached()
    {
        $user = User::factory()->create();
        $tenant = Tenant::create(['user_id' => $user->id, 'name' => 'Tenant']);
        $plan = Plan::create([
            'name' => 'Free',
            'slug' => 'free',
            'description' => 'Description',
            'base_price' => 0,
            'billing_cycle' => 'monthly',
            'limits' => ['sub_companies' => 1],
            'is_active' => true,
        ]);

        Subscription::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'price' => 0,
        ]);

        // Create initial company (limit 1 reached)
        TenantCompany::create([
            'tenant_id' => $tenant->id,
            'owner_id' => $user->id,
            'name' => 'Initial Company',
            'status' => 'active',
        ]);

        $this->actingAs($user);

        // Try to create another
        $response = $this->post(route('tenant.user-companies.store'), [
            'name' => 'Second Company',
        ]);

        $response->assertSessionHasErrors(['limit']);
        $this->assertDatabaseMissing('tenant_companies', ['name' => 'Second Company']);
    }

    public function test_user_can_add_member_and_create_user()
    {
        $user = User::factory()->create();
        $tenant = Tenant::create(['user_id' => $user->id, 'name' => 'Tenant']);
        $plan = Plan::create([
            'name' => 'Pro',
            'slug' => 'pro',
            'description' => 'Description',
            'base_price' => 10,
            'billing_cycle' => 'monthly',
            'limits' => ['employees' => 5],
            'is_active' => true,
        ]);

        Subscription::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'price' => 0,
        ]);

        $company = TenantCompany::create([
            'tenant_id' => $tenant->id,
            'owner_id' => $user->id,
            'name' => 'Company',
            'status' => 'active',
        ]);

        // Attach owner
        $company->members()->attach($user->id, ['role' => 'company_admin', 'is_active' => true]);

        $this->actingAs($user)->withSession(['tenant_id' => $company->id]);

        $response = $this->post(route('company-members.store'), [
            'email' => 'newuser@example.com',
            'name' => 'New User',
            'role' => 'employee',
        ]);

        $response->assertSessionHas('success');

        $newUser = User::where('email', 'newuser@example.com')->first();
        $this->assertNotNull($newUser);
        $this->assertEquals('New User', $newUser->name);

        $this->assertTrue($company->members()->where('user_id', $newUser->id)->exists());
    }

    public function test_cannot_add_member_if_limit_reached()
    {
         $user = User::factory()->create();
        $tenant = Tenant::create(['user_id' => $user->id, 'name' => 'Tenant']);
        $plan = Plan::create([
            'name' => 'Pro',
            'slug' => 'pro',
            'description' => 'Description',
            'base_price' => 10,
            'billing_cycle' => 'monthly',
            'limits' => ['employees' => 1], // Limit 1 (which is the owner)
            'is_active' => true,
        ]);

        Subscription::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'price' => 0,
        ]);

        $company = TenantCompany::create([
            'tenant_id' => $tenant->id,
            'owner_id' => $user->id,
            'name' => 'Company',
            'status' => 'active',
        ]);

        // Attach owner (1 member)
        $company->members()->attach($user->id, ['role' => 'company_admin', 'is_active' => true]);

        $this->actingAs($user)->withSession(['tenant_id' => $company->id]);

        $response = $this->post(route('company-members.store'), [
            'email' => 'newuser@example.com',
            'role' => 'employee',
        ]);

        $response->assertSessionHasErrors(['email']);
    }
}
