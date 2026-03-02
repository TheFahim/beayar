<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanySettingsRequest;
use App\Models\TenantCompany;
use App\Services\CompanySettingsService;
use App\Services\ExchangeRateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CompanySettingsController extends Controller
{
    public function __construct(
        private CompanySettingsService $settingsService,
        private ExchangeRateService $exchangeRateService
    ) {}

    /**
     * Show the settings form for a company.
     */
    public function edit(string $companyId): View
    {
        $company = TenantCompany::findOrFail($companyId);
        $this->authorizeCompanyAccess($company);

        $settings = $this->settingsService->getSettings($company);
        $currencies = $this->settingsService->getAvailableCurrencies();
        $currencyNames = $this->settingsService->getCurrencyNames();

        // Fetch all supported currencies from API
        $apiRates = $this->exchangeRateService->getRates();
        if ($apiRates['success']) {
            foreach ($apiRates['rates'] as $code => $rate) {
                if (!isset($currencies[$code])) {
                    // Use currency code as fallback symbol if not in our predefined list
                    $currencies[$code] = $code;
                }
            }
        }
        ksort($currencies);

        $dateFormats = $this->settingsService->getAvailableDateFormats();

        return view('tenant.companies.settings', compact(
            'company',
            'settings',
            'currencies',
            'currencyNames',
            'dateFormats',
        ));
    }

    /**
     * Update the settings for a company.
     */
    public function update(CompanySettingsRequest $request, string $companyId): RedirectResponse
    {
        $company = TenantCompany::findOrFail($companyId);
        $this->authorizeCompanyAccess($company);

        $settings = $request->validated();

        // Handle signature image upload
        if ($request->hasFile('signature_image')) {
            // Delete old signature if exists
            $currentSettings = $company->getSettings();
            if (!empty($currentSettings['signature_image'])) {
                Storage::disk('public')->delete($currentSettings['signature_image']);
            }
            // Store new signature
            $settings['signature_image'] = $request->file('signature_image')->store('signatures', 'public');
        }

        // Handle company seal image upload
        if ($request->hasFile('company_seal_image')) {
            // Delete old seal if exists
            $currentSettings = $company->getSettings();
            if (!empty($currentSettings['company_seal_image'])) {
                Storage::disk('public')->delete($currentSettings['company_seal_image']);
            }
            // Store new seal
            $settings['company_seal_image'] = $request->file('company_seal_image')->store('seals', 'public');
        }

        $this->settingsService->updateSettings($company, $settings);

        return redirect()
            ->route('tenant.company-settings.edit', $company->id)
            ->with('success', 'Company settings updated successfully.');
    }

    /**
     * Get available options for currencies and date formats (JSON).
     */
    public function getOptions(): JsonResponse
    {
        return response()->json([
            'currencies' => $this->settingsService->getAvailableCurrencies(),
            'date_formats' => $this->settingsService->getAvailableDateFormats(),
        ]);
    }

    /**
     * Authorize that the current user can manage company settings.
     */
    private function authorizeCompanyAccess(TenantCompany $company): void
    {
        $user = Auth::user();

        if ($company->owner_id === $user->id) {
            return;
        }

        $member = $company->members()->where('user_id', $user->id)->first();

        if (! $member || $member->pivot->role !== 'company_admin') {
            abort(403, 'Unauthorized. Only owner or company admin can manage settings.');
        }
    }
}
