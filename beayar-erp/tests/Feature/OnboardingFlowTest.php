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

    public function test_plan_page_displays_all_plans()
    {
        $this->seed(\Database\Seeders\PlansSeeder::class);
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('onboarding.plan'));
        $response->assertStatus(200);
        $response->assertSee('Free');
        $response->assertSee('Pro');
        $response->assertSee('Pro Plus');
        $response->assertSee('Custom');
    }

    public function test_user_can_select_pro_plan()
    {
        $this->seed(\Database\Seeders\PlansSeeder::class);
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post(route('onboarding.plan.store'), [
            'plan_type' => 'pro',
        ]);
        $response->assertRedirect(route('onboarding.company'));
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'plan_type' => 'pro',
        ]);
    }
}
