# Phase 4: Service Layer & Logic

## Overview
This phase implemented the business logic in a dedicated Service Layer, keeping Controllers slim and logic reusable. It also introduced critical Middleware.

## Services Implemented

### 1. Super Admin Services
- **`AdminService`**: Handles admin authentication and the complex "Impersonate Tenant" feature, allowing admins to log in as any user.
- **`PlatformBillingService`**: Generates `PlatformInvoice` records for subscription renewals and records payments.
- **`SystemSettingService`**: Manages global key-value configurations.

### 2. Subscription Engine
- **`SubscriptionService`**: The core enforcement engine. Checks limits (e.g., "max_users") and records usage metrics.
- **`PlanManagerService`**: Handles logic for upgrading/downgrading plans and calculating pro-rated charges (placeholder logic).

### 3. Tenant Business Logic
- **`QuotationService`**:
    - Creates initial quotations.
    - Handles **Revisions**: Clones the previous revision and its products to create a new version.
- **`TenantBillingService`**:
    - Manages bill generation logic.
    - Links multiple Challans to a single Bill.
- **`FinancialService`**: Aggregates data for the tenant dashboard (Revenue, Expenses).
- **`CouponService`**: Validates coupons for platform subscription discounts.

## Middleware
- **`TenantScope`**: Ensures every request has a valid `user_company_id` context.
- **`CheckSubscriptionLimits`**: Intercepts requests to ensure the tenant hasn't exceeded their plan limits before performing actions (e.g., creating a quote).
- **`AdminAuth`**: Protects super admin routes.

## Key Code Patterns
- **Dependency Injection**: Services are injected into Controllers.
- **Transactional Operations**: Critical actions (like creating a quotation with products) are wrapped in DB transactions.
