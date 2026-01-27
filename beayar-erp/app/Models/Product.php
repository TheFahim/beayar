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
        'specifications' => 'array',
    ];
}
