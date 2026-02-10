<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Quotation\QuotationCreateRequest;
use App\Http\Requests\Quotation\QuotationUpdateRequest;
use App\Models\Quotation;
use App\Services\Tenant\QuotationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    protected $quotationService;

    public function __construct(QuotationService $quotationService)
    {
        $this->quotationService = $quotationService;
    }

    public function index(): JsonResponse
    {
        $quotations = Quotation::with(['customer', 'latestRevision', 'status'])->latest()->paginate(20);

        return response()->json($quotations);
    }

    public function store(QuotationCreateRequest $request): JsonResponse
    {
        $quotation = $this->quotationService->createQuotation($request->user(), $request->validated());

        return response()->json($quotation, 201);
    }

    public function show(Quotation $quotation): JsonResponse
    {
        $quotation->load(['customer', 'revisions.products', 'latestRevision']);

        return response()->json($quotation);
    }

    public function update(QuotationUpdateRequest $request, Quotation $quotation): JsonResponse
    {
        $quotation->update($request->validated());

        return response()->json($quotation);
    }

    public function createRevision(Request $request, Quotation $quotation): JsonResponse
    {
        // Validate input for new revision products if provided, or clone previous
        // For simplicity, assuming request contains new product list similar to create
        $data = $request->validate([
            'currency' => ['required', 'string', 'size:3'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.product_id' => ['required', 'exists:products,id'],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
            'products.*.unit_price' => ['required', 'numeric', 'min:0'],
            'products.*.tax' => ['nullable', 'numeric', 'min:0'],
        ]);

        $revision = $this->quotationService->createRevision($quotation, $data);

        return response()->json($revision, 201);
    }

    public function destroy(Quotation $quotation): JsonResponse
    {
        $quotation->delete();

        return response()->json(['message' => 'Quotation deleted successfully']);
    }

    public function pdf(Quotation $quotation)
    {
        // PDF generation logic (using dompdf or similar)
        // return PDF download
        return response()->json(['message' => 'PDF generation not implemented yet']);
    }
}
