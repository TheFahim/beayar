<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use BelongsToCompany;

    protected $guarded = ['id'];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function specifications()
    {
        return $this->hasMany(Specification::class);
    }

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    public function quotationProducts()
    {
        return $this->hasMany(QuotationProduct::class);
    }

    public function challanProducts()
    {
        return $this->hasMany(ChallanProduct::class);
    }

    public function getIsDeletableAttribute()
    {
        return $this->quotationProducts()->doesntExist() && $this->challanProducts()->doesntExist();
    }
}
