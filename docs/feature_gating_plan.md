# Feature-Gating Plan — Dynamic Admin-Controlled Feature Restrictions

> The admin dynamically controls which features are included in **each plan** (Free, Pro, Pro Plus, Custom, or any future plan). Customers on a given plan are **restricted from / locked out of** features not assigned to that plan. The admin can change feature assignments at any time — no code changes required.

---

## 1. Current State Analysis

### What Already Exists

| Component | Status | Notes |
|---|---|---|
| `plans` table | ✅ Exists | Has `module_access` (JSON) and `limits` (JSON) columns |
| `modules` table | ✅ Exists | Stores add-on modules (inventory, accounting, hrm) with pricing |
| `subscriptions` table | ✅ Exists | Has `module_access` (JSON), `custom_limits` (JSON), and hard-coded limit columns |
| `feature_flags` table | ⚠️ Exists but global | Only has `name`, `is_enabled`, `description` — no per-plan association |
| `Subscription::hasModuleAccess()` | ✅ Exists | Checks if a module slug is in the subscription's `module_access` array |
| `User::hasModuleAccess()` | ✅ Exists | Delegates to subscription |
| `CheckSubscriptionLimits` middleware | ✅ Exists | Checks usage-based limits (quotation count, etc.) |
| Sidebar gating | ✅ Partial | Uses `Auth::user()->hasModuleAccess('quotations')` etc. for some links |

### What's Missing

1. **No granular feature-level gating** — The system only gates at the **module level** (quotations, billing, etc.). It cannot gate a *sub-feature* within a module (e.g., "export PDF" within quotations, or "revisions" within quotations).
2. **No `plan_features` pivot table** — The admin cannot define which features each plan includes via the admin panel.
3. **No `CheckFeatureAccess` middleware** — There is no middleware to block route access based on feature entitlements.
4. **No admin UI for feature management** — Admins can manage modules and plans, but cannot associate specific features to plans.
5. **No Blade directive** — No convenient `@canFeature('feature-slug')` directive for use in templates.

---

## 2. Proposed Architecture

### Core Concept

Introduce a **Feature** entity that the admin creates, edits, and assigns to plans entirely through the admin panel — **no code changes needed**. Each Feature optionally belongs to a Module. The admin **dynamically controls** which features each plan (Free, Pro, Pro Plus, Custom, or any plan created in the future) includes. When a tenant subscribes to a plan, their subscription inherits that plan's features. If the admin later adds or removes a feature from a plan, **all subscribers on that plan are affected immediately**. The system checks feature access at:

- **Route level** → middleware
- **View level** → Blade directive
- **Code level** → helper method on User/Subscription models

```
Plan ──┬── has many Features (via plan_features pivot)
       └── has limits (quotations, companies, employees)

Subscription ──┬── belongs to Plan
               ├── inherits Plan's features
               └── can override with custom feature_access (JSON)

Feature ──┬── belongs to a Module (optional, nullable)
          └── has: slug, name, description, module_id
```

### Feature Granularity Examples

The table below is just an **example default**. The admin can change all of this dynamically at any time.

| Module | Feature Slug | Description | Free | Pro | Pro Plus |
|---|---|---|---|---|---|
| *Core* | `dashboard` | Access to dashboard | ✅ | ✅ | ✅ |
| *Core* | `customers` | Customer management | ✅ | ✅ | ✅ |
| *Core* | `products` | Product catalog | ✅ | ✅ | ✅ |
| Quotations | `quotations.create` | Create quotations | ✅ | ✅ | ✅ |
| Quotations | `quotations.revisions` | Quotation revisions | ❌ | ✅ | ✅ |
| Quotations | `quotations.export_pdf` | Export quotation PDF | ❌ | ✅ | ✅ |
| Billing | `billing.create` | Create bills | ✅ | ✅ | ✅ |
| Billing | `billing.advance` | Advance billing | ❌ | ✅ | ✅ |
| Billing | `billing.running` | Running bills | ❌ | ✅ | ✅ |
| Challans | `challans.create` | Create challans | ✅ | ✅ | ✅ |
| Finance | `finance.dashboard` | Finance overview | ❌ | ✅ | ✅ |
| Received Bills | `received_bills.manage` | Manage received bills | ❌ | ✅ | ✅ |
| Organization | `organization.multi_company` | Multiple companies | ❌ | ✅ | ✅ |
| Organization | `organization.team_members` | Team member management | ✅ (limited) | ✅ | ✅ |
| Images | `images.library` | Image library | ✅ | ✅ | ✅ |
| Brand Origins | `brand_origins.manage` | Brand/origin management | ❌ | ✅ | ✅ |

