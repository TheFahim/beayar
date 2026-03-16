<?php

namespace App\Exceptions;

use Exception;
use App\Models\Bill;

/**
 * Exception thrown when attempting to modify a locked bill.
 * 
 * This exception is thrown when any of the 6 locking rules prevent
 * modification of a bill. The exception carries the bill instance
 * and the specific lock reason for proper error handling.
 */
class BillLockedException extends Exception
{
    /**
     * The bill that is locked.
     */
    protected Bill $bill;

    /**
     * The reason the bill is locked.
     */
    protected string $reason;

    /**
     * Human-readable messages for each lock reason.
     */
    protected array $reasonMessages = [
        'status_not_draft' => 'This bill cannot be edited because it is not in draft status.',
        'has_issued_child' => 'This bill cannot be edited because it has issued child bills.',
        'has_payments' => 'This bill cannot be edited because payments have been recorded.',
        'challan_quantity_violation' => 'This bill cannot be edited because it would reduce quantities below delivered amounts.',
        'advance_applied' => 'This bill cannot be edited because advance credit has been applied.',
        'has_advance_adjustments' => 'This bill cannot be edited because advance adjustments reference it.',
    ];

    /**
     * Create a new BillLockedException instance.
     *
     * @param Bill $bill The locked bill
     * @param string $reason The lock reason (must match a Bill::LOCK_REASON_* constant)
     */
    public function __construct(Bill $bill, string $reason)
    {
        $this->bill = $bill;
        $this->reason = $reason;

        $message = $this->reasonMessages[$reason] 
            ?? "This bill is locked and cannot be modified. Reason: {$reason}";

        parent::__construct($message, 422);
    }

    /**
     * Get the locked bill.
     */
    public function getBill(): Bill
    {
        return $this->bill;
    }

    /**
     * Get the lock reason.
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * Get the human-readable reason message.
     */
    public function getReasonMessage(): string
    {
        return $this->reasonMessages[$this->reason] ?? $this->getMessage();
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function render($request)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
                'errors' => [
                    'bill' => [$this->getReasonMessage()]
                ],
                'lock_reason' => $this->reason,
                'bill_id' => $this->bill->id,
            ], 422);
        }

        // For web requests, redirect back with error
        return redirect()
            ->back()
            ->withInput()
            ->withErrors(['bill' => $this->getReasonMessage()]);
    }
}
