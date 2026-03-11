# Phase 2 — Backend Core & Services (Days 3-5)

This phase builds the core backend infrastructure: the BillingService class, Bill model enhancements with the 6-rule locking system, custom exceptions, and all necessary relationships.

---

## Day 3 — BillingService Foundation & Bill Model Relationships

### 🎯 Goal
By the end of Day 3, you will have a complete BillingService class with all method signatures and the Bill model with full relationships and scopes.

### 📋 Prerequisites
- [ ] Phase 1 migrations completed
- [ ] BillPayment and BillAdvanceAdjustment models created
- [ ] Understanding of the existing billing workflow

---

### ⚙️ Backend Tasks

#### Task 1: Create BillLockedException

**File:** `app/Exceptions/BillLockedException.php` (NEW)

```php
<?php

namespace App\Exceptions;

use Exception;
use App\Models\Bill;

/**
 * Exception thrown when attempting to modify a locked bill.
 * 
 * This exception is thrown when any of the 6 locking rules prevent
 * modification of a bill. The exception carries the bill instance
 * and the specific lock reason for proper error handling.
 */
class BillLockedException extends Exception
{
    /**
     * The bill that is locked.
     */
    protected Bill $bill;

    /**
     * The reason the bill is locked.
     */
    protected string $reason;

    /**
     * Human-readable messages for each lock reason.
     */
    protected array $reasonMessages = [
        'status_not_draft' => 'This bill cannot be edited because it is not in draft status.',
        'has_issued_child' => 'This bill cannot be edited because it has issued child bills.',
        'has_payments' => 'This bill cannot be edited because payments have been recorded.',
        'challan_quantity_violation' => 'This bill cannot be edited because it would reduce quantities below delivered amounts.',
        'advance_applied' => 'This bill cannot be edited because advance credit has been applied.',
        'has_advance_adjustments' => 'This bill cannot be edited because advance adjustments reference it.',
    ];

    /**
     * Create a new BillLockedException instance.
     *
     * @param Bill $bill The locked bill
     * @param string $reason The lock reason (must match a Bill::LOCK_REASON_* constant)
     */
    public function __construct(Bill $bill, string $reason)
    {
        $this->bill = $bill;
        $this->reason = $reason;

        $message = $this->reasonMessages[$reason] 
            ?? "This bill is locked and cannot be modified. Reason: {$reason}";

        parent::__construct($message, 422);
    }

    /**
     * Get the locked bill.
     */
    public function getBill(): Bill
    {
        return $this->bill;
    }

    /**
     * Get the lock reason.
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * Get the human-readable reason message.
     */
    public function getReasonMessage(): string
    {
        return $this->reasonMessages[$this->reason] ?? $this->getMessage();
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function render($request)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
                'errors' => [
                    'bill' => [$this->getReasonMessage()]
                ],
                'lock_reason' => $this->reason,
                'bill_id' => $this->bill->id,
            ], 422);
        }

        // For web requests, redirect back with error
        return redirect()
            ->back()
            ->withInput()
            ->withErrors(['bill' => $this->getReasonMessage()]);
    }
}
```

#### Task 2: Update Exception Handler

**File:** `app/Exceptions/Handler.php` (MODIFICATION)

Ensure the handler properly renders BillLockedException:

```php
<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use App\Exceptions\BillLockedException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Handle BillLockedException
        $this->renderable(function (BillLockedException $e, $request) {
            return $e->render($request);
        });
    }
}
```

#### Task 3: Enhance Bill Model with Relationships

**File:** `app/Models/Bill.php` (MODIFICATION)

Add all relationships, scopes, and the locking guard:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Exceptions\BillLockedException;
use Illuminate\Support\Facades\DB;

