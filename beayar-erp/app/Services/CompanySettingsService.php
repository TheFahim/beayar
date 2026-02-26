<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Quotation;
use App\Models\TenantCompany;

class CompanySettingsService
{
    /** @var array<string, string> */
    public const AVAILABLE_CURRENCIES = [
        'BDT' => '৳',
        'USD' => '$',
        'EUR' => '€',
        'INR' => '₹',
        'RMB' => '¥',
        'SAR' => '﷼',
        'AED' => 'د.إ',
        'GBP' => '£',
        'JPY' => '¥',
        'CAD' => 'C$',
        'AUD' => 'A$',
        'CHF' => 'CHF',
        'SGD' => 'S$',
        'MYR' => 'RM',
        'THB' => '฿',
        'IDR' => 'Rp',
        'PHP' => '₱',
        'LKR' => 'රු',
        'NPR' => 'रू',
        'PKR' => '₨',
        'MVR' => 'Rf',
        'AFN' => '؋',
        'BTN' => 'Nu.',
        'BHD' => 'BD',
        'KWD' => 'KD',
        'OMR' => 'ر.ع.',
        'QAR' => 'ر.ق',
        'IRR' => '﷼',
        'IQD' => 'ع.د',
        'JOD' => 'د.ا',
        'LBP' => 'ل.ل',
        'SYP' => '£S',
        'YER' => '﷼',
        'EGP' => 'ج.م',
        'LYD' => 'ل.د',
        'TND' => 'د.ت',
        'DZD' => 'د.ج',
        'MAD' => 'د.م.',
        'SDG' => 'ج.س.',
        'ETB' => 'Br',
        'KES' => 'KSh',
        'UGX' => 'USh',
        'TZS' => 'TSh',
        'RWF' => 'RWF',
        'BIF' => 'FBu',
        'DJF' => 'Fdj',
        'SOS' => 'Sh.so.',
        'ERN' => 'Nfk',
        'SSP' => '£SS',
        'GHS' => 'GH₵',
        'NGN' => '₦',
        'XOF' => 'CFA',
        'XAF' => 'FCFA',
        'XCF' => 'KMF',
        'SCR' => '₨',
        'MUR' => '₨',
        'ZAR' => 'R',
        'BWP' => 'P',
        'SZL' => 'E',
        'LSL' => 'L',
        'NAD' => 'N$',
        'AOA' => 'Kz',
        'ZMW' => 'ZK',
        'MWK' => 'MK',
        'BZD' => 'BZ$',
        'GTQ' => 'Q',
        'HNL' => 'L',
        'NIO' => 'C$',
        'CRC' => '₡',
        'PAB' => 'B/.',
        'COP' => '$',
        'VEF' => 'Bs.',
        'GYD' => 'G$',
        'SRD' => '$',
        'TTD' => 'TT$',
        'JMD' => 'J$',
        'HTG' => 'G',
        'XCD' => 'EC$',
        'CUP' => '₱',
        'DOP' => 'RD$',
        'BOB' => 'Bs.',
        'PYG' => '₲',
        'UYU' => '$U',
        'CLP' => '$',
        'ARS' => '$',
        'FKP' => '£',
        'PEN' => 'S/',
        'NOK' => 'kr',
        'SEK' => 'kr',
        'DKK' => 'kr',
        'ISK' => 'kr',
        'PLN' => 'zł',
        'CZK' => 'Kč',
        'HUF' => 'Ft',
        'RON' => 'lei',
        'BGN' => 'лв',
        'HRK' => 'kn',
        'RUB' => '₽',
        'UAH' => '₴',
        'BYN' => 'Br',
        'MDL' => 'L',
        'ALL' => 'L',
        'MKD' => 'ден',
        'RSD' => 'дин.',
        'BAM' => 'KM',
        'MXN' => '$',
        'KRW' => '₩',
        'HKD' => 'HK$',
        'VND' => '₫',
        'LAK' => '₭',
        'KHR' => '៛',
        'MMK' => 'K',
    ];