> **Key point**: The admin creates features and toggles them per plan from the admin panel. If the admin creates a new plan (e.g., "Starter"), they choose which features it includes — the system does not hardcode any plan name or tier.

---

## 3. Database Changes

### 3.1 New `features` Table

```php
Schema::create('features', function (Blueprint $table) {
    $table->id();
    $table->string('name');                  // "Quotation Revisions"
    $table->string('slug')->unique();        // "quotations.revisions"
    $table->text('description')->nullable();
    $table->foreignId('module_id')           // optional parent module
          ->nullable()
          ->constrained('modules')
          ->nullOnDelete();
    $table->boolean('is_active')->default(true);
    $table->integer('sort_order')->default(0);
    $table->timestamps();
});
```

### 3.2 New `plan_features` Pivot Table

```php
Schema::create('plan_features', function (Blueprint $table) {
    $table->id();
    $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
    $table->foreignId('feature_id')->constrained()->cascadeOnDelete();
    $table->json('config')->nullable();      // per-feature config (e.g., limits)
    $table->timestamps();
    $table->unique(['plan_id', 'feature_id']);
});
```

### 3.3 Optional: Add `feature_access` JSON to `subscriptions`

Add a `feature_access` JSON column to `subscriptions` for per-subscription overrides (similar to how `module_access` works today):

```php
// Migration
$table->json('feature_access')->nullable(); // Override plan features per subscription
```

### 3.4 Existing Tables — No Changes Required

- `plans` — keep `module_access` and `limits` as-is for backward compatibility
- `modules` — keep as-is; features will reference modules via `module_id`
- `feature_flags` — keep for global on/off flags (different from plan-based gating)

---

## 4. Backend Implementation

### 4.1 New Model: `Feature`

```
app/Models/Feature.php
```

- `fillable`: name, slug, description, module_id, is_active, sort_order
- Relationships:
  - `module()` → `BelongsTo(Module::class)`
  - `plans()` → `BelongsToMany(Plan::class, 'plan_features')->withPivot('config')`

### 4.2 Update Model: `Module`

Add relationship:

```php
public function features(): HasMany
{
    return $this->hasMany(Feature::class);
}
```

### 4.3 Update Model: `Plan`

Add relationship:

```php
public function features(): BelongsToMany
{
    return $this->belongsToMany(Feature::class, 'plan_features')
                ->withPivot('config')
                ->withTimestamps();
}
```

### 4.4 Update Model: `Subscription`

Add method:

```php
public function hasFeatureAccess(string $featureSlug): bool
{
    // 1. Custom plan gets everything
    if ($this->plan_type === 'custom') {
        return true;
    }

    // 2. Check subscription-level override first
    $featureAccess = $this->feature_access ?? [];
    if (in_array($featureSlug, $featureAccess, true)) {
        return true;
    }

    // 3. Fall back to plan's features
    if ($this->plan) {
        return $this->plan->features()
            ->where('slug', $featureSlug)
            ->where('is_active', true)
            ->exists();
    }

    return false;
}
```

### 4.5 Update Model: `User`

Add helper:

```php
public function hasFeatureAccess(string $feature): bool
{
    if (! $this->subscription) {
        return false;
    }

    return $this->subscription->hasFeatureAccess($feature);
}
```

