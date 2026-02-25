<?php

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantCompany;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function createOwnerWithCompany(): array
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

    $company = TenantCompany::create([
        'tenant_id' => $tenant->id,
        'owner_id' => $user->id,
        'name' => 'Settings Test Company',
        'organization_type' => TenantCompany::TYPE_INDEPENDENT,
        'status' => 'active',
    ]);

    $company->members()->attach($user->id, ['role' => 'company_admin', 'is_active' => true]);

    $user->update(['current_tenant_company_id' => $company->id]);

    return [$user, $company, $tenant];
}

it('allows owner to view settings page', function () {
    [$user, $company] = createOwnerWithCompany();

    $response = $this->actingAs($user)
        ->withSession(['tenant_id' => $company->id])
        ->get(route('tenant.company-settings.edit', $company->id));

    $response->assertSuccessful();
    $response->assertSee('Company Settings');
    $response->assertSee($company->name);
});

it('allows owner to update settings with valid data', function () {
    [$user, $company] = createOwnerWithCompany();

    $response = $this->actingAs($user)
        ->withSession(['tenant_id' => $company->id])
        ->put(route('tenant.company-settings.update', $company->id), [
            'date_format' => 'Y-m-d',
            'currency' => 'USD',
            'currency_symbol' => '$',
            'quotation_prefix' => 'QTN-',
            'quotation_number_format' => 'QTN-{YYYY}-{SEQUENCE}',
            'vat_percentages' => [0, 5, 10],
            'vat_default_percentage' => 10,
        ]);

    $response->assertRedirect(route('tenant.company-settings.edit', $company->id));

    $company->refresh();
    $settings = $company->getSettings();
    expect($settings['date_format'])->toBe('Y-m-d');
    expect($settings['currency'])->toBe('USD');
    expect($settings['currency_symbol'])->toBe('$');
    expect($settings['quotation_prefix'])->toBe('QTN-');
    expect($settings['quotation_number_format'])->toBe('QTN-{YYYY}-{SEQUENCE}');
});

it('rejects invalid currency code', function () {
    [$user, $company] = createOwnerWithCompany();

    $response = $this->actingAs($user)
        ->withSession(['tenant_id' => $company->id])
        ->put(route('tenant.company-settings.update', $company->id), [
            'date_format' => 'd-m-Y',
            'currency' => 'INVALID',
            'currency_symbol' => '?',
            'quotation_prefix' => '',
            'quotation_number_format' => '{CUSTOMER_NO}-{YY}-{SEQUENCE}',
            'vat_percentages' => [0, 5, 10],
            'vat_default_percentage' => 10,
        ]);

    $response->assertSessionHasErrors('currency');
});

it('rejects invalid date format', function () {
    [$user, $company] = createOwnerWithCompany();

    $response = $this->actingAs($user)
        ->withSession(['tenant_id' => $company->id])
        ->put(route('tenant.company-settings.update', $company->id), [
            'date_format' => 'INVALID_FORMAT',
            'currency' => 'BDT',
            'currency_symbol' => '৳',
            'quotation_prefix' => '',
            'quotation_number_format' => '{CUSTOMER_NO}-{YY}-{SEQUENCE}',
            'vat_percentages' => [0, 5, 10],
            'vat_default_percentage' => 10,
        ]);

    $response->assertSessionHasErrors('date_format');
});

it('prevents non-owner non-admin from updating settings', function () {
    [$owner, $company, $tenant] = createOwnerWithCompany();

    $otherUser = User::factory()->create();
    $otherTenant = Tenant::create(['user_id' => $otherUser->id, 'name' => 'Other']);

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
        'tenant_id' => $otherTenant->id,
        'user_id' => $otherUser->id,
        'plan_id' => $plan->id,
        'status' => 'active',
        'starts_at' => now(),
        'price' => 0,
    ]);

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
        ->put(route('tenant.company-settings.update', $company->id), [
            'date_format' => 'Y-m-d',
            'currency' => 'USD',
            'currency_symbol' => '$',
            'quotation_prefix' => 'HACK-',
            'quotation_number_format' => 'HACK-{SEQUENCE}',
            'vat_percentages' => [0, 5, 10],
            'vat_default_percentage' => 10,
        ]);

    $response->assertForbidden();
});

it('returns settings options via JSON endpoint', function () {
    [$user, $company] = createOwnerWithCompany();

    $response = $this->actingAs($user)
        ->withSession(['tenant_id' => $company->id])
        ->getJson(route('tenant.company-settings.options'));

    $response->assertSuccessful();
    $response->assertJsonStructure(['currencies', 'date_formats']);
    $response->assertJsonPath('currencies.BDT', '৳');
    $response->assertJsonPath('currencies.USD', '$');
});

it('persists settings after update', function () {
    [$user, $company] = createOwnerWithCompany();

    // First update
    $this->actingAs($user)
        ->withSession(['tenant_id' => $company->id])
        ->put(route('tenant.company-settings.update', $company->id), [
            'date_format' => 'm/d/Y',
            'currency' => 'EUR',
            'currency_symbol' => '€',
            'quotation_prefix' => 'INV-',
            'quotation_number_format' => 'INV/{MM}/{YY}/{SEQUENCE}',
            'vat_percentages' => [0, 5, 8],
            'vat_default_percentage' => 8,
        ]);

    // Reload and verify
    $freshCompany = TenantCompany::find($company->id);
    $settings = $freshCompany->getSettings();

    expect($settings['date_format'])->toBe('m/d/Y');
    expect($settings['currency'])->toBe('EUR');
    expect($settings['quotation_number_format'])->toBe('INV/{MM}/{YY}/{SEQUENCE}');
});
