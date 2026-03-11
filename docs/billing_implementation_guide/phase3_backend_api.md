# Phase 3 — Backend API & Controllers (Days 6-7)

This phase implements the controllers and API endpoints that expose the BillingService functionality to the frontend.

---

## Day 6 — BillController & BillPaymentController

### 🎯 Goal
By the end of Day 6, you will have a complete BillController with all CRUD operations and a BillPaymentController for payment management.

### 📋 Prerequisites
- [ ] Phase 2 completed (BillingService, Form Requests, Policy)
- [ ] Routes file ready for modification
- [ ] Understanding of existing controller patterns in the codebase

---

### ⚙️ Backend Tasks

#### Task 1: Create BillController

**File:** `app/Http/Controllers/BillController.php` (NEW or MODIFICATION)

```php
<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Quotation;
use App\Services\BillingService;
use App\Http\Requests\CreateAdvanceBillRequest;
use App\Http\Requests\CreateRegularBillRequest;
use App\Http\Requests\ApplyAdvanceCreditRequest;
use App\Exceptions\BillLockedException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class BillController extends Controller
{
    public function __construct(
        protected BillingService $billingService
    ) {
        $this->middleware('auth');
    }

    /**
     * Display a listing of bills.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Bill::class);

        $query = Bill::forTenant()
            ->with(['quotation', 'payments']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('bill_type', $request->type);
        }

        // Filter by quotation
        if ($request->filled('quotation_id')) {
            $query->where('quotation_id', $request->quotation_id);
        }

        $bills = $query->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('bills.index', compact('bills'));
    }

    /**
     * Show the form for creating a new bill.
     */
    public function create(Request $request): View
    {
        $this->authorize('create', Bill::class);

        $type = $request->get('type', 'regular');
        $quotationId = $request->get('quotation_id');
        $quotation = null;

        if ($quotationId) {
            $quotation = Quotation::forTenant()
                ->with(['quotationRevision.quotationProducts'])
                ->findOrFail($quotationId);
        }

        // Get available advances for this quotation (if creating regular bill)
        $availableAdvances = collect();
        if ($type === 'regular' && $quotation) {
            $availableAdvances = Bill::forTenant()
                ->where('quotation_id', $quotationId)
                ->where('bill_type', Bill::TYPE_ADVANCE)
                ->where('status', '!=', Bill::STATUS_CANCELLED)
                ->get()
                ->filter(function ($bill) {
                    return bccomp($this->billingService->getUnappliedAdvanceBalance($bill), '0.00', 2) > 0;
                });
        }

        // Get billable challans for regular bills
        $billableChallans = collect();
        if ($type === 'regular' && $quotation) {
            $billableChallans = $this->billingService->getBillableChallans($quotation);
        }

        // Get parent advance bill for running bills
        $parentAdvance = null;
        if ($type === 'running' && $request->has('parent_bill_id')) {
            $parentAdvance = Bill::forTenant()
                ->where('bill_type', Bill::TYPE_ADVANCE)
                ->findOrFail($request->parent_bill_id);
        }

        return view("bills.create-{$type}", compact(
            'quotation',
            'type',
            'availableAdvances',
            'billableChallans',
            'parentAdvance'
        ));
    }

    /**
     * Store a newly created bill.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Bill::class);

        $type = $request->get('bill_type', 'regular');

        try {
            $bill = match ($type) {
                Bill::TYPE_ADVANCE => $this->storeAdvanceBill($request),
                Bill::TYPE_RUNNING => $this->storeRunningBill($request),
                Bill::TYPE_REGULAR => $this->storeRegularBill($request),
                default => throw new \InvalidArgumentException("Invalid bill type: {$type}"),
            };

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bill created successfully.',
                    'bill' => $bill->load(['quotation', 'billItems']),
                ], 201);
            }

            return redirect()
                ->route('bills.show', $bill)
                ->with('success', 'Bill created successfully.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Store an advance bill.
     */
    protected function storeAdvanceBill(Request $request): Bill
    {
        $validated = app(CreateAdvanceBillRequest::class)->validated();
        $quotation = Quotation::forTenant()->findOrFail($validated['quotation_id']);

        return $this->billingService->createAdvanceBill($validated, $quotation);
    }

    /**
     * Store a running bill.
     */
    protected function storeRunningBill(Request $request): Bill
    {
        $validated = $request->validate([
            'parent_bill_id' => 'required|exists:bills,id',
            'subtotal' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'bill_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:bill_date',
            'notes' => 'nullable|string|max:2000',
        ]);

        $parentBill = Bill::forTenant()
            ->where('bill_type', Bill::TYPE_ADVANCE)
            ->findOrFail($validated['parent_bill_id']);

        return $this->billingService->createRunningBill($validated, $parentBill);
    }

    /**
     * Store a regular bill.
     */
    protected function storeRegularBill(Request $request): Bill
    {
        $validated = app(CreateRegularBillRequest::class)->validated();
        $quotation = Quotation::forTenant()->findOrFail($validated['quotation_id']);

        $bill = $this->billingService->createRegularBill(
            $validated,
            $quotation,
            $validated['challan_ids']
        );

        // Apply advance credit if provided
        if (isset($validated['advance_adjustment'])) {
            $advanceBill = Bill::findOrFail($validated['advance_adjustment']['advance_bill_id']);
            $this->billingService->applyAdvanceCredit(
                $advanceBill,
                $bill,
                $validated['advance_adjustment']['amount']
            );
        }

        return $bill;
    }

    /**
     * Display the specified bill.
     */
    public function show(Bill $bill): View
    {
        $this->authorize('view', $bill);

        $bill->load([
            'quotation',
            'quotationRevision',
            'billItems',
            'challans',
            'payments.creator',
            'advanceAdjustmentsGiven',
            'advanceAdjustmentsReceived',
            'parentBill',
            'childBills',
        ]);

        return view('bills.show', compact('bill'));
    }

    /**
     * Show the form for editing the specified bill.
     */
    public function edit(Bill $bill): View
    {
        $this->authorize('update', $bill);

        $bill->load(['quotation', 'billItems', 'challans']);

        return view('bills.edit', compact('bill'));
    }

    /**
     * Update the specified bill.
     */
    public function update(Request $request, Bill $bill)
    {
        $this->authorize('update', $bill);

        try {
            $validated = $request->validate([
                'bill_date' => 'required|date',
                'due_date' => 'nullable|date|after_or_equal:bill_date',
                'notes' => 'nullable|string|max:2000',
                'terms_conditions' => 'nullable|string|max:5000',
                // Add other editable fields as needed
            ]);

            $bill->update($validated);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bill updated successfully.',
                    'bill' => $bill->fresh(),
                ]);
            }

            return redirect()
                ->route('bills.show', $bill)
                ->with('success', 'Bill updated successfully.');

        } catch (BillLockedException $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                    'lock_reason' => $e->getReason(),
                ], 422);
            }

            return back()->withErrors(['bill' => $e->getMessage()]);
        }
    }

    /**
     * Issue the specified bill.
     */
    public function issue(Request $request, Bill $bill)
    {
        $this->authorize('issue', $bill);

        try {
            $bill = $this->billingService->issueBill($bill);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bill issued successfully.',
                    'bill' => $bill,
                ]);
            }

            return redirect()
                ->route('bills.show', $bill)
                ->with('success', 'Bill issued successfully.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Cancel the specified bill.
     */
    public function cancel(Request $request, Bill $bill)
    {
        $this->authorize('cancel', $bill);

        $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        try {
            $bill = $this->billingService->cancelBill($bill, $request->reason);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bill cancelled successfully.',
                    'bill' => $bill,
                ]);
            }

            return redirect()
                ->route('bills.show', $bill)
                ->with('success', 'Bill cancelled successfully.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Reissue a cancelled bill (creates a new draft).
     */
    public function reissue(Request $request, Bill $bill)
    {
        $this->authorize('reissue', $bill);

        try {
            $newBill = $this->billingService->reissueBill($bill);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bill reissued successfully.',
                    'bill' => $newBill,
                ]);
            }

            return redirect()
                ->route('bills.edit', $newBill)
                ->with('success', 'Bill reissued. Please review and issue when ready.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Apply advance credit to a regular bill.
     */
    public function applyAdvance(ApplyAdvanceCreditRequest $request, Bill $bill)
    {
        $this->authorize('applyAdvance', $bill);

        try {
            $advanceBill = Bill::findOrFail($request->advance_bill_id);
            
            $adjustment = $this->billingService->applyAdvanceCredit(
                $advanceBill,
                $bill,
                $request->amount
            );

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Advance credit applied successfully.',
                    'adjustment' => $adjustment,
                    'bill' => $bill->fresh(),
                ]);
            }

            return redirect()
                ->route('bills.show', $bill)
                ->with('success', 'Advance credit applied successfully.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove advance credit from a regular bill.
     */
    public function removeAdvance(Request $request, Bill $bill, BillAdvanceAdjustment $adjustment)
    {
        $this->authorize('update', $bill);

        try {
            $this->billingService->removeAdvanceCredit($adjustment);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Advance credit removed successfully.',
                    'bill' => $bill->fresh(),
                ]);
            }

            return redirect()
                ->route('bills.show', $bill)
                ->with('success', 'Advance credit removed successfully.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete a draft bill.
     */
    public function destroy(Request $request, Bill $bill)
    {
        $this->authorize('delete', $bill);

        try {
            $bill->delete();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bill deleted successfully.',
                ]);
            }

            return redirect()
                ->route('bills.index')
                ->with('success', 'Bill deleted successfully.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
```