### 4.6 New Middleware: `CheckFeatureAccess`

```
app/Http/Middleware/CheckFeatureAccess.php
```

```php
public function handle(Request $request, Closure $next, string $feature): Response
{
    $user = $request->user();

    if (! $user || ! $user->hasFeatureAccess($feature)) {
        // For web requests → redirect with error
        // For API requests → return 403 JSON
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Your plan does not include access to this feature.',
                'feature' => $feature,
            ], 403);
        }

        return redirect()->back()->with('error',
            'Your current plan does not include this feature. Please upgrade.'
        );
    }

    return $next($request);
}
```

Register in `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'feature' => \App\Http\Middleware\CheckFeatureAccess::class,
    ]);
})
```

### 4.7 Route Usage

```php
// Protect entire resource
Route::resource('quotations', QuotationController::class)
    ->middleware('feature:quotations.create');

// Protect specific actions
Route::post('/quotations/{quotation}/revisions', ...)->middleware('feature:quotations.revisions');
Route::get('/quotations/{quotation}/export-pdf', ...)->middleware('feature:quotations.export_pdf');
```

### 4.8 Blade Directive (Optional but Recommended)

Register in `AppServiceProvider::boot()`:

```php
Blade::if('feature', function (string $feature) {
    return auth()->check() && auth()->user()->hasFeatureAccess($feature);
});
```

Usage in Blade:

```blade
@feature('quotations.revisions')
    <button>Create Revision</button>
@else
    <button disabled title="Upgrade to Pro">
        🔒 Create Revision (Pro)
    </button>
@endfeature
```

---

## 5. Admin Panel — Dynamic Feature Management

This is the core of the system. The admin has **full dynamic control** — no code deployments needed to change what any plan includes.

### 5.1 New Admin Routes

```php
// Feature CRUD — admin creates/edits/deletes features dynamically
Route::resource('features', AdminFeatureController::class)->names('features');

// Plan → Feature assignment — admin toggles features per plan
Route::put('/plans/{plan}/features', [AdminPlanController::class, 'syncFeatures'])
    ->name('plans.features.sync');
```

### 5.2 Admin Feature Controller

- `index()` — List all features, grouped by module. Admin can **create new features** on the fly (name, slug, module, description).
- `store()` — Create a new feature (e.g., admin adds a "Bulk Import" feature later — no code change needed, just add the feature slug checks to the relevant routes/views).
- `update()` — Edit feature name/slug/module/active status. **Toggling `is_active` to false globally disables** a feature for all plans.
- `destroy()` — Delete feature (with safety check).

### 5.3 Plan ↔ Feature Assignment (The Key Screen)

On the **Plan edit/create page**, add a **checkboxes section** listing all features grouped by module. The admin checks/unchecks features **per plan**. Changes take effect **immediately** for all subscribers on that plan.

```
┌─────────────────────────────────────────────┐
│  Edit Plan: [Free ▼]  [Pro ▼]  [Pro Plus ▼] │ ← Tabs or dropdown to switch plan
├─────────────────────────────────────────────┤
│  Core                                       │
│   ✅ Dashboard                              │
│   ✅ Customers                              │
│   ✅ Products                               │
│                                             │
│  Quotations                                 │
│   ✅ Create Quotation                       │
│   ❌ Quotation Revisions                    │
│   ❌ Export PDF                             │
│                                             │
│  Billing                                    │
│   ✅ Create Bills                           │
│   ❌ Advance Billing                        │
│   ❌ Running Bills                          │
│                                             │
│  [Save Feature Assignments]                 │
└─────────────────────────────────────────────┘
```

> The same screen works for **any plan** — Free, Pro, Pro Plus, Custom, or any new plan the admin creates in the future. The admin simply selects a plan and toggles its features.

### 5.4 How Dynamic Control Works End-to-End

