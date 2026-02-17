<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'price', 'description'];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function features(): HasMany
    {
        return $this->hasMany(Feature::class);
    }
}
