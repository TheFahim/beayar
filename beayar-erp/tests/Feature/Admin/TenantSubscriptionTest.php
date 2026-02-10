<?php

use App\Models\Admin;
use App\Models\Module;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->admin = Admin::factory()->create(['role' => 'super_admin']);
    $this->actingAs($this->admin, 'admin');

    // Setup basic tenant data
    $this->plan = Plan::create([
        'name' => 'Basic Plan',
        'slug' => 'basic',
        'base_price' => 10,
        'limits' => ['quotations' => 10],
        'module_access' => [],
    ]);

    $this->user = User::factory()->create();
    $this->tenant = Tenant::create(['user_id' => $this->user->id, 'name' => 'Test Tenant']);
    $this->company = UserCompany::create([
        'owner_id' => $this->user->id,
        'tenant_id' => $this->tenant->id,
        'name' => 'Test Company',
    ]);

    $this->subscription = Subscription::create([
        'user_id' => $this->user->id, // Legacy compatibility
        'tenant_id' => $this->tenant->id,
        'plan_id' => $this->plan->id,
        'status' => 'active',
        'price' => 10.00,
        'starts_at' => now(),
    ]);

    // Ensure the relationships are set, though create usually handles it.
    // Update user to point to subscription if needed by logic, but Subscription is hasOne on Tenant now.
});

test('admin can view tenant detail page', function () {
    $response = $this->get(route('admin.tenants.show', $this->company));
    $response->assertStatus(200);
    $response->assertSee('Test Company');
    $response->assertSee('Subscription');
});

test('admin can override subscription limits', function () {
    $response = $this->put(route('admin.tenants.subscription.update', $this->company), [
        'status' => 'active',
        'custom_limits' => [
            'quotations' => 100, // Override plan limit of 10
            'sub_companies' => 5,
        ],
    ]);

    $response->assertRedirect();
    $this->subscription->refresh();

    // Cast as array because JSON column
    $limits = $this->subscription->custom_limits;

    expect($limits['quotations'])->toBe(100);
    expect($limits['sub_companies'])->toBe(5);
});

test('admin can override module access', function () {
    Module::create(['name' => 'Extra', 'slug' => 'extra', 'price' => 5]);

    $response = $this->put(route('admin.tenants.subscription.update', $this->company), [
        'module_access' => ['extra'],
    ]);

    $response->assertRedirect();
    $this->subscription->refresh();

    expect($this->subscription->module_access)->toContain('extra');
});

test('admin can change subscription status', function () {
    $response = $this->put(route('admin.tenants.subscription.update', $this->company), [
        'status' => 'cancelled',
    ]);

    $response->assertRedirect();
    $this->subscription->refresh();

    expect($this->subscription->status)->toBe('cancelled');
});
