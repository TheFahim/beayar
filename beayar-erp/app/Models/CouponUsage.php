<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponUsage extends Model
{
    protected $table = 'coupon_usage';
    
    protected $guarded = ['id'];

    protected $casts = [
        'used_at' => 'datetime',
        'discount_applied' => 'decimal:2',
    ];

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function platformInvoice(): BelongsTo
    {
        return $this->belongsTo(PlatformInvoice::class);
    }
}
