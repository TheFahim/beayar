<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuotationRequest;
use App\Http\Requests\QuotationRevisionRequest;
use App\Models\Challan;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationRevision;
use App\Models\Specification;
use App\Services\ExchangeRateService;
use App\Services\QuotationQueryService;
use App\Services\QuotationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

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

        return view('dashboard.quotations.index', compact('quotations'));
    }

    /**
     * Show the form for creating a new quotation with initial revision.
     */
    public function create()
    {
        $customers = Customer::with('company:id,name')
            ->select('id', 'customer_name', 'company_id', 'customer_no')
            ->orderBy('customer_name')
            ->get();

        $products = Product::select('id', 'name', 'image_id')
            ->orderBy('name')
            ->get();

        $specifications = Specification::select('id', 'description')->get();

        return view('dashboard.quotations.create', compact(
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

            return redirect()->route('quotations.index')
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
        $quotation->load(['customer', 'customer.company']);

        $activeRevision = $quotation->getActiveRevision();
        if ($activeRevision) {
            $activeRevision->load([
                'products.product',
                'products.specification',
                'createdBy:id,name',
                'updatedBy:id,name',
                'challan',
            ]);
        }

        $hasChallan = $activeRevision?->hasChallan() ?? false;
        $isLocked = $hasChallan;

        return view('dashboard.quotations.show', compact('quotation', 'activeRevision', 'isLocked', 'hasChallan'));
    }

    /**
     * Show the form for editing the parent quotation info.
     */
    public function edit(Quotation $quotation, Request $request)
    {
        $revisions = $quotation->revisions()
            ->with(['createdBy:id,name', 'updatedBy:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        $loadRevision = $this->getRevisionForEdit($quotation, $request->get('revision_id'));

        $hasChallan = $loadRevision?->hasChallan() ?? false;
        $hasAnyChallan = $quotation->revisions()->whereHas('challan')->exists();
        $hasAnyBill = $quotation->hasBills();

        return view('dashboard.quotations.edit', compact(
            'quotation',
            'loadRevision',
            'hasChallan',
            'revisions',
            'hasAnyChallan',
            'hasAnyBill'
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
            'createdBy:id,name',
            'updatedBy:id,name',
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
    public function update(Request $request, Quotation $quotation)
    {
        if (! $quotation->isEditable()) {
            return redirect()->back()->with('error', 'Cannot modify quotation because it has associated bills.');
        }

        // Handle new revision creation
        if ($request->quotation_revision['new_revision'] ?? false) {
            return $this->handleNewRevisionFromUpdate($request, $quotation);
        }

        // Standard update
        $rules = (new QuotationRequest)->rules();
        $rules['quotation.quotation_no'] = [
            'required',
            'string',
            'max:255',
            Rule::unique('quotations', 'quotation_no')->ignore($quotation->id),
        ];

        $validated = $request->validate($rules);

        try {
            $this->quotationService->updateQuotation($quotation, $validated);

            return redirect()->route('quotations.index')
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
            'quotation.status' => 'nullable|string|in:in_progress,active,completed,cancelled',
        ]);

        $quotation->update($validatedData['quotation']);

        $this->storeRevision($request, $quotation);

        return redirect()->route('quotations.edit', $quotation)
            ->with('success', 'New Revision Added!');
    }

    /**
     * Remove the specified quotation from storage.
     */
    public function destroy(Quotation $quotation)
    {
        if (! $quotation->isDeletable()) {
            $message = $quotation->hasBills()
                ? 'Cannot delete quotation because it has associated bills.'
                : 'Cannot delete quotation. A challan has been created from this quotation.';

            return redirect()->route('quotations.index')->with('error', $message);
        }

        DB::beginTransaction();
        try {
            $quotation->delete();
            DB::commit();

            return redirect()->route('quotations.index')
                ->with('success', 'Quotation deleted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->route('quotations.index')
                ->with('error', 'Failed to delete quotation: '.$e->getMessage());
        }
    }

    /**
     * Remove a specific revision from quotation.
     */
    public function destroyRevision(Quotation $quotation, QuotationRevision $revision)
    {
        if ($quotation->hasBills()) {
            return redirect()->route('quotations.edit', $quotation->id)
                ->with('error', 'Cannot delete revision because quotation has associated bills.');
        }

        if ($revision->quotation_id !== $quotation->id) {
            return redirect()->route('quotations.edit', $quotation->id)
                ->with('error', 'Revision does not belong to this quotation.');
        }

        if ($revision->is_active) {
            return redirect()->route('quotations.edit', $quotation->id)
                ->with('error', 'Cannot delete active revision.');
        }

        DB::beginTransaction();
        try {
            $revision->delete();
            DB::commit();

            return redirect()->route('quotations.edit', $quotation->id)
                ->with('success', 'Revision deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to delete revision {$revision->id}: ".$e->getMessage());

            return redirect()->route('quotations.edit', $quotation->id)
                ->with('error', 'Failed to delete revision: '.$e->getMessage());
        }
    }

    /**
     * Update quotation status.
     */
    public function updateStatus(Request $request, Quotation $quotation)
    {
        $request->validate([
            'status' => 'required|in:in_progress,active,completed,cancelled',
        ]);

        if ($request->status === 'cancelled' && $quotation->hasChallan()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel quotation with existing challan.',
            ], 422);
        }

        $quotation->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
        ]);
    }

    /**
     * Store a new revision for an existing quotation.
     */
    public function storeRevision(Request $request, Quotation $quotation)
    {
        $validatedData = $request->validate((new QuotationRevisionRequest)->rules());

        try {
            $revision = $this->quotationService->createRevision($quotation, $validatedData);

            return redirect()->route('quotations.show', $quotation)
                ->with('success', "New revision {$revision->revision_no} created successfully for quotation {$quotation->quotation_no}");
        } catch (\Exception $e) {
            Log::error('Quotation revision creation failed: '.$e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create revision: '.$e->getMessage());
        }
    }

    /**
     * Activate a specific revision.
     */
    public function activateRevision(QuotationRevision $revision)
    {
        if (! $this->quotationService->canActivateRevision($revision)) {
            $quotation = $revision->quotation;
            $message = $quotation->hasBills()
                ? 'Cannot activate revision because quotation has associated bills.'
                : 'Cannot activate revision while a challan exists for this quotation.';

            return redirect()->back()->with('error', $message);
        }

        $this->quotationService->activateRevision($revision);

        return redirect()->back()
            ->with('success', "Revision {$revision->revision_no} activated successfully");
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
     * Search customers for AJAX requests.
     */
    public function searchCustomer(Request $request)
    {
        $query = $request->input('q');
        $perPage = (int) ($request->input('per_page') ?? 20);

        $customers = $this->queryService->searchCustomers($query, $perPage);

        return response()->json([
            'data' => $customers->items(),
            'current_page' => $customers->currentPage(),
            'last_page' => $customers->lastPage(),
            'per_page' => $customers->perPage(),
            'total' => $customers->total(),
        ]);
    }

    /**
     * Search products for AJAX requests.
     */
    public function searchProduct(Request $request)
    {
        $query = $request->input('q');
        $perPage = (int) ($request->input('per_page') ?? 20);

        $products = $this->queryService->searchProducts($query, $perPage);

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
        $product = Product::find($productId);

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

        $customer = Customer::findOrFail($request->customer_id);
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

    /**
     * Upload image for product creation within quotation.
     */
    public function uploadProductImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        if (! $request->hasFile('image')) {
            return response()->json(['success' => false, 'message' => 'No image file found.'], 422);
        }

        try {
            $file = $request->file('image');
            $uploadDir = 'uploads/images';
            $fullDirectoryPath = public_path($uploadDir);

            if (! file_exists($fullDirectoryPath)) {
                mkdir($fullDirectoryPath, 0755, true);
            }

            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $mime = $file->getClientMimeType();

            $finalFileName = pathinfo($originalName, PATHINFO_FILENAME).'-'.uniqid().'.'.$extension;
            $file->move($fullDirectoryPath, $finalFileName);
            $compressedSize = filesize($fullDirectoryPath.'/'.$finalFileName);

            $relativePath = $uploadDir.'/'.$finalFileName;

            $imageModel = \App\Models\Image::create([
                'name' => pathinfo($originalName, PATHINFO_FILENAME),
                'original_name' => $originalName,
                'file_name' => $finalFileName,
                'path' => $relativePath,
                'mime_type' => $mime,
                'size' => $compressedSize,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Image uploaded successfully!',
                'image' => [
                    'id' => $imageModel->id,
                    'name' => $imageModel->name,
                    'path' => asset($imageModel->path),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Image Upload Failed: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Server error: Could not upload the image.',
            ], 500);
        }
    }
}