    /** @var array<string, string> */
    public const CURRENCY_NAMES = [
        'BDT' => 'Bangladeshi Taka',
        'USD' => 'US Dollar',
        'EUR' => 'Euro',
        'INR' => 'Indian Rupee',
        'RMB' => 'Chinese Yuan',
        'SAR' => 'Saudi Riyal',
        'AED' => 'United Arab Emirates Dirham',
        'GBP' => 'British Pound',
        'JPY' => 'Japanese Yen',
        'CAD' => 'Canadian Dollar',
        'AUD' => 'Australian Dollar',
        'CHF' => 'Swiss Franc',
        'SGD' => 'Singapore Dollar',
        'MYR' => 'Malaysian Ringgit',
        'THB' => 'Thai Baht',
        'IDR' => 'Indonesian Rupiah',
        'PHP' => 'Philippine Peso',
        'LKR' => 'Sri Lankan Rupee',
        'NPR' => 'Nepalese Rupee',
        'PKR' => 'Pakistani Rupee',
        'MVR' => 'Maldivian Rufiyaa',
        'AFN' => 'Afghan Afghani',
        'BTN' => 'Bhutanese Ngultrum',
        'BHD' => 'Bahraini Dinar',
        'KWD' => 'Kuwaiti Dinar',
        'OMR' => 'Omani Rial',
        'QAR' => 'Qatari Riyal',
        'IRR' => 'Iranian Rial',
        'IQD' => 'Iraqi Dinar',
        'JOD' => 'Jordanian Dinar',
        'LBP' => 'Lebanese Pound',
        'SYP' => 'Syrian Pound',
        'YER' => 'Yemeni Rial',
        'EGP' => 'Egyptian Pound',
        'LYD' => 'Libyan Dinar',
        'TND' => 'Tunisian Dinar',
        'DZD' => 'Algerian Dinar',
        'MAD' => 'Moroccan Dirham',
        'SDG' => 'Sudanese Pound',
        'ETB' => 'Ethiopian Birr',
        'KES' => 'Kenyan Shilling',
        'UGX' => 'Ugandan Shilling',
        'TZS' => 'Tanzanian Shilling',
        'RWF' => 'Rwandan Franc',
        'BIF' => 'Burundian Franc',
        'DJF' => 'Djiboutian Franc',
        'SOS' => 'Somali Shilling',
        'ERN' => 'Eritrean Nakfa',
        'SSP' => 'South Sudanese Pound',
        'GHS' => 'Ghanaian Cedi',
        'NGN' => 'Nigerian Naira',
        'XOF' => 'West African CFA Franc',
        'XAF' => 'Central African CFA Franc',
        'XCF' => 'Comorian Franc',
        'SCR' => 'Seychellois Rupee',
        'MUR' => 'Mauritian Rupee',
        'ZAR' => 'South African Rand',
        'BWP' => 'Botswana Pula',
        'SZL' => 'Eswatini Lilangeni',
        'LSL' => 'Lesotho Loti',
        'NAD' => 'Namibian Dollar',
        'AOA' => 'Angolan Kwanza',
        'ZMW' => 'Zambian Kwacha',
        'MWK' => 'Malawian Kwacha',
        'BZD' => 'Belize Dollar',
        'GTQ' => 'Guatemalan Quetzal',
        'HNL' => 'Honduran Lempira',
        'NIO' => 'Nicaraguan Córdoba',
        'CRC' => 'Costa Rican Colón',
        'PAB' => 'Panamanian Balboa',
        'COP' => 'Colombian Peso',
        'VEF' => 'Venezuelan Bolívar',
        'GYD' => 'Guyana Dollar',
        'SRD' => 'Surinamese Dollar',
        'TTD' => 'Trinidad and Tobago Dollar',
        'JMD' => 'Jamaican Dollar',
        'HTG' => 'Haitian Gourde',
        'XCD' => 'East Caribbean Dollar',
        'CUP' => 'Cuban Peso',
        'DOP' => 'Dominican Peso',
        'BOB' => 'Bolivian Boliviano',
        'PYG' => 'Paraguayan Guaraní',
        'UYU' => 'Uruguayan Peso',
        'CLP' => 'Chilean Peso',
        'ARS' => 'Argentine Peso',
        'FKP' => 'Falkland Islands Pound',
        'PEN' => 'Peruvian Sol',
        'EUR' => 'Euro',
        'GBP' => 'British Pound',
        'CHF' => 'Swiss Franc',
        'NOK' => 'Norwegian Krone',
        'SEK' => 'Swedish Krona',
        'DKK' => 'Danish Krone',
        'ISK' => 'Icelandic Króna',
        'PLN' => 'Polish Złoty',
        'CZK' => 'Czech Koruna',
        'HUF' => 'Hungarian Forint',
        'RON' => 'Romanian Leu',
        'BGN' => 'Bulgarian Lev',
        'HRK' => 'Croatian Kuna',
        'RUB' => 'Russian Ruble',
        'UAH' => 'Ukrainian Hryvnia',
        'BYN' => 'Belarusian Ruble',
        'MDL' => 'Moldovan Leu',
        'ALL' => 'Albanian Lek',
        'MKD' => 'North Macedonian Denar',
        'RSD' => 'Serbian Dinar',
        'BAM' => 'Bosnia and Herzegovina Mark',
        'EUR' => 'Euro',
        'USD' => 'US Dollar',
        'CAD' => 'Canadian Dollar',
        'MXN' => 'Mexican Peso',
        'GTQ' => 'Guatemalan Quetzal',
        'BZD' => 'Belize Dollar',
        'HNL' => 'Honduran Lempira',
        'NIO' => 'Nicaraguan Córdoba',
        'CRC' => 'Costa Rican Colón',
        'PAB' => 'Panamanian Balboa',
        'COP' => 'Colombian Peso',
        'VEF' => 'Venezuelan Bolívar',
        'GYD' => 'Guyana Dollar',
        'SRD' => 'Surinamese Dollar',
        'BOB' => 'Bolivian Boliviano',
        'PYG' => 'Paraguayan Guaraní',
        'UYU' => 'Uruguayan Peso',
        'ARS' => 'Argentine Peso',
        'CLP' => 'Chilean Peso',
        'PEN' => 'Peruvian Sol',
        'USD' => 'US Dollar',
        'CAD' => 'Canadian Dollar',
        'MXN' => 'Mexican Peso',
        'GTQ' => 'Guatemalan Quetzal',
        'BZD' => 'Belize Dollar',
        'HNL' => 'Honduran Lempira',
        'NIO' => 'Nicaraguan Córdoba',
        'CRC' => 'Costa Rican Colón',
        'PAB' => 'Panamanian Balboa',
        'COP' => 'Colombian Peso',
        'VEF' => 'Venezuelan Bolívar',
        'GYD' => 'Guyana Dollar',
        'SRD' => 'Surinamese Dollar',
        'BOB' => 'Bolivian Boliviano',
        'PYG' => 'Paraguayan Guaraní',
        'UYU' => 'Uruguayan Peso',
        'ARS' => 'Argentine Peso',
        'CLP' => 'Chilean Peso',
        'PEN' => 'Peruvian Sol',
        'CNY' => 'Chinese Yuan',
        'JPY' => 'Japanese Yen',
        'KRW' => 'South Korean Won',
        'HKD' => 'Hong Kong Dollar',
        'SGD' => 'Singapore Dollar',
        'MYR' => 'Malaysian Ringgit',
        'THB' => 'Thai Baht',
        'IDR' => 'Indonesian Rupiah',
        'PHP' => 'Philippine Peso',
        'VND' => 'Vietnamese Đồng',
        'LAK' => 'Lao Kip',
        'KHR' => 'Cambodian Riel',
        'MMK' => 'Myanmar Kyat',
        'BDT' => 'Bangladeshi Taka',
        'LKR' => 'Sri Lankan Rupee',
        'NPR' => 'Nepalese Rupee',
        'PKR' => 'Pakistani Rupee',
        'MVR' => 'Maldivian Rufiyaa',
        'BTN' => 'Bhutanese Ngultrum',
        'AFN' => 'Afghan Afghani',
        'INR' => 'Indian Rupee',
        'MVR' => 'Maldivian Rufiyaa',
        'NPR' => 'Nepalese Rupee',
        'PKR' => 'Pakistani Rupee',
        'LKR' => 'Sri Lankan Rupee',
        'AFN' => 'Afghan Afghani',
        'BDT' => 'Bangladeshi Taka',
        'BTN' => 'Bhutanese Ngultrum',
        'MVR' => 'Maldivian Rufiyaa',
        'NPR' => 'Nepalese Rupee',
        'PKR' => 'Pakistani Rupee',
        'LKR' => 'Sri Lankan Rupee',
    ];

