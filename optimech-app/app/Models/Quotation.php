<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'quotation_no',
        'ship_to',
        'status',
        'regular_billing_locked',
    ];

    protected $casts = [
        'regular_billing_locked' => 'boolean',
    ];

    // =========================================================================
    // Relationships
    // =========================================================================

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(QuotationRevision::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    // =========================================================================
    // Scopes
    // =========================================================================

    /**
     * Filter by status.
     */
    public function scopeWithStatus(Builder $query, ?string $status): Builder
    {
        if (empty($status)) {
            return $query;
        }

        return $query->where('status', $status);
    }

    /**
     * Filter by active revision type.
     */
    public function scopeWithActiveRevisionType(Builder $query, ?string $type): Builder
    {
        if (empty($type)) {
            return $query;
        }

        return $query->whereHas('revisions', function ($revQuery) use ($type) {
            $revQuery->where('is_active', true)
                ->where('type', $type);
        });
    }

    /**
     * Filter by active revision saved_as.
     */
    public function scopeWithActiveRevisionSavedAs(Builder $query, ?string $savedAs): Builder
    {
        if (empty($savedAs)) {
            return $query;
        }

        return $query->whereHas('revisions', function ($revQuery) use ($savedAs) {
            $revQuery->where('is_active', true)
                ->where('saved_as', $savedAs);
        });
    }

    /**
     * Search across quotation, customer, company, and products.
     */
    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('quotation_no', 'LIKE', "%{$search}%")
                ->orWhereHas('customer', function ($customerQuery) use ($search) {
                    $customerQuery->where('customer_name', 'LIKE', "%{$search}%")
                        ->orWhere('customer_no', 'LIKE', "%{$search}%")
                        ->orWhereHas('company', function ($companyQuery) use ($search) {
                            $companyQuery->where('name', 'LIKE', "%{$search}%");
                        });
                })
                ->orWhereHas('revisions.products', function ($productQuery) use ($search) {
                    $productQuery->where('requision_no', 'LIKE', "%{$search}%");
                });
        });
    }

    /**
     * Filter by date range.
     */
    public function scopeDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        if (! empty($from)) {
            $query->whereDate('created_at', '>=', $from);
        }
        if (! empty($to)) {
            $query->whereDate('created_at', '<=', $to);
        }

        return $query;
    }

    // =========================================================================
    // Domain Methods
    // =========================================================================

    /**
     * Get the active revision for this quotation.
     */
    public function getActiveRevision(): ?QuotationRevision
    {
        return $this->revisions()
            ->where('is_active', true)
            ->latest()
            ->first();
    }

    /**
     * Check if quotation has any bills.
     */
    public function hasBills(): bool
    {
        return $this->bills()->exists();
    }

    /**
     * Check if quotation has any challan (via active revision).
     */
    public function hasChallan(): bool
    {
        $activeRevision = $this->getActiveRevision();

        return $activeRevision ? $activeRevision->hasChallan() : false;
    }

    /**
     * Check if quotation can be edited.
     */
    public function isEditable(): bool
    {
        return ! $this->hasBills();
    }

    /**
     * Check if quotation can be deleted.
     */
    public function isDeletable(): bool
    {
        if ($this->hasBills()) {
            return false;
        }

        return ! $this->hasChallan();
    }
}
