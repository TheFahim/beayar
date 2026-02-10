<?php

use App\Models\Admin;
use App\Models\Module;
use App\Models\Plan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->admin = Admin::factory()->create(['role' => 'super_admin']);
    $this->actingAs($this->admin, 'admin');

    // Seed some modules for testing
    Module::create(['name' => 'CRM', 'slug' => 'crm', 'price' => 10]);
    Module::create(['name' => 'HRM', 'slug' => 'hrm', 'price' => 15]);
});

test('admin can view plans page', function () {
    $response = $this->get(route('admin.plans.index'));
    $response->assertStatus(200);
});

test('admin can create a new plan with limits and modules', function () {
    $response = $this->post(route('admin.plans.store'), [
        'name' => 'Gold Plan',
        'slug' => 'gold-plan',
        'base_price' => 99.00,
        'billing_cycle' => 'monthly',
        'limits' => [
            'sub_companies' => 5,
            'quotations' => -1, // Unlimited
            'employees' => 10,
        ],
        'module_access' => ['crm', 'hrm'],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('plans', [
        'name' => 'Gold Plan',
        'slug' => 'gold-plan',
        'base_price' => 99.00,
    ]);

    $plan = Plan::where('slug', 'gold-plan')->first();
    expect($plan->limits['sub_companies'])->toBe(5);
    expect($plan->limits['quotations'])->toBe(-1);
    expect($plan->module_access)->toContain('crm', 'hrm');
});

test('admin can update an existing plan', function () {
    $plan = Plan::create([
        'name' => 'Silver Plan',
        'slug' => 'silver-plan',
        'base_price' => 49.00,
        'billing_cycle' => 'monthly',
        'limits' => ['sub_companies' => 1],
        'module_access' => ['crm'],
    ]);

    $response = $this->put(route('admin.plans.update', $plan), [
        'name' => 'Platinum Plan',
        'base_price' => 149.00,
        'billing_cycle' => 'yearly',
        'limits' => [
            'sub_companies' => 10,
            'quotations' => 500,
        ],
        'module_access' => ['crm', 'hrm'], // Added HRM
    ]);

    $response->assertRedirect();

    $plan->refresh();
    expect($plan->name)->toBe('Platinum Plan');
    expect($plan->base_price)->toBe('149.00');
    expect($plan->billing_cycle)->toBe('yearly');
    expect($plan->limits['sub_companies'])->toBe(10);
    expect($plan->module_access)->toContain('crm', 'hrm');
});

test('admin can deactivate a plan', function () {
    $plan = Plan::create([
        'name' => 'Old Plan',
        'slug' => 'old-plan',
        'base_price' => 10.00,
        'billing_cycle' => 'monthly',
        'is_active' => true,
    ]);

    $response = $this->delete(route('admin.plans.destroy', $plan));

    $response->assertRedirect();

    $plan->refresh();
    expect($plan->is_active)->toBeFalsy(); // Should be false (0)
});
