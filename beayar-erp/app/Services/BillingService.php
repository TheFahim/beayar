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

    /**
     * Create an advance bill
     *
     * @param array $data
     * @return Bill
     */
    public function createAdvance(array $data): Bill
    {
        return DB::transaction(function () use ($data) {
            return Bill::create([
                'quotation_id' => $data['quotation_id'],
                'quotation_revision_id' => $data['quotation_revision_id'] ?? null,
                'bill_type' => 'advance',
                'invoice_no' => $data['invoice_no'],
                'bill_date' => $data['bill_date'],
                'payment_received_date' => $data['payment_received_date'] ?? null,
                'total_amount' => $data['total_amount'] ?? 0,
                'bill_amount' => $data['bill_amount'] ?? 0,
                'due' => $data['due'] ?? 0,
                'bill_percentage' => $data['bill_percentage'] ?? 0,
                'status' => 'draft',
                'notes' => $data['notes'] ?? '',
            ]);
        });
    }

    /**
     * Create a running bill (installment)
     *
     * @param array $data
     * @return Bill
     */
    public function createRunning(array $data): Bill
    {
        return DB::transaction(function () use ($data) {
            return Bill::create([
                'quotation_id' => $data['quotation_id'],
                'quotation_revision_id' => $data['quotation_revision_id'] ?? null,
                'parent_bill_id' => $data['parent_bill_id'],
                'bill_type' => 'running',
                'invoice_no' => $data['invoice_no'],
                'bill_date' => Carbon::createFromFormat('d/m/Y', $data['bill_date'])->format('Y-m-d'),
                'payment_received_date' => ! empty($data['payment_received_date'])
                    ? Carbon::createFromFormat('d/m/Y', $data['payment_received_date'])->format('Y-m-d')
                    : null,
                'total_amount' => $data['total_amount'] ?? 0,
                'bill_amount' => $data['bill_amount'] ?? 0,
                'due' => $data['due'] ?? 0,
                'status' => 'draft',
                'notes' => $data['notes'] ?? '',
            ]);
        });
    }

    private function validateBillConstraints(array $data)
    {
        // Add validation logic if needed
    }
}
