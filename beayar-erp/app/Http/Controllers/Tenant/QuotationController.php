<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\QuotationRequest;
use App\Http\Requests\QuotationRevisionRequest;
use App\Http\Requests\QuotationUpdateRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationRevision;
use App\Models\QuotationStatus;
use App\Models\Specification;
use App\Services\ExchangeRateService;
use App\Services\QuotationQueryService;
use App\Services\QuotationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QuotationController extends Controller
{
    public function __construct(
        private QuotationService $quotationService,
        private QuotationQueryService $queryService,
        private ExchangeRateService $exchangeRateService
    ) {}

    /**
     * Display a listing of quotations with nested structure.
     */
    public function index(Request $request)
    {
        $query = $this->queryService->buildIndexQuery($request);
        $quotations = $query->paginate(15);

        $this->queryService->enrichQuotationsForIndex($quotations->getCollection());

        return view('tenant.quotations.index', compact('quotations'));
    }

    /**
     * Show the form for creating a new quotation with initial revision.
     */
    public function create()
    {
        $this->authorize('create', Quotation::class);

        $customers = Customer::with('customerCompany:id,name')
            ->select('id', 'name', 'customer_company_id', 'customer_no', 'address', 'phone', 'email', 'attention')
            ->orderBy('name')
            ->get();

        $products = Product::select('id', 'name', 'image_id')
            ->orderBy('name')
            ->get();

        $specifications = Specification::whereHas('product')
            ->select('id', 'description')
            ->get();

        return view('tenant.quotations.create', compact(
            'customers',
            'products',
            'specifications',
        ));
    }

    /**
     * Store a newly created quotation with its first revision.
     */
    public function store(QuotationRequest $request)
    {
        try {
            $quotation = $this->quotationService->createQuotation($request->validated());
            $revisionNo = $quotation->getActiveRevision()?->revision_no ?? 'R00';

            return redirect()->route('tenant.quotations.index')
                ->with('success', 'Quotation created successfully with revision '.$revisionNo);
        } catch (\Exception $e) {
            Log::error('Quotation creation failed: '.$e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create quotation: '.$e->getMessage());
        }
    }

    /**
     * Display the specified quotation with active revision.
     */
    public function show(Quotation $quotation)
    {
        $this->authorizeQuotation($quotation);

        $quotation->load(['customer', 'customer.customerCompany']);

        $activeRevision = $quotation->getActiveRevision();
        if ($activeRevision) {
            $activeRevision->load([
                'products.product',
                'products.specification',
                'products.brandOrigin',
                'createdBy:id,name',
                // 'updatedBy:id,name', // Check if updatedBy exists in migration/model
                'challan',
            ]);
        }

        $hasChallan = $activeRevision?->hasChallan() ?? false;
        $isLocked = $hasChallan;

        return view('tenant.quotations.show', compact('quotation', 'activeRevision', 'isLocked', 'hasChallan'));
    }

    /**
     * Show the form for editing the parent quotation info.
     */
    public function edit(Quotation $quotation, Request $request)
    {
        $this->authorizeQuotation($quotation);

        $revisions = $quotation->revisions()
            ->with(['createdBy:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        $loadRevision = $this->getRevisionForEdit($quotation, $request->get('revision_id'));

        $hasChallan = $loadRevision?->hasChallan() ?? false;
        $hasAnyChallan = $quotation->revisions()->whereHas('challan')->exists();
        $hasAnyBill = $quotation->hasBills();

        // Load dependencies for edit view
        $userCompanyId = auth()->user()->current_user_company_id;
        $customers = Customer::where('user_company_id', $userCompanyId)
            ->with('customerCompany:id,name')
            ->select('id', 'name', 'customer_company_id', 'customer_no', 'address', 'phone', 'email', 'attention')
            ->orderBy('name')
            ->get();

        $products = Product::where('user_company_id', $userCompanyId)
            ->select('id', 'name', 'image_id')
            ->orderBy('name')
            ->get();

        $specifications = Specification::whereHas('product', function ($q) use ($userCompanyId) {
            $q->where('user_company_id', $userCompanyId);
        })
            ->select('id', 'description')
            ->get();

        return view('tenant.quotations.edit', compact(
            'quotation',
            'loadRevision',
            'hasChallan',
            'revisions',
            'hasAnyChallan',
            'hasAnyBill',
            'customers',
            'products',
            'specifications'
        ));
    }

    /**
     * Get revision for edit page based on request or active revision.
     */
    private function getRevisionForEdit(Quotation $quotation, ?int $revisionId): ?QuotationRevision
    {
        $loadWith = [
            'products.product',
            'products.specification',
            'products.brandOrigin',
            'createdBy:id,name',
            // 'updatedBy:id,name',
        ];

        if ($revisionId) {
            $revision = $quotation->revisions()->where('id', $revisionId)->first();
            if ($revision) {
                $revision->load($loadWith);

                return $revision;
            }
        }

        $revision = $quotation->getActiveRevision();
        $revision?->load($loadWith);

        return $revision;
    }

    /**
     * Update parent quotation information.
     */
    public function update(QuotationUpdateRequest $request, Quotation $quotation)
    {
        $this->authorizeQuotation($quotation);

        if (! $quotation->isEditable()) {
            return redirect()->back()->with('error', 'Cannot modify quotation because it has associated bills.');
        }

        // Handle new revision creation
        if ($request->quotation_revision['new_revision'] ?? false) {
            return $this->handleNewRevisionFromUpdate($request, $quotation);
        }

        try {
            $this->quotationService->updateQuotation($quotation, $request->validated());

            return redirect()->route('tenant.quotations.index')
                ->with('success', 'Quotation updated successfully!');
        } catch (\Exception $e) {
            Log::error('Quotation update failed: '.$e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update quotation: '.$e->getMessage());
        }
    }

    /**
     * Handle new revision creation from update request.
     */
    private function handleNewRevisionFromUpdate(Request $request, Quotation $quotation)
    {
        $validatedData = $request->validate([
            'quotation.customer_id' => 'required|exists:customers,id',
            'quotation.quotation_no' => 'required|string|max:255',
            'quotation.ship_to' => 'nullable|string|max:1000',
            // 'quotation.status' => 'nullable|string|in:in_progress,active,completed,cancelled',
        ]);

        $quotation->update([
            'customer_id' => $validatedData['quotation']['customer_id'],
            'quotation_no' => $validatedData['quotation']['quotation_no'],
            'ship_to' => $validatedData['quotation']['ship_to'] ?? '',
        ]);

        // Validate revision data using QuotationRevisionRequest rules manually
        $revisionRequest = new QuotationRevisionRequest;
        $validator = \Validator::make($request->all(), $revisionRequest->rules());

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $this->quotationService->createRevision($quotation, $request->all());

        return redirect()->route('tenant.quotations.edit', $quotation)
            ->with('success', 'New Revision Added!');
    }

    /**
     * Update the status of the specified quotation.
     */
    public function updateStatus(Request $request, Quotation $quotation)
    {
        $this->authorizeQuotation($quotation);

        $validated = $request->validate([
            'status' => 'required|string|in:in_progress,active,completed,cancelled',
        ]);

        $quotation->update([
            'status' => $validated['status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully!',
            'status' => $quotation->status,
        ]);
    }

    /**
     * Activate a specific revision.
     */
    public function activateRevision(QuotationRevision $revision)
    {
        $quotation = $revision->quotation;
        $this->authorizeQuotation($quotation);

        if ($quotation->hasBills()) {
            return redirect()->back()->with('error', 'Cannot change active revision because quotation has associated bills.');
        }

        DB::transaction(function () use ($quotation, $revision) {
            // Deactivate all revisions
            $this->quotationService->deactivateAllRevisions($quotation);

            // Activate this revision
            $revision->update(['is_active' => true]);

            // Update quotation status if saved as quotation
            if (($revision->saved_as ?? 'draft') === 'quotation') {
                $activeStatus = QuotationStatus::forCurrentCompany()->where('name', 'Active')->first();
                if ($activeStatus) {
                    $quotation->update(['status_id' => $activeStatus->id]);
                }
            }
        });

        return redirect()->route('tenant.quotations.edit', $quotation->id)
            ->with('success', 'Revision activated successfully!');
    }

    /**
     * Remove the specified quotation from storage.
     */
    public function destroy(Quotation $quotation)
    {
        $this->authorizeQuotation($quotation);

        if (! $quotation->isDeletable()) {
            $message = $quotation->hasBills()
                ? 'Cannot delete quotation because it has associated bills.'
                : 'Cannot delete quotation. A challan has been created from this quotation.';

            return redirect()->route('tenant.quotations.index')->with('error', $message);
        }

        DB::beginTransaction();
        try {
            $quotation->delete();
            DB::commit();

            return redirect()->route('tenant.quotations.index')
                ->with('success', 'Quotation deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('tenant.quotations.index')
                ->with('error', 'Failed to delete quotation: '.$e->getMessage());
        }
    }

    /**
     * Remove a specific revision from quotation.
     */
    public function destroyRevision(Quotation $quotation, QuotationRevision $revision)
    {
        $this->authorizeQuotation($quotation);

        if ($quotation->hasBills()) {
            return redirect()->route('tenant.quotations.edit', $quotation->id)
                ->with('error', 'Cannot delete revision because quotation has associated bills.');
        }

        if ($revision->quotation_id !== $quotation->id) {
            return redirect()->route('tenant.quotations.edit', $quotation->id)
                ->with('error', 'Revision does not belong to this quotation.');
        }

        if ($revision->is_active) {
            return redirect()->route('tenant.quotations.edit', $quotation->id)
                ->with('error', 'Cannot delete active revision.');
        }

        DB::beginTransaction();
        try {
            $revision->delete();
            DB::commit();

            return redirect()->route('tenant.quotations.edit', $quotation->id)
                ->with('success', 'Revision deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to delete revision {$revision->id}: ".$e->getMessage());

            return redirect()->route('tenant.quotations.edit', $quotation->id)
                ->with('error', 'Failed to delete revision: '.$e->getMessage());
        }
    }

    /**
     * Lock a revision (when challan is created).
     */
    public function lockRevision(QuotationRevision $revision)
    {
        $revision->quotation->update(['status' => 'completed']);

        return true;
    }

    // =========================================================================
    // API Endpoints
    // =========================================================================

    /**
     * Search products for AJAX requests.
     */
    public function searchProduct(Request $request)
    {
        $query = $request->input('q');
        $perPage = (int) ($request->input('per_page') ?? 20);

        $products = Product::where('user_company_id', auth()->user()->current_user_company_id)
            ->where('name', 'like', "%{$query}%")
            ->select('id', 'name', 'image_id')
            ->paginate($perPage);

        return response()->json([
            'data' => $products->items(),
            'current_page' => $products->currentPage(),
            'last_page' => $products->lastPage(),
            'per_page' => $products->perPage(),
            'total' => $products->total(),
        ]);
    }

    /**
     * Get specifications for a specific product.
     */
    public function getProductSpecifications(Request $request, $productId)
    {
        $userCompanyId = auth()->user()->current_user_company_id;
        $product = Product::where('user_company_id', $userCompanyId)->find($productId);

        if (! $product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        $specifications = $product->specifications()
            ->select('id', 'description')
            ->orderBy('description')
            ->get();

        return response()->json([
            'success' => true,
            'specifications' => $specifications,
        ]);
    }

    /**
     * Get current exchange rate for a specific currency to BDT.
     */
    public function getExchangeRate(Request $request)
    {
        return response()->json($this->exchangeRateService->getRates());
    }

    /**
     * API: Get next quotation number based on customer_no.
     */
    public function getNextQuotationNo(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
        ]);

        $customer = Customer::where('user_company_id', auth()->user()->current_user_company_id)
            ->findOrFail($request->customer_id);

        $quotationNo = $this->quotationService->generateNextQuotationNo($customer);

        return response()->json(['quotation_no' => $quotationNo]);
    }

    // =========================================================================
    // Product Creation (AJAX)
    // =========================================================================

    /**
     * Create a new product with specification from quotation form.
     */
    public function createProduct(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'image_id' => 'nullable|exists:images,id',
            'specifications.0.description' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $product = Product::create([
                'user_company_id' => auth()->user()->current_user_company_id,
                'name' => $validated['name'],
                'image_id' => $validated['image_id'] ?? null,
            ]);

            $specifications = [];
            if (! empty($validated['specifications'][0]['description'])) {
                $specification = $product->specifications()->create([
                    'description' => $validated['specifications'][0]['description'],
                ]);

                $specifications[] = [
                    'id' => $specification->id,
                    'description' => $specification->description,
                ];
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully!',
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'image_id' => $product->image_id,
                    'specifications' => $specifications,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: '.$e->getMessage(),
                'errors' => [],
            ], 422);
        }
    }

    private function authorizeQuotation(Quotation $quotation)
    {
        if ($quotation->user_company_id !== auth()->user()->current_user_company_id) {
            abort(403);
        }
    }
}
