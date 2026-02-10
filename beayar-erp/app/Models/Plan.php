<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'limits', 'base_price', 'billing_cycle', 'is_active', 'module_access'];

    protected $casts = [
        'limits' => 'array',
        'module_access' => 'array',
        'base_price' => 'decimal:2',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }
}
