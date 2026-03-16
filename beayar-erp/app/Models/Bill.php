<?php

namespace App\Models;

use App\Exceptions\BillLockedException;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Bill extends Model
{
    use BelongsToTenant, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_company_id',
        'quotation_id',
        'quotation_revision_id',
        'parent_bill_id',
        'invoice_no',
        'bill_date',
        'payment_received_date',
        'bill_type',
        'total_amount',
        'bill_amount',
        'bill_percentage',
        'due',
        'shipping',
        'discount',
        'status',
        'notes',
        // Phase 1: Locking fields
        'is_locked',
        'lock_reason',
        'locked_at',
        // Phase 1: Credit tracking fields
        'advance_applied_amount',
        'net_payable_amount',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'bill_date' => 'date',
        'payment_received_date' => 'date',
        'total_amount' => 'decimal:2',
        'bill_percentage' => 'decimal:2',
        'paid' => 'decimal:2',
        'paid_percent' => 'decimal:2',
        'due' => 'decimal:2',
        'shipping' => 'decimal:2',
        'discount' => 'decimal:2',
        // Phase 1: Locking fields
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
        // Phase 1: Credit tracking fields
        'advance_applied_amount' => 'decimal:2',
        'net_payable_amount' => 'decimal:2',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function quotationRevision()
    {
        return $this->belongsTo(QuotationRevision::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Bill::class, 'parent_bill_id');
    }

    public function children()
    {
        return $this->hasMany(Bill::class, 'parent_bill_id');
    }

    public function challans()
    {
        return $this->belongsToMany(Challan::class, 'bill_challans')
            ->withPivot('id')
            ->withTimestamps();
    }

    public function items()
    {
        return $this->hasManyThrough(
            BillItem::class,
            BillChallan::class,
            'bill_id',          // Foreign key on bill_challans referencing bills
            'bill_challan_id',  // Foreign key on bill_items referencing bill_challans
            'id',               // Local key on bills
            'id'                // Local key on bill_challans
        );
    }

    // Removed bill_installments relations

    public function receivedBills()
    {
        return $this->hasMany(ReceivedBill::class);
    }

    // ==========================================
    // PHASE 2: ADDITIONAL RELATIONSHIPS
    // ==========================================

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
        $totalReceived = $this->payments()->sum('amount');

        // Total applied to other bills
        $totalApplied = $this->advanceAdjustmentsGiven()->sum('amount');

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
        $netPayable = $this->net_payable_amount ?? $this->total_amount ?? 0;
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
            $hasIssuedChild = $this->children()
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

    public function isAdvance()
    {
        return $this->bill_type === 'advance';
    }

    public function isRunning()
    {
        return $this->bill_type === 'running';
    }

    public function isRegular()
    {
        return $this->bill_type === 'regular';
    }

    /**
     * Status constants for easy reference
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
}