#### Task 2: Create BillPaymentController

**File:** `app/Http/Controllers/BillPaymentController.php` (NEW)

```php
<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillPayment;
use App\Services\BillingService;
use App\Http\Requests\RecordPaymentRequest;
use Illuminate\Http\Request;

class BillPaymentController extends Controller
{
    public function __construct(
        protected BillingService $billingService
    ) {
        $this->middleware('auth');
    }

    /**
     * Display payments for a bill.
     */
    public function index(Bill $bill)
    {
        $this->authorize('view', $bill);

        $payments = $bill->payments()
            ->with('creator')
            ->orderBy('payment_date', 'desc')
            ->get();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'payments' => $payments,
            ]);
        }

        return view('bills.payments', compact('bill', 'payments'));
    }

    /**
     * Store a new payment.
     */
    public function store(RecordPaymentRequest $request, Bill $bill)
    {
        $this->authorize('recordPayment', $bill);

        try {
            $payment = $this->billingService->recordPayment($bill, $request->validated());

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment recorded successfully.',
                    'payment' => $payment->load('creator'),
                    'bill' => $bill->fresh(),
                ], 201);
            }

            return redirect()
                ->route('bills.show', $bill)
                ->with('success', 'Payment recorded successfully.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Void/delete a payment.
     */
    public function destroy(Request $request, Bill $bill, BillPayment $payment)
    {
        $this->authorize('recordPayment', $bill);

        // Check if payment belongs to this bill
        if ($payment->bill_id !== $bill->id) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment does not belong to this bill.',
                ], 422);
            }

            return back()->withErrors(['error' => 'Payment does not belong to this bill.']);
        }

        // Only allow voiding if bill is not fully paid
        if ($bill->status === Bill::STATUS_PAID) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot void payment on a fully paid bill.',
                ], 422);
            }

            return back()->withErrors(['error' => 'Cannot void payment on a fully paid bill.']);
        }

        try {
            $payment->delete();

            // Update bill status
            $paidAmount = $bill->payments()->sum('amount');
            $netPayable = $bill->net_payable_amount ?? $bill->total;

            $newStatus = bccomp($paidAmount, '0.00', 2) <= 0
                ? Bill::STATUS_ISSUED
                : Bill::STATUS_PARTIALLY_PAID;

            $bill->update(['status' => $newStatus]);

            activity('billing')
                ->performedOn($bill)
                ->causedBy(auth()->user())
                ->withProperties(['payment_id' => $payment->id, 'amount' => $payment->amount])
                ->log("Payment of {$payment->amount} voided for {$bill->bill_number}");

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment voided successfully.',
                    'bill' => $bill->fresh(),
                ]);
            }

            return redirect()
                ->route('bills.show', $bill)
                ->with('success', 'Payment voided successfully.');

        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
```

