<?php

namespace App\Models;

use App\Traits\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Challan extends Model
{
    use BelongsToCompany;

    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date',
        'delivery_date' => 'date',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function revision(): BelongsTo
    {
        return $this->belongsTo(QuotationRevision::class, 'quotation_revision_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(ChallanProduct::class);
    }

    public function bills(): BelongsToMany
    {
        return $this->belongsToMany(Bill::class, 'bill_challans');
    }
}
