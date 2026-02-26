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

    protected function prepareForValidation(): void
    {
        $currencies = $this->input('quotation_currencies', []);
        if (!is_array($currencies)) {
            $currencies = [];
        }

        $normalized = array_values(array_unique(array_filter(array_map(static function ($value) {
            if (!is_string($value)) {
                return null;
            }

            $cleaned = strtoupper(trim($value));
            return $cleaned !== '' ? $cleaned : null;
        }, $currencies))));

        $exchangeCurrency = $this->input('exchange_rate_currency', 'BDT');
        if (!in_array($exchangeCurrency, $normalized, true)) {
            $normalized[] = $exchangeCurrency;
        }

        $this->merge([
            'quotation_currencies' => $normalized,
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $validCurrencies = array_keys(CompanySettingsService::AVAILABLE_CURRENCIES);
        $validDateFormats = array_keys(CompanySettingsService::AVAILABLE_DATE_FORMATS);
        $quotationCurrencies = $this->input('quotation_currencies', $validCurrencies);
        if (!is_array($quotationCurrencies) || empty($quotationCurrencies)) {
            $quotationCurrencies = $validCurrencies;
        }

        return [
            'date_format' => ['required', 'string', Rule::in($validDateFormats)],
            'currency' => ['required', 'string', Rule::in($quotationCurrencies)],
            'currency_symbol' => ['required', 'string', 'max:5'],
            'exchange_rate_currency' => ['required', 'string', Rule::in($validCurrencies)],
            'quotation_currencies' => [
                'required',
                'array',
                'min:1',
                function ($attribute, $value, $fail) {
                    if (!is_array($value)) {
                        return;
                    }
                    $hasForeign = false;
                    $exchangeRateCurrency = $this->input('exchange_rate_currency', 'BDT');
                    foreach ($value as $currency) {
                        if (is_string($currency) && strtoupper($currency) !== strtoupper($exchangeRateCurrency)) {
                            $hasForeign = true;
                            break;
                        }
                    }
                    if (!$hasForeign) {
                        $fail('Add at least one foreign currency for quotations.');
                    }
                },
            ],
            'quotation_currencies.*' => ['required', 'string', 'distinct', 'regex:/^[A-Z]{2,5}$/'],
            'quotation_prefix' => ['nullable', 'string', 'max:20'],
            'quotation_number_format' => ['required', 'string', 'max:100'],
            'vat_percentages' => ['required', 'array', 'min:1'],
            'vat_percentages.*' => ['required', 'numeric', 'min:0', 'max:100'],
            'vat_default_percentage' => ['required', 'numeric', 'min:0', 'max:100', Rule::in($this->input('vat_percentages', []))],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date_format.in' => 'The selected date format is not valid. Choose from: d-m-Y, Y-m-d, m/d/Y, d M, Y, d/m/Y.',
            'currency.in' => 'The selected currency is not valid.',
            'currency_symbol.max' => 'The currency symbol must not exceed 5 characters.',
            'quotation_prefix.max' => 'The quotation prefix must not exceed 20 characters.',
            'quotation_number_format.max' => 'The quotation number format must not exceed 100 characters.',
        ];
    }
}
