<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Challan;
use App\Models\ChallanProduct;
use App\Models\Quotation;
use App\Models\QuotationProduct;
use App\Models\QuotationRevision;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ChallanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $challans = Challan::with([
            'revision.quotation.customer.company',
            'revision.createdBy',
            'revision.products',
            'products.quotationProduct.product',
        ])
            ->withCount('bills')
            ->latest()->paginate(15);

        // Add challan fulfillment logic for each challan
        $challans->getCollection()->each(function ($challan) {
            $revision = $challan->revision;

            // Determine whether all products/quantities are fully challaned
            $allFulfilled = false;
            if ($revision) {
                $products = QuotationProduct::where('quotation_revision_id', $revision->id)
                    ->select('id', 'quantity')
                    ->get();

                if ($products->count() > 0) {
                    $allFulfilled = true;
                    foreach ($products as $p) {
                        $deliveredSum = ChallanProduct::where('quotation_product_id', $p->id)
                            ->whereHas('challan', function ($q) use ($revision) {
                                $q->where('quotation_revision_id', $revision->id);
                            })
                            ->sum('quantity');

                        if ((int) $deliveredSum < (int) $p->quantity) {
                            $allFulfilled = false;
                            break;
                        }
                    }
                }
            }

            // Set properties for view logic
            if ($challan->revision) {
                $challan->revision->challan_fulfilled = $allFulfilled;
            }
            $challan->can_continue_challan = $revision &&
                $revision->saved_as === 'quotation' &&
                $revision->type === 'normal' &&
                ! $allFulfilled;
        });

        return view('tenant.challans.index', compact('challans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Remove maintenance block and implement revision-based creation
        $quotationId = $request->query('quotation_id');

        if (! $quotationId) {
            abort(404);
        }

        $quotation = Quotation::with(['customer'])->find($quotationId);
        if (! $quotation) {
            abort(404, 'Quotation not found');
        }

        // $quotation->customer->load('company'); // company might be different in beayar-erp Customer model

        $revision = QuotationRevision::with(['products.challanProducts', 'quotation.customer'])
            ->where('quotation_id', $quotationId)
            ->where('is_active', true)
            ->latest()
            ->first();

        if (! $revision) {
            abort(404, 'Active revision not found');
        }

        // Block creating challan if revision is not 'normal' or saved_as is 'draft'
        if ($revision->type !== 'normal' || $revision->saved_as === 'draft') {
            abort(403, 'Challan can only be created for normal, non-draft quotations');
        }

        // Determine suggested challan number with incremental suffix per revision
        $existingNumbers = Challan::where('quotation_revision_id', $revision->id)->pluck('challan_no');
        $baseNo = $quotation->quotation_no;
        $maxSuffix = 0;
        foreach ($existingNumbers as $no) {
            if (preg_match('/^'.preg_quote($baseNo, '/').'-(\\d+)$/', $no, $m)) {
                $maxSuffix = max($maxSuffix, (int) $m[1]);
            }
        }
        $suggestedChallanNo = $baseNo.'-'.($maxSuffix + 1);
        $hasChallan = $existingNumbers->isNotEmpty();

        // Suggest PO No from latest challan for this revision (if any)
        $suggestedPoNo = $quotation->po_no;

        $suggestPdate = Challan::where('quotation_revision_id', $revision->id)
            ->latest()
            ->value('date');

        // Pass revision along with quotation; allow continuing challans when partial
        return view('tenant.challans.create', [
            'quotation' => $quotation,
            'revision' => $revision,
            'hasChallan' => $hasChallan,
            'suggestedChallanNo' => $suggestedChallanNo,
            'suggestedPoNo' => $suggestedPoNo,
            'suggestedDeliveryDate' => $suggestPdate,
        ]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Define validation rules with conditional uniqueness for po_no
        $rules = [
            'quotation_revision_id' => 'required|exists:quotation_revisions,id',
            'date' => 'required|date_format:d/m/Y',
            'challan_no' => [
                'required',
                'string',
                'max:255',
                Rule::unique('challans', 'challan_no')->where(function ($query) {
                    return $query->where('tenant_company_id', auth()->user()->current_tenant_company_id);
                }),
            ],
            'po_no' => [
                'required',
                'string',
                'max:255',
            ],
            'po_date' => 'nullable|date_format:d/m/Y',
            'items' => 'nullable|array',
            'items.*.selected' => 'sometimes|boolean',
            'items.*.quotation_product_id' => 'required_if:items.*.selected,1|exists:quotation_products,id',
            'items.*.quantity' => 'required_if:items.*.selected,1|integer|min:1',
            'items.*.remarks' => 'nullable|string|max:1000',
        ];

        // Validate payload for partial challans and proper formats
        $validator = Validator::make($request->all(), $rules);

        // Cross-field/domain validation: ensure selection valid and quantities within remaining
        $validator->after(function ($validator) use ($request) {
            $revisionId = $request->input('quotation_revision_id');
            $items = $request->input('items', []);
            $hasSelected = collect($items)->contains('selected', true);

            foreach ($items as $index => $item) {
                if (! empty($item['selected'])) {
                    $qpId = $item['quotation_product_id'] ?? null;
                    if (! $qpId) {
                        continue; // Base rules will surface missing product id
                    }

                    $qp = QuotationProduct::where('id', $qpId)
                        ->where('quotation_revision_id', $revisionId)
                        ->first();

                    if (! $qp) {
                        $validator->errors()->add("items.$index.quotation_product_id", 'Product does not belong to the active revision');

                        continue;
                    }

                    // Calculate remaining quantity based on previous challans
                    $deliveredSum = ChallanProduct::where('quotation_product_id', $qp->id)
                        ->whereHas('challan', function ($q) use ($revisionId) {
                            $q->where('quotation_revision_id', $revisionId);
                        })
                        ->sum('quantity');

                    $remaining = max(0, (int) $qp->quantity - $deliveredSum);

                    if ($remaining <= 0) {
                        $validator->errors()->add("items.$index.selected", 'Product has no remaining quantity');

                        continue;
                    }

                    $requested = (int) ($item['quantity'] ?? 0);
                    if ($requested < 1) {
                        $validator->errors()->add("items.$index.quantity", 'Quantity must be at least 1');
                    } elseif ($requested > $remaining) {
                        $validator->errors()->add("items.$index.quantity", "Requested quantity exceeds remaining ($remaining)");
                    }
                }
            }

            if (! $hasSelected) {
                $validator->errors()->add('items', 'Please select at least one product');
            }
        });

        $validated = $validator->validate();

        // Load revision context
        $revision = QuotationRevision::with('quotation')->findOrFail($validated['quotation_revision_id']);

        // Persist header and items atomically
        $challan = DB::transaction(function () use ($validated, $revision) {
            // Create the challan record
            $challan = Challan::create([
                'quotation_revision_id' => $revision->id,
                'quotation_id' => $revision->quotation_id,
                'customer_id' => $revision->quotation->customer_id,
                'challan_no' => $validated['challan_no'],
                'date' => Carbon::createFromFormat('d/m/Y', $validated['date'])->format('Y-m-d'),
            ]);

            $challan->revision->quotation->update([
                'po_no' => $validated['po_no'],
                'po_date' => ! empty($validated['po_date'])
                    ? Carbon::createFromFormat('d/m/Y', $validated['po_date'])->format('Y-m-d')
                    : null,
            ]);

            // Process selected items
            if (! empty($validated['items'])) {
                foreach ($validated['items'] as $item) {
                    if (empty($item['selected'])) {
                        continue;
                    }

                    $qp = QuotationProduct::where('id', $item['quotation_product_id'])
                        ->where('quotation_revision_id', $revision->id)
                        ->firstOrFail(); // This will throw a 404 if not found

                    // Recalculate delivered quantity to ensure consistency within transaction
                    $deliveredSum = ChallanProduct::where('quotation_product_id', $qp->id)
                        ->whereHas('challan', function ($q) use ($revision) {
                            $q->where('quotation_revision_id', $revision->id);
                        })
                        ->sum('quantity');

                    $requested = (int) $item['quantity'];

                    // Final check to ensure requested quantity doesn't exceed remaining
                    if ($deliveredSum + $requested > (int) $qp->quantity) {
                        abort(422, 'Requested quantity exceeds remaining for a product');
                    }

                    // Create the challan product record
                    ChallanProduct::create([
                        'quotation_product_id' => $qp->id,
                        'challan_id' => $challan->id,
                        'quantity' => $requested,
                        'remarks' => $item['remarks'] ?? null,
                    ]);
                }
            }

            return $challan;
        });

        return redirect()->route('tenant.challans.show', $challan->id)
            ->with('success', 'Challan created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Challan $challan)
    {
        $challan->load(['revision.quotation.customer', 'products.quotationProduct.product', 'bills']);

        $hasBill = $challan->bills()->exists();
        $latestBill = $hasBill ? $challan->bills()->latest()->first() : null;

        return view('tenant.challans.show', compact('challan', 'hasBill', 'latestBill'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Challan $challan)
    {
        $challan->load(['revision.quotation.customer', 'revision.products.challanProducts', 'products.quotationProduct.product']);

        return view('tenant.challans.edit', compact('challan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Challan $challan)
    {
        // Validate payload for partial challans and proper formats
        $validator = Validator::make($request->all(), [
            'quotation_revision_id' => 'required|exists:quotation_revisions,id',
            'date' => 'required|date_format:d/m/Y',
            'challan_no' => [
                'required',
                'string',
                'max:255',
                Rule::unique('challans', 'challan_no')
                    ->ignore($challan->id)
                    ->where(function ($query) {
                        return $query->where('tenant_company_id', auth()->user()->current_tenant_company_id);
                    }),
            ],
            'po_no' => [
                'required',
                'string',
                'max:255',
                // Unique po_no validation if needed, scoped to company? opitmech-app scoped it to quotation... wait
                // 'unique:quotations,po_no,'.$challan->revision->quotation_id,
            ],
            'po_date' => 'nullable|date_format:d/m/Y',
            'items' => 'nullable|array',
            'items.*.selected' => 'sometimes|boolean',
            'items.*.quotation_product_id' => 'required_if:items.*.selected,1|exists:quotation_products,id',
            'items.*.quantity' => 'required_if:items.*.selected,1|integer|min:1',
            'items.*.remarks' => 'nullable|string|max:1000',
        ]);

        // Cross-field/domain validation: ensure selection valid and quantities within remaining
        $validator->after(function ($validator) use ($request, $challan) {
            $revisionId = $request->input('quotation_revision_id');
            $items = $request->input('items', []);
            $hasSelected = false;

            foreach ($items as $index => $item) {
                if (! empty($item['selected'])) {
                    $hasSelected = true;

                    $qpId = $item['quotation_product_id'] ?? null;
                    if (! $qpId) {
                        // Base rules will surface missing product id
                        continue;
                    }

                    $qp = QuotationProduct::where('id', $qpId)
                        ->where('quotation_revision_id', $revisionId)
                        ->first();

                    if (! $qp) {
                        $validator->errors()->add("items.$index.quotation_product_id", 'Product does not belong to the active revision');

                        continue;
                    }

                    $ordered = (int) $qp->quantity;

                    // Calculate delivered sum excluding current challan
                    $deliveredSum = ChallanProduct::where('quotation_product_id', $qp->id)
                        ->whereHas('challan', function ($q) use ($revisionId, $challan) {
                            $q->where('quotation_revision_id', $revisionId)
                                ->where('id', '!=', $challan->id);
                        })
                        ->sum('quantity');

                    $remaining = max(0, $ordered - $deliveredSum);

                    if ($remaining <= 0) {
                        $validator->errors()->add("items.$index.selected", 'Product has no remaining quantity');

                        continue;
                    }

                    $requested = (int) ($item['quantity'] ?? 0);
                    if ($requested < 1) {
                        $validator->errors()->add("items.$index.quantity", 'Quantity must be at least 1');
                    }
                    if ($requested > $remaining) {
                        $validator->errors()->add("items.$index.quantity", "Requested quantity exceeds remaining ($remaining)");
                    }
                }
            }

            if (! $hasSelected) {
                $validator->errors()->add('items', 'Please select at least one product');
            }
        });

        $validated = $validator->validate();

        $challan->load('revision.quotation');

        // Update header and items atomically
        DB::transaction(function () use ($validated, $challan) {
            // Update challan header
            $challan->update([
                'challan_no' => $validated['challan_no'],
                'date' => ! empty($validated['date'])
                    ? Carbon::createFromFormat('d/m/Y', $validated['date'])->format('Y-m-d')
                    : null,
            ]);

            // Update quotation po_no and po_date
            $challan->revision->quotation->update([
                'po_no' => $validated['po_no'],
                'po_date' => ! empty($validated['po_date'])
                    ? Carbon::createFromFormat('d/m/Y', $validated['po_date'])->format('Y-m-d')
                    : null,
            ]);

            // Delete existing challan products
            ChallanProduct::where('challan_id', $challan->id)->delete();

            // Only process selected items; enforce not exceeding ordered quantities
            if (! empty($validated['items'])) {
                foreach ($validated['items'] as $item) {
                    // Skip if not selected
                    if (empty($item['selected'])) {
                        continue;
                    }

                    $qp = QuotationProduct::where('id', $item['quotation_product_id'])
                        ->where('quotation_revision_id', $challan->quotation_revision_id)
                        ->first();
                    if (! $qp) {
                        abort(422, 'Product does not belong to the active revision');
                    }

                    // Calculate delivered sum excluding current challan
                    $deliveredSum = ChallanProduct::where('quotation_product_id', $qp->id)
                        ->whereHas('challan', function ($q) use ($challan) {
                            $q->where('quotation_revision_id', $challan->quotation_revision_id)
                                ->where('id', '!=', $challan->id);
                        })
                        ->sum('quantity');

                    $requested = (int) ($item['quantity'] ?? 0);
                    if ($requested < 1) {
                        continue; // quantity validation already enforces min:1 when selected
                    }
                    if ($deliveredSum + $requested > (int) $qp->quantity) {
                        abort(422, 'Requested quantity exceeds remaining for a product');
                    }

                    ChallanProduct::create([
                        'quotation_product_id' => $qp->id,
                        'challan_id' => $challan->id,
                        'quantity' => $requested,
                        'remarks' => $item['remarks'] ?? null,
                    ]);
                }
            }
        });

        return redirect()->route('tenant.challans.show', $challan->id)->with('success', 'Challan updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Challan $challan)
    {
        $challan->load(['bills', 'revision.quotation']);

        $hasBill = DB::table('bill_challans')->where('challan_id', $challan->id)->exists();
        if ($hasBill) {
            abort(403);
        }

        try {
            $challan->delete();

            return redirect()->route('tenant.challans.index')->with('success', 'Challan Deleted successfully.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()->route('tenant.challans.index')->with('error', 'Failed to delete challan: '.$e->getMessage());
        }
    }

    public function getProductsByChallanIds(Request $request)
    {
        $challanIds = $request->query('challan_ids');

        if (empty($challanIds)) {
            return response()->json([]);
        }

        $products = ChallanProduct::with(['quotationProduct.product', 'quotationProduct.specification'])
            ->whereIn('challan_id', $challanIds)
            ->get();

        return response()->json($products);
    }
}
