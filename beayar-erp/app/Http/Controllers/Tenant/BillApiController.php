<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\Quotation;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class BillApiController extends Controller
{
    public function __construct(
        protected BillingService $billingService
    ) {
        $this->middleware('auth');
    }

    /**
     * Get billable challans for a quotation.
     * Returns challans that have unbilled quantities.
     */
    public function billableChallans(Request $request, Quotation $quotation): JsonResponse
    {
        $this->authorize('view', $quotation);

        $challans = $this->billingService->getBillableChallans($quotation);

        $data = $challans->map(function ($challan) {
            return [
                'id' => $challan->id,
                'challan_number' => $challan->challan_number,
                'challan_date' => $challan->challan_date?->format('d/m/Y'),
                'products' => $challan->challanProducts->map(function ($cp) {
                    $billedQty = DB::table('bill_items')
                        ->where('challan_product_id', $cp->id)
                        ->sum('quantity');
                    $unbilledQty = bcsub($cp->quantity, $billedQty, 2);

                    return [
                        'id' => $cp->id,
                        'product_name' => $cp->quotationProduct?->product_name ?? $cp->product_name,
                        'description' => $cp->description,
                        'quantity' => $cp->quantity,
                        'billed_quantity' => $billedQty,
                        'unbilled_quantity' => $unbilledQty,
                        'unit_price' => $cp->unit_price,
                    ];
                })->filter(function ($product) {
                    return bccomp($product['unbilled_quantity'], '0.00', 2) > 0;
                })->values(),
            ];
        });

        return response()->json([
            'success' => true,
            'challans' => $data,
        ]);
    }

    /**
     * Get available advances for a quotation.
     * Returns advance bills with remaining unapplied balance.
     */
    public function availableAdvances(Request $request, Quotation $quotation): JsonResponse
    {
        $this->authorize('view', $quotation);

        $advances = Bill::where('quotation_id', $quotation->id)
            ->where('bill_type', Bill::TYPE_ADVANCE)
            ->where('status', '!=', Bill::STATUS_CANCELLED)
            ->with(['payments'])
            ->get()
            ->map(function ($bill) {
                $balance = $this->billingService->getUnappliedAdvanceBalance($bill);

                return [
                    'id' => $bill->id,
                    'bill_number' => $bill->invoice_no,
                    'bill_date' => $bill->bill_date?->format('d/m/Y'),
                    'total_amount' => $bill->total_amount,
                    'paid_amount' => $bill->paid_amount,
                    'applied_amount' => bcsub($bill->paid_amount, $balance, 2),
                    'available_balance' => $balance,
                    'is_available' => bccomp($balance, '0.00', 2) > 0,
                ];
            })
            ->filter(function ($advance) {
                return $advance['is_available'];
            })
            ->values();

        return response()->json([
            'success' => true,
            'advances' => $advances,
        ]);
    }

    /**
     * Get advance balance for a specific bill.
     */
    public function advanceBalance(Request $request, Bill $bill): JsonResponse
    {
        $this->authorize('view', $bill);

        if ($bill->bill_type !== Bill::TYPE_ADVANCE) {
            return response()->json([
                'success' => false,
                'message' => 'This is not an advance bill.',
            ], 422);
        }

        $balance = $this->billingService->getUnappliedAdvanceBalance($bill);

        // Get adjustments made from this advance
        $adjustments = $bill->advanceAdjustmentsGiven()
            ->with('finalBill')
            ->get()
            ->map(function ($adj) {
                return [
                    'id' => $adj->id,
                    'final_bill_id' => $adj->final_bill_id,
                    'final_bill_number' => $adj->finalBill?->invoice_no,
                    'amount' => $adj->amount,
                    'created_at' => $adj->created_at->format('d/m/Y H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'bill' => [
                'id' => $bill->id,
                'bill_number' => $bill->invoice_no,
                'total' => $bill->total_amount,
                'paid_amount' => $bill->paid_amount,
                'available_balance' => $balance,
            ],
            'adjustments' => $adjustments,
        ]);
    }

    /**
     * Get bill summary for a quotation.
     * Returns all bills for a quotation with their status.
     */
    public function quotationBillSummary(Request $request, Quotation $quotation): JsonResponse
    {
        $this->authorize('view', $quotation);

        $bills = Bill::where('quotation_id', $quotation->id)
            ->with(['payments', 'parentBill', 'childBills'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($bill) {
                return [
                    'id' => $bill->id,
                    'bill_number' => $bill->invoice_no,
                    'bill_type' => $bill->bill_type,
                    'status' => $bill->status,
                    'bill_date' => $bill->bill_date?->format('d/m/Y'),
                    'total' => $bill->total_amount,
                    'paid_amount' => $bill->paid_amount,
                    'remaining_balance' => $bill->remaining_balance,
                    'advance_applied' => $bill->advance_applied_amount,
                    'net_payable' => $bill->net_payable_amount,
                    'is_locked' => $bill->is_locked,
                    'lock_reason' => $bill->lock_reason,
                    'parent_bill_number' => $bill->parentBill?->invoice_no,
                    'child_bills_count' => $bill->childBills->count(),
                ];
            });

        // Calculate totals
        $totals = [
            'total_bills' => $bills->count(),
            'total_amount' => $bills->sum('total'),
            'total_paid' => $bills->sum('paid_amount'),
            'by_type' => [
                'advance' => $bills->where('bill_type', 'advance')->count(),
                'running' => $bills->where('bill_type', 'running')->count(),
                'regular' => $bills->where('bill_type', 'regular')->count(),
            ],
            'by_status' => [
                'draft' => $bills->where('status', 'draft')->count(),
                'issued' => $bills->where('status', 'issued')->count(),
                'paid' => $bills->where('status', 'paid')->count(),
                'cancelled' => $bills->where('status', 'cancelled')->count(),
            ],
        ];

        return response()->json([
            'success' => true,
            'bills' => $bills,
            'totals' => $totals,
            'billing_stage' => $quotation->billing_stage,
        ]);
    }

    /**
     * Quick bill status check.
     */
    public function status(Request $request, Bill $bill): JsonResponse
    {
        $this->authorize('view', $bill);

        return response()->json([
            'success' => true,
            'bill' => [
                'id' => $bill->id,
                'bill_number' => $bill->invoice_no,
                'status' => $bill->status,
                'is_locked' => $bill->is_locked,
                'lock_reason' => $bill->lock_reason,
                'can_edit' => $bill->canBeEdited(),
                'can_issue' => $bill->status === Bill::STATUS_DRAFT,
                'can_cancel' => in_array($bill->status, [Bill::STATUS_ISSUED, Bill::STATUS_PARTIALLY_PAID]),
                'can_record_payment' => in_array($bill->status, [Bill::STATUS_ISSUED, Bill::STATUS_PARTIALLY_PAID]),
                'remaining_balance' => $bill->remaining_balance,
            ],
        ]);
    }

    /**
     * Search bills by number or quotation.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:2',
        ]);

        $query = Bill::with('quotation')
            ->where(function ($q) use ($request) {
                $q->where('invoice_no', 'like', "%{$request->query}%")
                  ->orWhereHas('quotation', function ($q) use ($request) {
                      $q->where('quotation_number', 'like', "%{$request->query}%");
                  });
            });

        $bills = $query->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($bill) {
                return [
                    'id' => $bill->id,
                    'bill_number' => $bill->invoice_no,
                    'bill_type' => $bill->bill_type,
                    'status' => $bill->status,
                    'total' => $bill->total_amount,
                    'quotation_number' => $bill->quotation?->quotation_number,
                ];
            });

        return response()->json([
            'success' => true,
            'bills' => $bills,
        ]);
    }
}
