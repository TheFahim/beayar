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
> **Strategy:** Single Database with `tenant_id` column on all tenant-scoped tables.

#### User Hierarchy Rules:
| Level | Entity | Description |
|-------|--------|-------------|
| 1 | **User** | Individual account holder, can operate in Personal or Company scope |
| 2 | **Company** | Primary business entity owned by a User |
| 3 | **Sub-Company** | Branch/Division under a Company (counts against subscription limit) |

#### Data Scoping:
- **Personal Scope:** User sees only their individual data.
- **Company Scope:** User sees all data belonging to the active company + its sub-companies.
- **Scope Toggle:** Stored in session, allows switching between Personal/Company views.

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

---

## 3. Pre-Requisites Checklist
- [ ] PHP 8.2+, Composer 2.x, Node.js 18+
- [ ] MySQL 8.0+
- [ ] Source databases backed up (`/database-backup/wesum_backup.sql`, `/database-backup/optimech_backup.sql`)

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
1.  **Users:** Add `current_company_id`, `current_scope`.
2.  **Companies:** `owner_id`, `parent_company_id`, `bin_no`.
3.  **Plans:** `name`, `limits` (JSON), `base_price`, `billing_cycle`.
4.  **Modules:** `slug`, `price`.
5.  **Subscriptions:** `user_id`, `plan_id`, `custom_limits` (JSON), `status`, `dates`.
6.  **SubscriptionUsage:** `subscription_id`, `metric`, `used`, `limit`.

#### Group 2: Core Business Tables
7.  **Customers:** Add `company_id` (Tenant Scoped).
8.  **Products:** Global catalog per company (`company_id`).
9.  **Quotations:** `customer_id`, `user_id`, `company_id`, `status_id`.
10. **QuotationRevisions:** Versioning system from Optimech.
11. **QuotationProducts:** Line items.

#### Group 3: Finance & Billing
12. **Challans:** Delivery tracking.
13. **Bills:** `bill_type` (advance, regular, running, final), `company_id`.
14. **Payments:** Enhanced tracking from Wesum (`company_id`).
15. **Expenses:** Categories and tracking (`company_id`).
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

1.  **Base Model:**
    *   Create `App\Models\BaseModel`.
    *   Implement `BelongsToCompany` trait (Global Scope).
2.  **Subscription Models:**
    *   `Plan`, `Module`, `Subscription`, `SubscriptionUsage`.
    *   Implement logic to check limits (`canCreate('quotation')`).
3.  **Core Models:**
    *   Implement relationships (e.g., Company has many Customers).
    *   Add `boot` methods for auto-generating IDs (e.g., "QT-2024-001").

---

## Phase 4: Service Layer
### Week 3

1.  **SubscriptionService:**
    *   `calculatePrice(array $features)`: Dynamic pricing logic.
    *   `checkLimit(User $user, string $metric)`: Boolean check.
    *   `recordUsage(User $user, string $metric)`: Increment counters.
2.  **QuotationService:**
    *   Handle revision creation/cloning.
    *   Calculate totals/taxes.
3.  **BillingService:**
    *   Handle "Advance" vs "Running" bill generation.
    *   Calculate "Due" based on partial payments.
4.  **Middleware:**
    *   `TenantScope`: Apply company context to requests.
    *   `CheckSubscription`: Block actions if limits exceeded.
5.  **CouponService:**
    *   `validateCoupon(string $code, Customer $customer)`: Check validity and eligibility.
    *   `applyCoupon(Coupon $coupon, float $amount)`: Calculate discount.
    *   `redeemCoupon(Coupon $coupon, Customer $customer)`: Process redemption and track usage.

---

## Phase 5: API Routes & Controllers
### Week 4

1.  **API Structure:**
    *   Prefix: `/api/v1`
    *   Middleware group: `['auth:sanctum', 'tenant.scope']`
2.  **Controllers:**
    *   `SubscriptionController`: Manage plans, upgrade/downgrade.
    *   `QuotationController`: CRUD + Revisions.
    *   `BillController`: Generation and status updates.
    *   `DashboardController`: Aggregated analytics.
    *   `CouponController`: Coupon management, validation, redemption.

---

## Phase 6: Frontend Merge
### Week 5

1.  **Layouts:**
    *   Main Layout with Sidebar (Company Switcher in header).
    *   Subscription Banner (if near limit/expired).
2.  **Pages:**
    *   **Dashboard:** Widgets for Revenue, Pending Quotes, Targets.
    *   **Quotation Builder:** Multi-step form (Create -> Add Items -> Review).
    *   **Subscription Manager:** "Build Your Plan" interactive UI.

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
