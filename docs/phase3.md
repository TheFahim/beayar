# Phase 3: Eloquent Models & Scoping

## Overview
This phase focused on creating the Object-Relational Mapping (ORM) layer, ensuring data security through Global Scopes, and implementing business logic helpers.

## Key Implementations

### 1. Base Model & Scoping
- **`BaseModel`**: Abstract class that all tenant models extend.
- **`BelongsToCompany` Trait**: Automatically applies a Global Scope to filter queries by `user_company_id`. This is the primary security mechanism for multi-tenancy.
    ```php
    static::addGlobalScope('company', function (Builder $builder) {
        if (auth()->check() && auth()->user()->current_user_company_id) {
            $builder->where('user_company_id', auth()->user()->current_user_company_id);
        }
    });
    ```

### 2. Super Admin Models
- **`Admin`**: Authenticatable model for the admin panel.
- **`PlatformInvoice` / `PlatformPayment`**: Models for platform revenue.
- **`SystemSetting`**: Helper to retrieve global configs.

### 3. Subscription Logic
- **`User` Model**: Added `canPerformAction($metric)` and `recordActionUsage($metric)` helpers.
- **`Subscription` Model**: Logic to check plan limits against `subscription_usage`.
- **`Plan` Model**: Defines base limits and features.

### 4. Core Business Models
- **`Quotation` Hierarchy**:
    - `Quotation` has many `QuotationRevision`.
    - `QuotationRevision` has many `QuotationProduct`.
    - `latestRevision()` relationship helper.
- **`Bill` Hierarchy**:
    - `Bill` belongs to many `Challan` (via `bill_challans`).
    - `Bill` has many `BillItem`.

## Outcomes
- Secure data access by default via Global Scopes.
- Rich relationship methods for navigating the complex schema.
- Built-in subscription enforcement methods on the User model.
