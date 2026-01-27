<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponLimit extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'min_expenditure' => 'decimal:2',
        'max_expenditure' => 'decimal:2',
    ];

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }
}
