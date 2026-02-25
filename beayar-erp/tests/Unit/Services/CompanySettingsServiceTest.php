<?php

use App\Models\Customer;
use App\Models\CustomerCompany;
use App\Models\Quotation;
use App\Models\TenantCompany;
use App\Services\CompanySettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function createCompanyWithSettings(array $settings = []): TenantCompany
{
    $company = TenantCompany::factory()->create([
        'settings' => array_merge(TenantCompany::DEFAULT_SETTINGS, $settings),
    ]);

    return $company;
}

function createCustomerForCompany(TenantCompany $company, string $customerNo = 'ACME'): Customer
{
    $customerCompany = CustomerCompany::create([
        'tenant_company_id' => $company->id,
        'name' => 'Test Customer Company',
        'company_code' => 'TCC',
        'address' => 'Test Address',
    ]);

    return Customer::create([
        'tenant_company_id' => $company->id,
        'customer_company_id' => $customerCompany->id,
        'name' => 'Test Customer',
        'customer_no' => $customerNo,
        'address' => 'Test Address',
    ]);
}

function createDefaultStatus(TenantCompany $company): int
{
    return \App\Models\QuotationStatus::create([
        'tenant_company_id' => $company->id,
        'name' => 'Draft',
        'color' => 'gray',
        'is_default' => true,
    ])->id;
}

it('generates quotation number with default format', function () {
    $service = app(CompanySettingsService::class);
    $company = createCompanyWithSettings();
    $customer = createCustomerForCompany($company, 'ACME');

    $result = $service->generateQuotationNumber($company, $customer);

    $expectedYear = date('y');
    expect($result)->toBe("ACME-{$expectedYear}-001");
});

it('generates quotation number with custom prefix format', function () {
    $service = app(CompanySettingsService::class);
    $company = createCompanyWithSettings([
        'quotation_prefix' => 'QTN-',
        'quotation_number_format' => '{PREFIX}{YYYY}-{SEQUENCE}',
    ]);
    $customer = createCustomerForCompany($company, 'ACME');

    $result = $service->generateQuotationNumber($company, $customer);

    $expectedYear = date('Y');
    expect($result)->toBe("QTN-{$expectedYear}-001");
});

it('generates quotation number with invoice style format', function () {
    $service = app(CompanySettingsService::class);
    $company = createCompanyWithSettings([
        'quotation_number_format' => 'INV/{MM}/{YY}/{SEQUENCE}',
    ]);
    $customer = createCustomerForCompany($company, 'ACME');

    $result = $service->generateQuotationNumber($company, $customer);

    $expectedMonth = date('m');
    $expectedYear = date('y');
    expect($result)->toBe("INV/{$expectedMonth}/{$expectedYear}/001");
});

it('increments sequence correctly', function () {
    $service = app(CompanySettingsService::class);
    $company = createCompanyWithSettings();
    $customer = createCustomerForCompany($company, 'ACME');

    $expectedYear = date('y');

    $statusId = createDefaultStatus($company);

    // Create existing quotation
    Quotation::create([
        'tenant_company_id' => $company->id,
        'customer_id' => $customer->id,
        'user_id' => $company->owner_id,
        'quotation_no' => "ACME-{$expectedYear}-001",
        'reference_no' => "ACME-{$expectedYear}-001",
        'status_id' => $statusId,
        'ship_to' => 'Test Ship To',
    ]);

    $result = $service->generateQuotationNumber($company, $customer);
    expect($result)->toBe("ACME-{$expectedYear}-002");
});

it('handles sequence extraction when sequence is not at the end', function () {
    $service = app(CompanySettingsService::class);
    $company = createCompanyWithSettings([
        'quotation_number_format' => '{SEQUENCE}-{YY}',
    ]);
    $customer = createCustomerForCompany($company, 'ACME');

    $expectedYear = date('y');

    $statusId = createDefaultStatus($company);

    // Create existing quotation 001-26
    Quotation::create([
        'tenant_company_id' => $company->id,
        'customer_id' => $customer->id,
        'user_id' => $company->owner_id,
        'quotation_no' => "001-{$expectedYear}",
        'reference_no' => "001-{$expectedYear}",
        'status_id' => $statusId,
        'ship_to' => 'Test Ship To',
    ]);

    $result = $service->generateQuotationNumber($company, $customer);
    expect($result)->toBe("002-{$expectedYear}");
});

it('handles ID and SEQUENCE correctly when ID comes first', function () {
    $service = app(CompanySettingsService::class);
    $company = createCompanyWithSettings([
        'quotation_number_format' => '{ID}-{SEQUENCE}',
    ]);
    $customer = createCustomerForCompany($company, 'ACME');

    // Assuming ID 1 exists (created above in other tests? No, RefreshDatabase clears it)
    // We need to make sure the ID logic works.
    // The ID logic in service uses Quotation::count() + 1.

    $statusId = createDefaultStatus($company);

    // Create existing quotation
    Quotation::create([
        'tenant_company_id' => $company->id,
        'customer_id' => $customer->id,
        'user_id' => $company->owner_id,
        'quotation_no' => "1-001",
        'reference_no' => "1-001",
        'status_id' => $statusId,
        'ship_to' => 'Test Ship To',
    ]);

    // Now count is 1. Next ID should be 2.
    // Sequence should be 2.

    $result = $service->generateQuotationNumber($company, $customer);
    expect($result)->toBe("2-002");
});

it('merges defaults with saved settings', function () {
    $service = app(CompanySettingsService::class);
    $company = TenantCompany::factory()->create([
        'settings' => ['currency' => 'USD'],
    ]);

    $settings = $service->getSettings($company);

    // Saved value is used
    expect($settings['currency'])->toBe('USD');
    // Defaults are filled in
    expect($settings['date_format'])->toBe('d-m-Y');
    expect($settings['quotation_number_format'])->toBe('{CUSTOMER_NO}-{YY}-{SEQUENCE}');
    expect($settings['quotation_currencies'])->toBe(['BDT', 'USD', 'EUR', 'INR', 'RMB']);
});

it('returns available currencies', function () {
    $service = app(CompanySettingsService::class);
    $currencies = $service->getAvailableCurrencies();

    expect($currencies)->toHaveKeys(['BDT', 'USD', 'EUR', 'INR', 'RMB']);
    expect($currencies['BDT'])->toBe('৳');
    expect($currencies['USD'])->toBe('$');
});

it('returns available date formats', function () {
    $service = app(CompanySettingsService::class);
    $formats = $service->getAvailableDateFormats();

    expect($formats)->toHaveKeys(['d-m-Y', 'Y-m-d', 'm/d/Y', 'd M, Y', 'd/m/Y']);
});

it('updates settings correctly', function () {
    $service = app(CompanySettingsService::class);
    $company = createCompanyWithSettings();

    $updated = $service->updateSettings($company, [
        'currency' => 'EUR',
        'currency_symbol' => '€',
    ]);

    expect($updated->getSettings()['currency'])->toBe('EUR');
    expect($updated->getSettings()['currency_symbol'])->toBe('€');
    // Other defaults remain
    expect($updated->getSettings()['date_format'])->toBe('d-m-Y');
});
