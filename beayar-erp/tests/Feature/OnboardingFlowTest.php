<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_plan_creates_default_plan_if_missing()
    {
        // Ensure no plans exist
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        Plan::truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('onboarding.plan.store'), [
            'plan_type' => 'free',
        ]);

        $response->assertRedirect(route('onboarding.company'));

        // Assert Plan was created
        $this->assertDatabaseHas('plans', ['slug' => 'free']);

        // Assert Tenant was created
        $this->assertNotNull($user->fresh()->tenant);

        // Assert Subscription was created
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'plan_type' => 'free',
            'price' => 0,
        ]);
    }

    public function test_store_plan_uses_existing_plan()
    {
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $plan = Plan::create([
            'name' => 'Existing Free',
            'slug' => 'free',
            'description' => 'Description',
            'base_price' => 0,
            'billing_cycle' => 'monthly',
        ]);

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('onboarding.plan.store'), [
            'plan_type' => 'free',
        ]);

        $response->assertRedirect(route('onboarding.company'));

        $this->assertDatabaseCount('plans', 1);
        $this->assertEquals($plan->id, $user->fresh()->tenant->subscription->plan_id);
    }
}
