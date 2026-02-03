<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'bill_challan_id',
        'quotation_product_id',
        'challan_product_id',
        'quantity',
        'remaining_quantity',
        'unit_price',
        'bill_price',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'remaining_quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'bill_price' => 'decimal:2',
    ];

    /**
     * Get the bill-challan pivot that owns this item.
     */
    public function billChallan(): BelongsTo
    {
        return $this->belongsTo(BillChallan::class, 'bill_challan_id');
    }

    /**
     * Get the quotation product for this bill item.
     */
    public function quotationProduct(): BelongsTo
    {
        return $this->belongsTo(QuotationProduct::class, 'quotation_product_id');
    }

    /**
     * Get the challan product for this bill item.
     */
    public function challanProduct(): BelongsTo
    {
        return $this->belongsTo(ChallanProduct::class, 'challan_product_id');
    }

    /**
     * Calculate bill price based on quantity and unit price.
     */
    public function calculateBillPrice(): void
    {
        $this->bill_price = ($this->quantity * $this->unit_price);
    }

    /**
     * Boot method to calculate bill price before saving.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function (BillItem $billItem) {
            $billItem->calculateBillPrice();
        });
    }
}
