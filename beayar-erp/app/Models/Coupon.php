<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'discount_amount' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'min_expenditure' => 'decimal:2',
        'max_expenditure' => 'decimal:2',
    ];

    public function usage(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function limits(): HasMany
    {
        return $this->hasMany(CouponLimit::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(CouponCustomer::class);
    }
}