    /** @var array<string, string> */
    public const AVAILABLE_DATE_FORMATS = [
        'd-m-Y' => 'DD-MM-YYYY (25-02-2026)',
        'Y-m-d' => 'YYYY-MM-DD (2026-02-25)',
        'm/d/Y' => 'MM/DD/YYYY (02/25/2026)',
        'd M, Y' => 'DD Mon, YYYY (25 Feb, 2026)',
        'd/m/Y' => 'DD/MM/YYYY (25/02/2026)',
    ];

    /**
     * Get all settings merged with defaults.
     *
     * @return array<string, mixed>
     */
    public function getSettings(TenantCompany $company): array
    {
        return $company->getSettings();
    }

    /**
     * Update settings for a company.
     *
     * @param  array<string, mixed>  $settings
     */
    public function updateSettings(TenantCompany $company, array $settings): TenantCompany
    {
        $currentSettings = $company->getSettings();
        $merged = array_merge($currentSettings, $settings);

        $company->update(['settings' => $merged]);

        return $company->fresh();
    }

    /**
     * Get available currencies with symbols.
     *
     * @return array<string, string>
     */
    public function getAvailableCurrencies(): array
    {
        return self::AVAILABLE_CURRENCIES;
    }

    /**
     * Get currency names.
     *
     * @return array<string, string>
     */
    public function getCurrencyNames(): array
    {
        return self::CURRENCY_NAMES;
    }

