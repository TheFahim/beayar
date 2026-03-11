# 🤖 AI Agent Instruction Prompt
## Billing Module — Full Implementation Guide Generator

> **How to use this prompt:**
> Feed this entire file to your local AI agent along with the files listed in the
> "Required Reading" section below. The agent will analyze all inputs and produce
> a complete, day-by-day implementation guide tailored to your exact codebase.

---

## 📁 Required Reading — Files You Must Load First

Before generating any implementation plan, you **must** read and fully internalize
the following files. Do not begin planning until all files are loaded.

```
1.  beayar_2026-03-11.sql (database-backup/beayar_2026-03-11.sql)                      ← Full MySQL database schema
2.  billing_module_architecture_report.docx (docs/billing_module_architecture_report.docx)    ← Architecture analysis report
                                                  (contains all standards, schema
                                                   change decisions, and workflow specs)
```

While reading, extract and hold in context:

- Every table name, column name, data type, foreign key, and index from the SQL file
- All 4 sections of the architecture report:
  - Section 1: Global standards verdict and the corrected billing type logic
  - Section 2: The 6 data-consistency locking rules and the state machine
  - Section 3: All schema changes (new tables, modified columns, dropped columns)
  - Section 4: The 8-step UX workflow and UI principles

---

## 🎯 Your Mission

You are a **Senior Laravel & Alpine.js Engineer** with deep expertise in:
- Laravel 11 (Eloquent ORM, Service classes, Form Requests, Policies, Events/Listeners)
- MySQL 8.x (migrations, indexes, constraints, transactions)
- Alpine.js v3, Blade templating, and Livewire-free UI patterns
- Accounting ERP systems (billing, invoicing, ledger integrity)

Your task is to produce a **complete, day-by-day implementation guide** for the
billing module refactor described in the architecture report. The guide must be
detailed enough that a mid-level Laravel developer can follow it without any
ambiguity.

---

## 📐 Implementation Context — Read This Carefully

### Tech Stack (Do NOT suggest alternatives)
- **Backend:** Laravel 11, PHP 8.2+
- **Database:** MySQL 8.4
- **Frontend:** Blade templates + Alpine.js v3 + vanilla JS
- **CSS:** Tailwind CSS (already configured)
- **Auth/Permissions:** Spatie Laravel-Permission (already installed — see schema:
  `model_has_permissions`, `model_has_roles`, `roles`, `permissions`)
- **Activity Logging:** Spatie Laravel-ActivityLog (already installed — see schema:
  `activity_log`)
- **Multi-tenancy:** Tenant-scoped via `tenant_company_id` on all business tables
- **Soft Deletes:** Already used on key tables (`deleted_at` columns present)
- **No Livewire** — all dynamic UI must use Alpine.js + fetch/axios

### Existing Billing Architecture (from the schema)
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

The `bills` table has:
- `bill_type` ENUM: `advance`, `regular`, `running`
- `parent_bill_id` (self-referential FK for Running bills linked to Advance bills)
- `status` ENUM: `draft`, `issued`, `paid`, `cancelled`
- `quotation_id` and `quotation_revision_id` foreign keys
- `regular_billing_locked` on the `quotations` table (to be REMOVED)

### What Changes Are Being Made (Summary)
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

## 📋 Guide Structure Requirements

Structure the implementation guide **exactly** as follows:

```
PHASE 1 — Database Foundation        (Days 1–2)
PHASE 2 — Backend Core & Services    (Days 3–5)
PHASE 3 — Backend API & Controllers  (Days 6–7)
PHASE 4 — Frontend: Bill Creation    (Days 8–10)
PHASE 5 — Frontend: Advance Credits  (Days 11–12)
PHASE 6 — Frontend: Correction Flow  (Days 13–14)
PHASE 7 — Testing & Hardening        (Days 15–16)
```

For **each day**, the guide must include all of the following sections:

---

### Per-Day Section Template

