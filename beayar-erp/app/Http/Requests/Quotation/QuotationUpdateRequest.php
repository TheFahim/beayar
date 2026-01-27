<?php

namespace App\Http\Requests\Quotation;

use Illuminate\Foundation\Http\FormRequest;

class QuotationUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'po_no' => ['nullable', 'string', 'max:50'],
            'ship_to' => ['nullable', 'string'],
            'status' => ['required', 'exists:quotation_statuses,id'],
        ];
    }
}
