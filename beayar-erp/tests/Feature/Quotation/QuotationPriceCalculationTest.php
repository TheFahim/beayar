<?php

namespace Tests\Feature\Quotation;

use App\Enums\FeatureEnum;
use App\Models\Customer;
use App\Models\CustomerCompany;
use App\Models\Feature;
use App\Models\Plan;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationStatus;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantCompany;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationPriceCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $company;
    protected $tenant;
    protected $customer;
    protected $product;
    protected $plan;
    protected $subscription;
    protected $priceCalcFeature;

    protected function setUp(): void
    {
        parent::setUp();

        // Create User
        $this->user = User::factory()->create([
            'current_tenant_company_id' => null,
            'current_scope' => 'company',
        ]);

        // Create Tenant & Company
        $this->tenant = Tenant::create(['user_id' => $this->user->id, 'name' => 'Test Tenant']);
        $this->company = TenantCompany::create([
            'name' => 'Test Company',
            'email' => 'test@company.com',
            'owner_id' => $this->user->id,
            'tenant_id' => $this->tenant->id,
        ]);

        $this->user->update(['current_tenant_company_id' => $this->company->id]);

        // Create Plan & Subscription
        $this->plan = Plan::create([
            'name' => 'Test Plan',
            'slug' => 'test-plan',
            'description' => 'Test Plan Description',
            'base_price' => 100,
            'billing_cycle' => 'monthly',
        ]);

        $this->subscription = Subscription::create([
            'tenant_id' => $this->tenant->id,
            'plan_id' => $this->plan->id,
            'status' => 'active',
            'price' => 100,
            'starts_at' => now(),
        ]);

        // Create Customer
        $customerCompany = CustomerCompany::create([
            'tenant_company_id' => $this->company->id,
            'name' => 'Test Customer Company',
            'address' => 'Test Address',
        ]);

        $this->customer = Customer::create([
            'tenant_company_id' => $this->company->id,
            'customer_company_id' => $customerCompany->id,
            'customer_no' => 'C-0001',
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'address' => 'Test Address',
        ]);

        // Create Default Status
        QuotationStatus::firstOrCreate([
            'name' => 'Draft',
            'tenant_company_id' => $this->company->id,
        ], ['is_default' => true]);

        // Create Product
        $this->product = Product::create([
            'tenant_company_id' => $this->company->id,
            'name' => 'Test Product',
            'unit' => 'pcs',
        ]);

        // Create Feature
        $this->priceCalcFeature = Feature::firstOrCreate([
            'slug' => FeatureEnum::MODULE_PRICE_CALCULATOR->value,
        ], [
            'name' => 'Price Calculator',
            'is_active' => true,
        ]);
    }

    public function test_free_plan_restrictions_on_price_calculation()
    {
        // Ensure feature is NOT attached to plan (Free Plan simulation)
        // $this->plan->features()->attach($this->priceCalcFeature); // Don't do this

        $payload = $this->getQuotationPayload();

        // Simulate sending calculation data that should be ignored
        $payload['quotation_products'][0]['weight'] = 10;
        $payload['quotation_products'][0]['tax_percentage'] = 15;
        $payload['quotation_products'][0]['margin'] = 20;
        $payload['quotation_products'][0]['unit_price'] = 500; // Manual price

        $response = $this->actingAs($this->user)
            ->post(route('tenant.quotations.store'), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $quotation = Quotation::latest()->first();
        $this->assertNotNull($quotation);
        $activeRevision = $quotation->getActiveRevision();
        $this->assertNotNull($activeRevision);

        $product = $activeRevision->products->first();

        // Verify restricted fields are zeroed out
        $this->assertEquals(0, $product->weight);
        $this->assertEquals(0, $product->tax_percentage);
        $this->assertEquals(0, $product->margin);

        // Verify allowed fields are saved
        $this->assertEquals(500, $product->unit_price);
        $this->assertEquals(100, $product->bdt_buying); // From payload
    }

    public function test_paid_plan_allows_price_calculation()
    {
        // Attach feature to plan (Paid Plan simulation)
        $this->plan->features()->attach($this->priceCalcFeature);

        $payload = $this->getQuotationPayload();

        // Send calculation data
        $payload['quotation_products'][0]['weight'] = 10;
        $payload['quotation_products'][0]['tax_percentage'] = 15;
        $payload['quotation_products'][0]['margin'] = 20;
        $payload['quotation_products'][0]['unit_price'] = 600; // Calculated price

        $response = $this->actingAs($this->user)
            ->post(route('tenant.quotations.store'), $payload);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $quotation = Quotation::latest()->first();
        $this->assertNotNull($quotation);
        $activeRevision = $quotation->getActiveRevision();
        $this->assertNotNull($activeRevision);

        $product = $activeRevision->products->first();

        // Verify fields are SAVED
        $this->assertEquals(10, $product->weight);
        $this->assertEquals(15, $product->tax_percentage);
        $this->assertEquals(20, $product->margin);
        $this->assertEquals(600, $product->unit_price);
    }

    private function getQuotationPayload()
    {
        return [
            'quotation' => [
                'customer_id' => $this->customer->id,
                'quotation_no' => 'QT-TEST-' . rand(1000, 9999),
                'ship_to' => 'Test Address',
            ],
            'quotation_revision' => [
                'type' => 'normal',
                'date' => '18/02/2026',
                'validity' => '25/02/2026',
                'currency' => 'BDT',
                'saved_as' => 'draft',
                'subtotal' => 500,
                'total' => 500,
                'terms_conditions' => 'Test Terms',
            ],
            'quotation_products' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 1,
                    'unit_price' => 500,
                    'bdt_buying' => 100,
                    'weight' => 0, // Default, overridden in test
                    'tax_percentage' => 0,
                    'margin' => 0,
                ]
            ]
        ];
    }
}
