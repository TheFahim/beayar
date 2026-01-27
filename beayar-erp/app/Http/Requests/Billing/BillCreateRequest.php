<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class BillCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bill_type' => ['required', 'in:advance,regular,running'],
            'challan_ids' => ['required', 'array'],
            'challan_ids.*' => ['exists:challans,id'],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }
}
