<?php

namespace App\Http\Requests;

use App\Models\ChallanProduct;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StoreRegularBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bill_type' => ['required', Rule::in(['regular'])],
            'quotation_id' => ['required', 'integer', 'exists:quotations,id'],
            'quotation_revision_id' => ['required', 'integer', 'exists:quotation_revisions,id'],
            'invoice_no' => ['required', 'string', Rule::unique('bills', 'invoice_no')],
            'bill_date' => ['required', 'date_format:d/m/Y'],
            'payment_received_date' => ['nullable', 'date_format:d/m/Y'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'vat' => ['nullable', 'numeric', 'min:0'],
            'shipping' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'round_up' => ['nullable', 'numeric', 'min:-100', 'max:100'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.quotation_product_id' => ['required', 'integer', 'exists:quotation_products,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.allocations' => ['required', 'array', 'min:1'],
            'items.*.allocations.*.challan_product_id' => ['required', 'integer', 'exists:challan_products,id'],
            'items.*.allocations.*.billed_quantity' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'bill_type.in' => 'Invalid bill type for regular bill.',
            'invoice_no.unique' => 'Invoice number must be unique.',
            'bill_date.date_format' => 'Bill date must be in dd/mm/yyyy format.',
            'payment_received_date.date_format' => 'Payment received date must be in dd/mm/yyyy format.',
            'items.required' => 'Please select at least one challan.',
            'items.*.allocations.required' => 'Each item must have at least one allocation.',
            'items.*.quotation_product_id.exists' => 'Invalid quotation product selected.',
            'items.*.quantity.min' => 'Item quantity must be at least 0.01.',
            'items.*.allocations.*.challan_product_id.exists' => 'Challan product not found.',
            'items.*.allocations.*.billed_quantity.min' => 'Billed quantity must be at least 0.01.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->validated();

            // Prevent bill creation for draft quotations (active revision must be saved as 'quotation')
            if (! empty($data['quotation_id'])) {
                $quotation = \App\Models\Quotation::with(['revisions' => function ($q) {
                    $q->where('is_active', 1)->select('id', 'quotation_id', 'saved_as');
                }])->find($data['quotation_id']);
                $activeRevision = optional($quotation)->revisions->first();
                if (! $activeRevision || ($activeRevision->saved_as ?? null) !== 'quotation') {
                    $validator->errors()->add('quotation_id', 'Bills cannot be created from draft quotations. Activate the quotation revision to proceed.');
                }
            }

            if (($data['bill_type'] ?? null) !== 'regular') {
                return;
            }

            if (! empty($data['quotation_id'])) {
                $quotation = \App\Models\Quotation::with('bills')->find($data['quotation_id']);
                $hasAdvance = optional($quotation)->bills()->where('bill_type', 'advance')->exists();
                if ($hasAdvance) {
                    $validator->errors()->add('bill_type', 'This quotation has already been used to create an advance bill and cannot generate regular bills');

                    return;
                }
            }

            if (empty($data['items']) || ! is_array($data['items'])) {
                return;
            }

            foreach ($data['items'] as $i => $item) {
                if (empty($item['allocations']) || ! is_array($item['allocations'])) {
                    $validator->errors()->add("items.$i.allocations", 'Allocations are required for each item');

                    continue;
                }

                $sumAlloc = 0.0;
                foreach ($item['allocations'] as $a => $allocation) {
                    $challanProductId = $allocation['challan_product_id'] ?? null;
                    $billedQty = (float) ($allocation['billed_quantity'] ?? 0);
                    // Additional validation logic here
                }
            }
        });
    }
}
