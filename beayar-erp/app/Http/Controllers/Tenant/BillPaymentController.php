<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\BillPayment;
use App\Services\BillingService;
use App\Http\Requests\RecordPaymentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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

        return view('tenant.bills.payments', compact('bill', 'payments'));
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
                ->route('tenant.bills.show', $bill)
                ->with('success', 'Payment recorded successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to record payment', [
                'bill_id' => $bill->id,
                'message' => $e->getMessage(),
            ]);

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

            // Use service method for consistent status updates
            $this->billingService->updateBillPaymentStatus($bill);

            activity('billing')
                ->performedOn($bill)
                ->causedBy(auth()->user())
                ->withProperties(['payment_id' => $payment->id, 'amount' => $payment->amount])
                ->log("Payment of {$payment->amount} voided for {$bill->invoice_no}");

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment voided successfully.',
                    'bill' => $bill->fresh(),
                ]);
            }

            return redirect()
                ->route('tenant.bills.show', $bill)
                ->with('success', 'Payment voided successfully.');

        } catch (\Exception $e) {
            Log::error('Failed to void payment', [
                'payment_id' => $payment->id,
                'bill_id' => $bill->id,
                'message' => $e->getMessage(),
            ]);

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
