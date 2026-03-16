<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Bill;
use App\Services\BillingService;

class ApplyAdvanceCreditRequest extends FormRequest
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
            'advance_bill_id' => [
                'required',
                'exists:bills,id,tenant_company_id,' . currentTenantId(),
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
            ],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $advanceBillId = $this->input('advance_bill_id');
            $amount = $this->input('amount');
            $finalBill = $this->route('bill');

            $advanceBill = Bill::find($advanceBillId);

            if (!$advanceBill || !$finalBill) {
                return;
            }

            // Must be advance type
            if ($advanceBill->bill_type !== Bill::TYPE_ADVANCE) {
                $validator->errors()->add(
                    'advance_bill_id',
                    'The selected bill is not an advance bill.'
                );
                return;
            }

            // Final bill must be regular type
            if ($finalBill->bill_type !== Bill::TYPE_REGULAR) {
                $validator->errors()->add(
                    'advance_bill_id',
                    'Credit can only be applied to regular bills.'
                );
                return;
            }

            // Must belong to same quotation
            if ($advanceBill->quotation_id !== $finalBill->quotation_id) {
                $validator->errors()->add(
                    'advance_bill_id',
                    'The advance bill must belong to the same quotation as this bill.'
                );
                return;
            }

            // Check available balance
            $billingService = app(BillingService::class);
            $availableBalance = $billingService->getUnappliedAdvanceBalance($advanceBill);

            if (bccomp($amount, $availableBalance, 2) > 0) {
                $validator->errors()->add(
                    'amount',
                    "Cannot apply {$amount}. Available balance is {$availableBalance}."
                );
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'advance_bill_id.required' => 'Please select an advance bill.',
            'advance_bill_id.exists' => 'The selected advance bill is invalid.',
            'amount.required' => 'Please enter an amount to apply.',
            'amount.min' => 'The amount must be at least 0.01.',
        ];
    }
}
