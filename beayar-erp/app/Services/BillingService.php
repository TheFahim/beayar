<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\BillAdvanceAdjustment;
use App\Models\BillItem;
use App\Models\BillPayment;
use App\Models\ChallanProduct;
use App\Models\Quotation;
use App\Models\QuotationProduct;
use App\Models\QuotationRevision;
use App\Exceptions\BillLockedException;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class BillingService
{
    /**
     * Create a new bill with items and allocations
     *
     * @throws ValidationException
     */
    public function createBill(array $data): Bill
    {
        return DB::transaction(function () use ($data) {
            DB::table('quotations')
                ->where('id', $data['quotation_id'] ?? null)
                ->lockForUpdate()
                ->first();
            // Validate parent constraints for running bills
            $this->validateBillConstraints($data);

            $activeRevision = QuotationRevision::where('quotation_id', $data['quotation_id'])->where('is_active', '1')->first();

            // Create the bill
            $bill = Bill::create([
                'quotation_id' => $data['quotation_id'],
                'quotation_revision_id' => $data['quotation_revision_id'] ?? null,
                'parent_bill_id' => $data['parent_bill_id'] ?? null,
                'bill_type' => $data['bill_type'] ?? 'regular',
                'invoice_no' => $data['invoice_no'],
                'bill_date' => Carbon::createFromFormat('d/m/Y', $data['bill_date'])->format('Y-m-d'),
                'payment_received_date' => !empty($data['payment_received_date'])
                    ? Carbon::createFromFormat('d/m/Y', $data['payment_received_date'])->format('Y-m-d')
                    : null,
                'total_amount' => ($activeRevision->total ?? 0),
                'discount' => $data['discount'] ?? 0,
                'shipping' => $data['shipping'] ?? 0,
                'status' => $data['status'] ?? 'draft',
                'notes' => $data['notes'] ?? '',
            ]);

            // Create bill items per allocation and attach challans
            $totalAmount = 0;
            $challanIds = [];
            foreach ($data['items'] as $itemData) {
                $quotationProduct = QuotationProduct::findOrFail($itemData['quotation_product_id']);
                $unitPrice = $quotationProduct->unit_price ?? 0;

                foreach ($itemData['allocations'] ?? [] as $allocation) {
                    $cp = ChallanProduct::findOrFail($allocation['challan_product_id']);
                    $challanId = $cp->challan_id;
                    $challanIds[$challanId] = true;

                    // Ensure pivot exists
                    $bill->challans()->syncWithoutDetaching([$challanId]);
                    $pivot = $bill->challans()->where('challan_id', $challanId)->first();
                    $billChallanId = optional($pivot)->pivot->id ?? (int) DB::table('bill_challans')
                        ->where('bill_id', $bill->id)
                        ->where('challan_id', $challanId)
                        ->value('id');

                    $quantity = (int) ($allocation['billed_quantity'] ?? 0);
                    $billedToDate = DB::table('bill_items')
                        ->join('bill_challans', 'bill_items.bill_challan_id', '=', 'bill_challans.id')
                        ->join('bills', 'bill_challans.bill_id', '=', 'bills.id')
                        ->where('bill_items.challan_product_id', $cp->id)
                        ->where('bills.bill_type', 'regular')
                        ->where('bills.status', '!=', 'cancelled')
                        ->sum('bill_items.quantity');

                    $remainingAfter = max(0, (int) ($cp->quantity ?? 0) - ($billedToDate + $quantity));

                    $billItem = BillItem::create([
                        'bill_challan_id' => $billChallanId,
                        'quotation_product_id' => $quotationProduct->id,
                        'challan_product_id' => $cp->id,
                        'quantity' => $quantity,
                        'remaining_quantity' => $remainingAfter,
                        'unit_price' => $unitPrice,
                        'bill_price' => ($unitPrice * $quantity),
                    ]);

                    $totalAmount += $billItem->bill_price;
                }
            }

            // Attach challans via pivot (idempotent)
            if (!empty($challanIds)) {
                $bill->challans()->syncWithoutDetaching(array_keys($challanIds));
            }

            // Update bill totals for regular bills
            $update = [];
            if (($bill->bill_type ?? 'regular') === 'regular') {
                $vat = (float) ($data['vat'] ?? 0);
                $finalTotal = ($totalAmount - ($bill->discount ?? 0)) + ($bill->shipping ?? 0) + $vat;
                $update['bill_amount'] = $finalTotal + ($data['round_up'] ?? 0);

                // Calculate due based on sibling bills:
                // due_amount = total_amount - SUM(all sibling bill_amount) - current bill_amount
                // Siblings are other regular, non-cancelled bills for the same quotation.
                $siblingsSum = (float) DB::table('bills')
                    ->where('quotation_id', $bill->quotation_id)
                    ->where('bill_type', 'regular')
                    ->where('status', '!=', 'cancelled')
                    ->where('id', '!=', $bill->id)
                    ->sum(DB::raw('COALESCE(bill_amount, 0)'));

                $computedDue = ($bill->total_amount ?? 0) - ($siblingsSum + $finalTotal);
                $update['due'] = max(0, $computedDue);
                $update['bill_percentage'] = $this->calculateBillPercentage($finalTotal, $bill->total_amount ?? 0);
            }
            if (!empty($update)) {
                $bill->update($update);
            }

            return $bill;
        });
    }

    public function calculateBillPercentage($bill_amount, $total_amount): float
    {
        if (!is_numeric($bill_amount) || !is_numeric($total_amount)) {
            throw ValidationException::withMessages([
                'bill_percentage' => 'Inputs must be numeric',
            ]);
        }

        $bill = (float) $bill_amount;
        $total = (float) $total_amount;

        if ($total === 0.0) {
            throw ValidationException::withMessages([
                'total_amount' => 'Total amount cannot be zero',
            ]);
        }

        return round(($bill / $total) * 100, 2);
    }

    /**
     * Create an advance bill without items
     */
    public function createAdvance(array $data): Bill
    {
        return DB::transaction(function () use ($data) {
            if (($data['bill_type'] ?? null) !== 'advance') {
                throw ValidationException::withMessages([
                    'bill_type' => 'Invalid bill type for advance creation',
                ]);
            }

            DB::table('quotations')
                ->where('id', $data['quotation_id'] ?? null)
                ->lockForUpdate()
                ->first();

            $existingAdvance = Bill::where('quotation_id', $data['quotation_id'] ?? null)
                ->where('bill_type', 'advance')
                ->exists();
            if ($existingAdvance) {
                Log::warning('Advance bill creation blocked: advance already exists', [
                    'quotation_id' => $data['quotation_id'] ?? null,
                ]);
                throw ValidationException::withMessages([
                    'bill_type' => 'This quotation already has an advance bill. You can view/edit the existing advance bill.',
                ]);
            }

            $bill = Bill::create([
                'quotation_id' => $data['quotation_id'],
                'quotation_revision_id' => $data['quotation_revision_id'] ?? null,
                'bill_type' => 'advance',
                'invoice_no' => $data['invoice_no'],
                'bill_date' => $data['bill_date'],
                'payment_received_date' => $data['payment_received_date'] ?? null,
                'total_amount' => $data['total_amount'] ?? 0,
                'bill_percentage' => $data['bill_percentage'] ?? null,
                'bill_amount' => $data['bill_amount'] ?? 0,
                'due' => $data['due'] ?? 0,
                'discount' => $data['discount'] ?? 0,
                'shipping' => $data['shipping'] ?? 0,
                'notes' => $data['notes'] ?? '',
            ]);

            return $bill;
        });
    }

    public function createRunning(array $data): Bill
    {
        return DB::transaction(function () use ($data) {
            if (($data['bill_type'] ?? null) !== 'running') {
                throw ValidationException::withMessages([
                    'bill_type' => 'Invalid bill type for running creation',
                ]);
            }

            $parent = Bill::find($data['parent_bill_id'] ?? null);
            if (!$parent) {
                throw ValidationException::withMessages([
                    'parent_bill_id' => 'Parent bill not found',
                ]);
            }

            if ($parent->bill_type !== 'advance') {
                throw ValidationException::withMessages([
                    'parent_bill_id' => 'Parent bill must be an advance bill',
                ]);
            }

            if (!empty($data['quotation_id']) && $parent->quotation_id != $data['quotation_id']) {
                throw ValidationException::withMessages([
                    'quotation_id' => 'Running bill must have same quotation as parent bill',
                ]);
            }

            $bill = Bill::create([
                'quotation_id' => $data['quotation_id'],
                'quotation_revision_id' => $data['quotation_revision_id'] ?? null,
                'parent_bill_id' => $parent->id,
                'bill_type' => 'running',
                'invoice_no' => $data['invoice_no'],
                'bill_date' => Carbon::createFromFormat('d/m/Y', $data['bill_date'])->format('Y-m-d'),
                'payment_received_date' => !empty($data['payment_received_date'])
                    ? Carbon::createFromFormat('d/m/Y', $data['payment_received_date'])->format('Y-m-d')
                    : null,
                'bill_percentage' => $data['bill_percentage'],
                'bill_amount' => $data['bill_amount'],
                'total_amount' => $data['total_amount'],
                'due' => $data['due'] ?? 0,
                'notes' => $data['notes'] ?? '',
                'status' => $data['status'] ?? 'draft',
            ]);

            return $bill;
        });
    }

    public function updateRunning(Bill $bill, array $data): Bill
    {
        return DB::transaction(function () use ($bill, $data) {
            $parent = Bill::find($bill->parent_bill_id);
            if (!$parent) {
                throw ValidationException::withMessages([
                    'parent_bill_id' => 'Parent bill not found',
                ]);
            }

            if ($parent->bill_type !== 'advance') {
                throw ValidationException::withMessages([
                    'parent_bill_id' => 'Parent bill must be an advance bill',
                ]);
            }

            if (!empty($data['quotation_id']) && $parent->quotation_id != $data['quotation_id']) {
                throw ValidationException::withMessages([
                    'quotation_id' => 'Running bill must have same quotation as parent bill',
                ]);
            }

            $bill->update([
                'invoice_no' => $data['invoice_no'],
                'bill_date' => Carbon::createFromFormat('d/m/Y', $data['bill_date'])->format('Y-m-d'),
                'payment_received_date' => !empty($data['payment_received_date'])
                    ? Carbon::createFromFormat('d/m/Y', $data['payment_received_date'])->format('Y-m-d')
                    : null,
                'bill_percentage' => $data['bill_percentage'],
                'bill_amount' => $data['bill_amount'],
                'due' => $data['due'] ?? $bill->due,
                'notes' => $data['notes'] ?? $bill->notes,
            ]);

            return $bill;
        });
    }

    public function updateRegular(Bill $bill, array $data): Bill
    {
        return DB::transaction(function () use ($bill, $data) {
            $this->validateBillConstraints($data);

            $activeRevision = QuotationRevision::where('quotation_id', $bill->quotation_id)->where('is_active', '1')->first();

            $bill->update([
                'invoice_no' => $data['invoice_no'],
                'bill_date' => Carbon::createFromFormat('d/m/Y', $data['bill_date'])->format('Y-m-d'),
                'payment_received_date' => !empty($data['payment_received_date'])
                    ? Carbon::createFromFormat('d/m/Y', $data['payment_received_date'])->format('Y-m-d')
                    : null,
                'total_amount' => $activeRevision->total ?? $bill->total_amount,
                'discount' => $data['discount'] ?? 0,
                'shipping' => $data['shipping'] ?? 0,
                'notes' => $data['notes'] ?? $bill->notes,
            ]);

            $pivotIds = DB::table('bill_challans')->where('bill_id', $bill->id)->pluck('id')->all();
            if (!empty($pivotIds)) {
                DB::table('bill_items')->whereIn('bill_challan_id', $pivotIds)->delete();
            }

            $totalAmount = 0;
            $challanIds = [];
            foreach ($data['items'] as $itemData) {
                $quotationProduct = QuotationProduct::findOrFail($itemData['quotation_product_id']);
                $unitPrice = $quotationProduct->unit_price ?? 0;

                foreach ($itemData['allocations'] ?? [] as $allocation) {
                    $cp = ChallanProduct::findOrFail($allocation['challan_product_id']);
                    $challanId = $cp->challan_id;
                    $challanIds[$challanId] = true;

                    $bill->challans()->syncWithoutDetaching([$challanId]);
                    $pivot = $bill->challans()->where('challan_id', $challanId)->first();
                    $billChallanId = optional($pivot)->pivot->id ?? (int) DB::table('bill_challans')
                        ->where('bill_id', $bill->id)
                        ->where('challan_id', $challanId)
                        ->value('id');

                    $quantity = (int) ($allocation['billed_quantity'] ?? 0);
                    $billedToDate = DB::table('bill_items')
                        ->join('bill_challans', 'bill_items.bill_challan_id', '=', 'bill_challans.id')
                        ->join('bills', 'bill_challans.bill_id', '=', 'bills.id')
                        ->where('bill_items.challan_product_id', $cp->id)
                        ->where('bills.bill_type', 'regular')
                        ->where('bills.status', '!=', 'cancelled')
                        ->sum('bill_items.quantity');

                    $remainingAfter = max(0, (int) ($cp->quantity ?? 0) - ($billedToDate + $quantity));

                    $billItem = BillItem::create([
                        'bill_challan_id' => $billChallanId,
                        'quotation_product_id' => $quotationProduct->id,
                        'challan_product_id' => $cp->id,
                        'quantity' => $quantity,
                        'remaining_quantity' => $remainingAfter,
                        'unit_price' => $unitPrice,
                        'bill_price' => ($unitPrice * $quantity),
                    ]);

                    $totalAmount += $billItem->bill_price;
                }
            }

            if (!empty($challanIds)) {
                $bill->challans()->sync(array_keys($challanIds));
            } else {
                $bill->challans()->sync([]);
            }

            if (($bill->bill_type ?? 'regular') === 'regular') {
                $vat = (float) ($data['vat'] ?? 0);
                $finalTotal = ($totalAmount - ($bill->discount ?? 0)) + ($bill->shipping ?? 0) + $vat + ($bill->round_up ?? 0);
                $update['bill_amount'] = $finalTotal + ($data['round_up'] ?? 0);

                $siblingsSum = (float) DB::table('bills')
                    ->where('quotation_id', $bill->quotation_id)
                    ->where('bill_type', 'regular')
                    ->where('status', '!=', 'cancelled')
                    ->where('id', '!=', $bill->id)
                    ->sum(DB::raw('COALESCE(bill_amount, 0)'));

                $computedDue = ($bill->total_amount ?? 0) - ($siblingsSum + $finalTotal);
                $update['due'] = max(0, $computedDue);
                $update['bill_percentage'] = $this->calculateBillPercentage($finalTotal, $bill->total_amount ?? 0);
            }
            $bill->update($update);

            return $bill;
        });
    }

    /**
     * Validate bill constraints
     *
     * @throws ValidationException
     */
    private function validateBillConstraints(array $data): void
    {
        // Validate bill_type
        if (!in_array($data['bill_type'], ['advance', 'regular', 'running'])) {
            throw ValidationException::withMessages([
                'bill_type' => 'Invalid bill type. Must be one of: advance, regular, running',
            ]);
        }

        // Validate running bill constraints
        if ($data['bill_type'] === 'running') {
            if (empty($data['parent_bill_id'])) {
                throw ValidationException::withMessages([
                    'parent_bill_id' => 'Parent bill is required for running bills',
                ]);
            }

            $parentBill = Bill::find($data['parent_bill_id']);
            if (!$parentBill) {
                throw ValidationException::withMessages([
                    'parent_bill_id' => 'Parent bill not found',
                ]);
            }

            if ($parentBill->bill_type !== 'advance') {
                throw ValidationException::withMessages([
                    'parent_bill_id' => 'Parent bill must be an advance bill',
                ]);
            }

            if ($parentBill->quotation_id != $data['quotation_id']) {
                throw ValidationException::withMessages([
                    'quotation_id' => 'Running bill must have same quotation as parent bill',
                ]);
            }

            $childrenSum = $parentBill->children()->where('bill_type', 'running')->sum('total_amount');
            $remainingAmount = max(0, ($parentBill->total_amount ?? 0) - $childrenSum);
            if (($data['bill_amount'] ?? 0) > $remainingAmount) {
                throw ValidationException::withMessages([
                    'bill_amount' => 'Running amount exceeds remaining balance',
                ]);
            }
            $maxPct = ($parentBill->total_amount ?? 0) > 0 ? ($remainingAmount / $parentBill->total_amount) * 100 : 0;
            if (($data['bill_percentage'] ?? 0) > $maxPct) {
                throw ValidationException::withMessages([
                    'bill_percentage' => 'Running percentage exceeds allowable remaining',
                ]);
            }
        }

        // Validate regular bill constraints
        if (($data['bill_type'] ?? null) === 'regular') {
            $quotationId = $data['quotation_id'] ?? null;
            if ($quotationId) {
                $hasAdvance = Bill::where('quotation_id', $quotationId)
                    ->where('bill_type', 'advance')
                    ->exists();
                if ($hasAdvance) {
                    Log::warning('Regular bill creation blocked: advance exists', [
                        'quotation_id' => $quotationId,
                    ]);
                    throw ValidationException::withMessages([
                        'bill_type' => 'This quotation has already been used to create an advance bill and cannot generate regular bills',
                    ]);
                }
            }
            if (empty($data['items']) || !is_array($data['items'])) {
                throw ValidationException::withMessages([
                    'items' => 'Items are required for regular bills',
                ]);
            }

            foreach ($data['items'] as $i => $item) {
                if (empty($item['allocations']) || !is_array($item['allocations'])) {
                    throw ValidationException::withMessages([
                        "items.$i.allocations" => 'Allocations are required for each item',
                    ]);
                }
                $sumAlloc = 0.0;
                foreach ($item['allocations'] as $a => $allocation) {
                    $challanProductId = $allocation['challan_product_id'] ?? null;
                    $billedQty = (float) ($allocation['billed_quantity'] ?? 0);
                    if (!$challanProductId || $billedQty <= 0) {
                        throw ValidationException::withMessages([
                            "items.$i.allocations.$a.billed_quantity" => 'Billed quantity must be greater than zero',
                        ]);
                    }

                    $cp = ChallanProduct::find($challanProductId);
                    if (!$cp) {
                        throw ValidationException::withMessages([
                            "items.$i.allocations.$a.challan_product_id" => 'Challan product not found',
                        ]);
                    }

                    // Alignment check: allocation must match item quotation product
                    if ((int) $cp->quotation_product_id !== (int) $item['quotation_product_id']) {
                        throw ValidationException::withMessages([
                            "items.$i.allocations.$a.challan_product_id" => 'Allocation product does not match item quotation product',
                        ]);
                    }

                    $billedToDate = DB::table('bill_items')
                        ->join('bill_challans', 'bill_items.bill_challan_id', '=', 'bill_challans.id')
                        ->join('bills', 'bill_challans.bill_id', '=', 'bills.id')
                        ->where('bill_items.challan_product_id', $challanProductId)
                        ->where('bills.bill_type', 'regular')
                        ->where('bills.status', '!=', 'cancelled')
                        ->sum('bill_items.quantity');

                    // $remaining = max(0, ($cp->quantity ?? 0) - $billedToDate);
                    // if ($billedQty > $remaining) {
                    //     throw ValidationException::withMessages([
                    //         "items.$i.allocations.$a.billed_quantity" => "Billed quantity exceeds remaining (remaining: $remaining)",
                    //     ]);
                    // }
                    $sumAlloc += $billedQty;
                }
                // Reconciliation: item quantity equals sum of allocation quantities
                if (abs($sumAlloc - (float) ($item['quantity'] ?? 0)) > 1e-6) {
                    throw ValidationException::withMessages([
                        "items.$i.quantity" => 'Item quantity must equal sum of allocation quantities',
                    ]);
                }
            }
        }

        // Validate advance bill constraints
        if ($data['bill_type'] === 'advance') {
            if (empty($data['quotation_revision_id'])) {
                throw ValidationException::withMessages([
                    'quotation_revision_id' => 'Quotation revision is required for advance bills',
                ]);
            }

            // Check if there are challans for this revision (simplified check)
            $hasChallans = ChallanProduct::whereHas('challan', function ($q) use ($data) {
                $q->where('quotation_revision_id', $data['quotation_revision_id']);
            })->exists();

            if ($hasChallans) {
                throw ValidationException::withMessages([
                    'bill_type' => 'Cannot create advance bill - challans already exist for this quotation',
                ]);
            }
        }
    }

    // ==========================================
    // PHASE 2: ADDITIONAL METHODS
    // ==========================================

    /**
     * Apply advance credit to a final bill.
     *
     * @param Bill $advanceBill The advance bill providing credit
     * @param Bill $finalBill The bill receiving credit (must be regular type)
     * @param string $amount Amount to apply (as string for precision)
     * @return BillAdvanceAdjustment
     * @throws \Exception
     */
    public function applyAdvanceCredit(Bill $advanceBill, Bill $finalBill, string $amount): BillAdvanceAdjustment
    {
        // Validation
        if ($advanceBill->bill_type !== Bill::TYPE_ADVANCE) {
            throw new \InvalidArgumentException('Source bill must be an advance bill.');
        }

        if ($finalBill->bill_type !== Bill::TYPE_REGULAR) {
            throw new \InvalidArgumentException('Target bill must be a regular bill.');
        }

        if ($advanceBill->quotation_id !== $finalBill->quotation_id) {
            throw new \InvalidArgumentException('Both bills must belong to the same quotation.');
        }

        // Check available balance
        $availableBalance = $this->getUnappliedAdvanceBalance($advanceBill);
        if (bccomp($amount, $availableBalance, 2) > 0) {
            throw new \InvalidArgumentException(
                "Cannot apply {$amount}. Available balance is {$availableBalance}."
            );
        }

        // Check if final bill can accept credit
        if (!$finalBill->canBeEdited()) {
            throw new BillLockedException($finalBill, $finalBill->getLockReason());
        }

        return DB::transaction(function () use ($advanceBill, $finalBill, $amount) {
            // Create the adjustment record
            $adjustment = BillAdvanceAdjustment::create([
                'advance_bill_id' => $advanceBill->id,
                'final_bill_id' => $finalBill->id,
                'tenant_company_id' => currentTenantId(),
                'amount' => $amount,
                'created_by' => auth()->id(),
                'notes' => null,
            ]);

            // Update the final bill's applied amount and net payable
            $currentApplied = $finalBill->advance_applied_amount ?? '0';
            $newApplied = bcadd($currentApplied, $amount, 2);
            $newNetPayable = bcsub($finalBill->total_amount ?? 0, $newApplied, 2);

            $finalBill->update([
                'advance_applied_amount' => $newApplied,
                'net_payable_amount' => max($newNetPayable, '0.00'),
            ]);

            Log::info('Advance credit applied', [
                'advance_bill_id' => $advanceBill->id,
                'final_bill_id' => $finalBill->id,
                'amount' => $amount,
            ]);

            return $adjustment;
        });
    }

    /**
     * Remove advance credit from a final bill.
     * Used during cancellation or correction workflows.
     *
     * @param BillAdvanceAdjustment $adjustment
     * @return void
     * @throws \Exception
     */
    public function removeAdvanceCredit(BillAdvanceAdjustment $adjustment): void
    {
        $finalBill = $adjustment->finalBill;
        $advanceBill = $adjustment->advanceBill;
        $amount = $adjustment->amount;

        DB::transaction(function () use ($adjustment, $finalBill, $advanceBill, $amount) {
            // Soft delete the adjustment
            $adjustment->delete();

            // Update the final bill
            $currentApplied = $finalBill->advance_applied_amount ?? '0';
            $newApplied = bcsub($currentApplied, $amount, 2);
            $newNetPayable = bcadd($finalBill->total_amount ?? 0, $amount, 2);

            $finalBill->update([
                'advance_applied_amount' => max($newApplied, '0.00'),
                'net_payable_amount' => $newNetPayable,
            ]);

            Log::info('Advance credit removed', [
                'advance_bill_id' => $advanceBill->id,
                'final_bill_id' => $finalBill->id,
                'amount' => $amount,
            ]);
        });
    }

    /**
     * Issue a bill (change status from draft to issued).
     *
     * @param Bill $bill
     * @return Bill
     * @throws BillLockedException|\Exception
     */
    public function issueBill(Bill $bill): Bill
    {
        if ($bill->status !== Bill::STATUS_DRAFT) {
            throw new \InvalidArgumentException('Only draft bills can be issued.');
        }

        return DB::transaction(function () use ($bill) {
            $oldStatus = $bill->status;

            $bill->update(['status' => Bill::STATUS_ISSUED]);

            // Lock the bill after issuing
            $bill->lock(Bill::LOCK_REASON_STATUS);

            Log::info('Bill issued', [
                'bill_id' => $bill->id,
                'old_status' => $oldStatus,
                'new_status' => Bill::STATUS_ISSUED,
            ]);

            return $bill->fresh();
        });
    }

    /**
     * Cancel a bill.
     *
     * @param Bill $bill
     * @param string|null $reason
     * @return Bill
     * @throws \Exception
     */
    public function cancelBill(Bill $bill, ?string $reason = null): Bill
    {
        if ($bill->status === Bill::STATUS_CANCELLED) {
            throw new \InvalidArgumentException('Bill is already cancelled.');
        }

        return DB::transaction(function () use ($bill, $reason) {
            $oldStatus = $bill->status;

            // Remove any advance adjustments if this is a regular bill
            if ($bill->bill_type === Bill::TYPE_REGULAR) {
                foreach ($bill->advanceAdjustmentsReceived as $adjustment) {
                    $this->removeAdvanceCredit($adjustment);
                }
            }

            $bill->update([
                'status' => Bill::STATUS_CANCELLED,
                'notes' => $reason ? ($bill->notes . "\nCancellation reason: " . $reason) : $bill->notes,
            ]);

            Log::info('Bill cancelled', [
                'bill_id' => $bill->id,
                'old_status' => $oldStatus,
                'reason' => $reason,
            ]);

            return $bill->fresh();
        });
    }

    /**
     * Record a payment for a bill.
     *
     * @param Bill $bill
     * @param array $data Payment data
     * @return BillPayment
     * @throws \Exception
     */
    public function recordPayment(Bill $bill, array $data): BillPayment
    {
        if (!in_array($bill->status, [Bill::STATUS_ISSUED, Bill::STATUS_PARTIALLY_PAID])) {
            throw new \InvalidArgumentException('Can only record payments for issued or partially paid bills.');
        }

        return DB::transaction(function () use ($bill, $data) {
            $payment = BillPayment::create([
                'bill_id' => $bill->id,
                'tenant_company_id' => currentTenantId(),
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'payment_date' => $data['payment_date'] ?? now(),
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            // Update bill status
            $this->updateBillPaymentStatus($bill);

            Log::info('Payment recorded', [
                'bill_id' => $bill->id,
                'payment_id' => $payment->id,
                'amount' => $data['amount'],
            ]);

            return $payment;
        });
    }

    /**
     * Update bill status based on payments.
     *
     * @param Bill $bill
     * @return void
     */
    protected function updateBillPaymentStatus(Bill $bill): void
    {
        $paidAmount = $bill->paid_amount;
        $netPayable = $bill->net_payable_amount ?? $bill->total_amount ?? 0;

        if (bccomp($paidAmount, '0.00', 2) <= 0) {
            // No payments
            $newStatus = Bill::STATUS_ISSUED;
        } elseif (bccomp($paidAmount, $netPayable, 2) >= 0) {
            // Fully paid
            $newStatus = Bill::STATUS_PAID;
        } else {
            // Partially paid
            $newStatus = Bill::STATUS_PARTIALLY_PAID;
        }

        if ($bill->status !== $newStatus) {
            $oldStatus = $bill->status;
            $bill->update(['status' => $newStatus]);

            Log::info('Bill status updated', [
                'bill_id' => $bill->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);
        }
    }

    /**
     * Get the unapplied advance balance for an advance bill.
     *
     * @param Bill $advanceBill
     * @return string
     */
    public function getUnappliedAdvanceBalance(Bill $advanceBill): string
    {
        if ($advanceBill->bill_type !== Bill::TYPE_ADVANCE) {
            return '0.00';
        }

        return $advanceBill->unapplied_amount;
    }

    /**
     * Get billable challans for a quotation.
     * Returns challans that have not been fully billed yet.
     *
     * @param Quotation $quotation
     * @return Collection
     */
    public function getBillableChallans(Quotation $quotation): Collection
    {
        // Get all challans for this quotation
        $challans = $quotation->challans()
            ->with(['challanProducts.quotationProduct'])
            ->get();

        // Filter out fully billed challans
        return $challans->filter(function ($challan) {
            foreach ($challan->challanProducts as $cp) {
                $billedQuantity = DB::table('bill_items')
                    ->where('challan_product_id', $cp->id)
                    ->sum('quantity');

                if (bccomp($cp->quantity, $billedQuantity, 2) > 0) {
                    return true; // Has unbilled quantity
                }
            }
            return false;
        });
    }
}
