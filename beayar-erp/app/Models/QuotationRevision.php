<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuotationRevision extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'date' => 'date',
        'valid_until' => 'date',
        'validity' => 'date',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'shipping' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(QuotationProduct::class);
    }
    
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function challan()
    {
        return $this->hasOne(Challan::class);
    }

    public function hasChallan(): bool
    {
        return $this->challan()->exists();
    }
}
