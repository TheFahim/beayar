<?php

namespace App\Http\Controllers;

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

        if (Auth::user()->hasRole('admin')) {
            $receivedBills = Bill::with(['receivedBills', 'challan', 'challan.quotation', 'challan.quotation.customer'])
                ->latest()
                ->get();
        } else {
            $receivedBills = Bill::with(['receivedBills', 'challan', 'challan.quotation', 'challan.quotation.customer'])
                ->whereHas('challan.quotation', function ($query) {
                    $query->where('user_id', Auth::id());
                })
                ->latest()
                ->get();
        }

        return view('dashboard.payments.index', compact('receivedBills'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        abort(403, 'This feature is not available yet');

        return view('dashboard.payments.create');
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

        if (! Auth::user()->hasRole('admin') && $bill->challan->revision->created_by != Auth::id()) {
            abort(403, 'Unauthorized');
        }

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

        return redirect()->route('received-bills.index')->with('success', 'Received bill created successfully.');
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

        if (! Auth::user()->hasRole('admin') && $receivedBill->challan->revision->created_by != Auth::id()) {
            abort(403, 'Unauthorized');
        }

        // Transform the payments data for the form
        // 1. Select only the necessary fields.
        // 2. Format the date to dd/mm/yyyy for the datepicker.
        $formattedPayments = $receivedBill->receivedBills->map(function ($payment) {
            return [
                'id' => $payment->id,
                'received_date' => Carbon::parse($payment->received_date)->format('d/m/Y'),
                'amount' => $payment->amount,
                'details' => $payment->details,
            ];
        })->toArray();

        return view('dashboard.payments.edit', compact('receivedBill', 'formattedPayments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bill $receivedBill)
    {
        $receivedBill->load('challan.revision');
        if (! Auth::user()->hasRole('admin') && $receivedBill->challan->revision->created_by != Auth::id()) {
            abort(403, 'Unauthorized');
        }
        // 1. VALIDATION
        // We validate that 'id' exists in the received_bills table if it's provided.
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
                // Get IDs of payments currently in the DB for this bill
                $existingPaymentIds = $receivedBill->receivedBills()->pluck('id')->all();
                // Find which IDs were in the DB but not in the submission
                $idsToDelete = array_diff($existingPaymentIds, $submittedPaymentIds);

                if (! empty($idsToDelete)) {
                    ReceivedBill::destroy($idsToDelete);
                }

                // 4. HANDLE CREATES AND UPDATES
                // Use upsert for high efficiency: it updates existing records
                // and creates new ones in a single query.
                if (! empty($paymentsToUpdateOrCreate)) {
                    ReceivedBill::upsert($paymentsToUpdateOrCreate, ['id'], ['amount', 'received_date', 'details']);
                }

                // 5. RECALCULATE BILL TOTALS (THE FIX)
                // After all operations, recalculate the total paid amount from the database
                // as the single source of truth.
                // We use fresh() to get the latest state of the bill relationship.
                $newTotalPaid = $receivedBill->fresh()->receivedBills()->sum('amount');

                $receivedBill->update([
                    'paid' => $newTotalPaid,
                    'due' => $receivedBill->payable - $newTotalPaid,
                ]);

            });
        } catch (\Exception $e) {

            Log::error('Quotation update failed: '.$e->getMessage().' on line '.$e->getLine().' in '.$e->getFile());

            // If anything goes wrong, redirect back with an error
            return back()->with('error', 'Failed to update payments: An Unexpected error occurred.');
        }

        return redirect()->route('received-bills.index')->with('success', 'Payments updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ReceivedBill $receivedBill)
    {
        //
    }
}
