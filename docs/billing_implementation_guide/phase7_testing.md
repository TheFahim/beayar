# Phase 7 — Testing & Hardening (Days 15-16)

This phase implements comprehensive tests and final QA for the billing module.

---

## Day 15 — Unit Tests

### 🎯 Goal
By the end of Day 15, you will have complete unit tests for the BillingService, Bill model, and advance adjustment logic.

### 📋 Prerequisites
- [ ] Phase 6 completed
- [ ] PHPUnit/Pest configured
- [ ] Test database setup

---

### 🧪 Testing Tasks

#### Task 1: Create BillingServiceTest

**File:** `tests/Unit/BillingServiceTest.php` (NEW)

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Bill;
use App\Models\Quotation;
use App\Models\TenantCompany;
use App\Models\User;
use App\Services\BillingService;
use App\Exceptions\BillLockedException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class BillingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BillingService $service;
    protected TenantCompany $tenant;
    protected User $user;
    protected Quotation $quotation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(BillingService::class);
        
        $this->tenant = TenantCompany::factory()->create();
        $this->user = User::factory()->create(['tenant_company_id' => $this->tenant->id]);
        $this->quotation = Quotation::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'billing_stage' => 'none',
        ]);

        // Set tenant context
        session(['tenant_company_id' => $this->tenant->id]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_creates_an_advance_bill()
    {
        $data = [
            'amount' => '10000.00',
            'tax_amount' => '1500.00',
            'bill_date' => now(),
            'notes' => 'Advance for project',
        ];

        $bill = $this->service->createAdvanceBill($data, $this->quotation);

        $this->assertInstanceOf(Bill::class, $bill);
        $this->assertEquals('advance', $bill->bill_type);
        $this->assertEquals('11500.00', $bill->total);
        $this->assertEquals('draft', $bill->status);
        $this->assertStringStartsWith('ADV-', $bill->bill_number);
        $this->assertEquals('advance_pending', $this->quotation->fresh()->billing_stage);
    }

    /** @test */
    public function it_creates_a_running_bill_linked_to_advance()
    {
        $advanceBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $this->quotation->id,
            'bill_type' => 'advance',
            'status' => 'issued',
        ]);

        $data = [
            'subtotal' => '5000.00',
            'tax_amount' => '750.00',
            'bill_date' => now(),
        ];

        $bill = $this->service->createRunningBill($data, $advanceBill);

        $this->assertEquals('running', $bill->bill_type);
        $this->assertEquals($advanceBill->id, $bill->parent_bill_id);
        $this->assertEquals('5750.00', $bill->total);
    }

    /** @test */
    public function it_creates_a_regular_bill()
    {
        $data = [
            'bill_date' => now(),
            'bill_items' => [
                [
                    'product_name' => 'Product A',
                    'quantity' => '10',
                    'unit_price' => '100.00',
                ],
                [
                    'product_name' => 'Product B',
                    'quantity' => '5',
                    'unit_price' => '200.00',
                ],
            ],
            'tax_amount' => '200.00',
            'shipping' => '50.00',
        ];

        $bill = $this->service->createRegularBill($data, $this->quotation, []);

        $this->assertEquals('regular', $bill->bill_type);
        $this->assertEquals('2000.00', $bill->subtotal);
        $this->assertEquals('2250.00', $bill->total);
        $this->assertEquals('regular_pending', $this->quotation->fresh()->billing_stage);
    }

    /** @test */
    public function it_applies_advance_credit_to_regular_bill()
    {
        // Create advance bill with payment
        $advanceBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $this->quotation->id,
            'bill_type' => 'advance',
            'status' => 'paid',
            'total' => '10000.00',
        ]);

        // Record payment on advance
        DB::table('bill_payments')->insert([
            'bill_id' => $advanceBill->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '10000.00',
            'payment_method' => 'bank_transfer',
            'payment_date' => now(),
            'created_by' => $this->user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create regular bill
        $regularBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $this->quotation->id,
            'bill_type' => 'regular',
            'status' => 'draft',
            'total' => '15000.00',
            'net_payable_amount' => '15000.00',
        ]);

        $adjustment = $this->service->applyAdvanceCredit($advanceBill, $regularBill, '5000.00');

        $this->assertDatabaseHas('bill_advance_adjustments', [
            'advance_bill_id' => $advanceBill->id,
            'final_bill_id' => $regularBill->id,
            'amount' => '5000.00',
        ]);

        $regularBill->refresh();
        $this->assertEquals('5000.00', $regularBill->advance_applied_amount);
        $this->assertEquals('10000.00', $regularBill->net_payable_amount);
    }

    /** @test */
    public function it_rejects_over_application_of_advance_credit()
    {
        $advanceBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $this->quotation->id,
            'bill_type' => 'advance',
            'status' => 'paid',
            'total' => '5000.00',
        ]);

        DB::table('bill_payments')->insert([
            'bill_id' => $advanceBill->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '5000.00',
            'payment_method' => 'cash',
            'payment_date' => now(),
            'created_by' => $this->user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $regularBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $this->quotation->id,
            'bill_type' => 'regular',
            'status' => 'draft',
            'total' => '10000.00',
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot apply');

        $this->service->applyAdvanceCredit($advanceBill, $regularBill, '99999.00');
    }

    /** @test */
    public function it_issues_a_bill_and_locks_it()
    {
        $bill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $this->quotation->id,
            'bill_type' => 'regular',
            'status' => 'draft',
        ]);

        $issuedBill = $this->service->issueBill($bill);

        $this->assertEquals('issued', $issuedBill->status);
        $this->assertTrue($issuedBill->is_locked);
        $this->assertEquals('status_not_draft', $issuedBill->lock_reason);
    }

    /** @test */
    public function it_cancels_a_bill_and_reverses_advance()
    {
        $advanceBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $this->quotation->id,
            'bill_type' => 'advance',
            'status' => 'paid',
            'total' => '10000.00',
        ]);

        DB::table('bill_payments')->insert([
            'bill_id' => $advanceBill->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '10000.00',
            'payment_method' => 'bank_transfer',
            'payment_date' => now(),
            'created_by' => $this->user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $regularBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $this->quotation->id,
            'bill_type' => 'regular',
            'status' => 'issued',
            'total' => '15000.00',
            'advance_applied_amount' => '5000.00',
            'net_payable_amount' => '10000.00',
        ]);

        DB::table('bill_advance_adjustments')->insert([
            'advance_bill_id' => $advanceBill->id,
            'final_bill_id' => $regularBill->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '5000.00',
            'created_by' => $this->user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cancelledBill = $this->service->cancelBill($regularBill, 'Customer request');

        $this->assertEquals('cancelled', $cancelledBill->status);
        $this->assertEquals('0.00', $cancelledBill->advance_applied_amount);
        
        // Verify adjustment was removed
        $this->assertSoftDeleted('bill_advance_adjustments', [
            'advance_bill_id' => $advanceBill->id,
            'final_bill_id' => $regularBill->id,
        ]);
    }

    /** @test */
    public function it_reissues_a_cancelled_bill()
    {
        $cancelledBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $this->quotation->id,
            'bill_type' => 'regular',
            'status' => 'cancelled',
            'total' => '10000.00',
            'subtotal' => '8500.00',
            'tax_amount' => '1500.00',
        ]);

        $newBill = $this->service->reissueBill($cancelledBill);

        $this->assertEquals('draft', $newBill->status);
        $this->assertEquals('regular', $newBill->bill_type);
        $this->assertEquals('10000.00', $newBill->total);
        $this->assertNotEquals($cancelledBill->bill_number, $newBill->bill_number);
        $this->assertEquals($cancelledBill->id, $newBill->reissued_from_id);
        $this->assertEquals($newBill->id, $cancelledBill->fresh()->reissued_to_id);
    }

    /** @test */
    public function it_records_payment_and_updates_status()
    {
        $bill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $this->quotation->id,
            'bill_type' => 'regular',
            'status' => 'issued',
            'total' => '10000.00',
            'net_payable_amount' => '10000.00',
        ]);

        $payment = $this->service->recordPayment($bill, [
            'amount' => '5000.00',
            'payment_method' => 'bank_transfer',
            'payment_date' => now(),
        ]);

        $this->assertDatabaseHas('bill_payments', [
            'bill_id' => $bill->id,
            'amount' => '5000.00',
        ]);

        $bill->refresh();
        $this->assertEquals('partially_paid', $bill->status);

        // Full payment
        $this->service->recordPayment($bill, [
            'amount' => '5000.00',
            'payment_method' => 'cash',
            'payment_date' => now(),
        ]);

        $bill->refresh();
        $this->assertEquals('paid', $bill->status);
    }

    /** @test */
    public function it_calculates_unapplied_advance_balance_correctly()
    {
        $advanceBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $this->quotation->id,
            'bill_type' => 'advance',
            'status' => 'paid',
            'total' => '20000.00',
        ]);

        // Total payments: 20000
        DB::table('bill_payments')->insert([
            'bill_id' => $advanceBill->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '20000.00',
            'payment_method' => 'bank_transfer',
            'payment_date' => now(),
            'created_by' => $this->user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Applied: 8000
        DB::table('bill_advance_adjustments')->insert([
            'advance_bill_id' => $advanceBill->id,
            'final_bill_id' => 999, // Dummy
            'tenant_company_id' => $this->tenant->id,
            'amount' => '8000.00',
            'created_by' => $this->user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $balance = $this->service->getUnappliedAdvanceBalance($advanceBill);

        $this->assertEquals('12000.00', $balance);
    }
}
```

#### Task 2: Create BillModelTest

**File:** `tests/Unit/BillModelTest.php` (NEW)

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Bill;
use App\Models\TenantCompany;
use App\Models\User;
use App\Exceptions\BillLockedException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BillModelTest extends TestCase
{
    use RefreshDatabase;

    protected TenantCompany $tenant;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = TenantCompany::factory()->create();
        $this->user = User::factory()->create(['tenant_company_id' => $this->tenant->id]);
        
        session(['tenant_company_id' => $this->tenant->id]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_be_edited_when_draft()
    {
        $bill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'status' => 'draft',
            'bill_type' => 'regular',
        ]);

        $this->assertTrue($bill->canBeEdited());
        $this->assertNull($bill->getLockReason());
    }

    /** @test */
    public function it_cannot_be_edited_when_not_draft()
    {
        $bill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'status' => 'issued',
            'bill_type' => 'regular',
        ]);

        $this->assertFalse($bill->canBeEdited());
        $this->assertEquals('status_not_draft', $bill->getLockReason());
    }

    /** @test */
    public function advance_bill_cannot_be_edited_when_child_is_issued()
    {
        $advanceBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'status' => 'draft',
            'bill_type' => 'advance',
        ]);

        Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'parent_bill_id' => $advanceBill->id,
            'status' => 'issued',
            'bill_type' => 'running',
        ]);

        $this->assertFalse($advanceBill->canBeEdited());
        $this->assertEquals('has_issued_child', $advanceBill->getLockReason());
    }

    /** @test */
    public function it_cannot_be_edited_when_has_payments()
    {
        $bill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'status' => 'draft',
            'bill_type' => 'regular',
        ]);

        \DB::table('bill_payments')->insert([
            'bill_id' => $bill->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '1000.00',
            'payment_method' => 'cash',
            'payment_date' => now(),
            'created_by' => $this->user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $bill->refresh();
        $this->assertFalse($bill->canBeEdited());
        $this->assertEquals('has_payments', $bill->getLockReason());
    }

    /** @test */
    public function advance_bill_cannot_be_edited_when_credit_applied()
    {
        $advanceBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'status' => 'draft',
            'bill_type' => 'advance',
        ]);

        \DB::table('bill_advance_adjustments')->insert([
            'advance_bill_id' => $advanceBill->id,
            'final_bill_id' => 999,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '500.00',
            'created_by' => $this->user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $advanceBill->refresh();
        $this->assertFalse($advanceBill->canBeEdited());
        $this->assertEquals('advance_applied', $advanceBill->getLockReason());
    }

    /** @test */
    public function regular_bill_cannot_be_edited_when_has_adjustments()
    {
        $regularBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'status' => 'draft',
            'bill_type' => 'regular',
        ]);

        \DB::table('bill_advance_adjustments')->insert([
            'advance_bill_id' => 888,
            'final_bill_id' => $regularBill->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '500.00',
            'created_by' => $this->user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $regularBill->refresh();
        $this->assertFalse($regularBill->canBeEdited());
        $this->assertEquals('has_advance_adjustments', $regularBill->getLockReason());
    }

    /** @test */
    public function it_throws_exception_on_update_when_locked()
    {
        $bill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'status' => 'issued',
            'bill_type' => 'regular',
            'total' => '1000.00',
        ]);

        $this->expectException(BillLockedException::class);

        $bill->update(['total' => '2000.00']);
    }

    /** @test */
    public function it_allows_lock_field_updates_even_when_locked()
    {
        $bill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'status' => 'issued',
            'bill_type' => 'regular',
            'is_locked' => true,
        ]);

        // This should not throw
        $bill->update([
            'is_locked' => true,
            'lock_reason' => 'status_not_draft',
        ]);

        $this->assertTrue($bill->fresh()->is_locked);
    }

    /** @test */
    public function it_calculates_remaining_balance_correctly()
    {
        $bill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'status' => 'issued',
            'bill_type' => 'regular',
            'total' => '10000.00',
            'net_payable_amount' => '8000.00', // After advance
        ]);

        \DB::table('bill_payments')->insert([
            'bill_id' => $bill->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '3000.00',
            'payment_method' => 'cash',
            'payment_date' => now(),
            'created_by' => $this->user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $bill->refresh();
        
        $this->assertEquals('3000.00', $bill->paid_amount);
        $this->assertEquals('5000.00', $bill->remaining_balance);
        $this->assertFalse($bill->is_fully_paid);
    }

    /** @test */
    public function scopes_filter_correctly()
    {
        Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'status' => 'draft',
            'bill_type' => 'advance',
        ]);

        Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'status' => 'issued',
            'bill_type' => 'regular',
        ]);

        Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'status' => 'paid',
            'bill_type' => 'running',
        ]);

        $this->assertEquals(1, Bill::forTenant()->draft()->count());
        $this->assertEquals(1, Bill::forTenant()->issued()->count());
        $this->assertEquals(1, Bill::forTenant()->advance()->count());
        $this->assertEquals(1, Bill::forTenant()->regular()->count());
        $this->assertEquals(1, Bill::forTenant()->running()->count());
        $this->assertEquals(2, Bill::forTenant()->unpaid()->count());
    }
}
```

#### Task 3: Create BillAdvanceAdjustmentTest

**File:** `tests/Unit/BillAdvanceAdjustmentTest.php` (NEW)

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Bill;
use App\Models\TenantCompany;
use App\Models\User;
use App\Services\BillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BillAdvanceAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    protected TenantCompany $tenant;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = TenantCompany::factory()->create();
        $this->user = User::factory()->create(['tenant_company_id' => $this->tenant->id]);
        
        session(['tenant_company_id' => $this->tenant->id]);
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_maintains_decimal_precision_in_calculations()
    {
        $advanceBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'bill_type' => 'advance',
            'status' => 'paid',
            'total' => '33333.33',
        ]);

        \DB::table('bill_payments')->insert([
            'bill_id' => $advanceBill->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '33333.33',
            'payment_method' => 'bank_transfer',
            'payment_date' => now(),
            'created_by' => $this->user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $regularBill1 = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $advanceBill->quotation_id,
            'bill_type' => 'regular',
            'status' => 'draft',
            'total' => '10000.00',
        ]);

        $regularBill2 = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $advanceBill->quotation_id,
            'bill_type' => 'regular',
            'status' => 'draft',
            'total' => '10000.00',
        ]);

        $service = app(BillingService::class);

        // Apply 11111.11 to first bill
        $service->applyAdvanceCredit($advanceBill, $regularBill1, '11111.11');

        // Apply 11111.11 to second bill
        $service->applyAdvanceCredit($advanceBill, $regularBill2, '11111.11');

        // Remaining should be exactly 11111.11
        $balance = $service->getUnappliedAdvanceBalance($advanceBill);
        
        $this->assertEquals('11111.11', $balance);
    }

    /** @test */
    public function it_prevents_duplicate_adjustments()
    {
        $advanceBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'bill_type' => 'advance',
            'status' => 'paid',
            'total' => '10000.00',
        ]);

        \DB::table('bill_payments')->insert([
            'bill_id' => $advanceBill->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '10000.00',
            'payment_method' => 'cash',
            'payment_date' => now(),
            'created_by' => $this->user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $regularBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $advanceBill->quotation_id,
            'bill_type' => 'regular',
            'status' => 'draft',
            'total' => '5000.00',
        ]);

        $service = app(BillingService::class);

        // First application should work
        $service->applyAdvanceCredit($advanceBill, $regularBill, '1000.00');

        // Second application to same pair should fail due to unique constraint
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        $service->applyAdvanceCredit($advanceBill, $regularBill, '500.00');
    }

    /** @test */
    public function it_correctly_reverses_adjustment_on_cancel()
    {
        $advanceBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'bill_type' => 'advance',
            'status' => 'paid',
            'total' => '10000.00',
        ]);

        \DB::table('bill_payments')->insert([
            'bill_id' => $advanceBill->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '10000.00',
            'payment_method' => 'cash',
            'payment_date' => now(),
            'created_by' => $this->user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $regularBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $advanceBill->quotation_id,
            'bill_type' => 'regular',
            'status' => 'issued',
            'total' => '15000.00',
            'advance_applied_amount' => '0.00',
            'net_payable_amount' => '15000.00',
        ]);

        $service = app(BillingService::class);
        $service->applyAdvanceCredit($advanceBill, $regularBill, '3000.00');

        $regularBill->refresh();
        $this->assertEquals('3000.00', $regularBill->advance_applied_amount);
        $this->assertEquals('12000.00', $regularBill->net_payable_amount);

        // Cancel the regular bill
        $service->cancelBill($regularBill);

        // Verify reversal
        $advanceBill->refresh();
        $this->assertEquals('10000.00', $service->getUnappliedAdvanceBalance($advanceBill));
    }
}
```

---

### ✅ End-of-Day Checklist (Day 15)

- [ ] BillingServiceTest created with all method tests
- [ ] BillModelTest created with all 6 locking rule tests
- [ ] BillAdvanceAdjustmentTest created with precision tests
- [ ] All tests pass: `php artisan test --filter=Unit`
- [ ] Test coverage > 80% for billing-related classes

### ⚠️ Pitfalls & Notes (Day 15)

1. **Tenant Context:** Every test must set the tenant context via session. Without this, tenant-scoped queries will fail.

2. **Decimal Precision:** Always use string comparisons for monetary values. Never use `assertEquals(100.00, $value)`.

3. **Factory States:** Create factory states for different bill types (`advance`, `regular`, `running`) to simplify test setup.

---

## Day 16 — Feature Tests & Final QA

### 🎯 Goal
By the end of Day 16, you will have complete feature tests for HTTP endpoints and a final QA checklist.

### 📋 Prerequisites
- [ ] Day 15 unit tests completed
- [ ] All routes registered

---

### 🧪 Testing Tasks

#### Task 1: Create CreateAdvanceBillTest

**File:** `tests/Feature/CreateAdvanceBillTest.php` (NEW)

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Bill;
use App\Models\Quotation;
use App\Models\TenantCompany;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateAdvanceBillTest extends TestCase
{
    use RefreshDatabase;

    protected TenantCompany $tenant;
    protected User $user;
    protected Quotation $quotation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = TenantCompany::factory()->create();
        $this->user = User::factory()->create(['tenant_company_id' => $this->tenant->id]);
        $this->quotation = Quotation::factory()->create(['tenant_company_id' => $this->tenant->id]);

        // Give user necessary permissions
        $this->user->givePermissionTo(['bills.view', 'bills.create', 'bills.issue']);
        
        session(['tenant_company_id' => $this->tenant->id]);
    }

    /** @test */
    public function guest_cannot_create_bill()
    {
        $response = $this->post(route('bills.store'), [
            'bill_type' => 'advance',
            'quotation_id' => $this->quotation->id,
            'amount' => '10000',
        ]);

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function unauthorized_user_cannot_create_bill()
    {
        $user = User::factory()->create(['tenant_company_id' => $this->tenant->id]);
        // No permissions
        
        $response = $this->actingAs($user)
            ->post(route('bills.store'), [
                'bill_type' => 'advance',
                'quotation_id' => $this->quotation->id,
                'amount' => '10000',
                'bill_date' => now()->format('d/m/Y'),
            ]);

        $response->assertForbidden();
    }

    /** @test */
    public function it_creates_advance_bill_successfully()
    {
        $response = $this->actingAs($this->user)
            ->post(route('bills.store'), [
                'bill_type' => 'advance',
                'quotation_id' => $this->quotation->id,
                'amount' => '10000.00',
                'tax_amount' => '1500.00',
                'bill_date' => now()->format('d/m/Y'),
                'notes' => 'Project advance',
            ]);

        $response->assertRedirect(route('bills.show', Bill::latest('id')->first()));
        
        $bill = Bill::where('bill_type', 'advance')->first();
        $this->assertNotNull($bill);
        $this->assertEquals('11500.00', $bill->total);
        $this->assertEquals('draft', $bill->status);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $response = $this->actingAs($this->user)
            ->post(route('bills.store'), [
                'bill_type' => 'advance',
                // Missing quotation_id and amount
            ]);

        $response->assertSessionHasErrors(['quotation_id', 'amount']);
    }

    /** @test */
    public function it_validates_quotation_belongs_to_tenant()
    {
        $otherTenant = TenantCompany::factory()->create();
        $otherQuotation = Quotation::factory()->create(['tenant_company_id' => $otherTenant->id]);

        $response = $this->actingAs($this->user)
            ->post(route('bills.store'), [
                'bill_type' => 'advance',
                'quotation_id' => $otherQuotation->id,
                'amount' => '10000',
                'bill_date' => now()->format('d/m/Y'),
            ]);

        $response->assertSessionHasErrors(['quotation_id']);
    }

    /** @test */
    public function it_issues_bill_successfully()
    {
        $bill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $this->quotation->id,
            'status' => 'draft',
            'bill_type' => 'advance',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('bills.issue', $bill));

        $response->assertRedirect(route('bills.show', $bill));
        
        $this->assertEquals('issued', $bill->fresh()->status);
        $this->assertTrue($bill->fresh()->is_locked);
    }

    /** @test */
    public function it_records_payment_on_issued_bill()
    {
        $this->user->givePermissionTo('bills.payments');
        
        $bill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $this->quotation->id,
            'status' => 'issued',
            'bill_type' => 'advance',
            'total' => '10000.00',
            'net_payable_amount' => '10000.00',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('bills.payments.store', $bill), [
                'amount' => '5000.00',
                'payment_method' => 'bank_transfer',
                'payment_date' => now()->format('d/m/Y'),
            ]);

        $response->assertRedirect();
        
        $this->assertEquals(1, $bill->payments()->count());
        $this->assertEquals('partially_paid', $bill->fresh()->status);
    }
}
```

