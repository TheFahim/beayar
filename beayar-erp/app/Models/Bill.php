<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Bill extends Model
{
    use BelongsToCompany;

    protected $guarded = ['id'];

    protected $casts = [
        'bill_date' => 'date',
        'payment_received_date' => 'date',
        'total_amount' => 'decimal:2',
        'bill_amount' => 'decimal:2',
        'due' => 'decimal:2',
        'discount' => 'decimal:2',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function quotationRevision(): BelongsTo
    {
        return $this->belongsTo(QuotationRevision::class);
    }

    public function parentBill(): BelongsTo
    {
        return $this->belongsTo(Bill::class, 'parent_bill_id');
    }

    public function subBills(): HasMany
    {
        return $this->hasMany(Bill::class, 'parent_bill_id');
    }

    public function billChallans(): HasMany
    {
        return $this->hasMany(BillChallan::class);
    }

    public function challans(): HasManyThrough
    {
        return $this->hasManyThrough(Challan::class, BillChallan::class, 'bill_id', 'id', 'id', 'challan_id');
    }

    public function receivedBills(): HasMany
    {
        return $this->hasMany(ReceivedBill::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
