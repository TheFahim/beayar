<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuotationProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'brand_origin_id',
        'quotation_revision_id',
        'size',
        'specification_id',
        'add_spec',
        'unit',
        'delivery_time',
        'unit_price',
        'quantity',
        'requision_no',
        'foreign_currency_buying',
        'bdt_buying',
        'air_sea_freight',
        'weight',
        'tax',
        'att',
        'margin',
    ];

    // =========================================================================
    // Relationships
    // =========================================================================

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function quotationRevision(): BelongsTo
    {
        return $this->belongsTo(QuotationRevision::class);
    }

    public function specification(): BelongsTo
    {
        return $this->belongsTo(Specification::class);
    }

    public function challanProducts(): HasMany
    {
        return $this->hasMany(ChallanProduct::class);
    }

    public function brandOrigin(): BelongsTo
    {
        return $this->belongsTo(BrandOrigin::class);
    }

    // =========================================================================
    // Domain Methods
    // =========================================================================

    /**
     * Get total delivered quantity from challans.
     */
    public function getDeliveredQuantity(): int
    {
        return (int) $this->challanProducts()
            ->whereHas('challan', function ($q) {
                $q->where('quotation_revision_id', $this->quotation_revision_id);
            })
            ->sum('quantity');
    }

    /**
     * Get remaining quantity to be delivered.
     */
    public function getRemainingQuantity(): int
    {
        return max(0, (int) $this->quantity - $this->getDeliveredQuantity());
    }

    /**
     * Check if product is fully delivered.
     */
    public function isFulfilled(): bool
    {
        return $this->getDeliveredQuantity() >= (int) $this->quantity;
    }

    /**
     * Create a QuotationProduct instance from array data.
     */
    public static function fromData(array $data, int $revisionId): self
    {
        $product = new self;
        $product->fill([
            'quotation_revision_id' => $revisionId,
            'product_id' => $data['product_id'],
            'size' => $data['size'] ?? null,
            'specification_id' => $data['specification_id'] ?? null,
            'add_spec' => $data['add_spec'] ?? null,
            'brand_origin_id' => $data['brand_origin_id'] ?? null,
            'unit' => $data['unit'] ?? null,
            'delivery_time' => $data['delivery_time'] ?? null,
            'unit_price' => $data['unit_price'],
            'quantity' => $data['quantity'],
            'requision_no' => $data['requision_no'] ?? null,
            'foreign_currency_buying' => $data['foreign_currency_buying'] ?? null,
            'bdt_buying' => $data['bdt_buying'] ?? null,
            'air_sea_freight' => $data['air_sea_freight'] ?? null,
            'weight' => $data['weight'] ?? null,
            'tax' => $data['tax'] ?? null,
            'att' => $data['att'] ?? null,
            'margin' => $data['margin'] ?? null,
        ]);

        // Handle non-fillable attributes
        if (array_key_exists('air_sea_freight_rate', $data)) {
            $product->air_sea_freight_rate = $data['air_sea_freight_rate'];
        }
        if (array_key_exists('tax_percentage', $data)) {
            $product->tax_percentage = $data['tax_percentage'];
        }
        if (array_key_exists('att_percentage', $data)) {
            $product->att_percentage = $data['att_percentage'];
        }
        if (array_key_exists('margin_value', $data)) {
            $product->margin_value = $data['margin_value'];
        }

        return $product;
    }
}
