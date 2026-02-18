<?php

use App\Models\Feature;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->plan = Plan::create([
        'name' => 'Free Plan',
        'slug' => 'free',
        'base_price' => 0,
        'billing_cycle' => 'monthly',
        'description' => 'Free plan for testing',
    ]);

    $this->user = User::factory()->create();
    $this->tenant = Tenant::create(['user_id' => $this->user->id, 'name' => 'Test Tenant']);
    $this->company = \App\Models\TenantCompany::create([
        'tenant_id' => $this->tenant->id,
        'owner_id' => $this->user->id,
        'name' => 'Test Company',
    ]);

    $this->user->current_tenant_company_id = $this->company->id;
    $this->user->save();

    $this->subscription = Subscription::create([
        'user_id' => $this->user->id,
        'tenant_id' => $this->tenant->id,
        'plan_id' => $this->plan->id,
        'status' => 'active',
        'price' => 0,
        'starts_at' => now(),
    ]);

    // Register a test route for middleware testing
    \Illuminate\Support\Facades\Route::post('/test-feature-gate', function () {
        return response()->json(['ok' => true]);
    })->middleware(['web', 'auth', 'feature:test.feature']);
});

test('user with plan that includes a feature has access', function () {
    $feature = Feature::create(['name' => 'Dashboard', 'slug' => 'dashboard', 'is_active' => true]);
    $this->plan->features()->attach($feature);

    expect($this->user->hasFeatureAccess('dashboard'))->toBeTrue();
});

test('user with plan that excludes a feature is denied', function () {
    Feature::create(['name' => 'Export PDF', 'slug' => 'quotations.export_pdf', 'is_active' => true]);

    expect($this->user->hasFeatureAccess('quotations.export_pdf'))->toBeFalse();
});

test('subscription-level override grants access', function () {
    Feature::create(['name' => 'Export PDF', 'slug' => 'quotations.export_pdf', 'is_active' => true]);

    // Feature not assigned to plan, but overridden at subscription level
    $this->subscription->update(['feature_access' => ['quotations.export_pdf']]);

    expect($this->user->fresh()->hasFeatureAccess('quotations.export_pdf'))->toBeTrue();
});

test('custom plan gets access to all features', function () {
    Feature::create(['name' => 'Anything', 'slug' => 'anything', 'is_active' => true]);

    $this->subscription->update(['plan_type' => 'custom']);

    expect($this->user->fresh()->hasFeatureAccess('anything'))->toBeTrue();
});

test('pro plan gets access to all features', function () {
    Feature::create(['name' => 'Anything', 'slug' => 'anything', 'is_active' => true]);

    // Create a pro plan
    $proPlan = Plan::create([
        'name' => 'Pro Plan',
        'slug' => 'pro',
        'base_price' => 49,
        'billing_cycle' => 'monthly',
        'description' => 'Pro plan',
    ]);

    // Update subscription to use Pro plan
    $this->subscription->update([
        'plan_id' => $proPlan->id,
        'plan_type' => 'pro'
    ]);

    // Refresh relationships
    $this->user->refresh();

    // Even though feature is not attached to plan, Pro should have access
    expect($this->user->hasFeatureAccess('anything'))->toBeTrue();
});

test('user without subscription is denied', function () {
    $userNoSub = User::factory()->create();

    expect($userNoSub->hasFeatureAccess('dashboard'))->toBeFalse();
});

test('inactive feature is denied even if assigned to plan', function () {
    $feature = Feature::create(['name' => 'Disabled', 'slug' => 'disabled.feature', 'is_active' => false]);
    $this->plan->features()->attach($feature);

    expect($this->user->hasFeatureAccess('disabled.feature'))->toBeFalse();
});

test('middleware blocks request for missing feature and returns 403 json', function () {
    $this->actingAs($this->user);

    $response = $this->postJson('/test-feature-gate', []);

    $response->assertForbidden();
    $response->assertJson(['feature' => 'test.feature']);
});

test('middleware allows request when feature is granted', function () {
    $feature = Feature::create(['name' => 'Test', 'slug' => 'test.feature', 'is_active' => true]);
    $this->plan->features()->attach($feature);

    $this->actingAs($this->user);

    $response = $this->postJson('/test-feature-gate', []);

    $response->assertSuccessful();
});
