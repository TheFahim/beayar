# Billing Module — Implementation Guide

## Project: Beayar ERP System
## Duration: 16 Working Days (3.5 Weeks)
## Stack: Laravel 11 · MySQL 8.4 · Alpine.js v3 · Blade · Tailwind CSS

---

## Quick Reference

| Phase | Days | Focus | Key Deliverables |
|-------|------|-------|------------------|
| **Phase 1** | 1-2 | Database Foundation | Migrations, new tables, schema fixes |
| **Phase 2** | 3-5 | Backend Core & Services | BillingService, Bill model, Exceptions |
| **Phase 3** | 6-7 | Backend API & Controllers | Controllers, Routes, Form Requests |
| **Phase 4** | 8-10 | Frontend: Bill Creation | Bill creation forms, Alpine.js components |
| **Phase 5** | 11-12 | Frontend: Advance Credits | Credit application UI, banners |
| **Phase 6** | 13-14 | Frontend: Correction Flow | Cancel/reissue workflow |
| **Phase 7** | 15-16 | Testing & Hardening | Unit tests, Feature tests, QA |

---

## Phase Files

Each phase has its own detailed implementation file:

- **[Phase 1 — Database Foundation](./phase1_database_foundation.md)** (Days 1-2)
- **[Phase 2 — Backend Core & Services](./phase2_backend_core.md)** (Days 3-5)
- **[Phase 3 — Backend API & Controllers](./phase3_backend_api.md)** (Days 6-7)
- **[Phase 4 — Frontend: Bill Creation](./phase4_frontend_bill_creation.md)** (Days 8-10)
- **[Phase 5 — Frontend: Advance Credits](./phase5_frontend_advance_credits.md)** (Days 11-12)
- **[Phase 6 — Frontend: Correction Flow](./phase6_frontend_correction.md)** (Days 13-14)
- **[Phase 7 — Testing & Hardening](./phase7_testing.md)** (Days 15-16)

---

## Pre-Implementation Checklist

Before starting, ensure the following:

- [ ] **Backup database** — Run a full MySQL dump before any migrations
- [ ] **Create feature branch** — `git checkout -b feature/billing-module-refactor`
- [ ] **Verify environment** — PHP 8.2+, MySQL 8.4+, Composer 2.x
- [ ] **Clear caches** — `php artisan cache:clear && php artisan config:clear`
- [ ] **Review existing code** — Read current Bill model and BillController

---

## Architecture Summary

### Current Billing Flow
```
quotations
  └── quotation_revisions
        └── quotation_products
              └── challan_products
                    └── challans
                          └── bill_challans (pivot)
                                └── bill_items
                                      └── bills
```

### Bill Types
| Type | Purpose | Status Flow |
|------|---------|-------------|
| **Advance** | Upfront payment before delivery | draft → issued → paid |
| **Running** | Interim bills linked to an Advance | draft → issued → paid |
| **Regular** | Final bill after all deliveries | draft → issued → partially_paid → paid/adjusted |

### What Changes

1. **Remove** `quotations.regular_billing_locked` column
2. **Add** `quotations.billing_stage` ENUM column
3. **Fix** `bills.due` and `bills.shipping` from `DOUBLE` → `DECIMAL(15,2)`
4. **Extend** `bills.status` enum with `partially_paid` and `adjusted`
5. **Add** locking columns to `bills`: `is_locked`, `lock_reason`, `locked_at`
6. **Add** credit tracking columns to `bills`: `advance_applied_amount`, `net_payable_amount`
7. **Create** new `bill_payments` table (full payment tracking)
8. **Create** new `bill_advance_adjustments` table (advance credit application)
9. **Add** performance indexes
10. **Refactor** all billing business logic into a `BillingService` class
11. **Implement** the 6-rule bill locking state machine
12. **Build** the advance credit application UI
13. **Build** the final (Regular) bill creation flow — no longer blocked by advance
14. **Build** the cancellation + reissue correction workflow

---

## The 6 Locking Rules

These rules determine when a bill can no longer be edited:

| Rule | Condition | Lock Reason |
|------|-----------|-------------|
| 1 | Status is not `draft` | `status_not_draft` |
| 2 | Child bill is `issued` or higher | `has_issued_child` |
| 3 | Payment record exists | `has_payments` |
| 4 | Challan quantities would drop below delivered | `challan_quantity_violation` |
| 5 | Advance credit has been applied | `advance_applied` |
| 6 | Regular bill has advance adjustments referencing it | `has_advance_adjustments` |

---

## Tech Stack Constraints

- **Backend:** Laravel 11, PHP 8.2+
- **Database:** MySQL 8.4
- **Frontend:** Blade templates + Alpine.js v3 + vanilla JS
- **CSS:** Tailwind CSS (already configured)
- **Auth/Permissions:** Spatie Laravel-Permission
- **Activity Logging:** Spatie Laravel-ActivityLog
- **Multi-tenancy:** Tenant-scoped via `tenant_company_id`
- **No Livewire** — all dynamic UI must use Alpine.js + fetch/axios

