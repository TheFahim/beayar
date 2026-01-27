<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class QuotationStatus extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(UserCompany::class, 'user_company_id');
    }

    // Custom Scope to get global + company specific
    public function scopeForCurrentCompany(Builder $query)
    {
        if (auth()->check() && auth()->user()->current_user_company_id) {
            return $query->where(function ($q) {
                $q->where('user_company_id', auth()->user()->current_user_company_id)
                  ->orWhereNull('user_company_id');
            });
        }
        return $query->whereNull('user_company_id');
    }
}
