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
- **`AdminAuthController`**: Admin login/logout.
- **`TenantManagementController`**: Tenant listing, suspension, and impersonation.
- **`PlatformRevenueController`**: Platform-wide financial analytics.
- **`GlobalCouponController`**: Management of global marketing campaigns.

### 2. Tenant API (`App\Http\Controllers\Api\V1`)
- **`SubscriptionController`**: Plan listing and purchasing logic.
- **`CompanyController`**: Management of customer sub-companies.
- **`ImageController`**: 
    - Image library management (upload, list, search).
    - Multi-tenant directory isolation.
    - Image compression (GD Library) and optimization.
- **`QuotationController`**:
    - Full CRUD for Quotations.
    - Revision management (`POST /{id}/revisions`).
    - PDF generation (`GET /{id}/pdf`).
- **`BillController`**: Invoicing and linking with Challans.
- **`FinanceController`**: Tenant-level dashboard stats (Expenses, Payments).
- **`CouponController`**: Coupon validation and redemption.

## API Structure
- **Versioning**: V1 namespace used for future-proofing.
- **Resource Classes**: Used standard Laravel API Resources (implied in controller logic) for consistent JSON responses.
- **Error Handling**: Validation errors return standard 422 Unprocessable Entity responses.

## Completion Status
- All Phase 5 tasks marked as complete in `task.md`.
- Routes verified via `php artisan route:list`.