    /**
     * Get available date formats with example output.
     *
     * @return array<string, string>
     */
    public function getAvailableDateFormats(): array
    {
        return self::AVAILABLE_DATE_FORMATS;
    }

    /**
     * Generate a quotation number based on company settings.
     */
    public function generateQuotationNumber(TenantCompany $company, Customer $customer): string
    {
        $settings = $company->getSettings();
        $format = $settings['quotation_number_format'] ?? '{CUSTOMER_NO}-{YY}-{SEQUENCE}';
        $prefix = $settings['quotation_prefix'] ?? '';

        $now = now();
        $customerNo = $customer->customer_no;

        // Build the static portion of the pattern (everything except {SEQUENCE} and {ID})
        $staticPart = $this->replaceStaticTags($format, $prefix, $customerNo, $now);

        // Calculate sequence
        $sequence = $this->calculateSequence($company, $customer, $format, $staticPart);

        // Replace dynamic tags
        $result = str_replace('{SEQUENCE}', $sequence, $staticPart);
        $result = str_replace('{ID}', (string) (Quotation::where('tenant_company_id', $company->id)->count() + 1), $result);

        return $result;
    }

    /**
     * Replace static tags in the format string.
     */
    private function replaceStaticTags(string $format, string $prefix, string $customerNo, \DateTimeInterface $now): string
    {
        $replacements = [
            '{PREFIX}' => $prefix,
            '{CUSTOMER_NO}' => $customerNo,
            '{YYYY}' => $now->format('Y'),
            '{YY}' => $now->format('y'),
            '{MM}' => $now->format('m'),
            '{DD}' => $now->format('d'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $format);
    }

    /**
     * Calculate the next sequence number based on existing quotations.
     */
    private function calculateSequence(TenantCompany $company, Customer $customer, string $format, string $staticPart): string
    {
        // Build the LIKE pattern from the static part with SEQUENCE replaced by a wildcard
        $likePattern = str_replace('{SEQUENCE}', '%', $staticPart);
        $likePattern = str_replace('{ID}', '%', $likePattern);

        $latestQuotation = Quotation::where('tenant_company_id', $company->id)
            ->where('customer_id', $customer->id)
            ->where('quotation_no', 'LIKE', $likePattern)
            ->orderByRaw('LENGTH(quotation_no) DESC')
            ->orderBy('quotation_no', 'desc')
            ->first();

        $nextNumber = 1;

        if ($latestQuotation) {
            // Extract the sequence from the existing quotation number
            $sequenceNumber = $this->extractSequenceFromQuotation(
                $latestQuotation->quotation_no,
                $staticPart
            );

            if ($sequenceNumber !== null) {
                $nextNumber = $sequenceNumber + 1;
            }
        }

        return str_pad((string) $nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Extract the sequence number from an existing quotation number.
     */
    private function extractSequenceFromQuotation(string $quotationNo, string $staticPart): ?int
    {
        // Create a regex from the static part to extract the sequence
        $pattern = preg_quote($staticPart, '/');
        $pattern = str_replace(preg_quote('{SEQUENCE}', '/'), '(\d+)', $pattern);
        $pattern = str_replace(preg_quote('{ID}', '/'), '\d+', $pattern);

        if (preg_match('/^'.$pattern.'$/', $quotationNo, $matches)) {
            return (int) $matches[1];
        }

        // Fallback: try to extract the last numeric segment
        $parts = preg_split('/[^0-9]+/', $quotationNo);
        $lastPart = end($parts);

        return is_numeric($lastPart) ? (int) $lastPart : null;
    }
}
