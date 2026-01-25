<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Challan extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_revision_id',
        'challan_no',
        'po_no',
        'date',
        'delivery_date',
    ];

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
