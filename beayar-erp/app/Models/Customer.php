<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    use BelongsToCompany, HasFactory;

    protected $guarded = ['id'];

    public function customerCompany(): BelongsTo
    {
        return $this->belongsTo(CustomerCompany::class);
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class);
    }

    public function getIsDeletableAttribute()
    {
        return $this->quotations()->doesntExist();
    }
}
