<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallanProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_product_id',
        'challan_id',
        'quantity',
        'remarks',
    ];

    public function challan(): BelongsTo
    {
        return $this->belongsTo(Challan::class);
    }

    public function quotationProduct(): BelongsTo
    {
        return $this->belongsTo(QuotationProduct::class);
    }
}