```markdown
## Day N — [Title]

### 🎯 Goal
One paragraph explaining what will be fully working by the end of this day.

### 📋 Prerequisites
- What must be completed before starting this day
- Any environment checks required

### 🗄️ Database / Migration Tasks
(Only if applicable)
- Exact migration file names (e.g., `2026_03_12_000001_add_locking_to_bills_table.php`)
- Complete migration `up()` and `down()` methods with full SQL/Eloquent syntax
- Note any data backfill needed for existing rows

### ⚙️ Backend Tasks
For each task:
- File path (e.g., `app/Services/BillingService.php`)
- Whether it is a NEW file or MODIFICATION to existing
- The complete method signature(s) to write
- Step-by-step logic description (enough to implement without guessing)
- Any Eloquent relationships, scopes, or observers to add/modify
- Any Form Request classes to create with validation rules
- Any Policy rules to update
- Any Events/Listeners to fire

### 🖥️ Frontend Tasks
For each task:
- Blade file path (e.g., `resources/views/bills/create.blade.php`)
- Whether it is NEW or MODIFICATION
- Alpine.js component structure (`x-data`, `x-init`, methods, reactive properties)
- Any fetch/axios calls (endpoint, payload shape, response handling)
- Form field definitions (name, type, validation hints, conditional display logic)
- Key UI states to handle (loading, success, error, locked, empty)
- Any reusable Blade components to extract

### ✅ End-of-Day Checklist
- [ ] Specific, testable checkbox items
- [ ] Each item must be verifiable by running a specific action or command

### ⚠️ Pitfalls & Notes
- Common mistakes to avoid for this specific day's work
- Edge cases to handle
```

---

## 🔒 Mandatory Content Requirements

The guide **must** include complete, copy-paste-ready code for the following
critical pieces. Do not describe them vaguely — write the full implementation:

### 1. BillingService Class Skeleton
Path: `app/Services/BillingService.php`

Must include these method signatures with full docblocks and logic descriptions:
```php
createAdvanceBill(array $data, Quotation $quotation): Bill
createRunningBill(array $data, Bill $parentAdvanceBill): Bill
createRegularBill(array $data, Quotation $quotation, array $challanIds): Bill
applyAdvanceCredit(Bill $advanceBill, Bill $finalBill, float $amount): BillAdvanceAdjustment
lockBill(Bill $bill, string $reason): void
cancelBill(Bill $bill): Bill
reissueBill(Bill $cancelledBill): Bill   // creates the revised draft copy
getUnappliedAdvanceBalance(Bill $advanceBill): float
getBillableChallans(Quotation $quotation): Collection  // only unfully-billed challans
```

### 2. Bill Model — Locking Guards
The `Bill` Eloquent model must include:
- A `canBeEdited(): bool` method implementing all 6 locking rules
- A `boot()` method with `static::updating()` observer that calls `canBeEdited()`
  and throws a `BillLockedException` if the bill is locked
- Scopes: `scopeDraft()`, `scopeIssued()`, `scopeUnpaid()`, `scopeAdvance()`,
  `scopeRegular()`, `scopeRunning()`
- Relationships: `payments()`, `advanceAdjustmentsGiven()`,
  `advanceAdjustmentsReceived()`, `parentBill()`, `childBills()`
- Accessor: `getUnappliedAmountAttribute()` using the formula from the report

### 3. BillLockedException
Path: `app/Exceptions/BillLockedException.php`
- Extend `\Exception`
- Include `$bill` and `$reason` properties
- Render as a JSON 422 response in `app/Exceptions/Handler.php`

### 4. The 6 Locking Rules — Explicit Implementation
Write the `canBeEdited()` method explicitly checking all 6 rules in order:
1. Status guard — only `draft` is editable
2. Child bill guard — if any child bill is `issued` or higher, parent is locked
3. Payment guard — if any record in `bill_payments` exists, locked
4. Challan link guard — quantities cannot be reduced below delivered amounts
5. Advance application guard — applied portion is immutable
6. Billing type guard — `regular` bills lock once any `bill_advance_adjustments`
   record references them as `final_bill_id`

### 5. CreateRegularBillRequest
Path: `app/Http/Requests/CreateRegularBillRequest.php`
Full validation rules including:
- `quotation_id` exists and belongs to tenant
- `challan_ids` array, each exists, each belongs to quotation, none already
  fully billed
- `advance_adjustment.advance_bill_id` optional, must be `advance` type,
  same quotation, must have available balance ≥ `advance_adjustment.amount`
- `bill_items` array with `challan_product_id`, `quantity`, `unit_price`
- Quantity cannot exceed remaining unbilled quantity for that challan product

