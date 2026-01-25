<?php

namespace Tests\Feature\Billing;

use App\Models\Bill;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationProduct;
use App\Models\QuotationRevision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillShowProductsDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_advance_bill_shows_quotation_products(): void
    {
        $user = User::factory()->create(['username' => 'developer']);
        $this->actingAs($user);

        $company = Company::factory()->create();
        $customer = Customer::factory()->create(['company_id' => $company->id]);
        $quotation = Quotation::factory()->create(['customer_id' => $customer->id]);
        $revision = QuotationRevision::factory()->create([
            'quotation_id' => $quotation->id,
            'is_active' => true,
            'saved_as' => 'quotation',
        ]);

        $product = Product::factory()->create(['name' => 'Test Widget']);
        QuotationProduct::factory()->create([
            'quotation_revision_id' => $revision->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'unit' => 'pcs',
            'unit_price' => 100.00,
        ]);

        $advance = Bill::create([
            'quotation_id' => $quotation->id,
            'quotation_revision_id' => $revision->id,
            'invoice_no' => 'ADV-DISPLAY-001',
            'bill_date' => now()->format('Y-m-d'),
            'bill_type' => 'advance',
            'status' => 'issued',
            'total_amount' => 300.00,
            'bill_percentage' => 30,
            'bill_amount' => 300.00,
            'due' => 0,
            'notes' => '',
        ]);

        $response = $this->get(route('bills.show', $advance));
        $response->assertStatus(200);
        $response->assertSee('Test Widget');
        $response->assertSee('pcs');
        $response->assertSee('100.00');
        $response->assertSee('300.00');
    }

    public function test_running_bill_shows_quotation_products_from_active_revision(): void
    {
        $user = User::factory()->create(['username' => 'developer']);
        $this->actingAs($user);

        $company = Company::factory()->create();
        $customer = Customer::factory()->create(['company_id' => $company->id]);
        $quotation = Quotation::factory()->create(['customer_id' => $customer->id]);
        $revision = QuotationRevision::factory()->create([
            'quotation_id' => $quotation->id,
            'is_active' => true,
            'saved_as' => 'quotation',
        ]);

        $product = Product::factory()->create(['name' => 'Active Gadget']);
        QuotationProduct::factory()->create([
            'quotation_revision_id' => $revision->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit' => 'unit',
            'unit_price' => 50.00,
        ]);

        $advance = Bill::create([
            'quotation_id' => $quotation->id,
            'quotation_revision_id' => $revision->id,
            'invoice_no' => 'ADV-PARENT-001',
            'bill_date' => now()->format('Y-m-d'),
            'bill_type' => 'advance',
            'status' => 'issued',
            'total_amount' => 100.00,
            'bill_percentage' => 10,
            'bill_amount' => 100.00,
            'due' => 900.00,
            'notes' => '',
        ]);

        $running = Bill::create([
            'quotation_id' => $quotation->id,
            'parent_bill_id' => $advance->id,
            'invoice_no' => 'RUN-DISPLAY-001',
            'bill_date' => now()->format('Y-m-d'),
            'bill_type' => 'running',
            'status' => 'issued',
            'total_amount' => 200.00,
            'bill_percentage' => 20,
            'bill_amount' => 200.00,
            'due' => 700.00,
            'notes' => '',
        ]);

        $response = $this->get(route('bills.show', $running));
        $response->assertStatus(200);
        $response->assertSee('Active Gadget');
        $response->assertSee('unit');
        $response->assertSee('50.00');
        $response->assertSee('100.00');
    }

    public function test_regular_bill_without_items_does_not_show_quotation_products(): void
    {
        $user = User::factory()->create(['username' => 'developer']);
        $this->actingAs($user);

        $company = Company::factory()->create();
        $customer = Customer::factory()->create(['company_id' => $company->id]);
        $quotation = Quotation::factory()->create(['customer_id' => $customer->id]);
        $revision = QuotationRevision::factory()->create([
            'quotation_id' => $quotation->id,
            'is_active' => true,
            'saved_as' => 'quotation',
        ]);

        $product = Product::factory()->create(['name' => 'Hidden Item']);
        QuotationProduct::factory()->create([
            'quotation_revision_id' => $revision->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit' => 'pcs',
            'unit_price' => 10.00,
        ]);

        $regular = Bill::create([
            'quotation_id' => $quotation->id,
            'invoice_no' => 'REG-DISPLAY-001',
            'bill_date' => now()->format('Y-m-d'),
            'bill_type' => 'regular',
            'status' => 'issued',
            'total_amount' => 0,
            'bill_amount' => 0,
            'due' => 0,
            'notes' => '',
        ]);

        $response = $this->get(route('bills.show', $regular));
        $response->assertStatus(200);
        $response->assertDontSee('Hidden Item');
        $response->assertSee('No items found for this bill');
    }
}
