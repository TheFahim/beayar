<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBillRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'quotation_id' => 'required|exists:quotations,id',
            'bill_type' => 'required|in:advance,regular,running',
            'parent_bill_id' => 'nullable|exists:bills,id',
            'quotation_revision_id' => 'nullable|exists:quotation_revisions,id',
            'po_no' => 'required|string|max:255',
            'invoice_no' => 'required|string|max:255|unique:bills,invoice_no',
            'bill_date' => 'required|date',
            'payment_received_date' => 'nullable|date',
            'discount' => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:draft,issued,paid,cancelled',
            'notes' => 'nullable|string',

            // Items validation
            'items' => 'required|array|min:1',
            'items.*.quotation_product_id' => 'required|exists:quotation_products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.allocations' => 'nullable|array',
            'items.*.allocations.*.challan_product_id' => 'required_with:items.*.allocations|exists:challan_products,id',
            'items.*.allocations.*.billed_quantity' => 'required_with:items.*.allocations|integer|min:1',

            // Running bill specific
            'installment_amount' => 'required_if:bill_type,running|numeric|min:0',
            'installment_percentage' => 'required_if:bill_type,running|numeric|min:0|max:100',

            // Regular bill specific
            'challan_ids' => 'required_if:bill_type,regular|array',
            'challan_ids.*' => 'exists:challans,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'bill_type.in' => 'Bill type must be one of: advance, regular, running',
            'items.required' => 'At least one bill item is required',
            'items.*.quotation_product_id.exists' => 'Invalid quotation product selected',
            'items.*.quantity.min' => 'Quantity must be at least 1',
            'installment_amount.required_if' => 'Installment amount is required for running bills',
            'installment_percentage.required_if' => 'Installment percentage is required for running bills',
            'challan_ids.required_if' => 'At least one challan must be selected for regular bills',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->validated();

            // Validate running bill constraints
            if ($data['bill_type'] === 'running') {
                if (empty($data['parent_bill_id'])) {
                    $validator->errors()->add('parent_bill_id', 'Parent bill is required for running bills');

                    return;
                }

                $parentBill = \App\Models\Bill::find($data['parent_bill_id']);
                if (! $parentBill) {
                    $validator->errors()->add('parent_bill_id', 'Parent bill not found');

                    return;
                }

                if ($parentBill->bill_type !== 'regular') {
                    $validator->errors()->add('parent_bill_id', 'Parent bill must be a regular bill');

                    return;
                }

                if ($parentBill->quotation_id != $data['quotation_id']) {
                    $validator->errors()->add('quotation_id', 'Running bill must have same quotation as parent bill');
                }
            }
        });
    }
}
