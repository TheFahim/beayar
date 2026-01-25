<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property array $quotation
 * @property array $quotation_revision
 * @property array $quotation_products
 */
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
        // Determine edit vs create via presence of quotation_revision.id (handled below)
        return [
            // Main quotation fields
            'quotation' => ['required', 'array'],
            'quotation.customer_id' => ['required', 'integer', 'exists:customers,id'],
            'quotation.quotation_no' => [
                'required',
                'string',
                'max:255',
                Rule::unique('quotations', 'quotation_no'),
            ],
            'quotation.ship_to' => ['nullable', 'string', 'max:1000'],

            // Quotation revision fields
            'quotation_revision' => ['required', 'array'],
            'quotation_revision.id' => [
                Rule::requiredIf(static function () {
                    $method = isset($_SERVER['REQUEST_METHOD']) ? strtolower($_SERVER['REQUEST_METHOD']) : null;
                    $isEditMethod = in_array($method, ['put', 'patch'], true);
                    $hasId = isset($_REQUEST['quotation_revision']['id']) && $_REQUEST['quotation_revision']['id'] !== '';

                    return $isEditMethod || $hasId;
                }),
                'integer',
                'exists:quotation_revisions,id',
            ],
            'quotation_revision.type' => ['required', 'string', 'in:normal,via'],
            'quotation_revision.date' => ['required', 'date_format:d/m/Y'],
            'quotation_revision.validity' => ['required', 'date_format:d/m/Y', 'after_or_equal:quotation_revision.date'],
            'quotation_revision.currency' => ['required', 'string', 'in:USD,EUR,BDT,RMB,INR'],
            'quotation_revision.exchange_rate' => ['required', 'numeric', 'min:0.01'],
            'quotation_revision.saved_as' => ['required', 'string', 'in:draft,quotation'],

            // Financial calculations
            'quotation_revision.subtotal' => ['required', 'numeric', 'min:0'],
            'quotation_revision.discount' => ['nullable', 'numeric', 'min:0'],
            'quotation_revision.discount_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'quotation_revision.discounted_price' => ['nullable', 'numeric', 'min:0'],
            'quotation_revision.vat_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'quotation_revision.vat_amount' => ['nullable', 'numeric', 'min:0'],
            'quotation_revision.shipping' => ['nullable', 'numeric', 'min:0'],
            'quotation_revision.total' => ['required', 'numeric', 'min:0'],
            'quotation_revision.terms_conditions' => ['nullable', 'string', 'max:10000'],

            // Products array validation
            'quotation_products' => ['required', 'array', 'min:1'],
            'quotation_products.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'quotation_products.*.brand_origin_id' => ['nullable', 'integer', 'exists:brand_origins,id'],
            'quotation_products.*.size' => ['nullable', 'string', 'max:255'],
            'quotation_products.*.specification_id' => ['nullable', 'integer', 'exists:specifications,id'],
            'quotation_products.*.add_spec' => ['nullable', 'string', 'max:255'],
            'quotation_products.*.delivery_time' => ['nullable', 'string', 'max:255'],
            'quotation_products.*.unit' => ['nullable', 'string', 'max:50'],
            'quotation_products.*.quantity' => ['required', 'integer', 'min:1'],
            'quotation_products.*.requision_no' => ['nullable', 'string', 'max:255'],

            // Product pricing fields
            'quotation_products.*.foreign_currency_buying' => ['nullable', 'numeric', 'min:0'],
            'quotation_products.*.bdt_buying' => ['nullable', 'numeric', 'min:0'],
            'quotation_products.*.weight' => ['nullable', 'numeric', 'min:0'],
            'quotation_products.*.air_sea_freight_rate' => ['nullable', 'numeric', 'min:0'],
            'quotation_products.*.air_sea_freight' => ['nullable', 'numeric', 'min:0'],

            // Tax and percentage fields
            'quotation_products.*.tax_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'quotation_products.*.tax' => ['nullable', 'numeric', 'min:0'],
            'quotation_products.*.att_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'quotation_products.*.att' => ['nullable', 'numeric', 'min:0'],
            'quotation_products.*.margin' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'quotation_products.*.margin_value' => ['nullable', 'numeric', 'min:0'],
            'quotation_products.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            // Main quotation messages
            'quotation.required' => 'Quotation data is required.',
            'quotation.customer_id.required' => 'Please select a customer.',
            'quotation.customer_id.exists' => 'The selected customer is invalid.',
            'quotation.quotation_no.required' => 'Quotation number is required.',
            'quotation.quotation_no.unique' => 'This quotation number has already been taken.',
            'quotation.ship_to.max' => 'Ship to address cannot exceed 1000 characters.',

            // Revision messages
            'quotation_revision.required' => 'Quotation revision data is required.',
            'quotation_revision.id.required' => 'Revision ID is required for editing.',
            'quotation_revision.id.exists' => 'The selected revision ID is invalid.',
            'quotation_revision.type.required' => 'Quotation type is required.',
            'quotation_revision.type.in' => 'Type must be normal or via.',
            'quotation_revision.date.required' => 'Quotation date is required.',
            'quotation_revision.date.date' => 'Please provide a valid quotation date.',
            'quotation_revision.validity.required' => 'Validity date is required.',
            'quotation_revision.validity.after_or_equal' => 'Validity date must be on or after the quotation date.',
            'quotation_revision.currency.required' => 'Currency is required.',
            'quotation_revision.currency.in' => 'Please select a valid currency (USD, EUR, BDT, GBP, JPY, RMB, INR).',
            'quotation_revision.exchange_rate.required' => 'Exchange rate is required.',
            'quotation_revision.exchange_rate.min' => 'Exchange rate must be greater than 0.',
            'quotation_revision.saved_as.required' => 'Please specify whether to save as draft or quotation.',
            'quotation_revision.saved_as.in' => 'Save as must be either draft or quotation.',

            // Financial calculation messages
            'quotation_revision.subtotal.required' => 'Subtotal is required.',
            'quotation_revision.subtotal.min' => 'Subtotal cannot be negative.',
            'quotation_revision.discount.min' => 'Discount cannot be negative.',
            'quotation_revision.discount_percentage.min' => 'Discount percentage cannot be negative.',
            'quotation_revision.discount_percentage.max' => 'Discount percentage cannot exceed 100%.',
            'quotation_revision.discounted_price.min' => 'Discounted price cannot be negative.',
            'quotation_revision.vat_percentage.max' => 'VAT percentage cannot exceed 100%.',
            'quotation_revision.vat_percentage.min' => 'VAT percentage cannot be negative.',
            'quotation_revision.vat_amount.min' => 'VAT amount cannot be negative.',
            'quotation_revision.shipping.min' => 'Shipping cost cannot be negative.',
            'quotation_revision.total.required' => 'Total amount is required.',
            'quotation_revision.total.min' => 'Total amount cannot be negative.',
            'quotation_revision.terms_conditions.max' => 'Terms and conditions cannot exceed 10000 characters.',

            // Products messages
            'quotation_products.required' => 'Please add at least one product to the quotation.',
            'quotation_products.min' => 'Please add at least one product to the quotation.',
            'quotation_products.*.product_id.required' => 'Product selection is required.',
            'quotation_products.*.product_id.exists' => 'The selected product is invalid.',
            'quotation_products.*.specification_id.exists' => 'The selected specification is invalid.',
            'quotation_products.*.add_spec.max' => 'Additional specification cannot exceed 1000 characters.',
            'quotation_products.*.delivery_time.max' => 'Delivery time cannot exceed 255 characters.',
            'quotation_products.*.unit.max' => 'Unit cannot exceed 50 characters.',
            'quotation_products.*.quantity.required' => 'Product quantity is required.',
            'quotation_products.*.quantity.min' => 'Product quantity must be at least 1.',

            // Product pricing messages
            'quotation_products.*.foreign_currency_buying.min' => 'Foreign currency buying price cannot be negative.',
            'quotation_products.*.bdt_buying.min' => 'BDT buying price cannot be negative.',
            'quotation_products.*.weight.min' => 'Weight cannot be negative.',
            'quotation_products.*.air_sea_freight_rate.min' => 'Air/Sea freight rate cannot be negative.',
            'quotation_products.*.air_sea_freight.min' => 'Air/Sea freight cannot be negative.',

            // Tax and percentage messages
            'quotation_products.*.tax_percentage.min' => 'Tax percentage cannot be negative.',
            'quotation_products.*.tax_percentage.max' => 'Tax percentage cannot exceed 100%.',
            'quotation_products.*.tax.min' => 'Tax cannot be negative.',
            'quotation_products.*.att_percentage.min' => 'ATT percentage cannot be negative.',
            'quotation_products.*.att_percentage.max' => 'ATT percentage cannot exceed 100%.',
            'quotation_products.*.att.min' => 'ATT cannot be negative.',
            'quotation_products.*.margin.min' => 'Margin cannot be negative.',
            'quotation_products.*.margin.max' => 'Margin cannot exceed 100%.',
            'quotation_products.*.margin_value.min' => 'Margin value cannot be negative.',
            'quotation_products.*.unit_price.required' => 'Unit price is required.',
            'quotation_products.*.unit_price.min' => 'Unit price cannot be negative.',
        ];
    }
}