#### Task 2: Create CreateRegularBillTest

**File:** `tests/Feature/CreateRegularBillTest.php` (NEW)

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Bill;
use App\Models\Challan;
use App\Models\Quotation;
use App\Models\TenantCompany;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateRegularBillTest extends TestCase
{
    use RefreshDatabase;

    protected TenantCompany $tenant;
    protected User $user;
    protected Quotation $quotation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = TenantCompany::factory()->create();
        $this->user = User::factory()->create(['tenant_company_id' => $this->tenant->id]);
        $this->quotation = Quotation::factory()->create(['tenant_company_id' => $this->tenant->id]);

        $this->user->givePermissionTo(['bills.view', 'bills.create']);
        
        session(['tenant_company_id' => $this->tenant->id]);
    }

    /** @test */
    public function it_creates_regular_bill_with_items()
    {
        $challan = Challan::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $this->quotation->id,
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('bills.store'), [
                'bill_type' => 'regular',
                'quotation_id' => $this->quotation->id,
                'challan_ids' => [$challan->id],
                'bill_items' => [
                    [
                        'product_name' => 'Product A',
                        'quantity' => '10',
                        'unit_price' => '100.00',
                    ],
                ],
                'bill_date' => now()->format('d/m/Y'),
            ]);

        $response->assertRedirect();
        
        $bill = Bill::where('bill_type', 'regular')->first();
        $this->assertNotNull($bill);
        $this->assertEquals('1000.00', $bill->subtotal);
        $this->assertEquals('1000.00', $bill->total);
    }

    /** @test */
    public function regular_bill_is_not_blocked_by_existing_advance()
    {
        // Create an advance bill for the same quotation
        Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $this->quotation->id,
            'bill_type' => 'advance',
            'status' => 'issued',
        ]);

        // This should NOT prevent creating a regular bill
        $response = $this->actingAs($this->user)
            ->post(route('bills.store'), [
                'bill_type' => 'regular',
                'quotation_id' => $this->quotation->id,
                'challan_ids' => [1],
                'bill_items' => [
                    ['product_name' => 'Product', 'quantity' => '1', 'unit_price' => '100'],
                ],
                'bill_date' => now()->format('d/m/Y'),
            ]);

        // Should succeed - no longer blocked
        $response->assertRedirect();
        $this->assertEquals(1, Bill::where('bill_type', 'regular')->count());
    }

    /** @test */
    public function it_validates_challans_belong_to_quotation()
    {
        $otherQuotation = Quotation::factory()->create(['tenant_company_id' => $this->tenant->id]);
        $challan = Challan::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $otherQuotation->id, // Different quotation
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('bills.store'), [
                'bill_type' => 'regular',
                'quotation_id' => $this->quotation->id,
                'challan_ids' => [$challan->id],
                'bill_items' => [
                    ['product_name' => 'Product', 'quantity' => '1', 'unit_price' => '100'],
                ],
                'bill_date' => now()->format('d/m/Y'),
            ]);

        $response->assertSessionHasErrors(['challan_ids']);
    }
}
```

#### Task 3: Create ApplyAdvanceCreditTest

**File:** `tests/Feature/ApplyAdvanceCreditTest.php` (NEW)

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Bill;
use App\Models\Quotation;
use App\Models\TenantCompany;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApplyAdvanceCreditTest extends TestCase
{
    use RefreshDatabase;

    protected TenantCompany $tenant;
    protected User $user;
    protected Quotation $quotation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = TenantCompany::factory()->create();
        $this->user = User::factory()->create(['tenant_company_id' => $this->tenant->id]);
        $this->quotation = Quotation::factory()->create(['tenant_company_id' => $this->tenant->id]);

        $this->user->givePermissionTo(['bills.view', 'bills.edit', 'bills.apply_advance']);
        
        session(['tenant_company_id' => $this->tenant->id]);
    }

    /** @test */
    public function it_applies_advance_credit_successfully()
    {
        $advanceBill = $this->createPaidAdvanceBill('10000.00');
        $regularBill = $this->createDraftRegularBill('15000.00');

        $response = $this->actingAs($this->user)
            ->post(route('bills.apply-advance', $regularBill), [
                'advance_bill_id' => $advanceBill->id,
                'amount' => '5000.00',
            ]);

        $response->assertRedirect();
        
        $regularBill->refresh();
        $this->assertEquals('5000.00', $regularBill->advance_applied_amount);
        $this->assertEquals('10000.00', $regularBill->net_payable_amount);
    }

    /** @test */
    public function it_rejects_over_application()
    {
        $advanceBill = $this->createPaidAdvanceBill('5000.00');
        $regularBill = $this->createDraftRegularBill('10000.00');

        $response = $this->actingAs($this->user)
            ->post(route('bills.apply-advance', $regularBill), [
                'advance_bill_id' => $advanceBill->id,
                'amount' => '99999.99', // Way more than available
            ]);

        $response->assertSessionHasErrors(['amount']);
    }

    /** @test */
    public function it_rejects_application_to_non_regular_bill()
    {
        $advanceBill = $this->createPaidAdvanceBill('10000.00');
        $runningBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $this->quotation->id,
            'bill_type' => 'running',
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('bills.apply-advance', $runningBill), [
                'advance_bill_id' => $advanceBill->id,
                'amount' => '1000.00',
            ]);

        $response->assertSessionHasErrors(['advance_bill_id']);
    }

    /** @test */
    public function it_rejects_application_from_different_quotation()
    {
        $otherQuotation = Quotation::factory()->create(['tenant_company_id' => $this->tenant->id]);
        
        $advanceBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $otherQuotation->id,
            'bill_type' => 'advance',
            'status' => 'paid',
        ]);

        \DB::table('bill_payments')->insert([
            'bill_id' => $advanceBill->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '10000.00',
            'payment_method' => 'cash',
            'payment_date' => now(),
            'created_by' => $this->user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $regularBill = $this->createDraftRegularBill('15000.00');

        $response = $this->actingAs($this->user)
            ->post(route('bills.apply-advance', $regularBill), [
                'advance_bill_id' => $advanceBill->id,
                'amount' => '1000.00',
            ]);

        $response->assertSessionHasErrors(['advance_bill_id']);
    }

    /** @test */
    public function it_rejects_partial_application()
    {
        $advanceBill = $this->createPaidAdvanceBill('10000.00');
        $regularBill1 = $this->createDraftRegularBill('10000.00');
        $regularBill2 = $this->createDraftRegularBill('10000.00');

        // Apply 7000 to first bill
        $this->actingAs($this->user)
            ->post(route('bills.apply-advance', $regularBill1), [
                'advance_bill_id' => $advanceBill->id,
                'amount' => '7000.00',
            ]);

        // Try to apply 4000 to second bill (only 3000 available)
        $response = $this->actingAs($this->user)
            ->post(route('bills.apply-advance', $regularBill2), [
                'advance_bill_id' => $advanceBill->id,
                'amount' => '4000.00',
            ]);

        $response->assertSessionHasErrors(['amount']);
    }

    protected function createPaidAdvanceBill(string $amount): Bill
    {
        $bill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $this->quotation->id,
            'bill_type' => 'advance',
            'status' => 'paid',
            'total' => $amount,
        ]);

        \DB::table('bill_payments')->insert([
            'bill_id' => $bill->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => $amount,
            'payment_method' => 'bank_transfer',
            'payment_date' => now(),
            'created_by' => $this->user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $bill;
    }

    protected function createDraftRegularBill(string $total): Bill
    {
        return Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $this->quotation->id,
            'bill_type' => 'regular',
            'status' => 'draft',
            'total' => $total,
            'net_payable_amount' => $total,
        ]);
    }
}
```

