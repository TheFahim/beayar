<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Challan extends Model
{
    use BelongsToCompany;

    protected $guarded = ['id'];

    protected $casts = [
        'challan_date' => 'date',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(ChallanProduct::class);
    }
}