---

### ✅ End-of-Day Checklist (Day 6)

- [ ] BillController created with all methods
- [ ] BillPaymentController created
- [ ] All controller methods authorize actions via Policy
- [ ] All methods handle both JSON and web responses
- [ ] BillingService injected via constructor
- [ ] Proper error handling with try/catch
- [ ] Activity logging in place

### ⚠️ Pitfalls & Notes (Day 6)

1. **Authorization First:** Always call `$this->authorize()` at the start of each method. This ensures unauthorized access is blocked before any processing.

2. **JSON vs Web:** Controllers must handle both AJAX requests (`expectsJson()`) and regular web requests. Return appropriate responses for each.

3. **Service Injection:** The BillingService is injected via constructor. This allows for easier testing and follows Laravel's dependency injection pattern.

4. **Policy Exceptions:** The `update` policy returns `false` for locked bills, which results in a 403 response. The BillLockedException is thrown by the model's boot method.

---

## Day 7 — API Controller & Routes

### 🎯 Goal
By the end of Day 7, you will have the BillApiController for AJAX endpoints and all routes registered.

### 📋 Prerequisites
- [ ] Day 6 controllers completed
- [ ] Understanding of route patterns in the codebase

---

### ⚙️ Backend Tasks

#### Task 1: Create BillApiController