1. **Admin creates a feature** (e.g., "Bulk Import" with slug `products.bulk_import`) via the Features page.
2. **Admin assigns it to plans** — checks the box for Pro and Pro Plus, leaves it unchecked for Free.
3. **Developer adds the middleware/directive** to the relevant route/view using the feature slug: `middleware('feature:products.bulk_import')` or `@feature('products.bulk_import')`.
4. **Done** — Free users see a locked/upgrade prompt, Pro/Pro Plus users have access.
5. **Later**: Admin decides to include it in Free too → just checks the box on the admin panel. No code deployment needed.

---

## 6. Frontend Integration

### 6.1 Sidebar Updates

Replace current `hasModuleAccess()` calls with `hasFeatureAccess()` for more granular control:

```blade
{{-- Before --}}
@if(Auth::user()->hasModuleAccess('quotations'))

{{-- After --}}
@feature('quotations.create')
```

### 6.2 In-Page Feature Gating

For features within a page (like buttons, tabs, sections), use the Blade directive:

```blade
@feature('billing.advance')
    <a href="{{ route('tenant.quotations.bills.advance.store', ...) }}">
        Create Advance Bill
    </a>
@else
    <span class="text-gray-400 cursor-not-allowed" title="Upgrade your plan">
        🔒 Advance Bill (Pro)
    </span>
@endfeature
```

### 6.3 Upgrade Prompts

When a feature is locked, show a clear upgrade prompt instead of hiding the element completely. This creates upsell opportunities:

```blade
@feature('quotations.revisions')
    {{-- full feature UI --}}
@else
    <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
        <p class="text-sm text-yellow-800">
            📌 Quotation Revisions are available on Pro and above.
        </p>
        <a href="{{ route('tenant.subscription.index') }}" class="text-blue-600 underline text-sm">
            Upgrade your plan →
        </a>
    </div>
@endfeature
```

---

## 7. Seeder for Initial Features

Create a `FeaturesSeeder` that seeds the initial features based on current app capabilities:

```php
$features = [
    // Core features (available on all plans including free)
    ['name' => 'Dashboard',           'slug' => 'dashboard',             'module_id' => null],
    ['name' => 'Customer Management', 'slug' => 'customers.manage',      'module_id' => null],
    ['name' => 'Product Catalog',     'slug' => 'products.manage',       'module_id' => null],
    ['name' => 'Image Library',       'slug' => 'images.library',        'module_id' => null],

    // Quotation features
    ['name' => 'Create Quotation',    'slug' => 'quotations.create',     'module_id' => $quotationsModule],
    ['name' => 'Edit Quotation',      'slug' => 'quotations.edit',       'module_id' => $quotationsModule],
    ['name' => 'Quotation Revisions', 'slug' => 'quotations.revisions',  'module_id' => $quotationsModule],
    ['name' => 'Export Quotation',    'slug' => 'quotations.export',     'module_id' => $quotationsModule],

    // Billing features
    ['name' => 'Create Bill',         'slug' => 'billing.create',        'module_id' => $billingModule],
    ['name' => 'Advance Billing',     'slug' => 'billing.advance',       'module_id' => $billingModule],
    ['name' => 'Running Bills',       'slug' => 'billing.running',       'module_id' => $billingModule],

    // Challan features
    ['name' => 'Challan Management',  'slug' => 'challans.manage',       'module_id' => $challansModule],

    // Finance
    ['name' => 'Finance Dashboard',   'slug' => 'finance.dashboard',     'module_id' => $financeModule],

    // Received Bills
    ['name' => 'Received Bills',      'slug' => 'received_bills.manage', 'module_id' => null],

    // Organization
    ['name' => 'Multiple Companies',  'slug' => 'organization.multi_company',  'module_id' => null],
    ['name' => 'Team Members',        'slug' => 'organization.team_members',   'module_id' => null],
    ['name' => 'Brand Origins',       'slug' => 'brand_origins.manage',        'module_id' => null],
];
```

Then associate features to plans via `PlanFeaturesSeeder`.

---

## 8. Migration Strategy (Zero Downtime)

