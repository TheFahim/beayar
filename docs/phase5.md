# Phase 5: API & Controllers

## Overview
This phase exposed the business logic via a RESTful API, secured with Laravel Sanctum and organized into Admin and Tenant namespaces.

## Infrastructure
- **Laravel Sanctum**: Installed and configured for API token authentication.
- **Route Groups**:
    - `/api/admin`: For Super Admins (Protected by `admin.auth`).
    - `/api/v1`: For Tenants (Protected by `tenant.scope`).
- **FormRequests**: Created dedicated validation classes for all inputs (e.g., `QuotationCreateRequest`, `CompanyCreateRequest`).

## Controllers

### 1. Admin API (`App\Http\Controllers\Admin`)
- **`AdminAuthController`**: Login, Logout, Me.
- **`TenantManagementController`**:
    - List all tenants.
    - Suspend tenants.
    - **Impersonate**: Returns a token to act as the tenant owner.
- **`PlatformRevenueController`**: Analytics on platform earnings.
- **`GlobalCouponController`**: Manage system-wide marketing campaigns.

### 2. Tenant API (`App\Http\Controllers\Api\V1`)
- **`SubscriptionController`**:
    - List Plans.
    - Purchase/Upgrade Subscription.
- **`CompanyController`**: Manage customer sub-companies.
- **`QuotationController`**:
    - CRUD for Quotations.
    - `POST /{id}/revisions`: Create a new version of a quote.
    - `GET /{id}/pdf`: (Placeholder) Generate PDF.
- **`BillController`**: Manage invoices and billing status.
- **`FinanceController`**: Dashboard analytics (Expenses vs Income).
- **`CouponController`**: Validate coupons during checkout.

## API Structure
- **Versioning**: V1 namespace used for future-proofing.
- **Resource Classes**: Used standard Laravel API Resources (implied in controller logic) for consistent JSON responses.
- **Error Handling**: Validation errors return standard 422 Unprocessable Entity responses.

## Completion Status
- All Phase 5 tasks marked as complete in `task.md`.
- Routes verified via `php artisan route:list`.
