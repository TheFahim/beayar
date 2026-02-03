<?php

namespace App\Http\Requests;

use App\Models\Bill;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRunningBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $quotation = $this->route('quotation');

        return [
            'bill_type' => ['required', Rule::in(['running'])],
            'quotation_id' => ['required', 'integer', Rule::in([$quotation?->id])],
            'parent_bill_id' => ['required', 'integer', 'exists:bills,id'],
            'invoice_no' => ['required', Rule::unique('bills', 'invoice_no')],
            'bill_date' => ['required', 'date_format:d/m/Y'],
            'payment_received_date' => ['nullable', 'date_format:d/m/Y'],
            'quotation_revision_id' => ['nullable', 'integer', 'exists:quotation_revisions,id'],
            'bill_amount' => ['required', 'numeric', 'min:0'],
            'bill_percentage' => ['required', 'numeric', 'min:1', 'max:100'],
            'due' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $parentId = $this->input('parent_bill_id');
            $amount = floatval($this->input('bill_amount'));
            $percentage = floatval($this->input('bill_percentage'));

            $quotation = $this->route('quotation');
            $parent = Bill::find($parentId);

            if (! $parent) {
                $validator->errors()->add('parent_bill_id', 'Selected parent bill not found.');

                return;
            }

            if ($parent->bill_type !== 'advance') {
                $validator->errors()->add('parent_bill_id', 'Parent bill must be an advance bill.');

                return;
            }

            if ($parent->quotation_id != $quotation?->id) {
                $validator->errors()->add('quotation_id', 'Running bill must have the same quotation as the parent bill.');

                return;
            }

            $remainingAmount = $parent->due;
            $maxPercentage = ($remainingAmount / $parent->total_amount) * 100;

            if ($amount > $remainingAmount) {
                $validator->errors()->add('bill_amount', 'Running amount exceeds remaining balance.');
            }

            if ($percentage > min(100, $maxPercentage)) {
                $validator->errors()->add('bill_percentage', 'Running percentage exceeds allowable remaining.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'bill_type.required' => 'Please select a bill type.',
            'bill_type.in' => 'Invalid bill type selected.',
            'invoice_no.required' => 'Please enter an invoice number.',
            'invoice_no.unique' => 'Invoice number must be unique.',
            'bill_date.required' => 'Please select a bill date.',
            'bill_date.date_format' => 'Please enter a valid date.',
            'payment_received_date.date_format' => 'Please enter a valid payment received date.',
            'quotation_revision_id.exists' => 'Selected quotation revision not found.',
            'bill_amount.required' => 'Please enter a running amount.',
            'bill_amount.numeric' => 'Running amount must be a valid number.',
            'bill_amount.min' => 'Running amount cannot be negative.',
            'bill_percentage.required' => 'Please enter a running percentage.',
            'bill_percentage.numeric' => 'Running percentage must be a valid number.',
            'bill_percentage.min' => 'Running percentage must be at least 1%.',
            'bill_percentage.max' => 'Running percentage cannot exceed 100%.',
            'due.numeric' => 'Due must be a valid number.',
            'due.min' => 'Due cannot be negative.',
            'parent_bill_id.required' => 'Please select an advance bill as parent.',
            'parent_bill_id.exists' => 'Selected parent bill not found.',
        ];
    }
}
