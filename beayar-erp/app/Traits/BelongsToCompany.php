<?php

namespace App\Traits;

use App\Models\UserCompany;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToCompany
{
    public static function bootBelongsToCompany()
    {
        // Global Scope
        static::addGlobalScope('user_company_id', function (Builder $builder) {
            if (auth()->check() && auth()->user()->current_user_company_id) {
                $builder->where('user_company_id', auth()->user()->current_user_company_id);
            }
        });

        // Auto-assign company on create
        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->current_user_company_id && !$model->user_company_id) {
                $model->user_company_id = auth()->user()->current_user_company_id;
            }
        });
    }

    public function company()
    {
        return $this->belongsTo(UserCompany::class, 'user_company_id');
    }
}
