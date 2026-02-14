<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\TenantCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupOfIndustriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_company()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'company_name' => 'Test Company',
        ]);

        $response->assertRedirect(route('tenant.dashboard'));

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
        $this->assertDatabaseHas('tenant_companies', [
            'name' => 'Test Company',
            'organization_type' => TenantCompany::TYPE_INDEPENDENT,
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $company = TenantCompany::where('name', 'Test Company')->first();

        $this->assertEquals($user->id, $company->owner_id);
    }

    protected function setupUserWithSubscription()
    {
        $user = User::factory()->create();
        $plan = \App\Models\Plan::forceCreate([
            'name' => 'Basic',
            'slug' => 'basic',
            'base_price' => 10,
            'billing_cycle' => 'monthly',
            'limits' => [],
        ]);
        \App\Models\Subscription::forceCreate([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'ends_at' => now()->addDays(30),
            'price' => 10.00,
        ]);

        return $user;
    }

    public function test_holding_company_cannot_create_operational_data()
    {
        $user = $this->setupUserWithSubscription();
        $holdingCompany = TenantCompany::create([
            'name' => 'Holding Corp',
            'owner_id' => $user->id,
            'organization_type' => TenantCompany::TYPE_HOLDING,
        ]);

        $this->actingAs($user)
            ->withSession(['tenant_id' => $holdingCompany->id]);

        // Try to create a product (assuming route is tenant.products.store)
        $response = $this->post(route('tenant.products.store'), [
            'name' => 'Test Product',
            // ... other fields
        ]);

        $response->assertForbidden();
    }

    public function test_subsidiary_can_create_operational_data()
    {
        $user = $this->setupUserWithSubscription();
        $holdingCompany = TenantCompany::create([
            'name' => 'Holding Corp',
            'owner_id' => $user->id,
            'organization_type' => TenantCompany::TYPE_HOLDING,
        ]);

        $subsidiary = TenantCompany::create([
            'name' => 'Subsidiary Inc',
            'owner_id' => $user->id,
            'parent_company_id' => $holdingCompany->id,
            'organization_type' => TenantCompany::TYPE_SUBSIDIARY,
        ]);

        $this->actingAs($user)
            ->withSession(['tenant_id' => $subsidiary->id]);

        // Try to create a product
        // Note: I need valid data for product creation or expect validation error (422), not 403.
        $response = $this->post(route('tenant.products.store'), [
            'name' => 'Test Product',
        ]);

        // If validation fails, it's 302 or 422, but definitely not 403.
        $this->assertNotEquals(403, $response->status());
    }

    public function test_aggregation_helper()
    {
        $user = $this->setupUserWithSubscription();
        $holding = TenantCompany::create([
            'name' => 'Holding',
            'owner_id' => $user->id,
            'organization_type' => TenantCompany::TYPE_HOLDING,
        ]);
        $sub1 = TenantCompany::create([
            'name' => 'Sub 1',
            'owner_id' => $user->id,
            'parent_company_id' => $holding->id,
            'organization_type' => TenantCompany::TYPE_SUBSIDIARY,
        ]);
        $sub2 = TenantCompany::create([
            'name' => 'Sub 2',
            'owner_id' => $user->id,
            'parent_company_id' => $holding->id,
            'organization_type' => TenantCompany::TYPE_SUBSIDIARY,
        ]);

        $ids = $holding->getGroupIds();

        $this->assertContains($holding->id, $ids);
        $this->assertContains($sub1->id, $ids);
        $this->assertContains($sub2->id, $ids);
        $this->assertCount(3, $ids);
    }
}
