<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\QuotationRevision;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingDebugTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();
        $this->actingAs($user);
    }

    /**
     * Test creating advance bill with detailed debugging
     */
    public function test_debug_advance_bill_creation()
    {
        // Create test data
        $customer = Customer::factory()->create();

        $quotation = Quotation::create([
            'customer_id' => $customer->id,
            'quotation_no' => 'QT-001',
            'ship_to' => 'Test Address',
            'status' => 'pending',
        ]);

        $activeRevision = QuotationRevision::create([
            'quotation_id' => $quotation->id,
            'type' => 'normal',
            'revision_no' => 'REV-001',
            'date' => '2025-01-01',
            'validity' => '2025-12-31',
            'currency' => 'BDT',
            'exchange_rate' => '1',
            'subtotal' => 9000.00,
            'discount_percentage' => 0,
            'discount_amount' => 0,
            'shipping' => 500.00,
            'vat_percentage' => 15,
            'vat_amount' => 1500.00,
            'total' => 10000.00,
            'terms_conditions' => 'Test terms',
            'saved_as' => 'quotation',
            'is_active' => true,
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        // Make the request
        $response = $this->post(route('bills.store'), [
            'quotation_id' => $quotation->id,
            'bill_type' => 'advance',
            'invoice_no' => 'ADV-001',
            'bill_date' => '2025-01-15',
            'total_amount' => 5000.00,
            'bill_percentage' => 50,
            'paid' => 5000.00,
            'due' => 0.00,
            'advance_percentage' => 50,
        ]);

        // Debug output
        dump('Response status:', $response->status());
        dump('Response content:', $response->getContent());

        // Get session from the test response
        $session = session()->all();
        dump('Session data:', $session);

        // Check what bills exist
        $allBills = Bill::all();
        dump('All bills in database:', $allBills->toArray());

        // Basic assertions
        $this->assertTrue(true); // Just to make the test pass for debugging
    }
}