### 6. Alpine.js — Regular Bill Create Page
Write the complete `x-data` object for the Regular bill creation page including:
```javascript
{
  // State
  selectedChallans: [],
  billItems: [],
  availableAdvances: [],
  selectedAdvance: null,
  advanceApplyAmount: 0,

  // Computed (use Alpine getter pattern)
  get subtotal() { ... },
  get netPayable() { ... },
  get maxApplicableAdvance() { ... },

  // Methods
  async loadChallans(quotationId) { ... },
  async loadBillItems(challanIds) { ... },
  async loadAvailableAdvances(quotationId) { ... },
  applyAdvanceCredit() { ... },
  removeAdvanceCredit() { ... },
  async submitBill() { ... }
}
```

### 7. Alpine.js — Advance Credit Banner Component
A reusable Blade/Alpine component `<x-advance-credit-banner :quotation-id="$quotation->id" />`
that shows:
- Total advance received for the quotation
- Total applied to date
- Available balance (highlighted if > 0)
- "Apply to this invoice" button (only shown when creating a Regular bill)

### 8. Bill Timeline Blade Component
A reusable `<x-bill-timeline :bills="$bills" />` component showing all bills for
a quotation in chronological order with color-coded badges:
- Advance = blue, Running = amber, Regular = green, Cancelled = gray, Draft = muted
- Lock icon on locked bills with tooltip showing `lock_reason`
- Click to expand showing amounts, dates, payment status

---

## 🗃️ API Endpoints to Define

The guide must define **all** of the following routes (to be added to
`routes/web.php` or `routes/api.php` as appropriate for the existing pattern):

| Method | URI | Controller@Method | Purpose |
|--------|-----|-------------------|---------|
| GET | `/bills/create?quotation_id=&type=` | `BillController@create` | Bill creation form (type-aware) |
| POST | `/bills` | `BillController@store` | Create any bill type |
| GET | `/bills/{bill}/edit` | `BillController@edit` | Edit form (draft only) |
| PUT | `/bills/{bill}` | `BillController@update` | Update draft bill |
| POST | `/bills/{bill}/issue` | `BillController@issue` | Issue a draft bill |
| POST | `/bills/{bill}/cancel` | `BillController@cancel` | Cancel an issued bill |
| POST | `/bills/{bill}/reissue` | `BillController@reissue` | Correction: cancel + create revised draft |
| POST | `/bills/{bill}/payments` | `BillPaymentController@store` | Record a payment |
| DELETE | `/bills/{bill}/payments/{payment}` | `BillPaymentController@destroy` | Void a payment (if allowed) |
| POST | `/bills/{bill}/apply-advance` | `BillController@applyAdvance` | Apply advance credit to final bill |
| GET | `/api/quotations/{quotation}/billable-challans` | `BillApiController@billableChallans` | AJAX: unchallaned challan list |
| GET | `/api/quotations/{quotation}/available-advances` | `BillApiController@availableAdvances` | AJAX: advances with remaining balance |
| GET | `/api/bills/{bill}/advance-balance` | `BillApiController@advanceBalance` | AJAX: remaining unapplied balance |

---

## 🧪 Testing Requirements

For **each phase**, include:

### Unit Tests
- `BillingServiceTest` — test each `BillingService` method in isolation
- `BillModelTest` — test all 6 locking rules with dedicated test cases
- `BillAdvanceAdjustmentTest` — test balance calculation accuracy (decimal precision)

### Feature Tests
- `CreateAdvanceBillTest` — full HTTP test: create, issue, record payment
- `CreateRegularBillTest` — full HTTP test including the "not blocked by advance" assertion
- `ApplyAdvanceCreditTest` — test partial and full application; test over-application rejection
- `BillCorrectionFlowTest` — cancel → reissue → verify advance unapplied

### Key Assertions to Include
For each test case, specify the exact assertions:
```php
// Example: Over-application must be rejected
$this->postJson('/bills/{regularBillId}/apply-advance', [
    'advance_bill_id' => $advanceBillId,
    'amount' => 99999.99  // more than available balance
])->assertStatus(422)
  ->assertJsonValidationErrors(['amount']);
```

---

## 📁 Complete File Change Manifest

At the end of the guide, produce a full table of every file to be created or
modified, in the order they should be worked on:

