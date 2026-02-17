<?php

use App\Models\Admin;
use App\Models\Feature;
use App\Models\Module;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->admin = Admin::factory()->create(['role' => 'super_admin']);
    $this->actingAs($this->admin, 'admin');
});

test('admin can view features page', function () {
    $response = $this->get(route('admin.features.index'));
    $response->assertStatus(200);
});

test('admin can create a feature', function () {
    $response = $this->post(route('admin.features.store'), [
        'name' => 'Export PDF',
        'slug' => 'quotations.export_pdf',
        'description' => 'Export quotations as PDF',
        'is_active' => true,
        'sort_order' => 0,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('features', [
        'name' => 'Export PDF',
        'slug' => 'quotations.export_pdf',
    ]);
});

test('admin can create a feature with module association', function () {
    $module = Module::create(['name' => 'Quotations', 'slug' => 'quotations', 'price' => 0, 'description' => 'Quotation module']);

    $response = $this->post(route('admin.features.store'), [
        'name' => 'Revisions',
        'slug' => 'quotations.revisions',
        'module_id' => $module->id,
    ]);

    $response->assertRedirect();

    $feature = Feature::where('slug', 'quotations.revisions')->first();
    expect($feature->module_id)->toBe($module->id);
});

test('admin can update a feature', function () {
    $feature = Feature::create([
        'name' => 'Old Name',
        'slug' => 'old.slug',
    ]);

    $response = $this->put(route('admin.features.update', $feature), [
        'name' => 'New Name',
        'slug' => 'new.slug',
        'description' => 'Updated description',
        'is_active' => false,
    ]);

    $response->assertRedirect();

    $feature->refresh();
    expect($feature->name)->toBe('New Name');
    expect($feature->slug)->toBe('new.slug');
    expect($feature->is_active)->toBeFalse();
});

test('admin can delete a feature', function () {
    $feature = Feature::create([
        'name' => 'To Delete',
        'slug' => 'delete.me',
    ]);

    $response = $this->delete(route('admin.features.destroy', $feature));

    $response->assertRedirect();
    $this->assertDatabaseMissing('features', ['id' => $feature->id]);
});

test('feature slug must be unique', function () {
    Feature::create(['name' => 'F1', 'slug' => 'dupe.slug']);

    $response = $this->post(route('admin.features.store'), [
        'name' => 'F2',
        'slug' => 'dupe.slug',
    ]);

    $response->assertSessionHasErrors('slug');
});

test('admin can sync features to a plan', function () {
    $plan = \App\Models\Plan::create([
        'name' => 'Test Plan',
        'slug' => 'test-plan',
        'base_price' => 10,
        'billing_cycle' => 'monthly',
        'description' => 'Test plan for feature sync',
    ]);

    $f1 = Feature::create(['name' => 'Feature 1', 'slug' => 'feat.1']);
    $f2 = Feature::create(['name' => 'Feature 2', 'slug' => 'feat.2']);

    $response = $this->put(route('admin.plans.features.sync', $plan), [
        'feature_ids' => [$f1->id, $f2->id],
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $plan->refresh();
    expect($plan->features)->toHaveCount(2);
    expect($plan->features->pluck('id')->toArray())->toContain($f1->id, $f2->id);
});
