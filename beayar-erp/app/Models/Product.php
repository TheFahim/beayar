<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use BelongsToTenant, HasFactory;

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
        return $this->hasManyThrough(
            ChallanProduct::class,
            QuotationProduct::class,
            'product_id', // Foreign key on quotation_products table...
            'quotation_product_id', // Foreign key on challan_products table...
            'id', // Local key on products table...
            'id' // Local key on quotation_products table...
        );
    }

    public function getIsDeletableAttribute()
    {
        return $this->quotationProducts()->doesntExist() && $this->challanProducts()->doesntExist();
    }
}
