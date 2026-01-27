<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillChallan extends Model
{
    protected $guarded = ['id'];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function challan(): BelongsTo
    {
        return $this->belongsTo(Challan::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(BillItem::class);
    }
}
