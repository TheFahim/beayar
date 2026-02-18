
# 🚀 Dynamic SaaS Subscription & Feature Management Implementation Guide

This document outlines the roadmap for implementing a dynamic subscription system where Admins can control features and limits from the control panel without changing code.

## 📋 Table of Contents
1. [Core Concept](#1-core-concept)
2. [Database Schema Overview](#2-database-schema-overview)
3. [Phase 1: Backend Foundation (Enums & Traits)](#3-phase-1-backend-foundation)
4. [Phase 2: Logic Layer (Service Manager)](#4-phase-2-logic-layer-service-manager)
5. [Phase 3: Admin Panel (Configuration UI)](#5-phase-3-admin-panel-configuration-ui)
6. [Phase 4: Enforcement (Middleware & Guards)](#6-phase-4-enforcement-middleware--guards)
7. [Phase 5: Frontend Integration (UX)](#7-phase-5-frontend-integration-ux)

---

## 1. Core Concept

The system distinguishes between two types of features:
1.  **Boolean Features (Modules):** Access is either `true` or `false` (e.g., "Inventory Module", "API Access").
2.  **Quota Features (Limits):** Access depends on a numeric count (e.g., "Max Users: 10", "Max Invoices: 100").

**The Flow:**
`Admin defines Plan` -> `Sets Features & Limits (saved in JSON)` -> `User Subscribes` -> `System Checks Usage vs Limit` -> `Allow/Deny Action`.

---

## 2. Database Schema Overview

We utilize the following existing tables:
*   **`plans`**: Contains base plan info.
*   **`features`**: Global list of all system capabilities (Name, Slug).
*   **`plan_features`**: **(CRITICAL)** Pivot table. Links Plans to Features.
    *   `config` column (JSON): Stores the specific limit for that plan (e.g., `{"limit": 50}`).
*   **`subscriptions`**: Links a Tenant to a Plan.
    *   `custom_limits` (JSON): Optional override for specific clients.
*   **`subscription_usage`**: Tracks real-time consumption (e.g., `metric: 'users', used: 5`).

---

## 3. Phase 1: Backend Foundation

### A. Define Feature Enums
Create `app/Enums/FeatureEnum.php` to avoid magic strings.

```php
namespace App\Enums;

enum FeatureEnum: string
{
    // Modules (Yes/No)
    case MODULE_INVENTORY = 'module_inventory';
    case MODULE_ACCOUNTS = 'module_accounts';

    // Limits (Numeric)
    case LIMIT_USERS = 'limit_users';
    case LIMIT_INVOICES = 'limit_invoices';
}
```

### B. The Logic Trait
Create `app/Traits/HasSubscriptionFeatures.php`. Use this in your **TenantCompany** model.

```php
trait HasSubscriptionFeatures
{
    /**
     * Check if tenant has access to a module (Boolean)
     */
    public function hasFeature(string $featureSlug): bool
    {
        // 1. Check Subscription Overrides (if any)
        if ($this->subscription && isset($this->subscription->feature_access[$featureSlug])) {
            return $this->subscription->feature_access[$featureSlug];
        }

        // 2. Check Plan Features (Cache this query for performance)
        return $this->subscription->plan->features->contains('slug', $featureSlug);
    }

    /**
     * Get the numeric limit for a feature
     * Returns -1 for unlimited, 0 for none, or the integer limit.
     */
    public function getFeatureLimit(string $featureSlug): int
    {
        // 1. Check Subscription Custom Limits
        if ($this->subscription && isset($this->subscription->custom_limits[$featureSlug])) {
            return (int) $this->subscription->custom_limits[$featureSlug];
        }

        // 2. Check Plan Configuration (pivot table)
        $feature = $this->subscription->plan->features()
                    ->where('slug', $featureSlug)->first();

        if ($feature && isset($feature->pivot->config['limit'])) {
            return (int) $feature->pivot->config['limit'];
        }

        return 0; // Default: No access
    }
}
```

---

## 4. Phase 2: Logic Layer (Service Manager)

Create `app/Services/SubscriptionManager.php`. This acts as the "Gatekeeper".

```php
class SubscriptionManager
{
    public function canConsume(TenantCompany $tenant, string $featureSlug, int $amount = 1): bool
    {
        $limit = $tenant->getFeatureLimit($featureSlug);

        // -1 represents Unlimited
        if ($limit === -1) return true;

        $usage = $tenant->subscription->usages()
                    ->firstOrCreate(['metric' => $featureSlug], ['used' => 0]);

        return ($usage->used + $amount) <= $limit;
    }

    public function consume(TenantCompany $tenant, string $featureSlug, int $amount = 1): void
    {
        if (!$this->canConsume($tenant, $featureSlug, $amount)) {
            throw new \Exception("Limit reached for {$featureSlug}");
        }

        $tenant->subscription->usages()
            ->where('metric', $featureSlug)
            ->increment('used', $amount);
    }

    public function reduce(TenantCompany $tenant, string $featureSlug, int $amount = 1): void
    {
        $tenant->subscription->usages()
            ->where('metric', $featureSlug)
            ->decrement('used', $amount);
    }
}
```

---

## 5. Phase 3: Admin Panel (Configuration UI)

This is where the Admin configures the logic dynamically.

### 1. Feature Management (CRUD)
*   **Page:** `/admin/features`
*   **Action:** Create features.
    *   *Input:* Name (e.g., "Max Users"), Slug (e.g., `limit_users`), Type (Boolean/Limit).

### 2. Plan Management (The Key Setup)
*   **Page:** `/admin/plans/create` or `/edit`
*   **UI Logic:**
    1.  Show list of all Features with Checkboxes.
    2.  If a feature is checked:
        *   If Type is **Boolean**: No extra input needed.
        *   If Type is **Limit**: Show an input box for "Quantity" (e.g., 10, 50, -1).
    3.  **On Save (Controller):**
        *   Construct the sync data for the pivot table.
        ```php
        // Example structure sent to sync()
        [
            1 => ['config' => json_encode(['limit' => 10])], // Feature ID 1 (Users)
            2 => ['config' => null],                         // Feature ID 2 (Inventory Module)
        ]
        ```

---

## 6. Phase 4: Enforcement (Middleware & Guards)

### A. Route Protection (Modules)
Create `EnsureFeatureAccess` middleware for entire routes.

```php
// Kernel.php or Route file
Route::middleware(['auth', 'feature:module_inventory'])->group(function () {
    Route::resource('products', ProductController::class);
});

// Middleware Logic
public function handle($request, Closure $next, $feature)
{
    if (!auth()->user()->currentTenant->hasFeature($feature)) {
        return redirect()->route('upgrade')->with('error', 'Module not included in plan.');
    }
    return $next($request);
}
```

### B. Action Protection (Limits)
In your Controllers (e.g., `UserController@store`).

```php
public function store(Request $request, SubscriptionManager $subManager)
{
    $tenant = auth()->user()->currentTenant;

    if (!$subManager->canConsume($tenant, FeatureEnum::LIMIT_USERS->value)) {
        return back()->with('error', 'User limit reached. Please upgrade your plan.');
    }

    // Create User...
    User::create($data);

    // Increment Usage
    $subManager->consume($tenant, FeatureEnum::LIMIT_USERS->value);
}
```

---

## 7. Phase 5: Frontend Integration (UX)

### A. Hide/Show Menu Items
In Blade/Vue/React, check permissions before rendering sidebar links.

```blade
{{-- Blade Example --}}
@if(auth()->user()->currentTenant->hasFeature('module_inventory'))
    <li><a href="/products">Inventory</a></li>
@endif
```

### B. Usage Progress Bars
Show the user how much they have consumed.

```blade
@php
    $limit = $tenant->getFeatureLimit('limit_users');
    $used = $tenant->subscription->usage('limit_users')->used ?? 0;
    $percent = ($limit > 0) ? ($used / $limit) * 100 : 0;
@endphp

<div class="usage-card">
    <p>Users: {{ $used }} / {{ $limit == -1 ? '∞' : $limit }}</p>
    <div class="progress-bar" style="width: {{ $percent }}%"></div>
    @if($used >= $limit && $limit != -1)
        <a href="/billing" class="btn-upgrade">Upgrade to add more</a>
    @endif
</div>
```

---

## Summary Checklist

- [ ] Create `FeatureEnum`.
- [ ] Implement `HasSubscriptionFeatures` Trait in Tenant Model.
- [ ] Create `SubscriptionManager` Service.
- [ ] Build Admin UI to sync features to plans with JSON config.
- [ ] Apply Middleware to Routes (`module_` features).
- [ ] Apply Service Checks in Controllers (`limit_` features).
- [ ] Add Frontend visual cues (menus/progress bars).

```
