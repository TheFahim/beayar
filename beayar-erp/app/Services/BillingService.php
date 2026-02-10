<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\BillItem;
use App\Models\ChallanProduct;
use App\Models\Quotation;
use App\Models\QuotationProduct;
use App\Models\QuotationRevision;
use Carbon\Carbon;
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
                'payment_received_date' => ! empty($data['payment_received_date'])
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
            if (! empty($challanIds)) {
                $bill->challans()->syncWithoutDetaching(array_keys($challanIds));
            }

            return $bill;
        });
    }

    public function calculateBillPercentage($bill_amount, $total_amount): float
    {
        if (! is_numeric($bill_amount) || ! is_numeric($total_amount)) {
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
            if (! $parent) {
                throw ValidationException::withMessages([
                    'parent_bill_id' => 'Parent bill not found',
                ]);
            }

            if ($parent->bill_type !== 'advance') {
                throw ValidationException::withMessages([
                    'parent_bill_id' => 'Parent bill must be an advance bill',
                ]);
            }

            if (! empty($data['quotation_id']) && $parent->quotation_id != $data['quotation_id']) {
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
                'payment_received_date' => ! empty($data['payment_received_date'])
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
            if (! $parent) {
                throw ValidationException::withMessages([
                    'parent_bill_id' => 'Parent bill not found',
                ]);
            }

            if ($parent->bill_type !== 'advance') {
                throw ValidationException::withMessages([
                    'parent_bill_id' => 'Parent bill must be an advance bill',
                ]);
            }

            if (! empty($data['quotation_id']) && $parent->quotation_id != $data['quotation_id']) {
                throw ValidationException::withMessages([
                    'quotation_id' => 'Running bill must have same quotation as parent bill',
                ]);
            }

            $bill->update([
                'invoice_no' => $data['invoice_no'],
                'bill_date' => Carbon::createFromFormat('d/m/Y', $data['bill_date'])->format('Y-m-d'),
                'payment_received_date' => ! empty($data['payment_received_date'])
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
                'payment_received_date' => ! empty($data['payment_received_date'])
                    ? Carbon::createFromFormat('d/m/Y', $data['payment_received_date'])->format('Y-m-d')
                    : null,
                'total_amount' => $activeRevision->total ?? $bill->total_amount,
                'discount' => $data['discount'] ?? 0,
                'shipping' => $data['shipping'] ?? 0,
                'notes' => $data['notes'] ?? $bill->notes,
            ]);

            $pivotIds = DB::table('bill_challans')->where('bill_id', $bill->id)->pluck('id')->all();
            if (! empty($pivotIds)) {
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

            if (! empty($challanIds)) {
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
        if (! in_array($data['bill_type'], ['advance', 'regular', 'running'])) {
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
            if (! $parentBill) {
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
            if (empty($data['items']) || ! is_array($data['items'])) {
                throw ValidationException::withMessages([
                    'items' => 'Items are required for regular bills',
                ]);
            }

            foreach ($data['items'] as $i => $item) {
                if (empty($item['allocations']) || ! is_array($item['allocations'])) {
                    throw ValidationException::withMessages([
                        "items.$i.allocations" => 'Allocations are required for each item',
                    ]);
                }
                $sumAlloc = 0.0;
                foreach ($item['allocations'] as $a => $allocation) {
                    $challanProductId = $allocation['challan_product_id'] ?? null;
                    $billedQty = (float) ($allocation['billed_quantity'] ?? 0);
                    if (! $challanProductId || $billedQty <= 0) {
                        throw ValidationException::withMessages([
                            "items.$i.allocations.$a.billed_quantity" => 'Billed quantity must be greater than zero',
                        ]);
                    }

                    $cp = ChallanProduct::find($challanProductId);
                    if (! $cp) {
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
}
