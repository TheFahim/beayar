<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillAdvanceAdjustment extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'advance_bill_id',
        'final_bill_id',
        'tenant_company_id',
        'amount',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the advance bill providing the credit.
     */
    public function advanceBill(): BelongsTo
    {
        return $this->belongsTo(Bill::class, 'advance_bill_id');
    }

    /**
     * Get the final bill receiving the credit.
     */
    public function finalBill(): BelongsTo
    {
        return $this->belongsTo(Bill::class, 'final_bill_id');
    }

    /**
     * Get the user who created this adjustment.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
