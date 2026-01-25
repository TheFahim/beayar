<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdvanceBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $bill = $this->route('bill');

        return [
            'bill_type' => ['required', Rule::in(['advance'])],
            'quotation_id' => ['required', 'integer', Rule::in([$bill?->quotation_id])],
            'quotation_revision_id' => ['required', 'integer'],
            'invoice_no' => ['required', Rule::unique('bills', 'invoice_no')->ignore($bill?->id)],
            'bill_date' => ['required', 'date_format:d/m/Y'],
            'payment_received_date' => ['nullable', 'date_format:d/m/Y'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'po_no' => ['nullable', 'string', 'max:255'],
            'bill_percentage' => ['nullable', 'numeric', 'min:1', 'max:100'],
            'bill_amount' => ['required', 'numeric', 'min:0'],
            'due' => ['required', 'numeric', 'min:0'],
        ];
    }
}
