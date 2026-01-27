<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillItem extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'bill_price' => 'decimal:2',
    ];

    public function billChallan(): BelongsTo
    {
        return $this->belongsTo(BillChallan::class);
    }

    public function quotationProduct(): BelongsTo
    {
        return $this->belongsTo(QuotationProduct::class);
    }

    public function challanProduct(): BelongsTo
    {
        return $this->belongsTo(ChallanProduct::class);
    }
}
