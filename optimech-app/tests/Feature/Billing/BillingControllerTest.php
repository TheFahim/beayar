<?php

namespace Tests\Feature\Billing;

use App\Models\Bill;
use App\Models\Challan;
use App\Models\ChallanProduct;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationProduct;
use App\Models\QuotationRevision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class BillingControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    protected Quotation $quotation;

    protected QuotationRevision $revision;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $company = Company::factory()->create();
        $customer = Customer::factory()->create(['company_id' => $company->id]);

        $this->quotation = Quotation::factory()->create([
            'customer_id' => $customer->id,
        ]);

        $this->revision = QuotationRevision::factory()->create([
            'quotation_id' => $this->quotation->id,
            'is_active' => true,
        ]);
    }

    /**
     * Test smart bill creation workflow - shows advance bill when no challans or advance bills exist
     */
    public function test_smart_bill_creation_shows_advance_bill_when_no_challans_or_advance_bills(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('bills.create-from-quotation', $this->quotation));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.bills.advance');
        $response->assertViewHas('quotation', $this->quotation);
        $response->assertViewHas('activeRevision', $this->revision);
    }

    /**
     * Test smart bill creation workflow - shows regular bill when challans exist
     */
    public function test_smart_bill_creation_shows_regular_bill_when_challans_exist(): void
    {
        $this->actingAs($this->user);

        // Create products and quotation products
        $product1 = Product::factory()->create();
        $product2 = Product::factory()->create();

        $quotationProduct1 = QuotationProduct::factory()->create([
            'quotation_revision_id' => $this->revision->id,
            'product_id' => $product1->id,
            'quantity' => 10,
            'unit_price' => 100.00,
            'tax_rate' => 10,
        ]);

        $quotationProduct2 = QuotationProduct::factory()->create([
            'quotation_revision_id' => $this->revision->id,
            'product_id' => $product2->id,
            'quantity' => 5,
            'unit_price' => 200.00,
            'tax_rate' => 15,
        ]);

        // Create challan with products
        $challan = Challan::factory()->create([
            'quotation_revision_id' => $this->revision->id,
            'challan_no' => 'CH-001',
            'challan_date' => now(),
        ]);

        ChallanProduct::factory()->create([
            'challan_id' => $challan->id,
            'quotation_product_id' => $quotationProduct1->id,
            'product_id' => $product1->id,
            'quantity' => 3,
            'billed_quantity' => 0,
        ]);

        ChallanProduct::factory()->create([
            'challan_id' => $challan->id,
            'quotation_product_id' => $quotationProduct2->id,
            'product_id' => $product2->id,
            'quantity' => 2,
            'billed_quantity' => 0,
        ]);

        $response = $this->get(route('bills.create-from-quotation', $this->quotation));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.bills.regular');
        $response->assertViewHas('quotation', $this->quotation);
        $response->assertViewHas('activeRevision', $this->revision);
        $response->assertViewHas('challans');

        $challans = $response->viewData('challans');
        $this->assertCount(1, $challans);
        $this->assertEquals($challan->id, $challans->first()->id);
    }

    /**
     * Test smart bill creation workflow - shows running bill when advance bills exist but no challans
     */
    public function test_smart_bill_creation_shows_running_bill_when_advance_bills_exist(): void
    {
        $this->actingAs($this->user);

        // Create advance bill first
        $advanceBill = Bill::factory()->create([
            'quotation_id' => $this->quotation->id,
            'bill_type' => 'advance',
            'invoice_no' => 'ADV-001',
            'bill_date' => now(),
            'total_amount' => 1000.00,
            'status' => 'draft',
        ]);

        $response = $this->get(route('bills.create-from-quotation', $this->quotation));

        $response->assertStatus(200);
        $response->assertViewIs('dashboard.bills.running');
        $response->assertViewHas('quotation', $this->quotation);
        $response->assertViewHas('advanceBills');

        $advanceBills = $response->viewData('advanceBills');
        $this->assertCount(1, $advanceBills);
        $this->assertEquals($advanceBill->id, $advanceBills->first()->id);
    }

    /**
     * Test storing advance bill with valid data
     */
    public function test_store_advance_bill_with_valid_data(): void
    {
        $this->actingAs($this->user);

        $billData = [
            'bill_type' => 'advance',
            'bill_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'advance_percentage' => 30,
            'notes' => 'Advance bill for testing',
            'discount' => 50.00,
            'shipping' => 25.00,
            'installments' => [
                [
                    'amount' => 300.00,
                    'due_date' => now()->addDays(15)->format('Y-m-d'),
                    'notes' => 'First installment',
                ],
                [
                    'amount' => 200.00,
                    'due_date' => now()->addDays(30)->format('Y-m-d'),
                    'notes' => 'Second installment',
                ],
            ],
        ];

        $response = $this->post(route('bills.store-from-quotation', $this->quotation), $billData);

        $response->assertRedirect();
        $this->assertDatabaseHas('bills', [
            'quotation_id' => $this->quotation->id,
            'bill_type' => 'advance',
            'discount' => 50.00,
            'shipping' => 25.00,
        ]);

        $bill = Bill::where('quotation_id', $this->quotation->id)->first();
        $this->assertNotNull($bill);
        $this->assertEquals(2, $bill->installments->count());
    }

    /**
     * Test storing regular bill with challan products
     */
    public function test_store_regular_bill_with_challan_products(): void
    {
        $this->actingAs($this->user);

        // Create products and quotation products
        $product = Product::factory()->create();
        $quotationProduct = QuotationProduct::factory()->create([
            'quotation_revision_id' => $this->revision->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 150.00,
            'tax_rate' => 12,
        ]);

        // Create challan with products
        $challan = Challan::factory()->create([
            'quotation_revision_id' => $this->revision->id,
            'challan_no' => 'CH-002',
        ]);

        $challanProduct = ChallanProduct::factory()->create([
            'challan_id' => $challan->id,
            'quotation_product_id' => $quotationProduct->id,
            'product_id' => $product->id,
            'quantity' => 5,
            'billed_quantity' => 0,
        ]);

        $billData = [
            'bill_type' => 'regular',
            'bill_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'notes' => 'Regular bill for challan products',
            'discount' => 25.00,
            'shipping' => 15.00,
            'challan_products' => [
                $challan->id => [
                    $challanProduct->id => [
                        'selected' => true,
                        'quantity' => 3,
                        'unit_price' => 150.00,
                        'tax_rate' => 12,
                    ],
                ],
            ],
        ];

        $response = $this->post(route('bills.store-from-quotation', $this->quotation), $billData);

        $response->assertRedirect();
        $this->assertDatabaseHas('bills', [
            'quotation_id' => $this->quotation->id,
            'bill_type' => 'regular',
            'discount' => 25.00,
            'shipping' => 15.00,
        ]);

        $bill = Bill::where('quotation_id', $this->quotation->id)->first();
        $this->assertNotNull($bill);
        $this->assertEquals(1, $bill->items->count());

        $billItem = $bill->items->first();
        $this->assertEquals(3, $billItem->quantity);
        $this->assertEquals(150.00, $billItem->unit_price);
        $this->assertEquals(54.00, $billItem->tax); // 12% of 150 * 3
    }

    /**
     * Test storing running bill with parent bill
     */
    public function test_store_running_bill_with_parent_bill(): void
    {
        $this->actingAs($this->user);

        // Create parent regular bill first
        $parentBill = Bill::factory()->create([
            'quotation_id' => $this->quotation->id,
            'bill_type' => 'regular',
            'invoice_no' => 'REG-PARENT',
            'total_amount' => 2000.00,
            'status' => 'draft',
        ]);

        $billData = [
            'bill_type' => 'running',
            'bill_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'parent_bill_id' => $parentBill->id,
            'running_percentage' => 25,
            'notes' => 'Running bill for parent bill',
            'installments' => [
                [
                    'amount' => 500.00,
                    'due_date' => now()->addDays(15)->format('Y-m-d'),
                    'notes' => 'Running installment 1',
                ],
            ],
        ];

        $response = $this->post(route('bills.store-from-quotation', $this->quotation), $billData);

        $response->assertRedirect();
        $this->assertDatabaseHas('bills', [
            'quotation_id' => $this->quotation->id,
            'bill_type' => 'running',
            'parent_bill_id' => $parentBill->id,
        ]);

        $bill = Bill::where('quotation_id', $this->quotation->id)
            ->where('bill_type', 'running')
            ->first();
        $this->assertNotNull($bill);
        $this->assertEquals($parentBill->id, $bill->parent_bill_id);
        $this->assertEquals(1, $bill->installments->count());
    }

    /**
     * Test validation for invalid bill data
     */
    public function test_validation_fails_for_invalid_bill_data(): void
    {
        $this->actingAs($this->user);

        $invalidData = [
            'bill_type' => 'invalid_type',
            'bill_date' => 'invalid-date',
            'due_date' => now()->subDays(1)->format('Y-m-d'), // Due date before bill date
            'advance_percentage' => 150, // Invalid percentage
            'discount' => -10, // Negative discount
            'shipping' => -5, // Negative shipping
        ];

        $response = $this->post(route('bills.store-from-quotation', $this->quotation), $invalidData);

        $response->assertSessionHasErrors([
            'bill_type',
            'bill_date',
            'due_date',
            'advance_percentage',
            'discount',
            'shipping',
        ]);
    }

    /**
     * Test authorization - unauthorized user cannot create bills
     */
    public function test_unauthorized_user_cannot_create_bills(): void
    {
        $unauthorizedUser = User::factory()->create();
        $this->actingAs($unauthorizedUser);

        $response = $this->get(route('bills.create-from-quotation', $this->quotation));

        $response->assertStatus(403);
    }

    /**
     * Test running bill validation - parent bill must be regular bill
     */
    public function test_running_bill_validation_parent_must_be_regular_bill(): void
    {
        $this->actingAs($this->user);

        // Create advance bill as parent (should fail)
        $advanceBill = Bill::factory()->create([
            'quotation_id' => $this->quotation->id,
            'bill_type' => 'advance',
            'invoice_no' => 'ADV-PARENT',
            'total_amount' => 1000.00,
            'status' => 'draft',
        ]);

        $billData = [
            'bill_type' => 'running',
            'bill_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'parent_bill_id' => $advanceBill->id,
            'running_percentage' => 25,
            'notes' => 'Invalid running bill',
        ];

        $response = $this->post(route('bills.store-from-quotation', $this->quotation), $billData);

        $response->assertSessionHasErrors(['parent_bill_id']);
    }

    /**
     * Test regular bill validation - must have challan products
     */
    public function test_regular_bill_validation_must_have_challan_products(): void
    {
        $this->actingAs($this->user);

        $billData = [
            'bill_type' => 'regular',
            'bill_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'notes' => 'Regular bill without challan products',
        ];

        $response = $this->post(route('bills.store-from-quotation', $this->quotation), $billData);

        $response->assertSessionHasErrors(['challan_products']);
    }

    /**
     * Test advance bill validation - must have advance percentage
     */
    public function test_advance_bill_validation_must_have_advance_percentage(): void
    {
        $this->actingAs($this->user);

        $billData = [
            'bill_type' => 'advance',
            'bill_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'notes' => 'Advance bill without percentage',
        ];

        $response = $this->post(route('bills.store-from-quotation', $this->quotation), $billData);

        $response->assertSessionHasErrors(['advance_percentage']);
    }

    /**
     * Test running bill validation - must have running percentage
     */
    public function test_running_bill_validation_must_have_running_percentage(): void
    {
        $this->actingAs($this->user);

        // Create parent regular bill
        $parentBill = Bill::factory()->create([
            'quotation_id' => $this->quotation->id,
            'bill_type' => 'regular',
            'invoice_no' => 'REG-PARENT-2',
            'total_amount' => 2000.00,
            'status' => 'draft',
        ]);

        $billData = [
            'bill_type' => 'running',
            'bill_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'parent_bill_id' => $parentBill->id,
            'notes' => 'Running bill without percentage',
        ];

        $response = $this->post(route('bills.store-from-quotation', $this->quotation), $billData);

        $response->assertSessionHasErrors(['running_percentage']);
    }
}
