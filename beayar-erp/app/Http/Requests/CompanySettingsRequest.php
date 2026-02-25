<?php

namespace App\Http\Requests;

use App\Services\CompanySettingsService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompanySettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $validCurrencies = array_keys(CompanySettingsService::AVAILABLE_CURRENCIES);
        $validDateFormats = array_keys(CompanySettingsService::AVAILABLE_DATE_FORMATS);

        return [
            'date_format' => ['required', 'string', Rule::in($validDateFormats)],
            'currency' => ['required', 'string', Rule::in($validCurrencies)],
            'currency_symbol' => ['required', 'string', 'max:5'],
            'quotation_prefix' => ['nullable', 'string', 'max:20'],
            'quotation_number_format' => ['required', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date_format.in' => 'The selected date format is not valid. Choose from: d-m-Y, Y-m-d, m/d/Y, d M, Y, d/m/Y.',
            'currency.in' => 'The selected currency is not valid. Choose from: BDT, USD, EUR, INR, RMB.',
            'currency_symbol.max' => 'The currency symbol must not exceed 5 characters.',
            'quotation_prefix.max' => 'The quotation prefix must not exceed 20 characters.',
            'quotation_number_format.max' => 'The quotation number format must not exceed 100 characters.',
        ];
    }
}
