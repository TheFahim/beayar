<?php

namespace Tests\Feature\Quotation;

use App\Models\BrandOrigin;
use App\Models\Customer;
use App\Models\CustomerCompany;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationStatus;
use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationCreateTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $company;
    protected $customer;
    protected $status;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup User and Company
        $this->user = User::factory()->create([
            'current_user_company_id' => null,
            'current_scope' => 'company',
        ]);

        $this->company = UserCompany::create([
            'name' => 'Test Company',
            'email' => 'test@company.com',
            'owner_id' => $this->user->id,
        ]);

        $this->user->update(['current_user_company_id' => $this->company->id]);

        // Setup Customer
        $customerCompany = CustomerCompany::create([
            'user_company_id' => $this->company->id,
            'name' => 'Test Customer Company',
        ]);

        $this->customer = Customer::create([
            'user_company_id' => $this->company->id,
            'customer_company_id' => $customerCompany->id,
            'customer_no' => 'C-0001',
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
        ]);

        // Setup Status
        $this->status = QuotationStatus::create([
            'name' => 'Draft',
            'user_company_id' => $this->company->id,
            'is_default' => true,
        ]);

        // Setup Product
        $this->product = Product::create([
            'user_company_id' => $this->company->id,
            'name' => 'Test Product',
            'unit' => 'pcs',
        ]);

        // Setup Brand Origin
        BrandOrigin::create([
            'user_company_id' => $this->company->id,
            'name' => 'Test Origin',
            'country' => 'Test Country',
        ]);
    }

    public function test_create_page_can_be_rendered()
    {
        $response = $this->actingAs($this->user)
            ->get(route('tenant.quotations.create'));

        $response->assertStatus(200);
        $response->assertViewIs('tenant.quotations.create');
        $response->assertViewHas(['customers', 'products', 'specifications']);
    }

    public function test_quotation_can_be_stored()
    {
        $data = [
            'quotation' => [
                'customer_id' => $this->customer->id,
                'quotation_no' => 'QT-TEST-001',
                'ship_to' => 'Test Address',
            ],
            'quotation_revision' => [
                'type' => 'normal',
                'date' => '01/01/2023',
                'validity' => '15/01/2023',
                'currency' => 'BDT',
                'exchange_rate' => 1,
                'subtotal' => 1000,
                'total' => 1000,
                'status' => 'draft',
                'saved_as' => 'draft',
            ],
            'quotation_products' => [
                [
                    'product_id' => $this->product->id,
                    'product_name' => $this->product->name,
                    'quantity' => 10,
                    'unit_price' => 100,
                    'total' => 1000,
                ]
            ],
        ];

        $response = $this->actingAs($this->user)
            ->post(route('tenant.quotations.store'), $data);

        $response->assertRedirect(route('tenant.quotations.index'));
        $this->assertDatabaseHas('quotations', [
            'quotation_no' => 'QT-TEST-001',
            'customer_id' => $this->customer->id,
        ]);
    }
}
