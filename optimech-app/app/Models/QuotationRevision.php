<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class QuotationRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'type',
        'revision_no',
        'date',
        'validity',
        'currency',
        'exchange_rate',
        'subtotal',
        'discount_percentage',
        'discount_amount',
        'shipping',
        'vat_percentage',
        'vat_amount',
        'total',
        'terms_conditions',
        'saved_as',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'date' => 'date',
        'validity' => 'date',
        'type' => 'string',
        'is_active' => 'boolean',
    ];

    // =========================================================================
    // Relationships
    // =========================================================================

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function products(): HasMany
    {
        return $this->hasMany(QuotationProduct::class);
    }

    public function challan(): HasOne
    {
        return $this->hasOne(Challan::class);
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    /**
     * Filter to only active revisions.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    // =========================================================================
    // Domain Methods
    // =========================================================================

    /**
     * Check if revision has a challan.
     */
    public function hasChallan(): bool
    {
        return $this->challan()->exists();
    }

    /**
     * Check if revision is locked (has challan).
     */
    public function isLocked(): bool
    {
        return $this->hasChallan();
    }

    /**
     * Check if all products are fulfilled via challans.
     */
    public function isAllProductsFulfilled(): bool
    {
        $products = $this->products()->select('id', 'quantity')->get();

        if ($products->isEmpty()) {
            return false;
        }

        foreach ($products as $product) {
            if (! $product->isFulfilled()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get total delivered quantity across all products.
     */
    public function getTotalDeliveredQuantity(): int
    {
        return $this->products->sum(fn ($product) => $product->getDeliveredQuantity());
    }

    /**
     * Get total ordered quantity across all products.
     */
    public function getTotalOrderedQuantity(): int
    {
        return (int) $this->products()->sum('quantity');
    }
}
