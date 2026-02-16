<?php

namespace Tests\Feature\Tenant;

use App\Models\TenantCompany;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use App\Models\Plan;
use App\Models\Subscription;

class CompanyEditDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function setupUserWithSubscription()
    {
        $user = User::factory()->create();
        $tenant = Tenant::create(['user_id' => $user->id, 'name' => 'Test Tenant']);

        $plan = Plan::firstOrCreate(
            ['slug' => 'pro'],
            [
                'name' => 'Pro',
                'description' => 'Description',
                'base_price' => 10,
                'billing_cycle' => 'monthly',
                'limits' => ['employees' => 5],
                'is_active' => true,
            ]
        );

        Subscription::create([
            'tenant_id' => $tenant->id,
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'price' => 0,
        ]);

        return [$user, $tenant];
    }

    public function test_owner_can_view_edit_page()
    {
        [$user, $tenant] = $this->setupUserWithSubscription();

        $company = TenantCompany::create([
            'tenant_id' => $tenant->id,
            'owner_id' => $user->id,
            'name' => 'My Company',
            'organization_type' => TenantCompany::TYPE_INDEPENDENT,
            'status' => 'active',
        ]);

        // Attach user as Admin (usually happens on create)
        $company->members()->attach($user->id, ['role' => 'company_admin', 'is_active' => true]);

        $response = $this->actingAs($user)
            ->withSession(['tenant_id' => $company->id])
            ->get(route('tenant.user-companies.edit', $company->id));

        $response->assertStatus(200);
        $response->assertSee('Edit Workspace');
        $response->assertSee('My Company');
    }

    public function test_owner_can_update_company()
    {
        [$user, $tenant] = $this->setupUserWithSubscription();

        $company = TenantCompany::create([
            'tenant_id' => $tenant->id,
            'owner_id' => $user->id,
            'name' => 'Old Name',
            'organization_type' => TenantCompany::TYPE_INDEPENDENT,
            'status' => 'active',
        ]);
        $company->members()->attach($user->id, ['role' => 'company_admin', 'is_active' => true]);

        $response = $this->actingAs($user)
            ->withSession(['tenant_id' => $company->id])
            ->put(route('tenant.user-companies.update', $company->id), [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);

        $response->assertRedirect(route('tenant.user-companies.index'));
        $this->assertDatabaseHas('tenant_companies', [
            'id' => $company->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);
    }

    public function test_non_owner_cannot_update_company()
    {
        [$owner, $tenant] = $this->setupUserWithSubscription();

        $company = TenantCompany::create([
            'tenant_id' => $tenant->id,
            'owner_id' => $owner->id,
            'name' => 'Company',
            'organization_type' => TenantCompany::TYPE_INDEPENDENT,
            'status' => 'active',
        ]);
        $company->members()->attach($owner->id, ['role' => 'company_admin', 'is_active' => true]);

        // Create another user with subscription so they don't get redirected
        [$otherUser, $otherTenant] = $this->setupUserWithSubscription();

        // Create a company for other user so they have a context
        $otherCompany = TenantCompany::create([
            'tenant_id' => $otherTenant->id,
            'owner_id' => $otherUser->id,
            'name' => 'Other Company',
            'organization_type' => TenantCompany::TYPE_INDEPENDENT,
            'status' => 'active',
        ]);
        $otherCompany->members()->attach($otherUser->id, ['role' => 'company_admin', 'is_active' => true]);

        $response = $this->actingAs($otherUser)
            ->withSession(['tenant_id' => $otherCompany->id])
            ->put(route('tenant.user-companies.update', $company->id), [
            'name' => 'Hacked Name',
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('tenant_companies', [
            'id' => $company->id,
            'name' => 'Company',
        ]);
    }

    public function test_owner_can_delete_company()
    {
        [$user, $tenant] = $this->setupUserWithSubscription();

        $company = TenantCompany::create([
            'tenant_id' => $tenant->id,
            'owner_id' => $user->id,
            'name' => 'To Delete',
            'organization_type' => TenantCompany::TYPE_INDEPENDENT,
            'status' => 'active',
        ]);
        $company->members()->attach($user->id, ['role' => 'company_admin', 'is_active' => true]);

        $response = $this->actingAs($user)
            ->withSession(['tenant_id' => $company->id])
            ->delete(route('tenant.user-companies.destroy', $company->id));

        $response->assertRedirect(route('tenant.user-companies.index'));
        $this->assertDatabaseMissing('tenant_companies', ['id' => $company->id]);
    }

    public function test_non_owner_cannot_delete_company()
    {
        [$owner, $tenant] = $this->setupUserWithSubscription();

        $company = TenantCompany::create([
            'tenant_id' => $tenant->id,
            'owner_id' => $owner->id,
            'name' => 'Cannot Delete',
            'organization_type' => TenantCompany::TYPE_INDEPENDENT,
            'status' => 'active',
        ]);
        $company->members()->attach($owner->id, ['role' => 'company_admin', 'is_active' => true]);

        [$otherUser, $otherTenant] = $this->setupUserWithSubscription();

        $otherCompany = TenantCompany::create([
            'tenant_id' => $otherTenant->id,
            'owner_id' => $otherUser->id,
            'name' => 'Other Company',
            'organization_type' => TenantCompany::TYPE_INDEPENDENT,
            'status' => 'active',
        ]);
        $otherCompany->members()->attach($otherUser->id, ['role' => 'company_admin', 'is_active' => true]);

        $response = $this->actingAs($otherUser)
            ->withSession(['tenant_id' => $otherCompany->id])
            ->delete(route('tenant.user-companies.destroy', $company->id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('tenant_companies', ['id' => $company->id]);
    }
}
