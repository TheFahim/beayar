<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Quotation;

class InvoiceNumberGenerator
{
    /**
     * Generate the next invoice number for a given quotation.
     *
     * Logic:
     * - Base: quotation_no
     * - Suffix: A, B, C... Z, ZA, ZB...
     *
     * @param Quotation $quotation
     * @return string
     */
    public function generate(Quotation $quotation): string
    {
        $quotationNo = $quotation->quotation_no;

        // Fetch all existing invoice numbers for this quotation to avoid DB calls in loop
        // We only care about those starting with the quotation number
        $existingInvoices = Bill::where('quotation_id', $quotation->id)
            ->pluck('invoice_no')
            ->filter(function ($invoiceNo) use ($quotationNo) {
                return str_starts_with($invoiceNo, $quotationNo);
            })
            ->map(function ($invoiceNo) use ($quotationNo) {
                return substr($invoiceNo, strlen($quotationNo));
            })
            ->flip()
            ->toArray();

        $index = 1;
        while (true) {
            $suffix = $this->getSuffix($index);
            // If existingInvoices is empty (no bills), suffix "" is checked.
            // If quotation_no is "Q-100", checks "Q-100".
            // If "Q-100" exists, checks "Q-100A".
            if (!isset($existingInvoices[$suffix])) {
                return $quotationNo . $suffix;
            }
            $index++;
        }
    }

    /**
     * Generate suffix based on index.
     * 0 -> ""
     * 1 -> "A"
     * ...
     * 26 -> "Z"
     * 27 -> "ZA"
     * ...
     *
     * @param int $n
     * @return string
     */
    private function getSuffix(int $n): string
    {
        if ($n === 0) {
            return '';
        }

        if ($n <= 26) {
            return chr(65 + $n - 1); // A is 65
        }

        // For n > 26:
        // 27 -> ZA.
        // We want 'Z' + suffix(n - 26).
        return 'Z' . $this->getSuffix($n - 26);
    }
}
