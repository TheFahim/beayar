<?php

use App\Models\Admin;
use App\Models\Module;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->admin = Admin::factory()->create(['role' => 'super_admin']);
    $this->actingAs($this->admin, 'admin');
});

test('admin can view modules page', function () {
    $response = $this->get(route('admin.modules.index'));
    $response->assertStatus(200);
});

test('admin can create a new module', function () {
    $response = $this->post(route('admin.modules.store'), [
        'name' => 'Inventory',
        'slug' => 'inventory',
        'price' => 29.00,
        'description' => 'Inventory management system',
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('modules', [
        'name' => 'Inventory',
        'slug' => 'inventory',
        'price' => 29.00,
    ]);
});

test('admin can update a module', function () {
    $module = Module::create([
        'name' => 'Old Name',
        'slug' => 'old-slug',
        'price' => 10.00,
    ]);

    $response = $this->put(route('admin.modules.update', $module), [
        'name' => 'New Name',
        'slug' => 'new-slug',
        'price' => 15.00,
        'description' => 'Updated description',
    ]);

    $response->assertRedirect();

    $module->refresh();
    expect($module->name)->toBe('New Name');
    expect($module->slug)->toBe('new-slug');
    expect($module->price)->toBe('15.00');
});

test('admin can delete a module', function () {
    $module = Module::create([
        'name' => 'To Delete',
        'slug' => 'delete-me',
        'price' => 5.00,
    ]);

    $response = $this->delete(route('admin.modules.destroy', $module));

    $response->assertRedirect();
    $this->assertDatabaseMissing('modules', ['id' => $module->id]);
});

test('module slug must be unique', function () {
    Module::create(['name' => 'M1', 'slug' => 'dupe', 'price' => 10]);

    $response = $this->post(route('admin.modules.store'), [
        'name' => 'M2',
        'slug' => 'dupe',
        'price' => 10,
    ]);

    $response->assertSessionHasErrors('slug');
});
