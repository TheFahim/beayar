<?php

namespace Tests\Feature\Quotation;

use App\Models\Customer;
use App\Models\CustomerCompany;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationRevision;
use App\Models\QuotationStatus;
use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationUpdateSpecificTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected $company;

    protected $customer;

    protected $status;

    protected $product;

    protected $quotation;

    protected $revision;

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

        // Setup Quotation with Revision
        $this->quotation = Quotation::create([
            'user_company_id' => $this->company->id,
            'user_id' => $this->user->id,
            'customer_id' => $this->customer->id,
            'quotation_no' => 'C-0001-0001',
            'reference_no' => 'C-0001-0001',
            'status_id' => $this->status->id,
            'ship_to' => 'Test Address',
        ]);

        $this->revision = QuotationRevision::create([
            'quotation_id' => $this->quotation->id,
            'revision_no' => 'R00',
            'date' => now(),
            'validity' => now()->addDays(15),
            'type' => 'normal',
            'currency' => 'BDT',
            'exchange_rate' => 121.50,
            'subtotal' => 1000,
            'total' => 1150,
            'created_by' => $this->user->id,
        ]);

        $this->revision->products()->create([
            'product_id' => $this->product->id,
            'product_name' => $this->product->name,
            'quantity' => 1,
            'unit_price' => 1000,
            'total' => 1000,
        ]);
    }

    public function test_can_update_quotation_with_ymd_date_format()
    {
        $response = $this->actingAs($this->user)
            ->put(route('tenant.quotations.update', $this->quotation), [
                'quotation' => [
                    'customer_id' => $this->customer->id,
                    'quotation_no' => 'C-0001-0001',
                    'ship_to' => 'Updated Address',
                ],
                'quotation_revision' => [
                    'id' => $this->revision->id,
                    'type' => 'normal',
                    'date' => '2026-02-03', // Y-m-d format
                    'validity' => '2026-02-18', // Y-m-d format
                    'currency' => 'BDT',
                    'exchange_rate' => 121.50,
                    'subtotal' => 1000,
                    'discount' => 0,
                    'discount_percentage' => 0,
                    'discounted_price' => 1000,
                    'vat_percentage' => 15,
                    'vat_amount' => 150,
                    'shipping' => 0,
                    'total' => 1150,
                    'saved_as' => 'draft',
                    'terms_conditions' => 'Test terms',
                ],
                'quotation_products' => [
                    [
                        'id' => $this->revision->products->first()->id,
                        'product_id' => $this->product->id,
                        'quantity' => 1,
                        'unit_price' => 1000,
                        'unit' => 'pcs',
                    ],
                ],
            ]);

        // Check for session errors if it fails
        if ($response->status() !== 302 || session()->has('errors')) {
            dump(session('errors'));
        }

        $response->assertRedirect(route('tenant.quotations.index'));
        $response->assertSessionHas('success');

        $this->quotation->refresh();
        $this->assertEquals('Updated Address', $this->quotation->ship_to);
    }

    public function test_can_update_quotation_with_dm_y_date_format()
    {
        $response = $this->actingAs($this->user)
            ->put(route('tenant.quotations.update', $this->quotation), [
                'quotation' => [
                    'customer_id' => $this->customer->id,
                    'quotation_no' => 'C-0001-0001',
                    'ship_to' => 'Updated Address',
                ],
                'quotation_revision' => [
                    'id' => $this->revision->id,
                    'type' => 'normal',
                    'date' => '03/02/2026', // d/m/Y format
                    'validity' => '18/02/2026', // d/m/Y format
                    'currency' => 'BDT',
                    'exchange_rate' => 121.50,
                    'subtotal' => 1000,
                    'discount' => 0,
                    'discount_percentage' => 0,
                    'discounted_price' => 1000,
                    'vat_percentage' => 15,
                    'vat_amount' => 150,
                    'shipping' => 0,
                    'total' => 1150,
                    'saved_as' => 'draft',
                    'terms_conditions' => 'Test terms',
                ],
                'quotation_products' => [
                    [
                        'id' => $this->revision->products->first()->id,
                        'product_id' => $this->product->id,
                        'quantity' => 1,
                        'unit_price' => 1000,
                        'unit' => 'pcs',
                    ],
                ],
            ]);

        // Check for session errors if it fails
        if ($response->status() !== 302 || session()->has('errors')) {
            dump(session('errors'));
        }

        $response->assertRedirect(route('tenant.quotations.index'));
        $response->assertSessionHas('success');
    }

    public function test_can_create_new_revision_with_ymd_date_format()
    {
        $response = $this->actingAs($this->user)
            ->put(route('tenant.quotations.update', $this->quotation), [
                'quotation' => [
                    'customer_id' => $this->customer->id,
                    'quotation_no' => 'C-0001-0001',
                    'ship_to' => 'Updated Address',
                ],
                'quotation_revision' => [
                    'new_revision' => true,
                    'type' => 'normal',
                    'date' => '2026-02-03', // Y-m-d format
                    'validity' => '2026-02-18', // Y-m-d format
                    'currency' => 'BDT',
                    'exchange_rate' => 121.50,
                    'subtotal' => 1000,
                    'discount' => 0,
                    'discount_percentage' => 0,
                    'discounted_price' => 1000,
                    'vat_percentage' => 15,
                    'vat_amount' => 150,
                    'shipping' => 0,
                    'total' => 1150,
                    'saved_as' => 'draft',
                    'terms_conditions' => 'Test terms',
                ],
                'quotation_products' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 1,
                        'unit_price' => 1000,
                        'unit' => 'pcs',
                    ],
                ],
            ]);

        // This will likely fail if QuotationRevisionRequest is not updated
        if ($response->status() !== 302 || session()->has('errors')) {
            // dump(session('errors'));
        }

        // If it fails with errors, we expect validation errors on date
        if (session()->has('errors')) {
            $errors = session('errors');
            if ($errors->has('quotation_revision.date')) {
                // It failed as expected, which means we need to fix QuotationRevisionRequest too
                // But for now, let's see if it passes
                $this->fail('Validation failed for date format in new revision');
            }
        }

        $response->assertRedirect(route('tenant.quotations.edit', $this->quotation));
        $response->assertSessionHas('success');
    }
}
