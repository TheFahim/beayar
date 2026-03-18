<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Bill;

class RecordPaymentRequest extends FormRequest
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
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
            ],
            'payment_method' => [
                'required',
                'in:cash,bank_transfer,check,credit_card,mfs,other',
            ],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $bill = $this->route('bill');
            $amount = $this->input('amount');

            if (!$bill) return;

            // Check bill is in correct status
            if (!in_array($bill->status, [Bill::STATUS_ISSUED, Bill::STATUS_PARTIALLY_PAID])) {
                $validator->errors()->add(
                    'amount',
                    'Payments can only be recorded for issued or partially paid bills.'
                );
                return;
            }

            // Check amount doesn't exceed remaining balance
            $remainingBalance = $bill->remaining_balance;
            if (bccomp($amount, $remainingBalance, 2) > 0) {
                $validator->errors()->add(
                    'amount',
                    "Payment amount cannot exceed remaining balance of {$remainingBalance}."
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
            'amount.required' => 'Please enter the payment amount.',
            'amount.min' => 'The payment amount must be at least 0.01.',
            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'Please select a valid payment method.',
            'payment_date.required' => 'Please enter the payment date.',
            'payment_date.before_or_equal' => 'Payment date cannot be in the future.',
        ];
    }
}
