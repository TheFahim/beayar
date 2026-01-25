<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public function quotation_products()
    {
        return $this->hasMany(QuotationProduct::class);
    }

    public function image()
    {
        return $this->belongsTo(Image::class);
    }

    /**
     * Get the specifications for the product.
     *
     * A product can have multiple specifications.
     */
    public function specifications()
    {
        return $this->hasMany(Specification::class);
    }

    /**
     * Get the brand origin for the product.
     *
     * A product can have a brand origin.
     */
}