Since the system already works with `module_access`, the migration can be **additive**:

1. **Phase 1** — Create `features` and `plan_features` tables + models + seeder. Existing `module_access` logic continues to work.
2. **Phase 2** — Add `hasFeatureAccess()` to models. Add the `CheckFeatureAccess` middleware. Add `@feature` Blade directive. Update routes and views to use feature-level checks alongside existing module checks.
3. **Phase 3** — Build admin UI for feature management (Feature CRUD + Plan↔Feature assignment).
4. **Phase 4** — Gradually migrate sidebar/views from `hasModuleAccess()` to `@feature()`. The old `hasModuleAccess()` can remain as a fallback.

---

## 9. Performance Considerations

- **Cache plan features**: Cache the plan's feature slugs per subscription (invalidate on plan/subscription update). Use `Cache::rememberForever("plan:{$planId}:features", ...)`.
- **Eager load**: When loading User/Subscription, eager-load `plan.features` to avoid N+1.
- **Flat array check**: For fastest lookup, store feature slugs as a flat array in a cached property on the Subscription model.

---

## 10. Summary of Files to Create/Modify

| Action | File | Description |
|---|---|---|
| **CREATE** | `database/migrations/..._create_features_table.php` | Features table |
| **CREATE** | `database/migrations/..._create_plan_features_table.php` | Pivot table |
| **CREATE** | `database/migrations/..._add_feature_access_to_subscriptions.php` | JSON override column |
| **CREATE** | `app/Models/Feature.php` | Feature model |
| **CREATE** | `app/Http/Middleware/CheckFeatureAccess.php` | Route middleware |
| **CREATE** | `app/Http/Controllers/Admin/FeatureController.php` | Admin CRUD |
| **CREATE** | `database/seeders/FeaturesSeeder.php` | Initial features |
| **CREATE** | `database/seeders/PlanFeaturesSeeder.php` | Feature↔Plan mapping |
| **CREATE** | `resources/views/admin/features/index.blade.php` | Admin UI |
| **MODIFY** | `app/Models/Plan.php` | Add `features()` relationship |
| **MODIFY** | `app/Models/Module.php` | Add `features()` relationship |
| **MODIFY** | `app/Models/Subscription.php` | Add `hasFeatureAccess()` |
| **MODIFY** | `app/Models/User.php` | Add `hasFeatureAccess()` |
| **MODIFY** | `bootstrap/app.php` | Register `feature` middleware alias |
| **MODIFY** | `app/Providers/AppServiceProvider.php` | Register `@feature` Blade directive |
| **MODIFY** | `routes/web.php` | Add admin feature routes + apply middleware |
| **MODIFY** | `resources/views/components/dashboard/common/sidebar.blade.php` | Use `@feature` directive |
| **MODIFY** | `resources/views/admin/plans/index.blade.php` | Feature checkboxes in plan form |

---

## 11. Decision Points for You

> [!IMPORTANT]
> These decisions affect the scope and behavior of the system. Please confirm before implementation.

1. **Approach: Hide vs Lock?** — Should locked features be **completely hidden** from the UI, or **visible but disabled** with an upgrade prompt? (Recommended: visible + locked with upgrade CTA)

2. **Backward compatibility** — Should `hasModuleAccess()` continue to work alongside `hasFeatureAccess()`, or should module_access be fully replaced by features? (Recommended: keep both, migrate gradually)

3. **Feature scope** — Do you want me to seed initial features based on the current app, or leave it empty for the admin to create manually? (Recommended: seed initial features, admin adds/removes later)

4. **Subscription-level overrides** — Should individual subscriptions be able to override plan features (e.g., grant a specific customer access to a Pro feature while on a cheaper plan)? (Recommended: yes, via `feature_access` JSON column)

5. **Cache invalidation strategy** — When the admin changes feature assignments for a plan, should it take effect immediately (cache bust) or after a short delay? (Recommended: immediately, with event-driven cache invalidation)
