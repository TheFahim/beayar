<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallanProduct extends Model
{
    protected $guarded = ['id'];

    public function challan(): BelongsTo
    {
        return $this->belongsTo(Challan::class);
    }

    public function quotationProduct(): BelongsTo
    {
        return $this->belongsTo(QuotationProduct::class);
    }
}
