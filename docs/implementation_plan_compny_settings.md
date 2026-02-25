# Company-Specific Settings Module

Enable each tenant company to configure **Date Format**, **Currency**, and **Quotation Number Generation** preferences via a JSON `settings` column on the `tenant_companies` table.

## Code Analysis — Current Quotation Number Logic

The quotation number is generated in **two places**:

| Location | Logic | Purpose |
|---|---|---|
| [QuotationService.php](file:///Users/fahim/Desktop/beayar/beayar-erp/app/Services/QuotationService.php#L387-L417) [generateNextQuotationNo()](file:///Users/fahim/Desktop/beayar/beayar-erp/app/Services/QuotationService.php#387-418) | `{customerNo}-{YY}-{sequence}` (padded 3 digits) | Primary quotation number generation |
| [Quotation.php](file:///Users/fahim/Desktop/beayar/beayar-erp/app/Models/Quotation.php#L17-L26) [boot()](file:///Users/fahim/Desktop/beayar/beayar-erp/app/Models/Quotation.php#17-27) creating hook | `QT-{YYYY}-{random5}` written to `reference_no` | Fallback if `reference_no` is empty |

The primary [generateNextQuotationNo()](file:///Users/fahim/Desktop/beayar/beayar-erp/app/Services/QuotationService.php#387-418) is called via `QuotationController::getNextQuotationNo()` (AJAX endpoint) and used in [createQuotation()](file:///Users/fahim/Desktop/beayar/beayar-erp/app/Services/QuotationService.php#16-67) / [updateQuotation()](file:///Users/fahim/Desktop/beayar/beayar-erp/app/Services/QuotationService.php#68-104).

## User Review Required

> [!IMPORTANT]
> **JSON column approach** — Using a single `settings` JSON column for flexibility. This means no strict DB-level constraints on individual settings values. Validation will be enforced at the application layer. This is deliberately chosen over individual columns so new settings can be added without migrations.

> [!WARNING]
> **Quotation number format change** — Existing quotations will retain their current number format. Only **new** quotations will use the company's custom format. The `{CUSTOMER_NO}` tag will remain the default component for backward compatibility.

---

## Proposed Changes

### Database Layer

#### [NEW] Migration: `add_settings_to_tenant_companies_table`

Add a nullable JSON `settings` column to `tenant_companies` with default values:

```php
$table->json('settings')->nullable()->default(json_encode([
    'date_format' => 'd-m-Y',
    'currency' => 'BDT',
    'currency_symbol' => '৳',
    'quotation_prefix' => '',
    'quotation_number_format' => '{CUSTOMER_NO}-{YY}-{SEQUENCE}',
]));
```

---

### Model Layer

#### [MODIFY] [TenantCompany.php](file:///Users/fahim/Desktop/beayar/beayar-erp/app/Models/TenantCompany.php)

- Add `settings` to the `casts()` method as `array`
- Add `getSettings()` helper that merges saved settings with defaults
- Add `getSetting(string $key)` accessor for individual settings

#### [MODIFY] [Quotation.php](file:///Users/fahim/Desktop/beayar/beayar-erp/app/Models/Quotation.php)

- Update [boot()](file:///Users/fahim/Desktop/beayar/beayar-erp/app/Models/Quotation.php#17-27) creating hook to use company's `quotation_prefix` setting when generating the fallback `reference_no`

#### [MODIFY] [TenantCompanyFactory.php](file:///Users/fahim/Desktop/beayar/beayar-erp/database/factories/TenantCompanyFactory.php)

- Add default `settings` array to factory definition

---

### Service Layer

#### [NEW] `app/Services/CompanySettingsService.php`

- `getSettings(TenantCompany $company): array` — returns merged settings with defaults
- `updateSettings(TenantCompany $company, array $settings): TenantCompany` — validates and saves settings
- `getAvailableCurrencies(): array` — returns predefined currency list (USD, EUR, BDT, INR, CNY)
- `getAvailableDateFormats(): array` — returns supported PHP date formats
- `generateQuotationNumber(TenantCompany $company, Customer $customer): string` — parses the format pattern and generates the number

#### [MODIFY] [QuotationService.php](file:///Users/fahim/Desktop/beayar/beayar-erp/app/Services/QuotationService.php#L387-L417)

Refactor [generateNextQuotationNo()](file:///Users/fahim/Desktop/beayar/beayar-erp/app/Services/QuotationService.php#387-418) to delegate to `CompanySettingsService::generateQuotationNumber()`. The new method will:

1. Load the company's `quotation_number_format` setting
2. Parse dynamic tags: `{YYYY}`, `{YY}`, `{MM}`, `{DD}`, `{CUSTOMER_NO}`, `{SEQUENCE}`, `{ID}`, `{PREFIX}`
3. Calculate `{SEQUENCE}` by querying existing quotations matching the same pattern prefix
4. Return the generated string

---

### Controller & Request Layer

#### [NEW] `app/Http/Controllers/Tenant/CompanySettingsController.php`

| Method | Route | Description |
|---|---|---|
| [edit($id)](file:///Users/fahim/Desktop/beayar/beayar-erp/app/Http/Controllers/Tenant/QuotationController.php#130-177) | `GET /user-companies/{id}/settings` | Show settings form |
| [update(Request, $id)](file:///Users/fahim/Desktop/beayar/beayar-erp/app/Http/Controllers/Tenant/QuotationController.php#206-243) | `PUT /user-companies/{id}/settings` | Save settings |
| `getOptions()` | `GET /company-settings/options` | JSON: available currencies, date formats |

#### [NEW] `app/Http/Requests/CompanySettingsRequest.php`

Validation rules:
- `date_format` — `required|in:d-m-Y,Y-m-d,m/d/Y,d M, Y,d/m/Y`
- `currency` — `required|in:USD,EUR,BDT,INR,CNY`
- `currency_symbol` — `required|string|max:5`
- `quotation_prefix` — `nullable|string|max:20`
- `quotation_number_format` — `required|string|max:100`

---

### Routes

#### [MODIFY] [web.php](file:///Users/fahim/Desktop/beayar/beayar-erp/routes/web.php)

Add inside the existing tenant middleware group:

```php
Route::get('/user-companies/{company}/settings', [CompanySettingsController::class, 'edit'])->name('tenant.company-settings.edit');
Route::put('/user-companies/{company}/settings', [CompanySettingsController::class, 'update'])->name('tenant.company-settings.update');
Route::get('/company-settings/options', [CompanySettingsController::class, 'getOptions'])->name('tenant.company-settings.options');
```

---

### Views

#### [MODIFY] [index.blade.php](file:///Users/fahim/Desktop/beayar/beayar-erp/resources/views/tenant/companies/index.blade.php)

- Rename the existing settings-icon button (gear icon, line 92) to route to the new settings page instead of edit
- Add a separate **edit** (pencil icon) button next to it for the existing edit functionality

#### [NEW] `resources/views/tenant/companies/settings.blade.php`

Settings form page with sections for:
- Date Format (dropdown)
- Currency (dropdown with symbol preview)
- Quotation Number Format (text input with tag reference guide)
- Live preview of generated quotation number

---

## Verification Plan

### Automated Tests

#### [NEW] `tests/Feature/Tenant/CompanySettingsTest.php`

Tests (Pest):
1. Owner can view settings page
2. Owner can update settings with valid data
3. Invalid currency code is rejected
4. Invalid date format is rejected
5. Non-owner/non-admin cannot update settings
6. Settings persist after update

#### [NEW] `tests/Unit/Services/CompanySettingsServiceTest.php`

Tests (Pest):
1. `generateQuotationNumber()` with default format produces `{customerNo}-{YY}-{SEQUENCE}`
2. Custom format `QTN-{YYYY}-{SEQUENCE}` produces correct output
3. Custom format `{PREFIX}-{ID}` uses company prefix
4. Sequence increments correctly
5. `getSettings()` merges defaults with saved settings

Run commands:
```bash
php artisan test tests/Feature/Tenant/CompanySettingsTest.php
php artisan test tests/Unit/Services/CompanySettingsServiceTest.php
php artisan test tests/Feature/Tenant/CompanyEditDeleteTest.php
php artisan test --filter=quotation
```

### Final check:
```bash
vendor/bin/pint --dirty
php artisan test
```
