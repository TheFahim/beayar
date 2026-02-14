<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationStatus extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(TenantCompany::class, 'tenant_company_id');
    }

    // Custom Scope to get global + company specific
    public function scopeForCurrentCompany(Builder $query)
    {
        if (auth()->check() && auth()->user()->current_tenant_company_id) {
            return $query->where(function ($q) {
                $q->where('tenant_company_id', auth()->user()->current_tenant_company_id)
                    ->orWhereNull('tenant_company_id');
            });
        }

        return $query->whereNull('tenant_company_id');
    }
}
