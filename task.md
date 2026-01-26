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
- [x] Create `quotations` table (base info)
- [x] Create `quotation_revisions` table (versioning, pricing)
- [x] Create `quotation_products` table (line items)
- [x] Create `challans` and `challan_products` tables
- [x] Create `bills`, `bill_challans`, and `bill_items` tables

### Financials & System
- [x] Create `payments` table (enhanced tracking)
- [x] Create `expenses` table
- [x] Create `sale_targets` table
- [x] Create `activity_logs` and `notifications` tables

### Coupon System (New Feature)
- [x] Create `coupons` table (types: unique, campaign)
- [x] Create `coupon_usage` table (track customer usage)
- [x] Create `coupon_limits` table (min/max expenditure limits)
- [x] Create `coupon_customers` table (link unique coupons to specific customers)
- [x] Create and run all Seeders (Plans, Roles, Statuses, Coupon Types)

---

## Phase 3: Eloquent Models & Scoping (Week 2)

### Base & Security
- [ ] Create `BelongsToCompany` trait (Global Scope for `company_id`)
- [ ] Create `BaseModel` extending Eloquent
- [ ] Implement `User` model with Multi-Tenancy helper methods

### Subscription Logic
- [ ] Create `Plan`, `Module`, `Subscription` models
- [ ] Add `hasFeature()` and `checkLimit()` methods to User/Subscription models
- [ ] Create `SubscriptionUsage` model with auto-reset logic

### Core Business Models
- [ ] Create `Company`, `Customer`, `Product` models
- [ ] Create `Quotation`, `QuotationRevision` models (configure relationships)
- [ ] Create `Bill`, `Payment`, `Expense` models
- [ ] Add Model Factories for all major entities (for testing)

---

## Phase 4: Service Layer & Logic (Week 3)

### Subscription Services
- [ ] Create `PricingCalculatorService` (Dynamic Custom Plan logic)
- [ ] Create `SubscriptionManager` (Handle upgrades/downgrades/usage)
- [ ] Create `CheckSubscriptionLimits` Middleware
- [ ] Create `TenantScope` Middleware (Enforce data isolation)

### Business Logic Services
- [ ] Create `QuotationService` (Handle revision cloning, status updates)
- [ ] Create `BillingService` (Calculate dues, handle partial payments)
- [ ] Create `FinancialDashboardService` (Aggregate data for charts)
- [ ] Create `TargetTrackingService` (Update sale targets on payment)
- [ ] Create `CouponService` (Validate, apply, track coupon usage)

---

## Phase 5: API & Controllers (Week 4)

### Infrastructure
- [ ] Setup API Routes (v1) with Middleware Groups
- [ ] Create FormRequest Validation classes for all inputs

### Feature Controllers
- [ ] `SubscriptionController` (Plans, Pricing, Purchase)
- [ ] `CompanyController` (Manage sub-companies)
- [ ] `QuotationController` (CRUD, Revisions, PDF generation)
- [ ] `BillController` (Invoicing, Status management)
- [ ] `FinanceController` (Expenses, Payments, Reports)
- [ ] `CouponController` (Coupon management, validation, redemption)

---

## Phase 6: Frontend Implementation (Week 5)

### Core UI
- [ ] Setup Main Layout with Sidebar & Company Switcher
- [ ] Create "Build Your Plan" Subscription Page (Interactive sliders)
- [ ] Create Dashboard (Widgets for limits, revenue, targets)

### Business UI
- [ ] Create Quotation Builder (Multi-step, Product selection)
- [ ] Create Billing Management View (Status indicators, Payment modal)
- [ ] Create Report Views (Charts, DataTables)

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