class Bill extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tenant_company_id',
        'quotation_id',
        'quotation_revision_id',
        'bill_type',
        'parent_bill_id',
        'bill_number',
        'bill_date',
        'due_date',
        'status',
        'subtotal',
        'tax_amount',
        'total',
        'due',
        'shipping',
        'notes',
        'terms_conditions',
        'is_locked',
        'lock_reason',
        'locked_at',
        'advance_applied_amount',
        'net_payable_amount',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'bill_date' => 'date',
        'due_date' => 'date',
        'locked_at' => 'datetime',
        'is_locked' => 'boolean',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'due' => 'decimal:2',
        'shipping' => 'decimal:2',
        'advance_applied_amount' => 'decimal:2',
        'net_payable_amount' => 'decimal:2',
    ];

    /**
     * Status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_ISSUED = 'issued';
    const STATUS_PAID = 'paid';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_PARTIALLY_PAID = 'partially_paid';
    const STATUS_ADJUSTED = 'adjusted';

    /**
     * Bill type constants
     */
    const TYPE_ADVANCE = 'advance';
    const TYPE_REGULAR = 'regular';
    const TYPE_RUNNING = 'running';

    /**
     * Lock reason constants
     */
    const LOCK_REASON_STATUS = 'status_not_draft';
    const LOCK_REASON_CHILD = 'has_issued_child';
    const LOCK_REASON_PAYMENTS = 'has_payments';
    const LOCK_REASON_CHALLAN = 'challan_quantity_violation';
    const LOCK_REASON_ADVANCE = 'advance_applied';
    const LOCK_REASON_ADJUSTMENTS = 'has_advance_adjustments';

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get the quotation this bill belongs to.
     */
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    /**
     * Get the quotation revision this bill belongs to.
     */
    public function quotationRevision(): BelongsTo
    {
        return $this->belongsTo(QuotationRevision::class);
    }

    /**
     * Get the tenant company.
     */
    public function tenantCompany(): BelongsTo
    {
        return $this->belongsTo(TenantCompany::class);
    }

    /**
     * Get the parent bill (for Running bills linked to Advance).
     */
    public function parentBill(): BelongsTo
    {
        return $this->belongsTo(Bill::class, 'parent_bill_id');
    }

    /**
     * Get the child bills (Running bills created from this Advance).
     */
    public function childBills(): HasMany
    {
        return $this->hasMany(Bill::class, 'parent_bill_id');
    }

    /**
     * Get the challans linked to this bill.
     */
    public function challans(): BelongsToMany
    {
        return $this->belongsToMany(Challan::class, 'bill_challans', 'bill_id', 'challan_id')
            ->withPivot(['tenant_company_id'])
            ->withTimestamps();
    }

    /**
     * Get the bill items for this bill.
     */
    public function billItems(): HasMany
    {
        return $this->hasMany(BillItem::class);
    }

    /**
     * Get the payments for this bill.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(BillPayment::class);
    }

    /**
     * Get the advance adjustments where this bill is the advance (credit given).
     */
    public function advanceAdjustmentsGiven(): HasMany
    {
        return $this->hasMany(BillAdvanceAdjustment::class, 'advance_bill_id');
    }

    /**
     * Get the advance adjustments where this bill is the final bill (credit received).
     */
    public function advanceAdjustmentsReceived(): HasMany
    {
        return $this->hasMany(BillAdvanceAdjustment::class, 'final_bill_id');
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope for tenant filtering.
     */
    public function scopeForTenant(Builder $query): Builder
    {
        return $query->where('tenant_company_id', currentTenantId());
    }

    /**
     * Scope for draft bills.
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    /**
     * Scope for issued bills.
     */
    public function scopeIssued(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ISSUED);
    }

    /**
     * Scope for unpaid bills (draft or issued, not paid).
     */
    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->whereIn('status', [
            self::STATUS_DRAFT,
            self::STATUS_ISSUED,
            self::STATUS_PARTIALLY_PAID,
        ]);
    }

    /**
     * Scope for advance bills.
     */
    public function scopeAdvance(Builder $query): Builder
    {
        return $query->where('bill_type', self::TYPE_ADVANCE);
    }

    /**
     * Scope for regular bills.
     */
    public function scopeRegular(Builder $query): Builder
    {
        return $query->where('bill_type', self::TYPE_REGULAR);
    }

    /**
     * Scope for running bills.
     */
    public function scopeRunning(Builder $query): Builder
    {
        return $query->where('bill_type', self::TYPE_RUNNING);
    }

    /**
     * Scope for locked bills.
     */
    public function scopeLocked(Builder $query): Builder
    {
        return $query->where('is_locked', true);
    }

    /**
     * Scope for unlocked bills.
     */
    public function scopeUnlocked(Builder $query): Builder
    {
        return $query->where('is_locked', false);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get the unapplied advance amount.
     * 
     * Formula: total_advance_amount - sum(all_applied_adjustments)
     * 
     * Only applicable for advance bills.
     */
    public function getUnappliedAmountAttribute(): string
    {
        if ($this->bill_type !== self::TYPE_ADVANCE) {
            return '0.00';
        }

        // Total received from payments
        $totalReceived = $this->payments()
            ->sum('amount');

        // Total applied to other bills
        $totalApplied = $this->advanceAdjustmentsGiven()
            ->sum('amount');

        return bcsub($totalReceived, $totalApplied, 2);
    }

    /**
     * Get the total paid amount.
     */
    public function getPaidAmountAttribute(): string
    {
        return $this->payments()->sum('amount');
    }

    /**
     * Get the remaining balance.
     */
    public function getRemainingBalanceAttribute(): string
    {
        $netPayable = $this->net_payable_amount ?? $this->total;
        return bcsub($netPayable, $this->paid_amount, 2);
    }

    /**
     * Check if the bill is fully paid.
     */
    public function getIsFullyPaidAttribute(): bool
    {
        return bccomp($this->remaining_balance, '0.00', 2) <= 0;
    }

    // ==========================================
    // LOCKING METHODS (6-RULE SYSTEM)
    // ==========================================

    /**
     * Check if the bill can be edited.
     * 
     * Implements the 6-rule locking system:
     * 1. Status guard — only draft is editable
     * 2. Child bill guard — if any child bill is issued or higher, parent is locked
     * 3. Payment guard — if any record in bill_payments exists, locked
     * 4. Challan link guard — quantities cannot be reduced below delivered amounts
     * 5. Advance application guard — applied portion is immutable
     * 6. Billing type guard — regular bills lock once adjustments reference them
     *
     * @return bool
     */
    public function canBeEdited(): bool
    {
        return $this->getLockReason() === null;
    }

    /**
     * Get the lock reason if the bill cannot be edited.
     * Returns null if the bill can be edited.
     *
     * @return string|null
     */
    public function getLockReason(): ?string
    {
        // Rule 1: Status guard — only draft is editable
        if ($this->status !== self::STATUS_DRAFT) {
            return self::LOCK_REASON_STATUS;
        }

        // Rule 2: Child bill guard — if any child bill is issued or higher
        if ($this->bill_type === self::TYPE_ADVANCE) {
            $hasIssuedChild = $this->childBills()
                ->whereIn('status', [
                    self::STATUS_ISSUED,
                    self::STATUS_PAID,
                    self::STATUS_PARTIALLY_PAID,
                    self::STATUS_ADJUSTED,
                ])
                ->exists();
            
            if ($hasIssuedChild) {
                return self::LOCK_REASON_CHILD;
            }
        }

        // Rule 3: Payment guard — if any payment record exists
        if ($this->payments()->exists()) {
            return self::LOCK_REASON_PAYMENTS;
        }

        // Rule 4: Challan link guard — quantities cannot be reduced below delivered
        // This is checked during updates, not here. We mark as locked if previously flagged.
        if ($this->is_locked && $this->lock_reason === self::LOCK_REASON_CHALLAN) {
            return self::LOCK_REASON_CHALLAN;
        }

        // Rule 5: Advance application guard — applied portion is immutable
        if ($this->bill_type === self::TYPE_ADVANCE && $this->advanceAdjustmentsGiven()->exists()) {
            return self::LOCK_REASON_ADVANCE;
        }

        // Rule 6: Regular bills lock once adjustments reference them
        if ($this->bill_type === self::TYPE_REGULAR && $this->advanceAdjustmentsReceived()->exists()) {
            return self::LOCK_REASON_ADJUSTMENTS;
        }

        return null;
    }

    /**
     * Lock the bill with a specific reason.
     *
     * @param string $reason
     * @return void
     */
    public function lock(string $reason): void
    {
        $this->update([
            'is_locked' => true,
            'lock_reason' => $reason,
            'locked_at' => now(),
        ]);
    }

    /**
     * Unlock the bill.
     * Only use this if you're certain the lock reason no longer applies.
     *
     * @return void
     */
    public function unlock(): void
    {
        $this->update([
            'is_locked' => false,
            'lock_reason' => null,
            'locked_at' => null,
        ]);
    }

    /**
     * Boot the model and register the updating event listener.
     * This prevents modifications to locked bills at the model level.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::updating(function (Bill $bill) {
            // Skip lock check if we're only updating lock-related fields
            $lockFields = ['is_locked', 'lock_reason', 'locked_at', 'updated_at'];
            $changingFields = array_keys($bill->getDirty());
            $onlyLockFields = empty(array_diff($changingFields, $lockFields));

            if ($onlyLockFields) {
                return true;
            }

            // Check if bill can be edited
            $lockReason = $bill->getLockReason();
            if ($lockReason !== null) {
                throw new BillLockedException($bill, $lockReason);
            }

            return true;
        });
    }
}
```

---

### ✅ End-of-Day Checklist (Day 3)

- [ ] BillLockedException created with proper message handling
- [ ] Exception Handler updated to render BillLockedException
- [ ] Bill model has all relationships defined
- [ ] Bill model has all scopes defined
- [ ] Bill model has `canBeEdited()` method with 6-rule check
- [ ] Bill model has `boot()` method with updating observer
- [ ] All accessor methods created (`unapplied_amount`, `paid_amount`, etc.)
- [ ] `php artisan tinker` can create Bill model without errors

### ⚠️ Pitfalls & Notes (Day 3)

1. **Decimal Comparisons:** Always use `bccomp()` for comparing monetary values. Never use `==` or `>`.

2. **Lock Check on Update:** The `boot()` method's updating observer throws an exception, which will roll back any transaction the bill is being updated in.

3. **Skipping Lock Fields:** The observer skips the lock check when only updating lock-related fields. This allows the `lock()` method to work on locked bills.

4. **Tenant Scoping:** Every scope should include tenant filtering. Never forget `scopeForTenant()`.

---

## Day 4 — BillingService Core Methods

### 🎯 Goal
By the end of Day 4, you will have the BillingService class with all core methods for creating bills, applying advance credit, and managing bill lifecycle.

### 📋 Prerequisites
- [ ] Day 3 tasks completed
- [ ] Bill model fully enhanced
- [ ] Understanding of the 3 bill types (Advance, Running, Regular)

---

### ⚙️ Backend Tasks

#### Task 1: Create BillingService Class

**File:** `app/Services/BillingService.php` (NEW)

```php
<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\BillAdvanceAdjustment;
use App\Models\BillPayment;
use App\Models\Challan;
use App\Models\Quotation;
use App\Models\QuotationRevision;
use App\Exceptions\BillLockedException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Billing Service
 * 
 * Centralized service for all billing operations. This service handles:
 * - Bill creation (Advance, Running, Regular)
 * - Advance credit application
 * - Bill locking and unlocking
 * - Bill cancellation and reissue
 * - Payment recording
 * 
 * All methods use transactions and proper decimal precision.
 */
class BillingService
{
    /**
     * Create an Advance Bill.
     * 
     * An advance bill is created before any delivery, typically for
     * receiving upfront payment. It can later have credit applied
     * to regular bills.
     *
     * @param array $data Bill data (amount, dates, notes, etc.)
     * @param Quotation $quotation The quotation this advance is for
     * @return Bill
     * @throws \Exception
     */
    public function createAdvanceBill(array $data, Quotation $quotation): Bill
    {
        return DB::transaction(function () use ($data, $quotation) {
            // Generate bill number
            $billNumber = $this->generateBillNumber('ADV');

            // Calculate totals
            $subtotal = $data['amount'] ?? '0.00';
            $taxAmount = $data['tax_amount'] ?? '0.00';
            $total = bcadd($subtotal, $taxAmount, 2);

            $bill = Bill::create([
                'tenant_company_id' => currentTenantId(),
                'quotation_id' => $quotation->id,
                'quotation_revision_id' => $quotation->active_revision_id,
                'bill_type' => Bill::TYPE_ADVANCE,
                'bill_number' => $billNumber,
                'bill_date' => $data['bill_date'] ?? now(),
                'due_date' => $data['due_date'] ?? null,
                'status' => Bill::STATUS_DRAFT,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'due' => $data['due'] ?? '0.00',
                'shipping' => $data['shipping'] ?? '0.00',
                'notes' => $data['notes'] ?? null,
                'terms_conditions' => $data['terms_conditions'] ?? null,
                'advance_applied_amount' => '0.00',
                'net_payable_amount' => $total,
            ]);

            // Log activity
            activity('billing')
                ->performedOn($bill)
                ->causedBy(auth()->user())
                ->log("Advance bill {$billNumber} created for quotation {$quotation->quotation_number}");

            // Update quotation billing stage
            $quotation->update(['billing_stage' => Quotation::BILLING_STAGE_ADVANCE_PENDING]);

            return $bill;
        });
    }

    /**
     * Create a Running Bill.
     * 
     * A running bill is linked to an advance bill and represents
     * interim billing during a project. It's typically used for
     * progress-based billing.
     *
     * @param array $data Bill data
     * @param Bill $parentAdvanceBill The parent advance bill
     * @return Bill
     * @throws \Exception
     */
    public function createRunningBill(array $data, Bill $parentAdvanceBill): Bill
    {
        if ($parentAdvanceBill->bill_type !== Bill::TYPE_ADVANCE) {
            throw new \InvalidArgumentException('Parent bill must be an advance bill.');
        }

        return DB::transaction(function () use ($data, $parentAdvanceBill) {
            $billNumber = $this->generateBillNumber('RUN');

            $subtotal = $data['subtotal'] ?? '0.00';
            $taxAmount = $data['tax_amount'] ?? '0.00';
            $total = bcadd($subtotal, $taxAmount, 2);

            $bill = Bill::create([
                'tenant_company_id' => currentTenantId(),
                'quotation_id' => $parentAdvanceBill->quotation_id,
                'quotation_revision_id' => $parentAdvanceBill->quotation_revision_id,
                'bill_type' => Bill::TYPE_RUNNING,
                'parent_bill_id' => $parentAdvanceBill->id,
                'bill_number' => $billNumber,
                'bill_date' => $data['bill_date'] ?? now(),
                'due_date' => $data['due_date'] ?? null,
                'status' => Bill::STATUS_DRAFT,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'due' => $data['due'] ?? '0.00',
                'shipping' => $data['shipping'] ?? '0.00',
                'notes' => $data['notes'] ?? null,
                'terms_conditions' => $data['terms_conditions'] ?? null,
                'advance_applied_amount' => '0.00',
                'net_payable_amount' => $total,
            ]);

            activity('billing')
                ->performedOn($bill)
                ->causedBy(auth()->user())
                ->log("Running bill {$billNumber} created linked to advance {$parentAdvanceBill->bill_number}");

            // Update quotation billing stage
            $quotation = $parentAdvanceBill->quotation;
            $quotation->update(['billing_stage' => Quotation::BILLING_STAGE_RUNNING_IN_PROGRESS]);

            return $bill;
        });
    }

    /**
     * Create a Regular Bill.
     * 
     * A regular bill is the final bill created after all deliveries.
     * It can have advance credit applied to reduce the payable amount.
     * 
     * IMPORTANT: Regular bills are no longer blocked by existing advances.
     * They can be created independently and credit can be applied later.
     *
     * @param array $data Bill data including challan_ids and bill_items
     * @param Quotation $quotation The quotation this bill is for
     * @param array $challanIds Array of challan IDs to include
     * @return Bill
     * @throws \Exception
     */
    public function createRegularBill(array $data, Quotation $quotation, array $challanIds = []): Bill
    {
        return DB::transaction(function () use ($data, $quotation, $challanIds) {
            $billNumber = $this->generateBillNumber('REG');

            // Calculate totals from bill items
            $subtotal = '0.00';
            $billItems = $data['bill_items'] ?? [];

            foreach ($billItems as $item) {
                $itemTotal = bcmul($item['quantity'], $item['unit_price'], 2);
                $subtotal = bcadd($subtotal, $itemTotal, 2);
            }

            $taxAmount = $data['tax_amount'] ?? '0.00';
            $shipping = $data['shipping'] ?? '0.00';
            $total = bcadd(bcadd($subtotal, $taxAmount, 2), $shipping, 2);

            $bill = Bill::create([
                'tenant_company_id' => currentTenantId(),
                'quotation_id' => $quotation->id,
                'quotation_revision_id' => $quotation->active_revision_id,
                'bill_type' => Bill::TYPE_REGULAR,
                'bill_number' => $billNumber,
                'bill_date' => $data['bill_date'] ?? now(),
                'due_date' => $data['due_date'] ?? null,
                'status' => Bill::STATUS_DRAFT,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $total,
                'due' => $data['due'] ?? '0.00',
                'shipping' => $shipping,
                'notes' => $data['notes'] ?? null,
                'terms_conditions' => $data['terms_conditions'] ?? null,
                'advance_applied_amount' => '0.00',
                'net_payable_amount' => $total,
            ]);

            // Link challans
            if (!empty($challanIds)) {
                foreach ($challanIds as $challanId) {
                    DB::table('bill_challans')->insert([
                        'bill_id' => $bill->id,
                        'challan_id' => $challanId,
                        'tenant_company_id' => currentTenantId(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Create bill items
            foreach ($billItems as $item) {
                DB::table('bill_items')->insert([
                    'bill_id' => $bill->id,
                    'challan_product_id' => $item['challan_product_id'] ?? null,
                    'product_name' => $item['product_name'],
                    'description' => $item['description'] ?? null,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => bcmul($item['quantity'], $item['unit_price'], 2),
                    'tenant_company_id' => currentTenantId(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            activity('billing')
                ->performedOn($bill)
                ->causedBy(auth()->user())
                ->log("Regular bill {$billNumber} created for quotation {$quotation->quotation_number}");

            // Update quotation billing stage
            $quotation->update(['billing_stage' => Quotation::BILLING_STAGE_REGULAR_PENDING]);

            return $bill;
        });
    }

    /**
     * Apply advance credit to a final bill.
     *
     * @param Bill $advanceBill The advance bill providing credit
     * @param Bill $finalBill The bill receiving credit (must be regular type)
     * @param string $amount Amount to apply (as string for precision)
     * @return BillAdvanceAdjustment
     * @throws \Exception
     */
    public function applyAdvanceCredit(Bill $advanceBill, Bill $finalBill, string $amount): BillAdvanceAdjustment
    {
        // Validation
        if ($advanceBill->bill_type !== Bill::TYPE_ADVANCE) {
            throw new \InvalidArgumentException('Source bill must be an advance bill.');
        }

        if ($finalBill->bill_type !== Bill::TYPE_REGULAR) {
            throw new \InvalidArgumentException('Target bill must be a regular bill.');
        }

        if ($advanceBill->quotation_id !== $finalBill->quotation_id) {
            throw new \InvalidArgumentException('Both bills must belong to the same quotation.');
        }

        // Check available balance
        $availableBalance = $this->getUnappliedAdvanceBalance($advanceBill);
        if (bccomp($amount, $availableBalance, 2) > 0) {
            throw new \InvalidArgumentException(
                "Cannot apply {$amount}. Available balance is {$availableBalance}."
            );
        }

        // Check if final bill can accept credit
        if (!$finalBill->canBeEdited()) {
            throw new BillLockedException($finalBill, $finalBill->getLockReason());
        }

        return DB::transaction(function () use ($advanceBill, $finalBill, $amount) {
            // Create the adjustment record
            $adjustment = BillAdvanceAdjustment::create([
                'advance_bill_id' => $advanceBill->id,
                'final_bill_id' => $finalBill->id,
                'tenant_company_id' => currentTenantId(),
                'amount' => $amount,
                'created_by' => auth()->id(),
                'notes' => null,
            ]);

            // Update the final bill's applied amount and net payable
            $currentApplied = $finalBill->advance_applied_amount;
            $newApplied = bcadd($currentApplied, $amount, 2);
            $newNetPayable = bcsub($finalBill->total, $newApplied, 2);

            $finalBill->update([
                'advance_applied_amount' => $newApplied,
                'net_payable_amount' => max($newNetPayable, '0.00'),
            ]);

            activity('billing')
                ->performedOn($finalBill)
                ->causedBy(auth()->user())
                ->withProperties([
                    'advance_bill_id' => $advanceBill->id,
                    'amount' => $amount,
                ])
                ->log("Applied {$amount} advance credit from {$advanceBill->bill_number}");

            return $adjustment;
        });
    }

    /**
     * Remove advance credit from a final bill.
     * Used during cancellation or correction workflows.
     *
     * @param BillAdvanceAdjustment $adjustment
     * @return void
     * @throws \Exception
     */
    public function removeAdvanceCredit(BillAdvanceAdjustment $adjustment): void
    {
        $finalBill = $adjustment->finalBill;
        $advanceBill = $adjustment->advanceBill;
        $amount = $adjustment->amount;

        DB::transaction(function () use ($adjustment, $finalBill, $advanceBill, $amount) {
            // Soft delete the adjustment
            $adjustment->delete();

            // Update the final bill
            $currentApplied = $finalBill->advance_applied_amount;
            $newApplied = bcsub($currentApplied, $amount, 2);
            $newNetPayable = bcadd($finalBill->total, $amount, 2);

            $finalBill->update([
                'advance_applied_amount' => max($newApplied, '0.00'),
                'net_payable_amount' => $newNetPayable,
            ]);

            activity('billing')
                ->performedOn($finalBill)
                ->causedBy(auth()->user())
                ->withProperties([
                    'advance_bill_id' => $advanceBill->id,
                    'amount' => $amount,
                ])
                ->log("Removed {$amount} advance credit from {$advanceBill->bill_number}");
        });
    }

    /**
     * Lock a bill with a specific reason.
     *
     * @param Bill $bill
     * @param string $reason
     * @return void
     */
    public function lockBill(Bill $bill, string $reason): void
    {
        $bill->lock($reason);

        activity('billing')
            ->performedOn($bill)
            ->causedBy(auth()->user())
            ->withProperties(['reason' => $reason])
            ->log("Bill {$bill->bill_number} locked: {$reason}");
    }

    /**
     * Issue a bill (change status from draft to issued).
     *
     * @param Bill $bill
     * @return Bill
     * @throws BillLockedException|\Exception
     */
    public function issueBill(Bill $bill): Bill
    {
        if ($bill->status !== Bill::STATUS_DRAFT) {
            throw new \InvalidArgumentException('Only draft bills can be issued.');
        }

        return DB::transaction(function () use ($bill) {
            $oldStatus = $bill->status;
            
            $bill->update(['status' => Bill::STATUS_ISSUED]);

            // Lock the bill after issuing
            $bill->lock(Bill::LOCK_REASON_STATUS);

            activity('billing')
                ->performedOn($bill)
                ->causedBy(auth()->user())
                ->withProperties(['old_status' => $oldStatus, 'new_status' => Bill::STATUS_ISSUED])
                ->log("Bill {$bill->bill_number} issued");

            // Update quotation billing stage based on bill type
            $this->updateQuotationBillingStage($bill);

            return $bill->fresh();
        });
    }

    /**
     * Cancel a bill.
     *
     * @param Bill $bill
     * @param string|null $reason
     * @return Bill
     * @throws \Exception
     */
    public function cancelBill(Bill $bill, ?string $reason = null): Bill
    {
        if ($bill->status === Bill::STATUS_CANCELLED) {
            throw new \InvalidArgumentException('Bill is already cancelled.');
        }

        return DB::transaction(function () use ($bill, $reason) {
            $oldStatus = $bill->status;

            // Remove any advance adjustments if this is a regular bill
            if ($bill->bill_type === Bill::TYPE_REGULAR) {
                foreach ($bill->advanceAdjustmentsReceived as $adjustment) {
                    $this->removeAdvanceCredit($adjustment);
                }
            }

            $bill->update([
                'status' => Bill::STATUS_CANCELLED,
                'notes' => $reason ? ($bill->notes . "\nCancellation reason: " . $reason) : $bill->notes,
            ]);

            activity('billing')
                ->performedOn($bill)
                ->causedBy(auth()->user())
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => Bill::STATUS_CANCELLED,
                    'reason' => $reason,
                ])
                ->log("Bill {$bill->bill_number} cancelled");

            return $bill->fresh();
        });
    }

    /**
     * Reissue a cancelled bill (creates a new draft copy).
     *
     * @param Bill $cancelledBill
     * @return Bill
     * @throws \Exception
     */
    public function reissueBill(Bill $cancelledBill): Bill
    {
        if ($cancelledBill->status !== Bill::STATUS_CANCELLED) {
            throw new \InvalidArgumentException('Only cancelled bills can be reissued.');
        }

        return DB::transaction(function () use ($cancelledBill) {
            // Create a new bill copying data from the cancelled one
            $newBill = $cancelledBill->replicate([
                'id',
                'bill_number',
                'status',
                'is_locked',
                'lock_reason',
                'locked_at',
                'created_at',
                'updated_at',
            ]);

            // Generate new bill number
            $prefix = match ($cancelledBill->bill_type) {
                Bill::TYPE_ADVANCE => 'ADV',
                Bill::TYPE_RUNNING => 'RUN',
                Bill::TYPE_REGULAR => 'REG',
                default => 'BIL',
            };
            $newBill->bill_number = $this->generateBillNumber($prefix);
            $newBill->status = Bill::STATUS_DRAFT;
            $newBill->is_locked = false;
            $newBill->lock_reason = null;
            $newBill->locked_at = null;
            $newBill->advance_applied_amount = '0.00';
            $newBill->net_payable_amount = $newBill->total;
            $newBill->save();

            // Copy bill items
            foreach ($cancelledBill->billItems as $item) {
                $newItem = $item->replicate(['id', 'bill_id', 'created_at', 'updated_at']);
                $newItem->bill_id = $newBill->id;
                $newItem->save();
            }

            activity('billing')
                ->performedOn($newBill)
                ->causedBy(auth()->user())
                ->withProperties(['original_bill_id' => $cancelledBill->id])
                ->log("Bill reissued from cancelled bill {$cancelledBill->bill_number}");

            return $newBill;
        });
    }

    /**
     * Get the unapplied advance balance for an advance bill.
     *
     * @param Bill $advanceBill
     * @return string
     */
    public function getUnappliedAdvanceBalance(Bill $advanceBill): string
    {
        if ($advanceBill->bill_type !== Bill::TYPE_ADVANCE) {
            return '0.00';
        }

        return $advanceBill->unapplied_amount;
    }

    /**
     * Get billable challans for a quotation.
     * Returns challans that have not been fully billed yet.
     *
     * @param Quotation $quotation
     * @return Collection
     */
    public function getBillableChallans(Quotation $quotation): Collection
    {
        // Get all challans for this quotation
        $challans = $quotation->challans()
            ->with(['challanProducts.quotationProduct'])
            ->get();

        // Filter out fully billed challans
        return $challans->filter(function ($challan) {
            foreach ($challan->challanProducts as $cp) {
                $billedQuantity = DB::table('bill_items')
                    ->where('challan_product_id', $cp->id)
                    ->sum('quantity');
                
                if (bccomp($cp->quantity, $billedQuantity, 2) > 0) {
                    return true; // Has unbilled quantity
                }
            }
            return false;
        });
    }

    /**
     * Record a payment for a bill.
     *
     * @param Bill $bill
     * @param array $data Payment data
     * @return BillPayment
     * @throws \Exception
     */
    public function recordPayment(Bill $bill, array $data): BillPayment
    {
        if (!in_array($bill->status, [Bill::STATUS_ISSUED, Bill::STATUS_PARTIALLY_PAID])) {
            throw new \InvalidArgumentException('Can only record payments for issued or partially paid bills.');
        }

        return DB::transaction(function () use ($bill, $data) {
            $payment = BillPayment::create([
                'bill_id' => $bill->id,
                'tenant_company_id' => currentTenantId(),
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'payment_date' => $data['payment_date'] ?? now(),
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Update bill status
            $this->updateBillPaymentStatus($bill);

            activity('billing')
                ->performedOn($bill)
                ->causedBy(auth()->user())
                ->withProperties([
                    'payment_id' => $payment->id,
                    'amount' => $data['amount'],
                    'method' => $data['payment_method'],
                ])
                ->log("Payment of {$data['amount']} recorded for {$bill->bill_number}");

            return $payment;
        });
    }

    /**
     * Update bill status based on payments.
     *
     * @param Bill $bill
     * @return void
     */
    protected function updateBillPaymentStatus(Bill $bill): void
    {
        $paidAmount = $bill->paid_amount;
        $netPayable = $bill->net_payable_amount ?? $bill->total;

        if (bccomp($paidAmount, '0.00', 2) <= 0) {
            // No payments
            $newStatus = Bill::STATUS_ISSUED;
        } elseif (bccomp($paidAmount, $netPayable, 2) >= 0) {
            // Fully paid
            $newStatus = Bill::STATUS_PAID;
        } else {
            // Partially paid
            $newStatus = Bill::STATUS_PARTIALLY_PAID;
        }

        if ($bill->status !== $newStatus) {
            $oldStatus = $bill->status;
            $bill->update(['status' => $newStatus]);

            activity('billing')
                ->performedOn($bill)
                ->causedBy(auth()->user())
                ->withProperties(['old_status' => $oldStatus, 'new_status' => $newStatus])
                ->log("Bill {$bill->bill_number} status changed to {$newStatus}");
        }
    }

    /**
     * Update quotation billing stage based on bill type and status.
     *
     * @param Bill $bill
     * @return void
     */
    protected function updateQuotationBillingStage(Bill $bill): void
    {
        $quotation = $bill->quotation;
        if (!$quotation) return;

        $stage = match (true) {
            $bill->bill_type === Bill::TYPE_ADVANCE && $bill->status === Bill::STATUS_ISSUED 
                => Quotation::BILLING_STAGE_ADVANCE_ISSUED,
            $bill->bill_type === Bill::TYPE_RUNNING => Quotation::BILLING_STAGE_RUNNING_IN_PROGRESS,
            $bill->bill_type === Bill::TYPE_REGULAR && $bill->status === Bill::STATUS_ISSUED 
                => Quotation::BILLING_STAGE_COMPLETED,
            default => $quotation->billing_stage,
        };

        if ($stage !== $quotation->billing_stage) {
            $quotation->update(['billing_stage' => $stage]);
        }
    }

    /**
     * Generate a unique bill number.
     *
     * @param string $prefix
     * @return string
     */
    protected function generateBillNumber(string $prefix): string
    {
        $year = now()->format('Y');
        $month = now()->format('m');

        // Get the last bill number for this prefix
        $lastBill = Bill::forTenant()
            ->where('bill_number', 'like', "{$prefix}-{$year}{$month}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastBill) {
            // Extract the sequence number
            $parts = explode('-', $lastBill->bill_number);
            $lastSequence = (int) substr(end($parts), 6);
            $newSequence = $lastSequence + 1;
        } else {
            $newSequence = 1;
        }

        return sprintf('%s-%s%s%04d', $prefix, $year, $month, $newSequence);
    }
}
```

---

### ✅ End-of-Day Checklist (Day 4)

- [ ] BillingService class created
- [ ] `createAdvanceBill()` method implemented
- [ ] `createRunningBill()` method implemented
- [ ] `createRegularBill()` method implemented
- [ ] `applyAdvanceCredit()` method implemented
- [ ] `removeAdvanceCredit()` method implemented
- [ ] `issueBill()` method implemented
- [ ] `cancelBill()` method implemented
- [ ] `reissueBill()` method implemented
- [ ] `recordPayment()` method implemented
- [ ] All methods use `DB::transaction()`
- [ ] All methods use `bcmath` functions for decimals
- [ ] All methods log activity

### ⚠️ Pitfalls & Notes (Day 4)

1. **String Amounts:** All monetary amounts should be passed as strings to prevent floating-point issues.

2. **Bill Number Generation:** The bill number format is `{PREFIX}-{YYYYMM}{SEQUENCE}`. Ensure this matches your existing pattern.

3. **Quotation Stage Updates:** Each bill creation updates the quotation's billing_stage. This is critical for tracking billing progress.

4. **Activity Logging:** Every significant action is logged. This is mandatory for audit trails.

---

## Day 5 — Form Requests & Policies

### 🎯 Goal
By the end of Day 5, you will have all Form Request classes for validation and a BillPolicy for authorization.

### 📋 Prerequisites
- [ ] Day 4 tasks completed
- [ ] BillingService fully implemented
- [ ] Understanding of validation requirements

---

### ⚙️ Backend Tasks

#### Task 1: Create CreateAdvanceBillRequest

**File:** `app/Http/Requests/CreateAdvanceBillRequest.php` (NEW)

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateAdvanceBillRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'quotation_id' => [
                'required',
                'exists:quotations,id,tenant_company_id,' . currentTenantId(),
            ],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'bill_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:bill_date'],
            'due' => ['nullable', 'numeric', 'min:0'],
            'shipping' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'terms_conditions' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'quotation_id.exists' => 'The selected quotation does not exist or does not belong to your company.',
            'amount.min' => 'The advance amount must be at least 0.01.',
            'due_date.after_or_equal' => 'The due date must be on or after the bill date.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'tax_amount' => $this->tax_amount ?? '0.00',
            'due' => $this->due ?? '0.00',
            'shipping' => $this->shipping ?? '0.00',
        ]);
    }
}
```

#### Task 2: Create CreateRegularBillRequest

**File:** `app/Http/Requests/CreateRegularBillRequest.php` (NEW)

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Bill;
use App\Models\Challan;

class CreateRegularBillRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'quotation_id' => [
                'required',
                'exists:quotations,id,tenant_company_id,' . currentTenantId(),
            ],
            'challan_ids' => ['required', 'array', 'min:1'],
            'challan_ids.*' => [
                'required',
                'exists:challans,id,tenant_company_id,' . currentTenantId(),
            ],
            'bill_items' => ['required', 'array', 'min:1'],
            'bill_items.*.challan_product_id' => [
                'nullable',
                'exists:challan_products,id',
            ],
            'bill_items.*.product_name' => ['required', 'string', 'max:255'],
            'bill_items.*.description' => ['nullable', 'string', 'max:1000'],
            'bill_items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'bill_items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'shipping' => ['nullable', 'numeric', 'min:0'],
            'due' => ['nullable', 'numeric', 'min:0'],
            'bill_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:bill_date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'terms_conditions' => ['nullable', 'string', 'max:5000'],
            
            // Advance adjustment (optional)
            'advance_adjustment' => ['nullable', 'array'],
            'advance_adjustment.advance_bill_id' => [
                'required_with:advance_adjustment',
                'exists:bills,id,tenant_company_id,' . currentTenantId(),
            ],
            'advance_adjustment.amount' => [
                'required_with:advance_adjustment',
                'numeric',
                'min:0.01',
            ],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validate challans belong to the quotation
            $quotationId = $this->input('quotation_id');
            $challanIds = $this->input('challan_ids', []);

            foreach ($challanIds as $challanId) {
                $challan = Challan::find($challanId);
                if ($challan && $challan->quotation_id != $quotationId) {
                    $validator->errors()->add(
                        'challan_ids',
                        "Challan {$challan->challan_number} does not belong to this quotation."
                    );
                }
            }

            // Validate advance adjustment if provided
            if ($this->has('advance_adjustment')) {
                $this->validateAdvanceAdjustment($validator);
            }

            // Validate bill item quantities don't exceed unbilled amounts
            $this->validateBillItemQuantities($validator);
        });
    }

    /**
     * Validate advance adjustment.
     */
    protected function validateAdvanceAdjustment($validator): void
    {
        $advanceBillId = $this->input('advance_adjustment.advance_bill_id');
        $amount = $this->input('advance_adjustment.amount');
        $quotationId = $this->input('quotation_id');

        $advanceBill = Bill::find($advanceBillId);

        if (!$advanceBill) {
            return; // Already validated by exists rule
        }

        // Must be advance type
        if ($advanceBill->bill_type !== Bill::TYPE_ADVANCE) {
            $validator->errors()->add(
                'advance_adjustment.advance_bill_id',
                'The selected bill is not an advance bill.'
            );
            return;
        }

        // Must belong to same quotation
        if ($advanceBill->quotation_id != $quotationId) {
            $validator->errors()->add(
                'advance_adjustment.advance_bill_id',
                'The advance bill must belong to the same quotation.'
            );
            return;
        }

        // Check available balance
        $billingService = app(\App\Services\BillingService::class);
        $availableBalance = $billingService->getUnappliedAdvanceBalance($advanceBill);

        if (bccomp($amount, $availableBalance, 2) > 0) {
            $validator->errors()->add(
                'advance_adjustment.amount',
                "Cannot apply {$amount}. Available balance is {$availableBalance}."
            );
        }
    }

    /**
     * Validate bill item quantities.
     */
    protected function validateBillItemQuantities($validator): void
    {
        // Implementation depends on your specific business logic
        // This validates that quantities don't exceed available unbilled amounts
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'challan_ids.required' => 'Please select at least one challan for billing.',
            'challan_ids.min' => 'Please select at least one challan for billing.',
            'bill_items.required' => 'Please add at least one bill item.',
            'bill_items.min' => 'Please add at least one bill item.',
            'bill_items.*.quantity.min' => 'Quantity must be at least 0.01.',
            'bill_items.*.unit_price.min' => 'Unit price cannot be negative.',
        ];
    }
}

#### Task 3: Create ApplyAdvanceCreditRequest

**File:** `app/Http/Requests/ApplyAdvanceCreditRequest.php` (NEW)

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Bill;
use App\Services\BillingService;

class ApplyAdvanceCreditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'advance_bill_id' => [
                'required',
                'exists:bills,id,tenant_company_id,' . currentTenantId(),
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
            ],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $advanceBillId = $this->input('advance_bill_id');
            $amount = $this->input('amount');
            $finalBill = $this->route('bill');

            $advanceBill = Bill::find($advanceBillId);

            if (!$advanceBill || !$finalBill) {
                return;
            }

            // Must be advance type
            if ($advanceBill->bill_type !== Bill::TYPE_ADVANCE) {
                $validator->errors()->add(
                    'advance_bill_id',
                    'The selected bill is not an advance bill.'
                );
                return;
            }

            // Final bill must be regular type
            if ($finalBill->bill_type !== Bill::TYPE_REGULAR) {
                $validator->errors()->add(
                    'advance_bill_id',
                    'Credit can only be applied to regular bills.'
                );
                return;
            }

            // Must belong to same quotation
            if ($advanceBill->quotation_id !== $finalBill->quotation_id) {
                $validator->errors()->add(
                    'advance_bill_id',
                    'The advance bill must belong to the same quotation as this bill.'
                );
                return;
            }

            // Check available balance
            $billingService = app(BillingService::class);
            $availableBalance = $billingService->getUnappliedAdvanceBalance($advanceBill);

            if (bccomp($amount, $availableBalance, 2) > 0) {
                $validator->errors()->add(
                    'amount',
                    "Cannot apply {$amount}. Available balance is {$availableBalance}."
                );
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'advance_bill_id.required' => 'Please select an advance bill.',
            'advance_bill_id.exists' => 'The selected advance bill is invalid.',
            'amount.required' => 'Please enter an amount to apply.',
            'amount.min' => 'The amount must be at least 0.01.',
        ];
    }
}
```

#### Task 4: Create RecordPaymentRequest

**File:** `app/Http/Requests/RecordPaymentRequest.php` (NEW)

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Bill;

class RecordPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
            ],
            'payment_method' => [
                'required',
                'in:cash,bank_transfer,check,credit_card,upi,other',
            ],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $bill = $this->route('bill');
            $amount = $this->input('amount');

            if (!$bill) return;

            // Check bill is in correct status
            if (!in_array($bill->status, [Bill::STATUS_ISSUED, Bill::STATUS_PARTIALLY_PAID])) {
                $validator->errors()->add(
                    'amount',
                    'Payments can only be recorded for issued or partially paid bills.'
                );
                return;
            }

            // Check amount doesn't exceed remaining balance
            $remainingBalance = $bill->remaining_balance;
            if (bccomp($amount, $remainingBalance, 2) > 0) {
                $validator->errors()->add(
                    'amount',
                    "Payment amount cannot exceed remaining balance of {$remainingBalance}."
                );
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Please enter the payment amount.',
            'amount.min' => 'The payment amount must be at least 0.01.',
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'Please select a valid payment method.',
            'payment_date.required' => 'Please enter the payment date.',
            'payment_date.before_or_equal' => 'Payment date cannot be in the future.',
        ];
    }
}
```

#### Task 5: Create BillPolicy

**File:** `app/Policies/BillPolicy.php` (NEW)

```php
<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Bill;
use App\Exceptions\BillLockedException;

class BillPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('bills.view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Bill $bill): bool
    {
        return $user->hasPermissionTo('bills.view') 
            && $bill->tenant_company_id === currentTenantId();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('bills.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Bill $bill): bool
    {
        if (!$user->hasPermissionTo('bills.edit')) {
            return false;
        }

        if ($bill->tenant_company_id !== currentTenantId()) {
            return false;
        }

        return $bill->canBeEdited();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Bill $bill): bool
    {
        // Only draft bills can be deleted
        if ($bill->status !== Bill::STATUS_DRAFT) {
            return false;
        }

        return $user->hasPermissionTo('bills.delete')
            && $bill->tenant_company_id === currentTenantId();
    }

    /**
     * Determine whether the user can issue the bill.
     */
    public function issue(User $user, Bill $bill): bool
    {
        if (!$user->hasPermissionTo('bills.issue')) {
            return false;
        }

        if ($bill->tenant_company_id !== currentTenantId()) {
            return false;
        }

        return $bill->status === Bill::STATUS_DRAFT;
    }

    /**
     * Determine whether the user can cancel the bill.
     */
    public function cancel(User $user, Bill $bill): bool
    {
        if (!$user->hasPermissionTo('bills.cancel')) {
            return false;
        }

        if ($bill->tenant_company_id !== currentTenantId()) {
            return false;
        }

        // Only issued bills can be cancelled
        return in_array($bill->status, [
            Bill::STATUS_ISSUED,
            Bill::STATUS_PARTIALLY_PAID,
        ]);
    }

    /**
     * Determine whether the user can reissue the bill.
     */
    public function reissue(User $user, Bill $bill): bool
    {
        if (!$user->hasPermissionTo('bills.reissue')) {
            return false;
        }

        if ($bill->tenant_company_id !== currentTenantId()) {
            return false;
        }

        return $bill->status === Bill::STATUS_CANCELLED;
    }

    /**
     * Determine whether the user can record payments.
     */
    public function recordPayment(User $user, Bill $bill): bool
    {
        if (!$user->hasPermissionTo('bills.payments')) {
            return false;
        }

        if ($bill->tenant_company_id !== currentTenantId()) {
            return false;
        }

        return in_array($bill->status, [
            Bill::STATUS_ISSUED,
            Bill::STATUS_PARTIALLY_PAID,
        ]);
    }

    /**
     * Determine whether the user can apply advance credit.
     */
    public function applyAdvance(User $user, Bill $bill): bool
    {
        if (!$user->hasPermissionTo('bills.apply_advance')) {
            return false;
        }

        if ($bill->tenant_company_id !== currentTenantId()) {
            return false;
        }

        // Only regular bills can have advance applied
        if ($bill->bill_type !== Bill::TYPE_REGULAR) {
            return false;
        }

        return $bill->canBeEdited();
    }
}
```

#### Task 6: Register Policy in AuthServiceProvider

**File:** `app/Providers/AuthServiceProvider.php` (MODIFICATION)

```php
<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Bill;
use App\Policies\BillPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Bill::class => BillPolicy::class,
        // ... other policies
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
```

---

### ✅ End-of-Day Checklist (Day 5)

- [ ] CreateAdvanceBillRequest created with validation rules
- [ ] CreateRegularBillRequest created with challan validation
- [ ] ApplyAdvanceCreditRequest created with balance check
- [ ] RecordPaymentRequest created with amount validation
- [ ] BillPolicy created with all authorization methods
- [ ] Policy registered in AuthServiceProvider
- [ ] All Form Requests include tenant validation
- [ ] All Form Requests have custom error messages

### ⚠️ Pitfalls & Notes (Day 5)

1. **Tenant Validation:** Every Form Request must validate that resources belong to the current tenant. This is a security requirement.

2. **Policy Response:** The `update` policy returns `false` for locked bills instead of throwing an exception. This allows for proper 403 responses.

3. **Advance Balance Check:** The advance balance check uses the BillingService, which must be injected via `app()` in the validator.

4. **Permission Names:** Ensure the permission names match what's set up in Spatie. Common names are `bills.view`, `bills.create`, `bills.edit`, etc.

---

## Phase 2 Summary

| Day | Files Created | Files Modified |
|-----|---------------|----------------|
| 3 | BillLockedException.php | Bill.php, Handler.php |
| 4 | BillingService.php | None |
| 5 | 4 Form Requests, BillPolicy.php | AuthServiceProvider.php |

**Next:** Proceed to [Phase 3 — Backend API & Controllers](./phase3_backend_api.md)
