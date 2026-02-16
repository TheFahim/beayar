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

    public function test_owner_can_see_available_users_from_other_companies()
    {
        // Setup Tenant Owner
        $owner = User::factory()->create();
        $tenant = Tenant::create(['user_id' => $owner->id, 'name' => 'Tenant']);

        // Create Plan & Subscription (ensure limits allow)
        $plan = Plan::create([
            'name' => 'Pro',
            'slug' => 'pro',
            'description' => 'Test Plan',
            'base_price' => 10,
            'limits' => ['sub_companies' => 2, 'employees' => 5],
            'is_active' => true,
            'billing_cycle' => 'monthly'
        ]);
        Subscription::create(['tenant_id' => $tenant->id, 'user_id' => $owner->id, 'plan_id' => $plan->id, 'status' => 'active', 'price' => 0]);

        // Create Company A & B
        $companyA = TenantCompany::create(['tenant_id' => $tenant->id, 'owner_id' => $owner->id, 'name' => 'Company A', 'status' => 'active']);
        $companyB = TenantCompany::create(['tenant_id' => $tenant->id, 'owner_id' => $owner->id, 'name' => 'Company B', 'status' => 'active']);

        // Attach owner to both
        $companyA->members()->attach($owner->id, ['role' => 'company_admin', 'is_active' => true]);
        $companyB->members()->attach($owner->id, ['role' => 'company_admin', 'is_active' => true]);

        // Create User 1 in Company A
        $user1 = User::factory()->create(['email' => 'user1@example.com']);
        $companyA->members()->attach($user1->id, ['role' => 'employee', 'is_active' => true]);

        // Act as Owner in Company B context
        $this->actingAs($owner)->withSession(['tenant_id' => $companyB->id]);

        // Visit Company B members page
        $response = $this->get(route('company-members.index'));

        // Assert User 1 is in availableUsers
        $response->assertStatus(200);
        $response->assertViewHas('availableUsers', function($users) use ($user1) {
            return $users->contains($user1);
        });

        // Now add User 1 to Company B
        $companyB->members()->attach($user1->id, ['role' => 'employee', 'is_active' => true]);

        // Visit again
        $response = $this->get(route('company-members.index'));

        // Assert User 1 is NOT in availableUsers
        $response->assertViewHas('availableUsers', function($users) use ($user1) {
            return !$users->contains($user1);
        });
    }

    public function test_owner_can_update_member_details()
    {
        $owner = User::factory()->create();
        $tenant = Tenant::create(['user_id' => $owner->id, 'name' => 'Tenant']);
        $plan = Plan::create(['name' => 'Pro', 'slug' => 'pro', 'base_price' => 10, 'limits' => ['sub_companies' => 2, 'employees' => 5], 'is_active' => true, 'billing_cycle' => 'monthly', 'description' => 'Test']);
        Subscription::create(['tenant_id' => $tenant->id, 'user_id' => $owner->id, 'plan_id' => $plan->id, 'status' => 'active', 'price' => 0]);

        $company = TenantCompany::create(['tenant_id' => $tenant->id, 'owner_id' => $owner->id, 'name' => 'Company', 'status' => 'active']);
        $company->members()->attach($owner->id, ['role' => 'company_admin', 'is_active' => true]);

        $employee = User::factory()->create(['name' => 'Old Name', 'email' => 'employee@example.com']);
        $company->members()->attach($employee->id, ['role' => 'employee', 'is_active' => true, 'joined_at' => now()->subYear()]);

        $this->actingAs($owner)->withSession(['tenant_id' => $company->id]);

        $newDate = now()->subMonth()->format('Y-m-d');

        $response = $this->put(route('company-members.update', $employee->id), [
            'name' => 'New Name',
            'email' => 'newemail@example.com',
            'phone' => '1234567890',
            'role' => 'company_admin',
            'is_active' => false,
            'joined_at' => $newDate,
        ]);

        $response->assertSessionHas('success');

        $employee->refresh();
        $this->assertEquals('New Name', $employee->name);
        $this->assertEquals('newemail@example.com', $employee->email);
        $this->assertEquals('1234567890', $employee->phone);

        $memberPivot = $company->members()->where('user_id', $employee->id)->first()->pivot;
        $this->assertEquals('company_admin', $memberPivot->role);
        $this->assertEquals(0, $memberPivot->is_active);
        $this->assertEquals($newDate, \Carbon\Carbon::parse($memberPivot->joined_at)->format('Y-m-d'));
    }

    public function test_owner_cannot_update_member_email_to_existing_email()
    {
        $owner = User::factory()->create();
        $tenant = Tenant::create(['user_id' => $owner->id, 'name' => 'Tenant']);
        $plan = Plan::create(['name' => 'Pro', 'slug' => 'pro', 'base_price' => 10, 'limits' => ['sub_companies' => 2, 'employees' => 5], 'is_active' => true, 'billing_cycle' => 'monthly', 'description' => 'Test']);
        Subscription::create(['tenant_id' => $tenant->id, 'user_id' => $owner->id, 'plan_id' => $plan->id, 'status' => 'active', 'price' => 0]);

        $company = TenantCompany::create(['tenant_id' => $tenant->id, 'owner_id' => $owner->id, 'name' => 'Company', 'status' => 'active']);
        $company->members()->attach($owner->id, ['role' => 'company_admin', 'is_active' => true]);

        $employee1 = User::factory()->create(['email' => 'employee1@example.com']);
        $employee2 = User::factory()->create(['email' => 'employee2@example.com']);
        $company->members()->attach($employee1->id, ['role' => 'employee', 'is_active' => true]);
        $company->members()->attach($employee2->id, ['role' => 'employee', 'is_active' => true]);

        $this->actingAs($owner)->withSession(['tenant_id' => $company->id]);

        $response = $this->put(route('company-members.update', $employee1->id), [
            'name' => 'New Name',
            'email' => 'employee2@example.com', // Existing email
            'phone' => '1234567890',
            'role' => 'employee',
            'is_active' => true,
        ]);

        $response->assertSessionHasErrors('email');
    }
}
