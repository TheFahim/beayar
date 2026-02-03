<?php

namespace App\Http\Requests;

use App\Models\Bill;
use App\Models\Quotation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdvanceBillRequest extends FormRequest
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
        $quotation = $this->route('quotation');
        $billType = $this->input('bill_type');

        $base = [
            'bill_type' => ['required', Rule::in(['advance', 'running'])],
            'invoice_no' => ['required', Rule::unique('bills', 'invoice_no')],
            'bill_date' => ['required', 'date_format:d/m/Y'],
            'payment_received_date' => ['nullable', 'date_format:d/m/Y'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        if ($billType === 'running') {
            return array_merge($base, [
                'quotation_id' => ['required', 'integer', Rule::in([$quotation?->id])],
                'parent_bill_id' => ['required', 'integer', 'exists:bills,id'],
                'installment_amount' => ['required', 'numeric', 'min:0'],
                'installment_percentage' => ['required', 'numeric', 'min:1', 'max:100'],
            ]);
        }

        return array_merge($base, [
            'quotation_id' => ['required', 'integer', Rule::in([$quotation?->id])],
            'quotation_revision_id' => ['required', 'integer'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'po_no' => ['required', 'string', 'max:255'],
            'bill_percentage' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'bill_amount' => ['required', 'numeric', 'min:0'],
            'due' => ['required', 'numeric', 'min:0'],
        ]);
    }

    /**
     * Validation rules for advance bills.
     */
    protected function advanceBillRules(): array
    {
        return [
            'bill_percentage' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'due' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Validation rules for regular bills.
     */
    protected function regularBillRules(): array
    {
        return [];
    }

    /**
     * Validation rules for running bills.
     */
    protected function runningBillRules(): array
    {
        return [];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $billType = $this->input('bill_type');

            if ($billType === 'advance') {
                $this->validateAdvanceBill($validator);
            }

            if ($billType === 'running') {
                $this->validateRunningBill($validator);
            }

            // Prevent bill creation for draft quotations (active revision must be saved as 'quotation')
        });
    }

    protected function validateAdvanceBill($validator)
    {
        // Add specific validation for advance bills if needed
    }

    protected function validateRunningBill($validator)
    {
        // Add specific validation for running bills if needed
    }
}
