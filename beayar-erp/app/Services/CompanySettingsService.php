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
