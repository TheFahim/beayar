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
        // Check if user has permission or if subscription limits allow billing creation (optional)

        $bill = Bill::create(array_merge(
            $request->validated(),
            [
                'user_company_id' => $request->user()->current_user_company_id,
                'invoice_no' => 'INV-' . strtoupper(uniqid()),
                'status' => 'draft'
            ]
        ));

        // Link Challans if provided
        if ($request->has('challan_ids')) {
            $bill->challans()->attach($request->challan_ids);

            // Recalculate bill totals based on challans (simplified logic)
            // In a real scenario, we'd iterate challan items and sum them up
            // $total = $bill->challans->sum('total_amount');
            // $bill->update(['total_amount' => $total, 'due' => $total]);
        }

        return response()->json($bill->load('challans'), 201);
    }

    public function show(Bill $bill): JsonResponse
    {
        $bill->load(['challans', 'items']);
        return response()->json($bill);
    }
}
