<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserCompany extends Model
{
    protected $guarded = ['id'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function parentCompany(): BelongsTo
    {
        return $this->belongsTo(UserCompany::class, 'parent_company_id');
    }

    public function subCompanies(): HasMany
    {
        return $this->hasMany(UserCompany::class, 'parent_company_id');
    }

    public function customerCompanies(): HasMany
    {
        return $this->hasMany(CustomerCompany::class);
    }
}
