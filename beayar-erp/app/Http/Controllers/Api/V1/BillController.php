<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\BillCreateRequest;
use App\Models\Bill;
use App\Services\Tenant\TenantBillingService;
use Illuminate\Http\JsonResponse;

class BillController extends Controller
{
    protected $billingService;

    public function __construct(TenantBillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    public function index(): JsonResponse
    {
        $bills = Bill::with(['challans', 'items'])->latest()->paginate(20);
        return response()->json($bills);
    }

    public function store(BillCreateRequest $request): JsonResponse
    {
        // We need to map request data to what service expects
        // Service expects User, type, and challan models/ids
        
        // This is a simplified call, actual service method signature might need adjustment or strict matching
        // Let's assume we implement a method in service to handle raw data or we fetch challans here
        
        // For now, pseudo-implementation as Service might need specific objects
        // $bill = $this->billingService->createBill($request->user(), ...);
        
        // Placeholder response
        return response()->json(['message' => 'Bill creation logic to be connected to Service'], 201);
    }

    public function show(Bill $bill): JsonResponse
    {
        $bill->load(['challans', 'items']);
        return response()->json($bill);
    }
}
