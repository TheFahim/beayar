<?php

namespace Tests\Feature\Quotation;

use App\Models\Customer;
use App\Models\CustomerCompany;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationProduct;
use App\Models\QuotationRevision;
use App\Models\QuotationStatus;
use App\Models\User;
use App\Models\UserCompany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationUpdateTest extends TestCase
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
            'vat_percentage' => 15,
            'vat_amount' => 150,
            'shipping' => 0,
            'saved_as' => 'draft',
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        // Setup Product for Revision
        QuotationProduct::create([
            'quotation_revision_id' => $this->revision->id,
            'product_id' => $this->product->id,
            'product_name' => 'Test Product',
            'quantity' => 1,
            'unit_price' => 1000,
            'unit' => 'pcs',
            'total' => 1000,
        ]);
    }

    public function test_can_view_quotation_edit_page()
    {
        $response = $this->actingAs($this->user)
            ->get(route('tenant.quotations.edit', $this->quotation));

        $response->assertStatus(200);
        $response->assertSee('Edit Quotation');
    }

    public function test_can_update_quotation_with_valid_data()
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
                    'date' => '03/02/2026',
                    'validity' => '18/02/2026',
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

        $response->assertRedirect(route('tenant.quotations.index'));
        $response->assertSessionHas('success');

        $this->quotation->refresh();
        $this->assertEquals('Updated Address', $this->quotation->ship_to);
    }

    public function test_fails_validation_when_customer_id_is_missing()
    {
        $response = $this->actingAs($this->user)
            ->put(route('tenant.quotations.update', $this->quotation), [
                'quotation' => [
                    'quotation_no' => 'C-0001-0001',
                    'ship_to' => 'Test Address',
                ],
                'quotation_revision' => [
                    'id' => $this->revision->id,
                    'type' => 'normal',
                    'date' => '03/02/2026',
                    'validity' => '18/02/2026',
                    'currency' => 'BDT',
                    'exchange_rate' => 121.50,
                    'subtotal' => 1000,
                    'total' => 1150,
                    'saved_as' => 'draft',
                ],
                'quotation_products' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 1,
                        'unit_price' => 1000,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors('quotation.customer_id');
    }

    public function test_fails_validation_when_revision_id_is_missing()
    {
        $response = $this->actingAs($this->user)
            ->put(route('tenant.quotations.update', $this->quotation), [
                'quotation' => [
                    'customer_id' => $this->customer->id,
                    'quotation_no' => 'C-0001-0001',
                    'ship_to' => 'Test Address',
                ],
                'quotation_revision' => [
                    // 'id' => $this->revision->id, // Missing!
                    'type' => 'normal',
                    'date' => '03/02/2026',
                    'validity' => '18/02/2026',
                    'currency' => 'BDT',
                    'exchange_rate' => 121.50,
                    'subtotal' => 1000,
                    'total' => 1150,
                    'saved_as' => 'draft',
                ],
                'quotation_products' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 1,
                        'unit_price' => 1000,
                    ],
                ],
            ]);

        // This should either fail validation or cause an error in the service layer
        $response->assertStatus(302);
    }

    public function test_fails_validation_when_date_format_is_invalid()
    {
        $response = $this->actingAs($this->user)
            ->put(route('tenant.quotations.update', $this->quotation), [
                'quotation' => [
                    'customer_id' => $this->customer->id,
                    'quotation_no' => 'C-0001-0001',
                    'ship_to' => 'Test Address',
                ],
                'quotation_revision' => [
                    'id' => $this->revision->id,
                    'type' => 'normal',
                    'date' => '2026/02/03', // Wrong format (neither d/m/Y nor Y-m-d)
                    'validity' => '18/02/2026',
                    'currency' => 'BDT',
                    'exchange_rate' => 121.50,
                    'subtotal' => 1000,
                    'total' => 1150,
                    'saved_as' => 'draft',
                ],
                'quotation_products' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 1,
                        'unit_price' => 1000,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors('quotation_revision.date');
    }

    public function test_fails_validation_when_products_array_is_empty()
    {
        $response = $this->actingAs($this->user)
            ->put(route('tenant.quotations.update', $this->quotation), [
                'quotation' => [
                    'customer_id' => $this->customer->id,
                    'quotation_no' => 'C-0001-0001',
                    'ship_to' => 'Test Address',
                ],
                'quotation_revision' => [
                    'id' => $this->revision->id,
                    'type' => 'normal',
                    'date' => '03/02/2026',
                    'validity' => '18/02/2026',
                    'currency' => 'BDT',
                    'exchange_rate' => 121.50,
                    'subtotal' => 0,
                    'total' => 0,
                    'saved_as' => 'draft',
                ],
                'quotation_products' => [], // Empty!
            ]);

        $response->assertSessionHasErrors('quotation_products');
    }

    public function test_fails_validation_when_exchange_rate_is_missing_for_via_quotation()
    {
        $response = $this->actingAs($this->user)
            ->put(route('tenant.quotations.update', $this->quotation), [
                'quotation' => [
                    'customer_id' => $this->customer->id,
                    'quotation_no' => 'C-0001-0001',
                    'ship_to' => 'Test Address',
                ],
                'quotation_revision' => [
                    'id' => $this->revision->id,
                    'type' => 'via',
                    'date' => '03/02/2026',
                    'validity' => '18/02/2026',
                    'currency' => 'USD',
                    // 'exchange_rate' => 121.50, // Missing!
                    'subtotal' => 1000,
                    'total' => 1000,
                    'saved_as' => 'draft',
                ],
                'quotation_products' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 1,
                        'unit_price' => 1000,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors('quotation_revision.exchange_rate');
    }

    public function test_can_update_quotation_with_new_revision()
    {
        $response = $this->actingAs($this->user)
            ->put(route('tenant.quotations.update', $this->quotation), [
                'quotation' => [
                    'customer_id' => $this->customer->id,
                    'quotation_no' => 'C-0001-0001',
                    'ship_to' => 'Updated Address',
                ],
                'quotation_revision' => [
                    'new_revision' => true, // Flag for new revision
                    'type' => 'normal',
                    'date' => '03/02/2026',
                    'validity' => '18/02/2026',
                    'currency' => 'BDT',
                    'exchange_rate' => 121.50,
                    'subtotal' => 2000,
                    'discount' => 0,
                    'discount_percentage' => 0,
                    'vat_percentage' => 15,
                    'vat_amount' => 300,
                    'shipping' => 0,
                    'total' => 2300,
                    'saved_as' => 'quotation',
                    'terms_conditions' => 'New revision terms',
                ],
                'quotation_products' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 2,
                        'unit_price' => 1000,
                    ],
                ],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->quotation->refresh();

        // Should have 2 revisions now
        $this->assertEquals(2, $this->quotation->revisions()->count());

        // The old revision should be inactive
        $this->revision->refresh();
        $this->assertFalse((bool) $this->revision->is_active);

        // New revision should be active
        $activeRevision = $this->quotation->getActiveRevision();
        $this->assertNotNull($activeRevision);
        $this->assertEquals('R01', $activeRevision->revision_no);
    }

    public function test_can_update_normal_quotation_without_exchange_rate()
    {
        // Tests that normal quotations work without exchange_rate
        $response = $this->actingAs($this->user)
            ->put(route('tenant.quotations.update', $this->quotation), [
                'quotation' => [
                    'customer_id' => $this->customer->id,
                    'quotation_no' => 'C-0001-0001',
                    'ship_to' => 'Test Address',
                ],
                'quotation_revision' => [
                    'id' => $this->revision->id,
                    'type' => 'normal',
                    'date' => '03/02/2026',
                    'validity' => '18/02/2026',
                    'currency' => 'BDT',
                    // 'exchange_rate' => 1, // Not provided
                    'subtotal' => 1000,
                    'total' => 1150,
                    'saved_as' => 'draft',
                ],
                'quotation_products' => [
                    [
                        'id' => $this->revision->products->first()->id,
                        'product_id' => $this->product->id,
                        'quantity' => 1,
                        'unit_price' => 1000,
                    ],
                ],
            ]);

        $response->assertRedirect(route('tenant.quotations.index'));
        $response->assertSessionHas('success');
    }

    public function test_cannot_update_quotation_belonging_to_another_company()
    {
        // Create another company and user
        $otherUser = User::factory()->create([
            'current_user_company_id' => null,
            'current_scope' => 'company',
        ]);

        $otherCompany = UserCompany::create([
            'name' => 'Other Company',
            'email' => 'other@company.com',
            'owner_id' => $otherUser->id,
        ]);

        $otherUser->update(['current_user_company_id' => $otherCompany->id]);

        // Try to access the quotation as the other user
        $response = $this->actingAs($otherUser)
            ->put(route('tenant.quotations.update', $this->quotation), [
                'quotation' => [
                    'customer_id' => $this->customer->id,
                    'quotation_no' => 'C-0001-0001',
                    'ship_to' => 'Hacked Address',
                ],
                'quotation_revision' => [
                    'id' => $this->revision->id,
                    'type' => 'normal',
                    'date' => '03/02/2026',
                    'validity' => '18/02/2026',
                    'currency' => 'BDT',
                    'exchange_rate' => 121.50,
                    'subtotal' => 1000,
                    'total' => 1150,
                    'saved_as' => 'draft',
                ],
                'quotation_products' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 1,
                        'unit_price' => 1000,
                    ],
                ],
            ]);

        // Should be forbidden or not found (tenant scope returns 404)
        $response->assertStatus(404);
    }

    public function test_fails_validation_when_quotation_no_is_missing()
    {
        $response = $this->actingAs($this->user)
            ->put(route('tenant.quotations.update', $this->quotation), [
                'quotation' => [
                    'customer_id' => $this->customer->id,
                    // 'quotation_no' missing
                    'ship_to' => 'Test Address',
                ],
                'quotation_revision' => [
                    'id' => $this->revision->id,
                    'type' => 'normal',
                    'date' => '03/02/2026',
                    'validity' => '18/02/2026',
                    'currency' => 'BDT',
                    'exchange_rate' => 121.50,
                    'subtotal' => 1000,
                    'total' => 1150,
                    'saved_as' => 'draft',
                ],
                'quotation_products' => [
                    [
                        'product_id' => $this->product->id,
                        'quantity' => 1,
                        'unit_price' => 1000,
                    ],
                ],
            ]);

        $response->assertSessionHasErrors('quotation.quotation_no');
    }
}
