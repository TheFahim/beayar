<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Determine edit vs create via presence of quotation_revision.id
        return [
            // Main quotation fields
            'quotation' => ['required', 'array'],
            'quotation.customer_id' => ['required', 'integer', 'exists:customers,id'],
            'quotation.quotation_no' => [
                'required',
                'string',
                'max:255',
                Rule::unique('quotations', 'quotation_no')->ignore($this->quotation['id'] ?? null),
                // Also check reference_no just in case
                Rule::unique('quotations', 'reference_no')->ignore($this->quotation['id'] ?? null),
            ],
            'quotation.ship_to' => ['nullable', 'string', 'max:1000'],

            // Quotation revision fields
            'quotation_revision' => ['required', 'array'],
            'quotation_revision.id' => [
                'nullable',
                'integer',
                'exists:quotation_revisions,id',
            ],
            'quotation_revision.type' => ['required', 'string', 'in:normal,via'],
            'quotation_revision.date' => ['required', 'date_format:d/m/Y'],
            'quotation_revision.validity' => ['required', 'date_format:d/m/Y'], // Removed after_or_equal check to simplify for now
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
}