#### Task 4: Create BillCorrectionFlowTest

**File:** `tests/Feature/BillCorrectionFlowTest.php` (NEW)

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Bill;
use App\Models\Quotation;
use App\Models\TenantCompany;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BillCorrectionFlowTest extends TestCase
{
    use RefreshDatabase;

    protected TenantCompany $tenant;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = TenantCompany::factory()->create();
        $this->user = User::factory()->create(['tenant_company_id' => $this->tenant->id]);

        $this->user->givePermissionTo([
            'bills.view', 'bills.cancel', 'bills.reissue', 'bills.apply_advance'
        ]);
        
        session(['tenant_company_id' => $this->tenant->id]);
    }

    /** @test */
    public function it_cancels_issued_bill()
    {
        $bill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'status' => 'issued',
            'bill_type' => 'regular',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('bills.cancel', $bill), [
                'reason' => 'Customer requested correction',
            ]);

        $response->assertRedirect();
        
        $this->assertEquals('cancelled', $bill->fresh()->status);
    }

    /** @test */
    public function it_unapplies_advance_on_cancel()
    {
        $quotation = Quotation::factory()->create(['tenant_company_id' => $this->tenant->id]);
        
        $advanceBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => 'advance',
            'status' => 'paid',
            'total' => '10000.00',
        ]);

        \DB::table('bill_payments')->insert([
            'bill_id' => $advanceBill->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '10000.00',
            'payment_method' => 'cash',
            'payment_date' => now(),
            'created_by' => $this->user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $regularBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => 'regular',
            'status' => 'issued',
            'total' => '15000.00',
            'advance_applied_amount' => '5000.00',
            'net_payable_amount' => '10000.00',
        ]);

        \DB::table('bill_advance_adjustments')->insert([
            'advance_bill_id' => $advanceBill->id,
            'final_bill_id' => $regularBill->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '5000.00',
            'created_by' => $this->user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('bills.cancel', $regularBill), [
                'reason' => 'Correction needed',
            ]);

        $response->assertRedirect();
        
        // Verify advance was unapplied
        $advanceBill->refresh();
        $this->assertEquals('10000.00', $advanceBill->unapplied_amount);
        
        // Verify adjustment was soft deleted
        $this->assertSoftDeleted('bill_advance_adjustments', [
            'advance_bill_id' => $advanceBill->id,
            'final_bill_id' => $regularBill->id,
        ]);
    }

    /** @test */
    public function it_reissues_cancelled_bill()
    {
        $cancelledBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'status' => 'cancelled',
            'bill_type' => 'regular',
            'total' => '10000.00',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('bills.reissue', $cancelledBill));

        $response->assertRedirect();
        
        $newBill = Bill::where('reissued_from_id', $cancelledBill->id)->first();
        $this->assertNotNull($newBill);
        $this->assertEquals('draft', $newBill->status);
        $this->assertEquals('regular', $newBill->bill_type);
        $this->assertEquals('10000.00', $newBill->total);
        $this->assertNotEquals($cancelledBill->bill_number, $newBill->bill_number);
    }

    /** @test */
    public function complete_correction_workflow()
    {
        $quotation = Quotation::factory()->create(['tenant_company_id' => $this->tenant->id]);
        
        // 1. Create and pay advance bill
        $advanceBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => 'advance',
            'status' => 'paid',
            'total' => '10000.00',
        ]);

        \DB::table('bill_payments')->insert([
            'bill_id' => $advanceBill->id,
            'tenant_company_id' => $this->tenant->id,
            'amount' => '10000.00',
            'payment_method' => 'bank_transfer',
            'payment_date' => now(),
            'created_by' => $this->user->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Create regular bill
        $regularBill = Bill::factory()->create([
            'tenant_company_id' => $this->tenant->id,
            'quotation_id' => $quotation->id,
            'bill_type' => 'regular',
            'status' => 'issued',
            'total' => '15000.00',
            'net_payable_amount' => '15000.00',
        ]);

        // 3. Apply advance credit
        $this->actingAs($this->user)
            ->post(route('bills.apply-advance', $regularBill), [
                'advance_bill_id' => $advanceBill->id,
                'amount' => '5000.00',
            ]);

        $regularBill->refresh();
        $this->assertEquals('5000.00', $regularBill->advance_applied_amount);

        // 4. Cancel the regular bill
        $this->actingAs($this->user)
            ->post(route('bills.cancel', $regularBill), [
                'reason' => 'Price correction needed',
            ]);

        $regularBill->refresh();
        $this->assertEquals('cancelled', $regularBill->status);
        $this->assertEquals('0.00', $regularBill->advance_applied_amount);

        // 5. Verify advance balance is restored
        $advanceBill->refresh();
        $this->assertEquals('10000.00', $advanceBill->unapplied_amount);

        // 6. Reissue the cancelled bill
        $this->actingAs($this->user)
            ->post(route('bills.reissue', $regularBill));

        $newBill = Bill::where('reissued_from_id', $regularBill->id)->first();
        $this->assertNotNull($newBill);
        $this->assertEquals('draft', $newBill->status);

        // 7. Apply advance credit to new bill
        $this->actingAs($this->user)
            ->post(route('bills.apply-advance', $newBill), [
                'advance_bill_id' => $advanceBill->id,
                'amount' => '5000.00',
            ]);

        $newBill->refresh();
        $this->assertEquals('5000.00', $newBill->advance_applied_amount);
    }
}
```

---

### ✅ End-of-Day Checklist (Day 16)

- [ ] CreateAdvanceBillTest created
- [ ] CreateRegularBillTest created with "not blocked by advance" assertion
- [ ] ApplyAdvanceCreditTest created with over-application rejection
- [ ] BillCorrectionFlowTest created with complete workflow
- [ ] All tests pass: `php artisan test`
- [ ] Test coverage report generated: `php artisan test --coverage`
- [ ] Manual QA checklist completed

### ⚠️ Pitfalls & Notes (Day 16)

1. **Permission Setup:** Each test must grant the necessary permissions to the user. Use Spatie's `givePermissionTo()` method.

2. **Tenant Context:** Always set `session(['tenant_company_id' => $tenant->id])` before making requests.

3. **Redirection Testing:** Use `assertRedirect()` without URL for generic checks, or `assertRedirect(route('bills.show', $bill))` for specific route checks.

4. **Session Errors:** Use `assertSessionHasErrors(['field'])` to check validation errors.

---

## Final QA Checklist

After all tests pass, complete this manual QA checklist:

### Bill Creation
- [ ] Can create advance bill with valid data
- [ ] Can create running bill linked to advance
- [ ] Can create regular bill with challan selection
- [ ] Regular bill creation NOT blocked by existing advance
- [ ] Validation errors display correctly

### Bill Operations
- [ ] Can issue a draft bill
- [ ] Can cancel an issued bill
- [ ] Can reissue a cancelled bill
- [ ] Can edit draft bills only
- [ ] Locked bills show proper error message

### Advance Credit
- [ ] Advance credit banner displays correctly
- [ ] Can apply advance credit to regular bill
- [ ] Over-application is rejected
- [ ] Can remove credit from draft bill
- [ ] Credit is restored on bill cancellation

### Payments
- [ ] Can record payment on issued bill
- [ ] Status updates to partially_paid
- [ ] Status updates to paid when fully paid
- [ ] Can void payment on non-paid bill

### Permissions
- [ ] Unauthorized users cannot access bills
- [ ] Cross-tenant access is blocked
- [ ] Each action respects permission settings

### UI/UX
- [ ] All forms have proper error handling
- [ ] Loading states display during submission
- [ ] Success messages display after actions
- [ ] Timeline shows all bills correctly
- [ ] Correction history displays correctly

---

## Phase 7 Summary

| Day | Files Created |
|-----|---------------|
| 15 | BillingServiceTest.php, BillModelTest.php, BillAdvanceAdjustmentTest.php |
| 16 | CreateAdvanceBillTest.php, CreateRegularBillTest.php, ApplyAdvanceCreditTest.php, BillCorrectionFlowTest.php |

---

## Implementation Complete

All 7 phases of the billing module implementation guide are now complete. The guide covers:

- **Phase 1:** Database migrations and schema changes
- **Phase 2:** BillingService, Bill model, Form Requests, Policy
- **Phase 3:** Controllers and API endpoints
- **Phase 4:** Bill creation forms (Advance, Running, Regular)
- **Phase 5:** Advance credit management UI
- **Phase 6:** Cancellation and reissue workflow
- **Phase 7:** Unit and feature tests

**Total Duration:** 16 working days

**Next Steps:**
1. Review each phase file
2. Set up the development environment
3. Begin implementation following the day-by-day guide
4. Run tests after each phase
5. Conduct final QA before deployment