| Day | File Path | Action | Description |
|-----|-----------|--------|-------------|
| 1 | `database/migrations/YYYY_MM_DD_remove_regular_billing_locked.php` | CREATE | ... |
| ... | ... | ... | ... |

---

## ⚡ Additional Instructions for the AI Agent

1. **Be explicit about namespaces.** Every class must show its full namespace
   declaration and all `use` imports.

2. **Respect existing patterns.** Before writing any controller or service,
   infer the existing code style from the schema (e.g., all tables use
   `tenant_company_id` scoping — every query must scope to the current tenant).
   All service methods should throw typed exceptions, not return booleans.

3. **Transactions are mandatory** for any operation that touches more than one
   table. Wrap in `DB::transaction()` with rollback on exception.

4. **Activity logging is mandatory** for every bill state transition. Use the
   existing `activity_log` table via Spatie ActivityLog:
   ```php
   activity('billing')
       ->performedOn($bill)
       ->causedBy(auth()->user())
       ->withProperties(['old_status' => $old, 'new_status' => $new])
       ->log('Bill status changed');
   ```

5. **Decimal precision guard.** Any method that calculates money amounts must
   use PHP's `bcmath` functions (`bcadd`, `bcsub`, `bcmul`, `bcdiv`) with scale
   of 2. Never use native `+`, `-`, `*`, `/` on monetary values.

6. **Tenant scoping guard.** Add a `scopeForTenant(Builder $query): Builder`
   scope to the `Bill` model that applies `where('tenant_company_id', currentTenantId())`.
   Every query in controllers and services must use this scope.

7. **No raw SQL.** Use Eloquent query builder throughout. The only exception is
   for the advance balance calculation which may use a `selectRaw` with a subquery.

8. **Alpine.js state must be server-validated.** Never trust client-side
   computed values. All amounts submitted in forms must be re-validated and
   re-computed server-side before persisting.

9. **Error handling in Alpine.js.** Every `fetch`/`axios` call must handle
   422 validation errors and display them inline next to the relevant fields,
   not just as a toast.

10. **The guide must be self-contained.** A developer who has not read the
    architecture report must be able to follow this guide purely from its content.
    Include enough context in each day's intro to explain *why* each change is
    being made, not just *what* to do.

---

## 📤 Output Format

Produce the implementation guide as a **single `.md` file** with the following
document structure:

```markdown
# Billing Module — Implementation Guide
## Project: [ERP System Name]
## Duration: 16 Working Days (3.5 Weeks)
## Stack: Laravel 11 · MySQL 8.4 · Alpine.js v3 · Blade · Tailwind CSS

---

## Quick Reference
[A summary table of all phases, days, and goals]

---

## Pre-Implementation Checklist
[Environment checks, backup reminders, branch strategy]

---

## PHASE 1 — Database Foundation (Days 1–2)
### Day 1 — ...
### Day 2 — ...

## PHASE 2 — Backend Core & Services (Days 3–5)
...

[Continue for all 7 phases and 16 days]

---

## Appendix A — Complete File Manifest
## Appendix B — All New Database Objects (DDL)
## Appendix C — All New Routes
## Appendix D — Environment Variables & Config Changes (if any)
```

---

## 🚦 Final Checklist Before You Begin Generating

Confirm you have done all of the following before writing a single line of
the implementation guide:

- [ ] Read the entire SQL schema and can name every table and its purpose
- [ ] Identified that `bills.due` and `bills.shipping` are `DOUBLE` (a bug)
- [ ] Identified that `quotations.regular_billing_locked` exists and must be removed
- [ ] Understood the `bill_challans` → `bill_items` relationship chain
- [ ] Read all 4 sections of the architecture report
- [ ] Understand the difference between the 3 bill types and the new workflow
- [ ] Noted that `parent_bill_id` on `bills` is the self-referential FK for running bills
- [ ] Understood that `bill_advance_adjustments` is a NEW table that does not exist yet
- [ ] Understood that `bill_payments` is a NEW table that does not exist yet
- [ ] Are ready to produce code in Laravel 11 syntax (not Laravel 9 or 10)

**Only begin generating the implementation guide after confirming all items above.**

---

*End of AI Agent Instruction Prompt*
*Architecture Report Reference: billing_module_architecture_report.docx*
*Schema Reference: beayar_2026-03-11.sql*
