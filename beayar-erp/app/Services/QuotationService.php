<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Quotation;
use App\Models\QuotationProduct;
use App\Models\QuotationRevision;
use App\Models\QuotationStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuotationService
{
    /**
     * Create a new quotation with its first revision and products.
     */
    public function createQuotation(array $data): Quotation
    {
        return DB::transaction(function () use ($data) {
            $quotationData = $data['quotation'];
            $revisionData = $data['quotation_revision'];
            $productsData = $data['quotation_products'];

            // Get default status
            $status = QuotationStatus::forCurrentCompany()
                ->where('is_default', true)
                ->first();

            if (! $status) {
                // Fallback or create default
                $status = QuotationStatus::firstOrCreate(
                    ['name' => 'Draft', 'tenant_company_id' => Auth::user()->current_tenant_company_id],
                    ['color' => 'gray', 'is_default' => true]
                );
            }

            // Create parent quotation
            $quotation = Quotation::create([
                'tenant_company_id' => Auth::user()->current_tenant_company_id,
                'user_id' => Auth::id(),
                'customer_id' => $quotationData['customer_id'],
                'quotation_no' => $quotationData['quotation_no'],
                'reference_no' => $quotationData['quotation_no'], // Sync reference_no for backward compatibility
                'ship_to' => $quotationData['ship_to'] ?? '',
                'status_id' => $status->id,
            ]);

            // Create initial revision
            $revision = $this->createRevisionForQuotation($quotation, $revisionData, true);

            // Sync products
            $this->syncRevisionProducts($revision, $productsData);

            // Update status if saved as quotation (Active)
            if (($revisionData['saved_as'] ?? 'draft') === 'quotation') {
                $activeStatus = QuotationStatus::forCurrentCompany()->where('name', 'Active')->first();
                if ($activeStatus) {
                    $quotation->update(['status_id' => $activeStatus->id]);
                }
            }

            return $quotation;
        });
    }

    /**
     * Update an existing quotation with its revision and products.
     */
    public function updateQuotation(Quotation $quotation, array $data): Quotation
    {
        return DB::transaction(function () use ($quotation, $data) {
            $quotationData = $data['quotation'];
            $revisionData = $data['quotation_revision'];
            $productsData = $data['quotation_products'];

            // Update parent quotation
            $quotation->update([
                'customer_id' => $quotationData['customer_id'],
                'quotation_no' => $quotationData['quotation_no'],
                'reference_no' => $quotationData['quotation_no'],
                'ship_to' => $quotationData['ship_to'] ?? $quotation->ship_to,
            ]);

            // Load and update active revision
            $revision = QuotationRevision::findOrFail($revisionData['id']);
            $this->updateRevision($revision, $revisionData);

            // Sync products
            $this->syncRevisionProducts($revision, $productsData);

            // Update quotation status if saved as quotation
            if (($revisionData['saved_as'] ?? 'draft') === 'quotation') {
                $activeStatus = QuotationStatus::forCurrentCompany()->where('name', 'Active')->first();
                if ($activeStatus) {
                    $quotation->update(['status_id' => $activeStatus->id]);
                }
            }

            return $quotation;
        });
    }

    /**
     * Create a new revision for an existing quotation.
     */
    public function createRevision(Quotation $quotation, array $data): QuotationRevision
    {
        return DB::transaction(function () use ($quotation, $data) {
            $revisionData = $data['quotation_revision'];
            $productsData = $data['quotation_products'];

            // Deactivate all existing revisions
            $this->deactivateAllRevisions($quotation);

            // Create new revision
            $revision = $this->createRevisionForQuotation($quotation, $revisionData, true);

            // Sync products
            $this->syncRevisionProducts($revision, $productsData);

            // Update quotation status if saved as quotation
            if (($revisionData['saved_as'] ?? 'draft') === 'quotation') {
                $activeStatus = QuotationStatus::forCurrentCompany()->where('name', 'Active')->first();
                if ($activeStatus) {
                    $quotation->update(['status_id' => $activeStatus->id]);
                }
            }

            return $revision;
        });
    }

    /**
     * Create a revision record for a quotation.
     */
    private function createRevisionForQuotation(
        Quotation $quotation,
        array $revisionData,
        bool $isActive = true
    ): QuotationRevision {
        $revisionNo = $this->generateRevisionNo($quotation);

        // Convert dates from d/m/Y to Y-m-d
        $date = date('Y-m-d');
        // Handle validity date format
        try {
            $validity = Carbon::createFromFormat('d/m/Y', $revisionData['validity'])->format('Y-m-d');
        } catch (\Exception $e) {
            $validity = null;
        }

        $revision = QuotationRevision::create([
            'quotation_id' => $quotation->id,
            'date' => $date,
            'type' => $revisionData['type'],
            'revision_no' => $revisionNo,
            'validity' => $validity,
            'valid_until' => $validity, // Sync valid_until
            'currency' => $revisionData['currency'],
            'exchange_rate' => $revisionData['exchange_rate'] ?? 1,
            'subtotal' => $revisionData['subtotal'] ?? 0,
            'shipping' => $revisionData['shipping'] ?? 0,
            'shipping_cost' => $revisionData['shipping'] ?? 0, // Sync shipping_cost
            'vat_percentage' => $revisionData['vat_percentage'] ?? 0,
            'vat_amount' => $revisionData['vat_amount'] ?? 0,
            'total' => $revisionData['total'] ?? 0,
            'terms_conditions' => $revisionData['terms_conditions'] ?? null,
            'saved_as' => $revisionData['saved_as'],
            'is_active' => $isActive,
            'created_by' => Auth::id(),
            // 'updated_by' => Auth::id(), // QuotationRevision in beayar-erp might not have updated_by, check migration
        ]);

        // Set non-fillable attributes if any, or ensuring saving logic
        $revision->discount_percentage = $revisionData['discount_percentage'] ?? 0;
        $revision->discount_amount = $revisionData['discount'] ?? 0;
        $revision->save();

        return $revision;
    }

    /**
     * Update an existing revision with new data.
     */
    private function updateRevision(QuotationRevision $revision, array $revisionData): void
    {
        // Convert dates from d/m/Y to Y-m-d
        try {
            $date = Carbon::createFromFormat('d/m/Y', $revisionData['date'])->format('Y-m-d');
        } catch (\Exception $e) {
            $date = $revisionData['date']; // Fallback
        }

        try {
            $validity = Carbon::createFromFormat('d/m/Y', $revisionData['validity'])->format('Y-m-d');
        } catch (\Exception $e) {
            $validity = $revisionData['validity']; // Fallback
        }

        $revision->update([
            'date' => $date,
            'type' => $revisionData['type'],
            'validity' => $validity,
            'valid_until' => $validity,
            'currency' => $revisionData['currency'],
            'exchange_rate' => $revisionData['exchange_rate'] ?? 1,
            'subtotal' => $revisionData['subtotal'] ?? 0,
            'shipping' => $revisionData['shipping'] ?? 0,
            'shipping_cost' => $revisionData['shipping'] ?? 0,
            'vat_percentage' => $revisionData['vat_percentage'] ?? 0,
            'vat_amount' => $revisionData['vat_amount'] ?? 0,
            'total' => $revisionData['total'] ?? 0,
            'terms_conditions' => $revisionData['terms_conditions'] ?? null,
            'saved_as' => $revisionData['saved_as'],
            // 'updated_by' => Auth::id(),
        ]);

        $revision->discount_percentage = $revisionData['discount_percentage'] ?? 0;
        $revision->discount_amount = $revisionData['discount'] ?? 0;
        $revision->save();
    }

    /**
     * Sync products for a revision - handles create, update, and delete.
     */
    public function syncRevisionProducts(QuotationRevision $revision, array $productsData): void
    {
        // Collect existing product IDs from payload
        $payloadIds = collect($productsData)
            ->map(fn ($p) => $p['id'] ?? null)
            ->filter()
            ->values()
            ->all();

        // Delete products not present in payload
        if (! empty($payloadIds)) {
            $revision->products()->whereNotIn('id', $payloadIds)->delete();
        } else {
            $revision->products()->delete();
        }

        foreach ($productsData as $productData) {
            $fillable = $this->buildProductFillable($productData);

            if (isset($productData['id'])) {
                $existing = $revision->products()->where('id', $productData['id'])->first();
                if ($existing) {
                    $this->updateProduct($existing, $fillable, $productData);
                } else {
                    $this->createProduct($revision, $fillable, $productData);
                }
            } else {
                $this->createProduct($revision, $fillable, $productData);
            }
        }
    }

    /**
     * Build fillable array for product data.
     */
    private function buildProductFillable(array $productData): array
    {
        // Beayar uses product_name, opimech doesn't explicitly pass it in fillable but beayar needs it
        // We assume productData has what we need or we fetch from Product model if product_id is set
        $productName = $productData['product_name'] ?? '';
        if (empty($productName) && ! empty($productData['product_id'])) {
            $prod = \App\Models\Product::find($productData['product_id']);
            $productName = $prod ? $prod->name : '';
        }

        return [
            'product_id' => $productData['product_id'],
            'product_name' => $productName, // beayar requirement
            'size' => $productData['size'] ?? null,
            'specification_id' => $productData['specification_id'] ?? null,
            'add_spec' => $productData['add_spec'] ?? null,
            'brand_origin_id' => $productData['brand_origin_id'] ?? null,
            'unit' => $productData['unit'] ?? null,
            'delivery_time' => $productData['delivery_time'] ?? null,
            'unit_price' => $productData['unit_price'],
            'quantity' => $productData['quantity'],
            'requision_no' => $productData['requision_no'] ?? null,
            'foreign_currency_buying' => $productData['foreign_currency_buying'] ?? null,
            'bdt_buying' => $productData['bdt_buying'] ?? null,
            'air_sea_freight' => $productData['air_sea_freight'] ?? null,
            'weight' => $productData['weight'] ?? null,
            'tax' => $productData['tax'] ?? null,
            'att' => $productData['att'] ?? null,
            'margin' => $productData['margin'] ?? null,
            'total' => $productData['total'] ?? 0, // beayar requirement
        ];
    }

    /**
     * Update an existing product with non-fillable handling.
     */
    private function updateProduct(QuotationProduct $product, array $fillable, array $productData): void
    {
        $product->fill($fillable);
        $this->setNonFillableProductAttributes($product, $productData);
        $product->save();
    }

    /**
     * Create a new product with non-fillable handling.
     */
    private function createProduct(QuotationRevision $revision, array $fillable, array $productData): void
    {
        $product = new QuotationProduct;
        $product->fill(array_merge($fillable, [
            'quotation_revision_id' => $revision->id,
        ]));
        $this->setNonFillableProductAttributes($product, $productData);
        $product->save();
    }

    /**
     * Set non-fillable product attributes.
     */
    private function setNonFillableProductAttributes(QuotationProduct $product, array $productData): void
    {
        if (array_key_exists('air_sea_freight_rate', $productData)) {
            $product->air_sea_freight_rate = $productData['air_sea_freight_rate'];
        }
        if (array_key_exists('tax_percentage', $productData)) {
            $product->tax_percentage = $productData['tax_percentage'];
            $product->tax_rate = $productData['tax_percentage']; // Sync tax_rate
        }
        if (array_key_exists('att_percentage', $productData)) {
            $product->att_percentage = $productData['att_percentage'];
        }
        if (array_key_exists('margin_value', $productData)) {
            $product->margin_value = $productData['margin_value'];
        }
    }

    /**
     * Deactivate all revisions for a quotation.
     */
    public function deactivateAllRevisions(Quotation $quotation): void
    {
        $quotation->revisions()->update(['is_active' => false]);
    }

    /**
     * Generate revision number for a quotation.
     */
    public function generateRevisionNo(Quotation $quotation): string
    {
        $lastRevision = $quotation->revisions()
            ->orderBy('created_at', 'desc')
            ->first();

        if (! $lastRevision) {
            return 'R00';
        }

        $lastRevNo = $lastRevision->revision_no; // E.g. R00
        $num = (int) substr($lastRevNo, 1);
        $num++;

        return 'R'.str_pad($num, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Generate next quotation number for a customer.
     */
    public function generateNextQuotationNo(Customer $customer): string
    {
        $customerNo = $customer->customer_no;

        // Beayar: filter by company
        $latestQuotation = Quotation::where('tenant_company_id', Auth::user()->current_tenant_company_id)
            ->where('customer_id', $customer->id)
            ->where('quotation_no', 'LIKE', $customerNo.'-%')
            ->orderBy('quotation_no', 'desc')
            ->first();

        $nextNumber = 1;

        if ($latestQuotation) {
            $parts = explode('-', $latestQuotation->quotation_no);
            $lastNumber = end($parts);

            if (is_numeric($lastNumber)) {
                $nextNumber = (int) $lastNumber + 1;
            }
        }

        $sequence = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return $customerNo.'-'.$sequence;
    }

    /**
     * Check if a revision can be activated.
     */
    public function canActivateRevision(QuotationRevision $revision): bool
    {
        $quotation = $revision->quotation;

        if ($quotation->bills()->exists()) {
            return false;
        }

        // Check if any revision has a challan
        $hasChallan = $quotation->revisions()
            ->whereHas('challan')
            ->exists();

        return ! $hasChallan;
    }

    /**
     * Activate a specific revision.
     */
    public function activateRevision(QuotationRevision $revision): void
    {
        $quotation = $revision->quotation;

        $this->deactivateAllRevisions($quotation);
        $revision->update(['is_active' => true]);
    }
}
