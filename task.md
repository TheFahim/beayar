# Application Merger Task List

## Overview
Merge Optimech and Wesum Laravel applications into a unified ERP system with **Multi-Tenancy** and **Subscription** support.

---

## Phase 1: Project Setup (Week 1, Days 1-2)
- [x] Analyze conversation summary and requirements
- [x] Analyze both application codebases
- [x] Create implementation plan document
- [x] Create new Laravel project
- [x] Configure environment and database connections
- [x] Install required packages (composer + npm)
- [x] Set up Git repository and branching strategy
- [x] Configure Source DB Credentials in .env (currently using placeholders)

---

## Phase 2: Database Migrations (Week 1, Days 3-5)

### Super Admin & Platform (New)
- [x] Create `admins` table (Platform super admins)
- [x] Create `system_settings` table (Global configs, payment keys)
- [x] Create `platform_invoices` table (Separate from tenant bills)
- [x] Create `platform_payments` table (Separate from tenant payments)
- [x] Create `feature_flags` table (Global feature management)

### Multi-Tenancy & Subscriptions (Critical)
- [x] Create `users` table (add `current_user_company_id`, `current_scope`)
- [x] Create `user_companies` table (add `owner_id`, `parent_company_id`)
- [x] Create `customer_companies` table (belongs to `user_companies`)
- [x] Create `plans` table (Free, Pro, Pro Plus, Custom configs)
- [x] Create `modules` table (available add-ons)
- [x] Create `subscriptions` table (link User -> Plan, custom limits JSON)
- [x] Create `subscription_usage` table (track monthly usage)

### Core Business Entities
- [x] Create `customers` table (add `customer_company_id`)
- [x] Create `products` table (global catalog per user_company)
- [x] Create `brand_origins` and `specifications` tables
- [x] Create `quotation_statuses` and `expense_categories` tables

### Workflow Tables
- [x] Create `quotations` table (base info, po_no, ship_to)
- [x] Create `quotation_revisions` table (versioning, pricing, tax, terms)
- [x] Create `quotation_products` table (line items, linked to revision)
- [x] Create `challans` and `challan_products` tables
- [x] Create `bills` table (Optimech structure: advance/regular/running types)
- [x] Create `bill_challans` table (link bills to challans)
- [x] Create `bill_items` table (granular line items linked to challan products)
- [x] Create `received_bills` table (payment tracking)

### Financials & System
- [x] Create `payments` table (enhanced tracking)
- [x] Create `expenses` table
- [x] Create `sale_targets` table
- [x] Create `activity_logs` and `notifications` tables

### Coupon System (New Feature)
- [x] Create `coupons` table (types: unique, campaign)
- [x] Create `coupon_usage` table (track customer usage, linked to platform_invoices)
- [x] Create `coupon_limits` table (min/max expenditure limits)
- [x] Create `coupon_customers` table (link unique coupons to specific customers)
- [x] Create and run all Seeders (Plans, Roles, Statuses, Coupon Types)

---

## Phase 3: Eloquent Models & Scoping (Week 2)

### Super Admin Models (New)
- [x] Create `Admin` model (Authenticatable)
- [x] Create `SystemSetting` model (Key-value store)
- [x] Create `PlatformInvoice` and `PlatformPayment` models
- [x] Create `FeatureFlag` model

### Base & Security
- [x] Create `BelongsToCompany` trait (Global Scope for `company_id`)
- [x] Create `BaseModel` extending Eloquent
- [x] Implement `User` model with Multi-Tenancy helper methods

### Subscription Logic
- [x] Create `Plan`, `Module`, `Subscription` models
- [x] Add `hasFeature()` and `checkLimit()` methods to User/Subscription models
- [x] Create `SubscriptionUsage` model with auto-reset logic

### Core Business Models
- [x] Create `UserCompany`, `CustomerCompany`, `Customer` models
- [x] Create `Product`, `BrandOrigin`, `Specification` models
- [x] Create `Quotation`, `QuotationRevision`, `QuotationProduct` models
- [x] Create `Challan`, `ChallanProduct` models
- [x] Create `Bill`, `BillChallan`, `BillItem`, `ReceivedBill` models
- [x] Create `Payment`, `Expense`, `SaleTarget` models
- [ ] Add Model Factories for all major entities (for testing)

---

## Phase 4: Service Layer & Logic (Week 3)

### Super Admin Services
- [x] Create `AdminService` (Auth, Tenant Impersonation)
- [x] Create `PlatformBillingService` (Invoicing, Payment Recording)
- [x] Create `SystemSettingService` (Global Configs)

### Subscription Engine
- [x] Create `SubscriptionService` (Limits, Usage Tracking)
- [x] Create `PlanManagerService` (Upgrades, Downgrades, Renewals)
- [x] Create `CheckSubscriptionLimits` Middleware

### Tenant Business Logic
- [x] Create `QuotationService` (Revisions, Calculations)
- [x] Create `TenantBillingService` (Advance/Running Bills, Challan Linking)
- [x] Create `FinancialService` (Tenant Dashboard Aggregation)
- [x] Create `CouponService` (Platform Discounts)
- [x] Create `TenantScope` Middleware
- [x] Create `AdminAuth` Middleware

---

## Phase 5: API & Controllers (Week 4)

### Infrastructure
- [x] Setup API Routes (v1) with Middleware Groups
- [x] Setup Admin API Routes (`/api/admin`)
- [x] Create FormRequest Validation classes for all inputs

### Admin Controllers
- [x] `AdminAuthController`: Admin login/logout.
- [x] `TenantManagementController`: Tenant listing, suspension, and impersonation.
- [x] `PlatformRevenueController`: Platform-wide financial analytics.
- [x] `GlobalCouponController`: Management of global marketing campaigns.

### Feature Controllers
- [x] `SubscriptionController`: Plan listing and purchasing logic.
- [x] `CompanyController`: Management of customer sub-companies.
- [x] `QuotationController` (Full CRUD, Revision management, PDF generation)
- [x] `BillController` (Invoicing and linking with Challans)
- [x] `FinanceController` (Tenant-level dashboard stats)
- [x] `CouponController` (Coupon validation and redemption)

---

## Phase 6: Frontend Implementation (Week 5)

### Super Admin UI
- [x] Create Admin Dashboard (Revenue, Tenant Growth)
- [x] Create Tenant Management View (List, Details, Suspend)
- [x] Create Plan Management View (Edit Plan features/prices)
- [x] Create Coupon Management View (Global Campaigns)

### Core UI
- [x] Setup Main Layout with Sidebar & Company Switcher
- [x] Create "Build Your Plan" Subscription Page (Interactive sliders)
- [x] Create Dashboard (Widgets for limits, revenue, targets)

### Business UI
- [x] Create Quotation Builder (Multi-step, Product selection)
- [x] Create Billing Management View (Status indicators, Payment modal)
- [x] Create Report Views (Charts, DataTables)

---

## Phase 7: Migration & Testing (Week 6-7)

### Data Migration
- [ ] Write script to migrate Wesum users/data to new schema
- [ ] Write script to migrate Optimech users/data to new schema
- [ ] Verify data integrity (Foreign keys, Totals)

### Verification
- [ ] Write Unit Tests for Pricing Calculator
- [ ] Write Feature Tests for Tenant Isolation (Security)
- [ ] Write Feature Tests for Subscription Limits
- [ ] Manual End-to-End Testing of all workflows
- [ ] Generate API Documentation (Swagger/Postman)
