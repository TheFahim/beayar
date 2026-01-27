<?php

namespace App\Http\Requests\Quotation;

use Illuminate\Foundation\Http\FormRequest;

class QuotationCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->canPerformAction('quotations');
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'po_no' => ['nullable', 'string', 'max:50'],
            'ship_to' => ['nullable', 'string'],
            'currency' => ['required', 'string', 'size:3'],
            'products' => ['required', 'array', 'min:1'],
            'products.*.product_id' => ['required', 'exists:products,id'],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
            'products.*.unit_price' => ['required', 'numeric', 'min:0'],
            'products.*.tax' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
