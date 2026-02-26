<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    /**
     * Default fallback rates when API is unavailable.
     */
    private const FALLBACK_RATES = [
        'USD' => 121.50,
        'EUR' => 142.80,
        'RMB' => 17.08,
        'INR' => 1.45,
    ];

    /**
     * Get exchange rates from API with fallback.
     *
     * @return array{success: bool, rates: array, last_updated: string, fallback?: bool, message?: string}
     */
    public function getRates(string $baseCurrency = 'BDT'): array
    {
        // Try to retrieve valid rates from cache first
        $cacheKey = 'exchange_rates_' . $baseCurrency;
        $cached = \Illuminate\Support\Facades\Cache::get($cacheKey);
        if ($cached && !($cached['fallback'] ?? false)) {
            return $cached;
        }

        try {
            $data = $this->fetchRatesFromApi($baseCurrency);

            if (!$this->isValidApiResponse($data)) {
                throw new \Exception('Invalid API response format or missing currency rates.');
            }

            $result = [
                'success' => true,
                'rates' => $this->calculateRates($data['rates']),
                'last_updated' => $data['date'] ?? date('Y-m-d'),
                'fallback' => false,
            ];

            // Cache successful response for 12 hours
            \Illuminate\Support\Facades\Cache::put($cacheKey, $result, 43200);

            return $result;
        } catch (\Exception $e) {
            Log::warning('Exchange rate API unavailable: ' . $e->getMessage());

            // If we have stale cache (even if expired or marked fallback previously but valid data exists?), use it?
            // For now, just return fallback if fetch fails and no valid cache.
            return $this->getFallbackResponse($baseCurrency);
        }
    }

    /**
     * Fetch rates from the external API.
     */
    private function fetchRatesFromApi(string $baseCurrency): array
    {
        $url = 'https://api.exchangerate-api.com/v4/latest/' . $baseCurrency;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, 'OptiMech/1.0');
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            throw new \Exception('Failed to fetch exchange rates from API.');
        }

        return json_decode($response, true) ?? [];
    }

    /**
     * Validate API response has required rates.
     */
    private function isValidApiResponse(array $data): bool
    {
        return isset($data['rates']) && is_array($data['rates']);
    }

    /**
     * Calculate BDT rates from API response (inverse of provided rates).
     */
    private function calculateRates(array $apiRates): array
    {
        $rates = [];
        foreach ($apiRates as $currency => $rate) {
            if ($rate > 0) {
                // Handle RMB mapping (API uses CNY)
                $key = ($currency === 'CNY') ? 'RMB' : $currency;
                $rates[$key] = round(1 / $rate, 2);
            }
        }
        return $rates;
    }

    /**
     * Get fallback rates response.
     */
    public function getFallbackResponse(string $baseCurrency = 'BDT'): array
    {
        return [
            'success' => true,
            'rates' => $this->getFallbackRates($baseCurrency),
            'last_updated' => date('Y-m-d'),
            'fallback' => true,
            'message' => 'Using fallback rates due to API unavailability.',
        ];
    }

    /**
     * Get fallback rates only.
     */
    public function getFallbackRates(string $baseCurrency = 'BDT'): array
    {
        $rates = self::FALLBACK_RATES;
        $rates['BDT'] = 1.0;

        if ($baseCurrency === 'BDT') {
            unset($rates['BDT']);
            return $rates;
        }

        $baseRate = $rates[$baseCurrency] ?? 1.0;
        $converted = [];
        foreach ($rates as $currency => $rate) {
            if ($currency !== $baseCurrency) {
                // 1 USD = 121.50 BDT, 1 INR = 1.45 BDT
                // So 1 USD = (121.50 / 1.45) INR
                $converted[$currency] = round($rate / $baseRate, 4);
            }
        }

        return $converted;
    }
}