**File:** `app/Http/Controllers/BillApiController.php` (NEW)

```php
<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Quotation;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
                    $billedQty = \DB::table('bill_items')
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

        $advances = Bill::forTenant()
            ->where('quotation_id', $quotation->id)
            ->where('bill_type', Bill::TYPE_ADVANCE)
            ->where('status', '!=', Bill::STATUS_CANCELLED)
            ->with(['payments'])
            ->get()
            ->map(function ($bill) {
                $balance = $this->billingService->getUnappliedAdvanceBalance($bill);
                
                return [
                    'id' => $bill->id,
                    'bill_number' => $bill->bill_number,
                    'bill_date' => $bill->bill_date?->format('d/m/Y'),
                    'total_amount' => $bill->total,
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
                    'final_bill_number' => $adj->finalBill?->bill_number,
                    'amount' => $adj->amount,
                    'created_at' => $adj->created_at->format('d/m/Y H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'bill' => [
                'id' => $bill->id,
                'bill_number' => $bill->bill_number,
                'total' => $bill->total,
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

        $bills = Bill::forTenant()
            ->where('quotation_id', $quotation->id)
            ->with(['payments', 'parentBill', 'childBills'])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($bill) {
                return [
                    'id' => $bill->id,
                    'bill_number' => $bill->bill_number,
                    'bill_type' => $bill->bill_type,
                    'status' => $bill->status,
                    'bill_date' => $bill->bill_date?->format('d/m/Y'),
                    'total' => $bill->total,
                    'paid_amount' => $bill->paid_amount,
                    'remaining_balance' => $bill->remaining_balance,
                    'advance_applied' => $bill->advance_applied_amount,
                    'net_payable' => $bill->net_payable_amount,
                    'is_locked' => $bill->is_locked,
                    'lock_reason' => $bill->lock_reason,
                    'parent_bill_number' => $bill->parentBill?->bill_number,
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
                'bill_number' => $bill->bill_number,
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

        $query = Bill::forTenant()
            ->with('quotation')
            ->where(function ($q) use ($request) {
                $q->where('bill_number', 'like', "%{$request->query}%")
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
                    'bill_number' => $bill->bill_number,
                    'bill_type' => $bill->bill_type,
                    'status' => $bill->status,
                    'total' => $bill->total,
                    'quotation_number' => $bill->quotation?->quotation_number,
                ];
            });

        return response()->json([
            'success' => true,
            'bills' => $bills,
        ]);
    }
}
```

#### Task 2: Register Routes

**File:** `routes/web.php` (MODIFICATION)

Add the following routes to your web.php file:

```php
<?php

use App\Http\Controllers\BillController;
use App\Http\Controllers\BillPaymentController;
use App\Http\Controllers\BillApiController;

// Bill Routes
Route::middleware(['auth', 'tenant'])->group(function () {
    
    // Bill CRUD
    Route::get('/bills', [BillController::class, 'index'])->name('bills.index');
    Route::get('/bills/create', [BillController::class, 'create'])->name('bills.create');
    Route::post('/bills', [BillController::class, 'store'])->name('bills.store');
    Route::get('/bills/{bill}', [BillController::class, 'show'])->name('bills.show');
    Route::get('/bills/{bill}/edit', [BillController::class, 'edit'])->name('bills.edit');
    Route::put('/bills/{bill}', [BillController::class, 'update'])->name('bills.update');
    Route::delete('/bills/{bill}', [BillController::class, 'destroy'])->name('bills.destroy');
    
    // Bill Actions
    Route::post('/bills/{bill}/issue', [BillController::class, 'issue'])->name('bills.issue');
    Route::post('/bills/{bill}/cancel', [BillController::class, 'cancel'])->name('bills.cancel');
    Route::post('/bills/{bill}/reissue', [BillController::class, 'reissue'])->name('bills.reissue');
    
    // Advance Credit
    Route::post('/bills/{bill}/apply-advance', [BillController::class, 'applyAdvance'])->name('bills.apply-advance');
    Route::delete('/bills/{bill}/advance-adjustments/{adjustment}', [BillController::class, 'removeAdvance'])
        ->name('bills.remove-advance');
    
    // Bill Payments
    Route::get('/bills/{bill}/payments', [BillPaymentController::class, 'index'])->name('bills.payments.index');
    Route::post('/bills/{bill}/payments', [BillPaymentController::class, 'store'])->name('bills.payments.store');
    Route::delete('/bills/{bill}/payments/{payment}', [BillPaymentController::class, 'destroy'])
        ->name('bills.payments.destroy');
});
```

