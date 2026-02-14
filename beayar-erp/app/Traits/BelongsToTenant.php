<?php

namespace App\Traits;

use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

trait BelongsToTenant
{
    /**
     * The "booted" method of the model.
     */
    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Model $model) {
            if (Session::has('tenant_id')) {
                $model->tenant_company_id = Session::get('tenant_id');
            }
        });
    }

    /**
     * Get the company that owns the model.
     */
    public function company()
    {
        return $this->belongsTo(\App\Models\TenantCompany::class, 'tenant_company_id');
    }
}
