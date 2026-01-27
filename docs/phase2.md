# Phase 2: Database Migrations

## Overview
This phase involved designing and creating the database schema to support Multi-Tenancy, Subscriptions, and the unified business logic of Optimech and Wesum.

## Schema Architecture

### 1. Super Admin & Platform
New tables were created to manage the platform itself, separate from tenant data:
- **`admins`**: For platform super admins.
- **`system_settings`**: Global configurations (e.g., payment keys).
- **`platform_invoices`**: To bill tenants for their subscriptions.
- **`platform_payments`**: To track revenue from tenants.
- **`feature_flags`**: To toggle global features.

### 2. Multi-Tenancy & Subscriptions
The core structure for the SaaS model:
- **`users`**: Added `current_user_company_id` and `current_scope` for context switching.
- **`user_companies`**: The tenant entity.
- **`customer_companies`**: Sub-companies belonging to tenants.
- **`plans`**: Subscription tiers (Free, Pro, Pro Plus, Custom).
- **`modules`**: Add-on features available for purchase.
- **`subscriptions`**: Links Users to Plans with custom limits (JSON).
- **`subscription_usage`**: Tracks usage against plan limits (e.g., "quotations_created").

### 3. Core Business Entities
Unified tables from both legacy systems:
- **`customers`**: Scoped to `customer_company_id`.
- **`products`**: Global catalog per `user_company`.
- **`quotations`**: The central sales document.
- **`quotation_revisions`**: Version control for quotations (pricing, terms).
- **`quotation_products`**: Line items for revisions.
- **`challans`**: Delivery notes.
- **`bills`**: Supports "Advance", "Regular", and "Running" bill types.
- **`bill_challans`**: Many-to-Many link between bills and delivery notes.
- **`payments`**: Tenant-level payment tracking.
- **`expenses`**: Business expense tracking.

### 4. Coupon System (New)
A flexible coupon engine:
- **`coupons`**: unique or campaign-based.
- **`coupon_usage`**: Linked to `platform_invoices`.
- **`coupon_limits`**: Constraints on usage.

## Key Decisions
- **Single Database Multi-Tenancy**: All tenants share the same database, separated by `user_company_id`.
- **JSON Limits**: Subscription limits are stored as JSON for flexibility.
- **Revision History**: Quotations use a revision system to track changes over time without losing history.

## Verification
- All migrations ran successfully.
- Seeders were created for default Plans, Roles, and Statuses.
