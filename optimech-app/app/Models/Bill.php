<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'quotation_id',
        'quotation_revision_id',
        'parent_bill_id',
        'invoice_no',
        'bill_date',
        'payment_received_date',
        'bill_type',
        'total_amount',
        'bill_percentage',
        'due',
        'shipping',
        'discount',
        'status',
        'notes',
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

    /**
     * Check if this is an advance bill
     */
    public function isAdvance(): bool
    {
        return $this->bill_type === 'advance';
    }

    /**
     * Check if this is a regular bill
     */
    public function isRegular(): bool
    {
        return $this->bill_type === 'regular';
    }

    /**
     * Check if this is a running bill
     */
    public function isRunning(): bool
    {
        return $this->bill_type === 'running';
    }

    /**
     * Get the total billed amount for this bill (including installments)
     */
    public function getTotalBilledAmount(): float
    {
        $childrenTotal = $this->children()
            ->where('bill_type', 'running')
            ->sum('total_amount');

        return $childrenTotal > 0 ? (float) $childrenTotal : (float) $this->total_amount;
    }

    /**
     * Get the remaining amount that can be billed
     */
    public function getRemainingAmount(): float
    {
        $totalBilled = $this->getTotalBilledAmount();

        return max(0, $this->total_amount - $totalBilled);
    }

    /**
     * Get the remaining percentage that can be billed
     */
    public function getRemainingPercentage(): float
    {
        $childrenPercent = $this->children()
            ->where('bill_type', 'running')
            ->sum('bill_percentage');

        return max(0.0, 100.0 - (float) $childrenPercent);
    }
}
