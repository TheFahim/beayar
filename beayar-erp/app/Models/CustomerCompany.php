<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerCompany extends Model
{
    use BelongsToCompany, HasFactory;

    protected $guarded = ['id'];

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
