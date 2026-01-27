# Complete Application Merger Implementation Plan
## Optimech + Wesum → Unified ERP System

> **📋 Target Audience:** Development Team
> **⏱️ Estimated Duration:** 7-8 Weeks
> **🎯 Goal:** Merge two Laravel billing applications into one unified system with Multi-Tenancy & Subscriptions.

---

## Table of Contents
1. [Project Overview](#1-project-overview)
2. [Architecture Overview](#2-architecture-overview)
3. [Pre-Requisites Checklist](#3-pre-requisites-checklist)
4. [Phase 1: Project Setup (Week 1, Days 1-2)](#phase-1-project-setup)
5. [Phase 2: Database Migrations (Week 1, Days 3-5)](#phase-2-database-migrations)
6. [Phase 3: Eloquent Models (Week 2)](#phase-3-eloquent-models)
7. [Phase 4: Service Layer (Week 3)](#phase-4-service-layer)
8. [Phase 5: API Routes & Controllers (Week 4)](#phase-5-api-routes--controllers)
9. [Phase 6: Frontend Merge (Week 5)](#phase-6-frontend-merge)
10. [Phase 7: Data Migration Scripts (Week 6)](#phase-7-data-migration-scripts)
11. [Phase 8: Testing & Verification (Week 7)](#phase-8-testing--verification)

---

## 1. Project Overview

### Key Architectural Decisions
1.  **Base System:** Optimech (more sophisticated structure)
2.  **Added Features:** Wesum's expense management, sale targets, enhanced payments
3.  **Database Strategy:** Single Database with `tenant_id` (company_id) for multi-tenancy
4.  **Status Management:** Use reference table instead of enums for flexibility
5.  **Subscription Model:** 4-tier pricing (Free, Pro, ProPlus, Custom) with dynamic pricing calculator

---

## 2. Architecture Overview

### 2.1 Multi-Tenancy Architecture
> **Strategy:** Single Database with `user_company_id` column on all tenant-scoped tables.

#### User Hierarchy Rules:
| Level | Entity | Description |
|-------|--------|-------------|
| 1 | **User** | Individual account holder, belongs to a User Company |
| 2 | **User Company** | Primary business entity owned by a User (The Tenant) |
| 3 | **Customer Company** | Company belonging to a User's Customer (under User Company) |

#### Data Scoping:
- **Personal Scope:** User sees only their individual data.
- **Company Scope:** User sees all data belonging to the active `user_company`.
- **Scope Toggle:** Stored in session, allows switching between `user_companies`.

### 2.2 Subscription & Billing Model
> **Business Requirement:** 4-tier subscription system with resource limits.

#### Plan Comparison:
| Feature | Free | Pro | Pro Plus | Custom |
|---------|------|-----|----------|--------|
| **Sub-Companies** | 1 | 5 | 15 | User-defined |
| **Monthly Quotations** | 20 | 100 | Unlimited | User-defined |
| **Employees** | 3 | 10 | 50 | User-defined |
| **Extra Modules** | ❌ | Basic | Advanced | Selectable |
| **Pricing** | Free | $29/mo | $79/mo | Calculated |

**Dynamic Pricing Formula:**
`Base Price + (Sub-Companies × $5) + (Employees × $2) + (Quotations tier) + (Modules)`

### 2.3 Coupon System Architecture
> **Business Requirement:** Flexible coupon system with unique and campaign-based discounts.

#### Coupon Types:
| Type | Description | Usage | Target |
|------|-------------|--------|---------|
| **Unique Coupon** | Single-use, customer-specific | One customer only | Specific clients |
| **Campaign Coupon** | Multi-use, common code | All eligible users | Broad campaigns |

#### Coupon Features:
- **Discount Types:** Percentage-based or fixed amount
- **Expenditure Limits:** Min/max spend requirements
- **Redemption Methods:** URL claim + code entry
- **Usage Rules:** One coupon per customer, tracked usage
- **Eligibility:** Based on customer expenditure history

### 2.4 Super Admin Architecture (New)
> **Goal:** Centralized platform management for the "Beayar" owners.

#### Admin Roles:
1.  **Super Admin:** Full access to system settings, payments, and tenant data.
2.  **Support:** Read-only access to tenant data for troubleshooting.

#### Admin Capabilities:
-   **Tenant Management:** View all User Companies, login as tenant (impersonate), suspend accounts.
-   **Platform Billing:** Separate `platform_invoices` and `platform_payments` tables to track subscription revenue.
-   **Global Coupons:** Create system-wide coupons applicable to `platform_invoices`.
-   **Revenue Dashboard:** View total MRR/ARR from Platform Billing data.

### 2.5 Payment Gateway Architecture
> **Provider:** Stripe / Paddle (TBD)

#### Flow:
1.  **Plan Selection:** User selects Plan in frontend.
2.  **Checkout:** Redirected to Payment Provider.
3.  **Webhook:** Provider notifies `webhook-endpoint`.
4.  **Fulfillment:** System creates/renews `Subscription` record and logs `PlatformPayment`.
5.  **Coupons:** `coupon_usage` is now linked to `platform_invoices` (not tenant bills).

---

## 3. Pre-Requisites Checklist
- [x] PHP 8.2+, Composer 2.x, Node.js 18+
- [x] MySQL 8.0+
- [x] Source databases backed up (`/database-backup/wesum_backup.sql`, `/database-backup/optimech_backup.sql`)

---

## Phase 1: Project Setup
### Week 1, Days 1-2

1.  **Initialize Project:**
    *   `composer create-project laravel/laravel unified-erp`
    *   Configure `.env` with main DB and source DB connections.
2.  **Install Packages:**
    *   `spatie/laravel-permission` (RBAC)
    *   `intervention/image` (Image handling)
    *   `spatie/laravel-activitylog` (Audit trails)
    *   `pestphp/pest` (Testing)
3.  **Frontend Setup:**
    *   Install Tailwind, Flowbite, Alpine.js, ApexCharts.
    *   Configure Vite.

---

## Phase 2: Database Migrations
### Week 1, Days 3-5

#### Group 1: Foundation & Multi-Tenancy
1.  **Users:** Add `current_user_company_id`, `current_scope`.
2.  **UserCompanies:** `owner_id`, `parent_company_id`, `bin_no`.
3.  **CustomerCompanies:** `user_company_id`, `name`, `address`.
4.  **Plans:** `name`, `limits` (JSON), `base_price`, `billing_cycle`.
5.  **Modules:** `slug`, `price`.
6.  **Subscriptions:** `user_id`, `plan_id`, `custom_limits` (JSON), `status`, `dates`.
7.  **SubscriptionUsage:** `subscription_id`, `metric`, `used`, `limit`.

#### Group 2: Core Business Tables
8.  **Customers:** Add `customer_company_id` (Client Scoped).
9.  **Products:** Global catalog per company (`user_company_id`).
10. **Quotations:** `customer_id`, `user_id`, `user_company_id`, `status_id`, `po_no`, `ship_to`.
11. **QuotationRevisions:** `quotation_id`, `revision_no`, `currency`, `subtotal`, `total`, `terms`.
12. **QuotationProducts:** Line items (linked to `quotation_revisions`).

#### Group 3: Finance & Billing
13. **Challans:** Delivery tracking.
14. **Bills:** `bill_type` (advance, regular, running), `parent_bill_id`, `invoice_no`, `user_company_id`.
15. **BillChallans:** Link Bills to Challans (Many-to-Many).
16. **BillItems:** Line items linked to `challan_products` for granular tracking.
17. **ReceivedBills:** Track partial payments against bills.
18. **Payments:** Enhanced tracking from Wesum (`company_id`).
19. **Expenses:** Categories and tracking (`company_id`).
16. **SaleTargets:** Per user/month (`company_id`).

#### Group 4: System
17. **WorkflowSettings:** Toggle features per company.
18. **ActivityLogs:** Audit trail.
19. **Notifications:** System alerts.

#### Group 5: Coupon System (New Feature)
20. **Coupons:** Core coupon table with type, discount, limits.
21. **CouponUsage:** Track customer usage (one-time per customer).
22. **CouponLimits:** Min/max expenditure requirements.
23. **CouponCustomers:** Link unique coupons to specific customers.
24. **CouponCampaigns:** Manage campaign-based common coupons.

---

## Phase 3: Eloquent Models
### Week 2

1.  **Super Admin Models (New):**
    *   `Admin`: Authenticatable for admin panel.
    *   `PlatformInvoice`, `PlatformPayment`: Admin-side billing.
    *   `SystemSetting`: Global configurations.
2.  **Base Model:**
    *   Create `App\Models\BaseModel`.
    *   Implement `BelongsToCompany` trait (Global Scope).
3.  **Subscription Models:**
    *   `Plan`, `Module`, `Subscription`, `SubscriptionUsage`.
    *   Implement logic to check limits (`canCreate('quotation')`).
4.  **Core Models:**
    *   Implement relationships (e.g., UserCompany has many CustomerCompanies).
    *   Add `boot` methods for auto-generating IDs (e.g., "QT-2024-001").
    *   **Billing Hierarchy:** `Bill` -> `BillChallan` -> `BillItem`.
    *   **Quotation Hierarchy:** `Quotation` -> `QuotationRevision` -> `QuotationProduct`.

---

## Phase 4: Service Layer
### Week 3

1.  **Super Admin & Platform Services:**
    *   **`PlatformBillingService`**: Generates `PlatformInvoice` for subscriptions, handles `PlatformPayment` recording.
    *   **`AdminService`**: Admin authentication and tenant management (impersonation, suspension).
    *   **`SystemSettingService`**: Global configuration management.

2.  **Subscription Engine:**
    *   **`SubscriptionService`**: Core logic for `checkLimit()`, `recordUsage()`, and feature gating.
    *   **`PlanManagerService`**: Handles upgrades/downgrades, calls `PlatformBillingService` to generate invoices.

3.  **Tenant Business Logic:**
    *   **`QuotationService`**: Manages `Quotation` lifecycle, `Revision` cloning, and `QuotationProduct` syncing.
    *   **`TenantBillingService`**: Implements Optimech's logic for "Advance" vs "Running" bills, manages `BillChallan` links.
    *   **`FinancialService`**: Aggregates `Expense` and `Payment` data for tenant dashboards.

4.  **Coupon System:**
    *   **`CouponService`**: Validates coupons and applies discounts to `PlatformInvoice` (not tenant bills).

5.  **Middleware:**
    *   `TenantScope`: Apply company context to requests.
    *   `CheckSubscription`: Block actions if limits exceeded.
    *   `AdminAuth`: Protect admin routes.

---

## Phase 5: API Routes & Controllers
### Week 4

1.  **API Structure:**
    *   **Admin API:** Prefix `/api/admin`, Middleware `['auth:sanctum', 'admin.auth']`.
    *   **Tenant API:** Prefix `/api/v1`, Middleware `['auth:sanctum', 'tenant.scope']`.
    *   **Validation:** Dedicated `FormRequest` classes for all write operations.

2.  **Admin Controllers:**
    *   `AdminAuthController`: Admin login/logout.
    *   `TenantManagementController`: Tenant listing, suspension, and impersonation.
    *   `PlatformRevenueController`: Platform-wide financial analytics.
    *   `GlobalCouponController`: Management of global marketing campaigns.

3.  **Tenant Controllers:**
    *   `SubscriptionController`: Plan listing and purchasing logic.
    *   `CompanyController`: Management of customer sub-companies.
    *   `QuotationController`: Full CRUD, Revision management, PDF generation.
    *   `BillController`: Invoicing and linking with Challans.
    *   `FinanceController`: Tenant-level dashboard stats (Expenses, Payments).
    *   `CouponController`: Coupon validation and redemption.

---

## Phase 6: Frontend Implementation
### Week 5

1.  **Super Admin UI:**
    *   **Dashboard:** Platform-wide metrics (MRR, Tenant Growth).
    *   **Tenant Management:** List view, Details view, Suspension controls, Impersonation button.
    *   **Plan Management:** UI to edit plan features and pricing.
    *   **Global Coupons:** Interface for creating marketing campaigns.

2.  **Core Tenant UI:**
    *   **Authentication:** Login, Register, Password Reset.
    *   **Layouts:** App Shell with Sidebar navigation and Company Switcher.
    *   **Subscription:** "Build Your Plan" interactive UI for upgrades/downgrades.
    *   **Dashboard:** Tenant-specific widgets (Revenue, Quota usage, Targets).

3.  **Business Logic UI:**
    *   **Quotation Builder:** Multi-step wizard (Customer -> Items -> Review -> Send).
    *   **Billing Manager:** Invoice list, Status tracking, Payment recording modal.
    *   **Finance & Reports:** Charts for expenses vs. income, detailed data tables.
    *   **Settings:** Company profile, Sub-company management, User roles.

---

## Phase 7: Data Migration Scripts
### Week 6

1.  **Wesum Migration:**
    *   Map simple quotations to `Quotations` + `Revision R1`.
    *   Assign to default "Free" plan or "Custom" based on usage.
2.  **Optimech Migration:**
    *   Map complex revisions directly.
    *   Migrate company hierarchy.
3.  **Verification:**
    *   Check total revenue matches source.
    *   Check customer counts match.

---

## Phase 8: Testing & Verification
### Week 7

1.  **Unit Tests:**
    *   Test dynamic pricing calculator.
    *   Test subscription limit enforcement.
2.  **Feature Tests:**
    *   Full quotation-to-bill flow.
    *   Tenant isolation (User A cannot see User B's data).
3.  **Security Audit:**
    *   Verify API endpoints enforce `company_id` checks.
