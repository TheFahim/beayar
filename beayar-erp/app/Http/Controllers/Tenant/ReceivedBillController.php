<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\ReceivedBill;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReceivedBillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $receivedBills = Bill::with(['receivedBills', 'challan', 'challan.quotation', 'challan.quotation.customer'])
            ->latest()
            ->paginate(15);

        return view('tenant.payments.index', compact('receivedBills'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort(403, 'This feature is not available yet');

        return view('tenant.payments.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bill_id' => 'required|exists:bills,id',
            'payment' => 'required|array|min:1',
            'payment.*.amount' => 'required|numeric',
            'payment.*.received_date' => 'required|date_format:d/m/Y',
            'payment.*.details' => 'nullable|string|max:255',
        ]);

        $paid = 0;

        $bill = Bill::find($validated['bill_id']);

        $bill->load('challan.revision');

        foreach ($validated['payment'] as $payment) {
            $paid += $payment['amount'];

            ReceivedBill::create([
                'bill_id' => $validated['bill_id'],
                'amount' => $payment['amount'],
                'received_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $payment['received_date'])->format('Y-m-d'),
                'details' => $payment['details'] ?? null,
            ]);
        }

        $bill->update([
            'paid' => $bill->paid + $paid,
            'due' => $bill->due - $paid,
        ]);

        return redirect()->route('tenant.received-bills.index')->with('success', 'Received bill created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ReceivedBill $receivedBill)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bill $receivedBill)
    {
        // Eager load the related payments
        $receivedBill->load(['receivedBills', 'challan.revision']);

        // Transform the payments data for the form
        $formattedPayments = $receivedBill->receivedBills->map(function ($payment) {
            return [
                'id' => $payment->id,
                'received_date' => Carbon::parse($payment->received_date)->format('d/m/Y'),
                'amount' => $payment->amount,
                'details' => $payment->details,
            ];
        })->toArray();

        return view('tenant.payments.edit', compact('receivedBill', 'formattedPayments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bill $receivedBill)
    {
        $receivedBill->load('challan.revision');

        // 1. VALIDATION
        $validated = $request->validate([
            'payment' => 'required|array',
            'payment.*.id' => 'nullable|integer|exists:received_bills,id',
            'payment.*.amount' => 'required|numeric|min:0',
            'payment.*.received_date' => 'required|date_format:d/m/Y',
            'payment.*.details' => 'nullable|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($validated, $receivedBill) {

                // 2. PREPARE DATA
                $submittedPaymentIds = [];
                $paymentsToUpdateOrCreate = [];

                foreach ($validated['payment'] as $paymentData) {
                    // Prepare data for upsert and collect submitted IDs
                    $data = [
                        'id' => $paymentData['id'] ?? null,
                        'bill_id' => $receivedBill->id,
                        'amount' => $paymentData['amount'],
                        'received_date' => Carbon::createFromFormat('d/m/Y', $paymentData['received_date'])->format('Y-m-d'),
                        'details' => $paymentData['details'] ?? null,
                    ];
                    $paymentsToUpdateOrCreate[] = $data;

                    if (isset($paymentData['id'])) {
                        $submittedPaymentIds[] = $paymentData['id'];
                    }
                }

                // 3. HANDLE DELETIONS
                $existingPaymentIds = $receivedBill->receivedBills()->pluck('id')->all();
                $idsToDelete = array_diff($existingPaymentIds, $submittedPaymentIds);

                if (! empty($idsToDelete)) {
                    ReceivedBill::destroy($idsToDelete);
                }

                // 4. HANDLE CREATES AND UPDATES
                if (! empty($paymentsToUpdateOrCreate)) {
                    ReceivedBill::upsert($paymentsToUpdateOrCreate, ['id'], ['amount', 'received_date', 'details']);
                }

                // 5. RECALCULATE BILL TOTALS
                $newTotalPaid = $receivedBill->fresh()->receivedBills()->sum('amount');

                $receivedBill->update([
                    'paid' => $newTotalPaid,
                    'due' => $receivedBill->payable - $newTotalPaid,
                ]);

            });
        } catch (\Exception $e) {

            Log::error('Quotation update failed: '.$e->getMessage().' on line '.$e->getLine().' in '.$e->getFile());

            return back()->with('error', 'Failed to update payments: An Unexpected error occurred.');
        }

        return redirect()->route('tenant.received-bills.index')->with('success', 'Payments updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReceivedBill $receivedBill)
    {
        //
    }
}
