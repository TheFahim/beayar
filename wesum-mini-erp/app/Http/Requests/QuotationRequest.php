<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuotationRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {


        $quotationId = $this->route('quotation') ?? null;


        $customer = $this->route('qoutation') ? $this->route('qoutation')->customer : null;
        $customerIdToIgnore = $customer ? $customer->id : null;


        // --- Base rules that apply in all cases ---
        $rules = [
            'quotation' => ['required', 'array'],

            'quotation.quotation_no' => [
                'required',
                'string',
                'max:255',
                // If we are updating, ignore the current quotation's ID.
                Rule::unique('quotations', 'quotation_no')->ignore($quotationId),
            ],

            'quotation.subtotal' => ['required', 'numeric', 'min:0'],
            'quotation.vat' => ['required', 'numeric', Rule::in([0,10, 15])],
            'quotation.total' => ['required', 'numeric', 'min:0'],
            'quotation.terms_conditions' => ['nullable', 'string', 'max:10000'],

            'product' => ['required', 'array', 'min:1'],
            'product.*.name' => ['required', 'string', 'max:255'],
            'product.*.id' => ['nullable', 'integer', 'exists:products,id'],
            'product.*.unit' => ['required', 'string', 'max:255'],
            'product.*.price' => ['required', 'numeric', 'min:0'],
            'product.*.quantity' => ['required', 'integer', 'min:1'],
            'product.*.amount' => ['required', 'numeric', 'min:0'],
            'product.*.specs' => ['nullable', 'string'],
            'product.*.remarks' => ['nullable', 'string'],
        ];

        if ($quotationId) {
            $rules['quotation.id'] = ['required', 'integer', 'exists:quotations,id'];
        }

        // --- Conditional Customer Validation ---
        // Check if an existing customer ID has been submitted.
        if ($this->input('customer.id')) {
            // SCENARIO 1: An existing customer was selected.
            // We only need to validate the ID.
            $customerRules = [
                'customer.id' => ['required', 'integer', 'exists:customers,id'],
            ];
        } else {
            // SCENARIO 2: A new customer is being created.
            // All customer fields are required, and customer_no must be unique.
            $customerRules = [
                'customer.customer_no' => [
                    'required',
                    'string',
                    'max:255',
                    // Check for uniqueness in the 'customers' table in the 'customer_no' column.
                    Rule::unique('customers', 'customer_no')->ignore($customerIdToIgnore)
                ],
                'customer.customer_name' => ['required', 'string', 'max:255'],
                'customer.designation' => ['required', 'string', 'max:255'],
                'customer.company_name' => ['required', 'string', 'max:255'],
                'customer.address' => ['required', 'string', 'max:1000'],
                'customer.phone' => ['required', 'string', 'max:255'],
                'customer.bin_no' => ['nullable', 'string', 'max:255'],
            ];
        }

        // Merge the dynamic customer rules with the base rules and return.
        return array_merge($rules, $customerRules);
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            // General Messages
            'product.min' => 'Please add at least one product to the quotation.',
            'quotation.vat.in' => 'The selected VAT is invalid. Please choose 10% or 15%.',

            // Product Specific Messages
            'product.*.name.required' => 'The product name field is required.',
            'product.*.price.numeric' => 'The product price must be a number.',
            'product.*.quantity.integer' => 'The product quantity must be a whole number.',

            // Customer Specific Messages
            'customer.id.exists' => 'The selected customer is invalid or does not exist.',
            'customer.customer_no.unique' => 'This Customer ID has already been taken.',
        ];
    }
}
