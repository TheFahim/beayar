<?php

namespace App\Services;

use App\Models\Challan;
use App\Models\ChallanProduct;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationProduct;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class QuotationQueryService
{
    /**
     * Build the index query with filters applied.
     */
    public function buildIndexQuery(Request $request): Builder
    {
        $query = Quotation::with([
            'customer:id,name,customer_no,user_company_id',
            'customer.customerCompany:id,name', // beayar uses customerCompany relationship
            'bills',
            'revisions' => function ($q) {
                $q->select('id', 'type', 'quotation_id', 'date', 'currency', 'exchange_rate', 'total', 'revision_no', 'saved_as', 'created_by')
                    ->with(['products.product:id,name', 'createdBy:id,name'])
                    ->where('is_active', true)
                    ->latest()
                    ->limit(1);
            },
        ])->withCount('revisions');

        // Apply tenant scope if not already applied by global scope
        // $query->where('user_company_id', Auth::user()->current_user_company_id);

        // Apply filters
        $this->applyStatusFilter($query, $request->status);
        $this->applyTypeFilter($query, $request->type);
        $this->applySavedAsFilter($query, $request->saved_as);
        $this->applySearchFilter($query, $request->search);
        $this->applyDateRangeFilter($query, $request->date_from, $request->date_to);

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Filter by status.
     */
    private function applyStatusFilter(Builder $query, ?string $status): void
    {
        if (! empty($status)) {
            // beayar uses status_id, but UI might pass string 'active', 'in_progress' etc.
            // We can join quotation_statuses table or check if input is numeric
            if (is_numeric($status)) {
                $query->where('status_id', $status);
            } else {
                $query->whereHas('status', function ($q) use ($status) {
                    $q->where('name', $status);
                });
            }
        }
    }

    /**
     * Filter by quotation type (active revision).
     */
    private function applyTypeFilter(Builder $query, ?string $type): void
    {
        if (! empty($type)) {
            $query->whereHas('revisions', function ($revQuery) use ($type) {
                $revQuery->where('is_active', true)
                    ->where('type', $type);
            });
        }
    }

    /**
     * Filter by saved_as (active revision).
     */
    private function applySavedAsFilter(Builder $query, ?string $savedAs): void
    {
        if (! empty($savedAs)) {
            $query->whereHas('revisions', function ($revQuery) use ($savedAs) {
                $revQuery->where('is_active', true)
                    ->where('saved_as', $savedAs);
            });
        }
    }

    /**
     * Apply search filter across quotation, customer, company, and products.
     */
    private function applySearchFilter(Builder $query, ?string $search): void
    {
        if (empty($search)) {
            return;
        }

        $query->where(function ($q) use ($search) {
            $q->where('quotation_no', 'LIKE', "%{$search}%")
                ->orWhere('reference_no', 'LIKE', "%{$search}%")
                ->orWhereHas('customer', function ($customerQuery) use ($search) {
                    $customerQuery->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('customer_no', 'LIKE', "%{$search}%")
                        ->orWhereHas('customerCompany', function ($companyQuery) use ($search) {
                            $companyQuery->where('name', 'LIKE', "%{$search}%");
                        });
                })
                ->orWhereHas('revisions.products', function ($productQuery) use ($search) {
                    $productQuery->where('requision_no', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('revisions.createdBy', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'LIKE', "%{$search}%");
                });
        });
    }

    /**
     * Apply date range filter.
     */
    private function applyDateRangeFilter(Builder $query, ?string $dateFrom, ?string $dateTo): void
    {
        if (! empty($dateFrom)) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if (! empty($dateTo)) {
            $query->whereDate('created_at', '<=', $dateTo);
        }
    }

    /**
     * Enrich quotations with computed properties for index view.
     */
    public function enrichQuotationsForIndex(Collection $quotations): Collection
    {
        return $quotations->each(function ($quotation) {
            $activeRevision = $quotation->revisions->first();

            // Check if challan exists
            $quotation->has_challan = $activeRevision
                ? Challan::where('quotation_revision_id', $activeRevision->id)->exists()
                : false;

            // Bill-related computed properties
            $this->computeBillProperties($quotation);

            // Challan fulfillment status
            $quotation->challan_fulfilled = $quotation->has_challan
                && $this->isAllProductsFulfilled($activeRevision);

            // Type from active revision
            $quotation->type = $activeRevision?->type;
        });
    }

    /**
     * Compute bill-related properties for a quotation.
     */
    private function computeBillProperties(Quotation $quotation): void
    {
        $latestBill = $quotation->bills
            ->sortByDesc(fn ($b) => [$b->bill_date, $b->id])
            ->first();

        $quotation->continueBill = $latestBill && (float) ($latestBill->due ?? 0) > 0;
        $quotation->canCreateBill = ! $latestBill || (float) ($latestBill->due ?? 0) > 0;
        $quotation->latestBillId = $latestBill?->id;
        $quotation->parentBillId = $latestBill?->parent_bill_id;
        $quotation->latestBillType = $latestBill?->bill_type;
        $quotation->hasAdvanceBill = $quotation->bills->where('bill_type', 'advance')->isNotEmpty();
    }

    /**
     * Check if all products are fulfilled for a revision.
     */
    private function isAllProductsFulfilled($revision): bool
    {
        if (! $revision) {
            return false;
        }

        $products = QuotationProduct::where('quotation_revision_id', $revision->id)
            ->select('id', 'quantity')
            ->get();

        if ($products->count() === 0) {
            return false;
        }

        foreach ($products as $product) {
            $deliveredSum = ChallanProduct::where('quotation_product_id', $product->id)
                ->whereHas('challan', function ($q) use ($revision) {
                    $q->where('quotation_revision_id', $revision->id);
                })
                ->sum('quantity');

            if ((int) $deliveredSum < (int) $product->quantity) {
                return false;
            }
        }

        return true;
    }

    /**
     * Search customers with pagination.
     */
    public function searchCustomers(?string $query, int $perPage = 20): LengthAwarePaginator
    {
        $perPage = min(max($perPage, 1), 50);

        $customerQuery = Customer::with('customerCompany:id,name')
            ->where('user_company_id', Auth::user()->current_user_company_id)
            ->select('id', 'name', 'customer_no', 'customer_company_id', 'address', 'phone', 'email', 'attention')
            ->orderBy('name');

        if ($query) {
            $customerQuery->where(function ($builder) use ($query) {
                $builder->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('customer_no', 'LIKE', "%{$query}%")
                    ->orWhere('phone', 'LIKE', "%{$query}%")
                    ->orWhere('email', 'LIKE', "%{$query}%")
                    ->orWhere('attention', 'LIKE', "%{$query}%")
                    ->orWhereHas('customerCompany', function ($companyQuery) use ($query) {
                        $companyQuery->where('name', 'LIKE', "%{$query}%");
                    });
            });
        }

        return $customerQuery->paginate($perPage)->appends([
            'q' => $query,
            'per_page' => $perPage,
        ]);
    }

    /**
     * Search products with pagination.
     */
    public function searchProducts(?string $query, int $perPage = 20): LengthAwarePaginator
    {
        $perPage = min(max($perPage, 1), 50);

        $productQuery = Product::with([
            'specifications:id,product_id,description',
            'image:id,path',
        ])
            ->where('user_company_id', Auth::user()->current_user_company_id)
            ->select('id', 'name', 'image_id')
            ->orderBy('name');

        if (! empty($query)) {
            $productQuery->where(function ($builder) use ($query) {
                $builder->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('id', 'LIKE', "%{$query}%");
            });
        }

        $products = $productQuery->paginate($perPage)->appends([
            'q' => $query,
            'per_page' => $perPage,
        ]);

        // Add first specification for auto-selection
        $products->getCollection()->each(function ($product) {
            $product->first_specification = $product->specifications->first();
        });

        return $products;
    }
}
