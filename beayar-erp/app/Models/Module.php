<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = ['slug', 'price'];

    protected $casts = [
        'price' => 'decimal:2',
    ];
}