**File:** `routes/api.php` (MODIFICATION)

Add the following routes to your api.php file:

```php
<?php

use App\Http\Controllers\BillApiController;
use Illuminate\Support\Facades\Route;

// API Routes (require auth)
Route::middleware(['auth:sanctum', 'tenant'])->group(function () {
    
    // Bill API endpoints
    Route::prefix('api')->group(function () {
        
        // Quotation-related endpoints
        Route::get('/quotations/{quotation}/billable-challans', [BillApiController::class, 'billableChallans'])
            ->name('api.quotations.billable-challans');
        
        Route::get('/quotations/{quotation}/available-advances', [BillApiController::class, 'availableAdvances'])
            ->name('api.quotations.available-advances');
        
        Route::get('/quotations/{quotation}/bill-summary', [BillApiController::class, 'quotationBillSummary'])
            ->name('api.quotations.bill-summary');
        
        // Bill endpoints
        Route::get('/bills/{bill}/advance-balance', [BillApiController::class, 'advanceBalance'])
            ->name('api.bills.advance-balance');
        
        Route::get('/bills/{bill}/status', [BillApiController::class, 'status'])
            ->name('api.bills.status');
        
        Route::get('/bills/search', [BillApiController::class, 'search'])
            ->name('api.bills.search');
    });
});
```

#### Task 3: Create Route Helper for Tenant

If not already present, ensure you have a helper function for getting the current tenant ID:

**File:** `app/helpers.php` (MODIFICATION or CREATE)

```php
<?php

if (!function_exists('currentTenantId')) {
    /**
     * Get the current tenant company ID.
     *
     * @return int|null
     */
    function currentTenantId(): ?int
    {
        // Adjust this based on your multi-tenancy implementation
        // Example: session-based, middleware-set, or user-based
        
        return session('tenant_company_id') 
            ?? auth()->user()?->tenant_company_id 
            ?? null;
    }
}

if (!function_exists('currentTenant')) {
    /**
     * Get the current tenant company model.
     *
     * @return \App\Models\TenantCompany|null
     */
    function currentTenant(): ?\App\Models\TenantCompany
    {
        $id = currentTenantId();
        if (!$id) return null;
        
        return \App\Models\TenantCompany::find($id);
    }
}
```

Make sure this file is loaded in `composer.json`:

```json
{
    "autoload": {
        "files": [
            "app/helpers.php"
        ]
    }
}
```

Then run:
```bash
composer dump-autoload
```

---

### ✅ End-of-Day Checklist (Day 7)

- [ ] BillApiController created with all AJAX endpoints
- [ ] Routes registered in web.php
- [ ] API routes registered in api.php
- [ ] Route helper functions available
- [ ] All routes have proper middleware
- [ ] `php artisan route:list` shows all new routes
- [ ] Test each route with Postman or similar tool

### ⚠️ Pitfalls & Notes (Day 7)

1. **Route Order:** Ensure route order doesn't cause conflicts. Routes with parameters should come after static routes.

2. **Middleware:** All billing routes should have `auth` and `tenant` middleware. The tenant middleware ensures proper tenant scoping.

3. **Route Naming:** Use consistent naming conventions (`bills.index`, `bills.store`, etc.) for easier URL generation with `route()`.

4. **API Authentication:** API routes use `auth:sanctum` for token-based authentication. Adjust if using a different authentication method.

---

## Phase 3 Summary

| Day | Files Created | Files Modified |
|-----|---------------|----------------|
| 6 | BillController.php, BillPaymentController.php | None |
| 7 | BillApiController.php | routes/web.php, routes/api.php |

**Next:** Proceed to [Phase 4 — Frontend: Bill Creation](./phase4_frontend_bill_creation.md)
