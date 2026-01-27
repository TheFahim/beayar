<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\Customer;
use App\Models\CustomerCompany;
use App\Models\Payment;
use App\Models\QuotationStatus;
use App\Models\User;
use App\Models\UserCompany;
use App\Services\Tenant\QuotationService;
use App\Services\Tenant\TenantBillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CriticalWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $company;
    protected $customer;
    protected $quotationService;
    protected $billingService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->quotationService = new QuotationService();
        $this->billingService = new TenantBillingService();

        // 1. Setup User and Company
        $this->user = User::factory()->create([
            'current_user_company_id' => null,
            'current_scope' => 'company'
        ]);
        
        $this->company = UserCompany::create([
            'name' => 'Workflow Test Company',
            'owner_id' => $this->user->id,
            'email' => 'workflow@test.com'
        ]);

        $this->user->update(['current_user_company_id' => $this->company->id]);

        // 2. Setup Customer
        $customerCompany = CustomerCompany::create([
            'user_company_id' => $this->company->id,
            'name' => 'Client Corp'
        ]);
        
        $this->customer = Customer::create([
            'customer_company_id' => $customerCompany->id,
            'name' => 'John Client',
            'email' => 'john@client.com'
        ]);
    }

    public function test_full_quote_to_payment_cycle()
    {
        // Step 1: Create Quotation
        $status = QuotationStatus::create([
            'name' => 'Draft', 
            'user_company_id' => $this->company->id
        ]);

        $quoteData = [
            'customer_id' => $this->customer->id,
            'status_id' => $status->id,
            'po_no' => 'PO-WF-001',
            'subtotal' => 5000,
            'total' => 5250, // 5% Tax assumed in logic or just passed
            'products' => [
                [
                    'product_name' => 'Consulting Service',
                    'quantity' => 10,
                    'unit_price' => 500,
                    'total' => 5000
                ]
            ]
        ];

        $quotation = $this->quotationService->createQuotation($this->user, $quoteData);
        
        $this->assertDatabaseHas('quotations', ['id' => $quotation->id]);
        $this->assertEquals(5250, $quotation->activeRevision->total);

        // Step 2: Convert to Bill
        $bill = $this->billingService->createBillFromQuotation($quotation, $this->user);

        $this->assertDatabaseHas('bills', ['id' => $bill->id]);
        $this->assertEquals($quotation->id, $bill->quotation_id);
        $this->assertEquals(5250, $bill->total_amount);
        $this->assertEquals(5250, $bill->due);
        $this->assertEquals('draft', $bill->status);

        // Step 3: Record Payment (Partial)
        $paymentAmount = 2000;
        $payment = Payment::create([
            'user_company_id' => $this->company->id,
            'customer_id' => $this->customer->id,
            'bill_id' => $bill->id,
            'payment_no' => 'PAY-' . uniqid(),
            'date' => now(),
            'amount' => $paymentAmount,
            'method' => 'bank_transfer'
        ]);

        // Verify Payment
        $this->assertDatabaseHas('payments', ['id' => $payment->id]);

        // Step 4: Update Bill Due Amount (Simulating logic usually in a Service/Controller)
        $bill->due = $bill->total_amount - $bill->payments()->sum('amount');
        $bill->save();

        $this->assertEquals(3250, $bill->due); // 5250 - 2000

        // Step 5: Full Payment
        $remainingAmount = 3250;
        Payment::create([
            'user_company_id' => $this->company->id,
            'customer_id' => $this->customer->id,
            'bill_id' => $bill->id,
            'payment_no' => 'PAY-' . uniqid(),
            'date' => now(),
            'amount' => $remainingAmount,
            'method' => 'cash'
        ]);

        $bill->due = $bill->total_amount - $bill->payments()->sum('amount');
        if ($bill->due <= 0) {
            $bill->status = 'paid';
        }
        $bill->save();

        $this->assertEquals(0, $bill->due);
        $this->assertEquals('paid', $bill->status);
    }
}
