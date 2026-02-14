<?php

namespace App\Traits;

use App\Models\TenantCompany;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToCompany
{
    public static function bootBelongsToCompany()
    {
        // Global Scope
        static::addGlobalScope('tenant_company_id', function (Builder $builder) {
            if (auth()->check() && auth()->user()->current_tenant_company_id) {
                $builder->where('tenant_company_id', auth()->user()->current_tenant_company_id);
            }
        });

        // Auto-assign company on create
        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->current_tenant_company_id && ! $model->tenant_company_id) {
                $model->tenant_company_id = auth()->user()->current_tenant_company_id;
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(TenantCompany::class, 'tenant_company_id');
    }
}