---

## Key Files to Create/Modify

### New Files
| File | Purpose |
|------|---------|
| `app/Services/BillingService.php` | Core billing business logic |
| `app/Exceptions/BillLockedException.php` | Custom exception for locked bills |
| `app/Http/Controllers/BillPaymentController.php` | Payment management |
| `app/Http/Controllers/BillApiController.php` | AJAX endpoints |
| `app/Http/Requests/CreateRegularBillRequest.php` | Validation for regular bill creation |
| `app/Http/Requests/CreateAdvanceBillRequest.php` | Validation for advance bill creation |
| `app/Http/Requests/ApplyAdvanceCreditRequest.php` | Validation for credit application |
| `app/Http/Requests/RecordPaymentRequest.php` | Validation for payment recording |
| `database/migrations/*_create_bill_payments_table.php` | Payments table |
| `database/migrations/*_create_bill_advance_adjustments_table.php` | Advance adjustments table |
| `database/migrations/*_add_locking_to_bills_table.php` | Locking columns |
| `database/migrations/*_fix_bills_decimal_columns.php` | Decimal precision fix |
| `database/migrations/*_update_quotations_billing_stage.php` | Remove locked, add stage |
| `resources/views/bills/create-regular.blade.php` | Regular bill creation |
| `resources/views/bills/create-advance.blade.php` | Advance bill creation |
| `resources/views/bills/partials/advance-credit-banner.blade.php` | Reusable banner component |
| `resources/views/bills/partials/bill-timeline.blade.php` | Timeline component |
| `tests/Unit/BillingServiceTest.php` | Unit tests |
| `tests/Unit/BillModelTest.php` | Model tests |
| `tests/Feature/CreateAdvanceBillTest.php` | Feature tests |
| `tests/Feature/CreateRegularBillTest.php` | Feature tests |
| `tests/Feature/ApplyAdvanceCreditTest.php` | Feature tests |
| `tests/Feature/BillCorrectionFlowTest.php` | Feature tests |

### Modified Files
| File | Changes |
|------|---------|
| `app/Models/Bill.php` | Add relationships, scopes, locking guards |
| `app/Models/Quotation.php` | Add billing_stage accessor/mutator |
| `app/Http/Controllers/BillController.php` | Refactor to use BillingService |
| `routes/web.php` | Add new bill-related routes |
| `routes/api.php` | Add AJAX endpoints |

---

## API Endpoints Overview

| Method | URI | Purpose |
|--------|-----|---------|
| GET | `/bills/create?quotation_id=&type=` | Bill creation form |
| POST | `/bills` | Create any bill type |
| GET | `/bills/{bill}/edit` | Edit form (draft only) |
| PUT | `/bills/{bill}` | Update draft bill |
| POST | `/bills/{bill}/issue` | Issue a draft bill |
| POST | `/bills/{bill}/cancel` | Cancel an issued bill |
| POST | `/bills/{bill}/reissue` | Cancel + create revised draft |
| POST | `/bills/{bill}/payments` | Record a payment |
| DELETE | `/bills/{bill}/payments/{payment}` | Void a payment |
| POST | `/bills/{bill}/apply-advance` | Apply advance credit |
| GET | `/api/quotations/{quotation}/billable-challans` | AJAX: unbilled challans |
| GET | `/api/quotations/{quotation}/available-advances` | AJAX: advances with balance |
| GET | `/api/bills/{bill}/advance-balance` | AJAX: remaining balance |

---

## Decimal Precision Rules

All monetary calculations must use PHP's `bcmath` functions:

```php
// ✅ CORRECT
$total = bcadd($subtotal, $tax, 2);
$difference = bcsub($total, $paid, 2);

// ❌ WRONG
$total = $subtotal + $tax;  // Floating point errors
```

---

## Tenant Scoping

Every query must scope to the current tenant:

```php
// In Model
public function scopeForTenant(Builder $query): Builder
{
    return $query->where('tenant_company_id', currentTenantId());
}

// In Controller/Service
Bill::forTenant()->where('status', 'draft')->get();
```

---

## Activity Logging Pattern

Every bill state transition must be logged:

```php
activity('billing')
    ->performedOn($bill)
    ->causedBy(auth()->user())
    ->withProperties(['old_status' => $old, 'new_status' => $new])
    ->log('Bill status changed');
```

---

## Next Steps

1. Start with **[Phase 1 — Database Foundation](./phase1_database_foundation.md)**
2. Follow each phase in order
3. Complete each day's checklist before moving on
4. Run tests after each phase

---

*Generated from: billing_module_implementation_prompt.md*
*Architecture Report: billing_module_architecture_report.docx*
*Schema Reference: beayar_2026-03-11.sql*
