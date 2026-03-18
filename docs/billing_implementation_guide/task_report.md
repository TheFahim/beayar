# Billing Module Implementation Task Report

**Date:** 2026-03-16
**Status:** Phase 1, Phase 2, Phase 3, Phase 4, Phase 5 & Phase 6 Completed

---

## Summary

This report documents the implementation work completed for the billing module refactor. Phase 1 (Database Foundation), Phase 2 (Backend Core & Services), Phase 3 (Backend API & Controllers), Phase 4 (Frontend Bill Creation), Phase 5 (Frontend Advance Credits), and Phase 6 (Frontend Correction Flow) have been successfully implemented.

---

## Phase 1: Database Foundation

### Migrations Verified (All 8 migrations present and correct)

| Migration | Status | Description |
|------------|--------|-------------|
| `2026_03_12_000001_fix_bills_decimal_columns.php` | ✅ Verified | Changes `due` and `shipping` columns from DOUBLE to DECIMAL(15,2) |
| `2026_03_12_000002_add_locking_to_bills_table.php` | ✅ Verified | Adds `is_locked`, `lock_reason`, `locked_at` columns |
| `2026_03_12_000003_add_credit_tracking_to_bills_table.php` | ✅ Verified | Adds `advance_applied_amount`, `net_payable_amount` columns |
| `2026_03_12_000004_extend_bills_status_enum.php` | ✅ Verified | Extends status ENUM with `partially_paid`, `adjusted` |
| `2026_03_12_000005_create_bill_payments_table.php` | ✅ Verified | Creates `bill_payments` table with payment tracking |
| `2026_03_12_000006_create_bill_advance_adjustments_table.php` | ✅ Verified | Creates `bill_advance_adjustments` table for credit application |
| `2026_03_12_000007_update_quotations_billing_stage.php` | ✅ Verified | Replaces `regular_billing_locked` with `billing_stage` ENUM |
| `2026_03_12_000008_add_billing_performance_indexes.php` | ✅ Verified | Adds performance indexes for billing queries |

### Models Updated

#### Bill Model (`app/Models/Bill.php`)
- **Added fillable fields:**
  - `is_locked`, `lock_reason`, `locked_at` (locking fields)
  - `advance_applied_amount`, `net_payable_amount` (credit tracking fields)
- **Added casts:**
  - `is_locked` => `boolean`
  - `locked_at` => `datetime`
  - `advance_applied_amount` => `decimal:2`
  - `net_payable_amount` => `decimal:2`
- **Added constants:**
  - Status constants: `STATUS_DRAFT`, `STATUS_ISSUED`, `STATUS_PAID`, `STATUS_CANCELLED`, `STATUS_PARTIALLY_PAID`, `STATUS_ADJUSTED`
  - Type constants: `TYPE_ADVANCE`, `TYPE_REGULAR`, `TYPE_RUNNING`
  - Lock reason constants: `LOCK_REASON_STATUS`, `LOCK_REASON_CHILD`, `LOCK_REASON_PAYMENTS`, `LOCK_REASON_CHALLAN`, `LOCK_REASON_ADVANCE`, `LOCK_REASON_ADJUSTMENTS`

#### Quotation Model (`app/Models/Quotation.php`)
- **Added billing stage constants:**
  - `BILLING_STAGE_NONE`, `BILLING_STAGE_ADVANCE_PENDING`, `BILLING_STAGE_ADVANCE_ISSUED`
  - `BILLING_STAGE_RUNNING_IN_PROGRESS`, `BILLING_STAGE_REGULAR_PENDING`
  - `BILLING_STAGE_COMPLETED`, `BILLING_STAGE_CANCELLED`

### New Models Created

#### BillPayment Model (`app/Models/BillPayment.php`)
- Tracks individual payment records for bills
- Fields: `bill_id`, `tenant_company_id`, `amount`, `payment_method`, `payment_date`, `reference_number`, `notes`, `created_by`, `updated_by`
- Payment method constants: `METHOD_CASH`, `METHOD_BANK_TRANSFER`, `METHOD_CHECK`, `METHOD_CREDIT_CARD`, `METHOD_MFS`, `METHOD_OTHER`
- Relationships: `bill()`, `creator()`, `updater()`

#### BillAdvanceAdjustment Model (`app/Models/BillAdvanceAdjustment.php`)
- Tracks advance credit application from advance bills to regular bills
- Fields: `advance_bill_id`, `final_bill_id`, `tenant_company_id`, `amount`, `created_by`, `notes`
- Relationships: `advanceBill()`, `finalBill()`, `creator()`

---

## Phase 2: Backend Core & Services

### Exception Created

#### BillLockedException (`app/Exceptions/BillLockedException.php`)
- Custom exception for handling locked bill modifications
- Human-readable messages for each lock reason
- Renders JSON response for API requests, redirect for web requests

### Bill Model Enhancements

#### Additional Relationships
- `payments()` - HasMany relationship to BillPayment
- `advanceAdjustmentsGiven()` - HasMany for credit given from advance bills
- `advanceAdjustmentsReceived()` - HasMany for credit received on regular bills

#### Query Scopes
- `scopeDraft()`, `scopeIssued()`, `scopeUnpaid()`
- `scopeAdvance()`, `scopeRegular()`, `scopeRunning()`
- `scopeLocked()`, `scopeUnlocked()`

#### Accessors
- `getUnappliedAmountAttribute()` - Available credit for advance bills
- `getPaidAmountAttribute()` - Total payments received
- `getRemainingBalanceAttribute()` - Amount still owed
- `getIsFullyPaidAttribute()` - Boolean check for full payment

#### 6-Rule Locking System
- **Rule 1:** Status guard - only draft bills are editable
- **Rule 2:** Child bill guard - advance bills with issued children are locked
- **Rule 3:** Payment guard - bills with payments are locked
- **Rule 4:** Challan link guard - quantity violations lock bills
- **Rule 5:** Advance application guard - applied advances are immutable
- **Rule 6:** Adjustment reference guard - regular bills with adjustments are locked

Methods: `canBeEdited()`, `getLockReason()`, `lock()`, `unlock()`

#### Model Boot Event
- Automatic lock check on model update
- Throws `BillLockedException` if modification is blocked

### BillingService Enhancements (`app/Services/BillingService.php`)

#### New Methods Added
- `applyAdvanceCredit()` - Apply advance credit to a regular bill
- `removeAdvanceCredit()` - Remove applied credit (for cancellations)
- `issueBill()` - Change status from draft to issued
- `cancelBill()` - Cancel a bill with reason
- `recordPayment()` - Record a payment for a bill
- `updateBillPaymentStatus()` - Update status based on payment totals
- `getUnappliedAdvanceBalance()` - Get available advance credit
- `getBillableChallans()` - Get unbilled challans for a quotation

### Form Requests Created

#### ApplyAdvanceCreditRequest (`app/Http/Requests/ApplyAdvanceCreditRequest.php`)
- Validates advance bill selection and amount
- Checks advance bill type and quotation match
- Validates available balance before application

#### RecordPaymentRequest (`app/Http/Requests/RecordPaymentRequest.php`)
- Validates payment amount and method
- Checks bill status (issued or partially paid)
- Validates amount doesn't exceed remaining balance

### BillPolicy Enhancements (`app/Policies/BillPolicy.php`)

#### New Policy Methods
- `issue()` - Check if user can issue a bill
- `cancel()` - Check if user can cancel a bill
- `recordPayment()` - Check if user can record payments
- `applyAdvance()` - Check if user can apply advance credit

#### Updated Methods
- `update()` - Now uses `canBeEdited()` for lock checking
- `delete()` - Now restricts to draft bills only

---

## Phase 3: Backend API & Controllers

### Controllers Created

#### BillController Enhancements (`app/Http/Controllers/Tenant/BillController.php`)
- **New methods added:**
  - `issue()` - Issue a draft bill
  - `cancel()` - Cancel an issued/partially paid bill
  - `applyAdvance()` - Apply advance credit to a regular bill
  - `removeAdvance()` - Remove applied advance credit
- All methods support both JSON and web responses
- Authorization via BillPolicy

#### BillPaymentController (`app/Http/Controllers/Tenant/BillPaymentController.php`)
- **Methods:**
  - `index()` - List payments for a bill
  - `store()` - Record a new payment
  - `destroy()` - Void a payment
- Payment voiding updates bill status automatically
- Activity logging for audit trail

#### BillApiController (`app/Http/Controllers/Tenant/BillApiController.php`)
- **AJAX endpoints for frontend:**
  - `billableChallans()` - Get unbilled challans for a quotation
  - `availableAdvances()` - Get advance bills with available credit
  - `advanceBalance()` - Get advance bill balance and adjustments
  - `quotationBillSummary()` - Get all bills for a quotation with totals
  - `status()` - Quick bill status check
  - `search()` - Search bills by number or quotation

### Helper Functions Created

#### `app/helpers.php`
- `currentTenantId()` - Get current tenant company ID from session or user
- `currentTenant()` - Get current tenant company model
- `tenant()` - Alias for currentTenant()
- Registered in `composer.json` autoload files

### Routes Registered

#### Web Routes (`routes/web.php`)
- **Bill Actions:**
  - `POST /bills/{bill}/issue` - Issue a bill
  - `POST /bills/{bill}/cancel` - Cancel a bill
  - `POST /bills/{bill}/apply-advance` - Apply advance credit
  - `DELETE /bills/{bill}/advance-adjustments/{adjustment}` - Remove advance credit
- **Bill Payments:**
  - `GET /bills/{bill}/payments` - List payments
  - `POST /bills/{bill}/payments` - Record payment
  - `DELETE /bills/{bill}/payments/{payment}` - Void payment
- **API Endpoints:**
  - `GET /api/quotations/{quotation}/billable-challans`
  - `GET /api/quotations/{quotation}/available-advances`
  - `GET /api/quotations/{quotation}/bill-summary`
  - `GET /api/bills/{bill}/advance-balance`
  - `GET /api/bills/{bill}/status`
  - `GET /api/bills/search`

---

## Files Modified/Created Summary

### Phase 1
| File | Action |
|------|--------|
| `app/Models/Bill.php` | Modified |
| `app/Models/Quotation.php` | Modified |
| `app/Models/BillPayment.php` | Created |
| `app/Models/BillAdvanceAdjustment.php` | Created |

### Phase 2
| File | Action |
|------|--------|
| `app/Exceptions/BillLockedException.php` | Created |
| `app/Models/Bill.php` | Modified |
| `app/Services/BillingService.php` | Modified |
| `app/Http/Requests/ApplyAdvanceCreditRequest.php` | Created |
| `app/Http/Requests/RecordPaymentRequest.php` | Created |
| `app/Policies/BillPolicy.php` | Modified |

### Phase 3
| File | Action |
|------|--------|
| `app/helpers.php` | Created |
| `app/Http/Controllers/Tenant/BillController.php` | Modified |
| `app/Http/Controllers/Tenant/BillPaymentController.php` | Created |
| `app/Http/Controllers/Tenant/BillApiController.php` | Created |
| `routes/web.php` | Modified |
| `composer.json` | Modified |

---

## Phase 4: Frontend Bill Creation

### Blade Views Implemented

#### Regular Bill Creation (`resources/views/tenant/bills/create-regular.blade.php`)
- **Features:**
  - Challan selection with expandable accordion UI
  - Dynamic bill items with quantity input and price calculation
  - Alpine.js state management for selections and totals
  - Client-side validation matching backend validation rules
  - Flowbite datepicker for bill_date and payment_received_date
  - Previous bills display for reference
  - Discount, VAT, shipping, and round-up calculations
  - Formatted with Tailwind CSS and dark mode support

#### Advance Bill Creation (`resources/views/tenant/bills/create-advance.blade.php`)
- **Features:**
  - Percentage-based advance calculation
  - Amount-to-percentage reverse calculation
  - Due amount computation
  - Quotation details sidebar
  - Flowbite datepicker integration
  - Validation for percentage bounds (1-100%)
  - Support for VIA currency type from quotation revision

#### Running Bill Creation (`resources/views/tenant/bills/create-running.blade.php`)
- **Features:**
  - Parent advance bill details display
  - Running percentage and amount inputs with sync
  - Remaining balance calculation after this bill
  - Bill history timeline showing all previous bills
  - Invoice number auto-generation (parent-A, parent-B pattern)
  - Currency support matching parent bill revision

### New Components Created

#### Bill Timeline Component (`resources/views/tenant/bills/partials/bill-timeline.blade.php`)
- Visual timeline showing all bills for a quotation
- Color-coded icons by bill type (amber for advance, purple for running, green for regular)
- Status badges with appropriate colors
- Lock indicator for locked bills
- Expandable details section with Alpine.js collapse
- Links to individual bill views

#### Bill Management View (`resources/views/tenant/bills/manage.blade.php`)
- **Action Buttons (Policy-gated):**
  - Print View - Opens print-friendly invoice view
  - Edit - Only visible when `@can('update', $bill)`
  - Issue Bill - Only visible when `@can('issue', $bill)`
  - Record Payment - Only visible when `@can('recordPayment', $bill)`
  - Cancel Bill - Only visible when `@can('cancel', $bill)`
- **Payment Recording Modal:**
  - Amount input with max validation
  - Payment method dropdown
  - Flowbite datepicker for payment date
  - Reference number and notes fields
- **Cancel Bill Modal:**
  - Reason textarea
  - Warning for applied advance credit
- **Summary Section:**
  - Subtotal, discount, shipping, tax breakdown
  - Advance applied amount (highlighted in green)
  - Net payable calculation
  - Paid amount and remaining balance
- **Bill Items Table:** For regular bills showing product details
- **Payments List:** With void option for non-paid bills

### UI/UX Features

- **Consistent Color Coding:**
  - Advance bills: Amber/Orange theme
  - Running bills: Purple/Indigo theme
  - Regular bills: Green/Indigo theme
- **Status Badges:**
  - Draft: Gray
  - Issued: Blue
  - Paid: Green
  - Cancelled: Red
  - Partially Paid: Yellow
  - Adjusted: Indigo
- **Dark Mode Support:** All views support dark mode with appropriate color classes
- **Responsive Design:** Mobile-friendly layouts with grid systems
- **Sticky Action Bar:** Quick access to primary actions while scrolling
- **Validation Error Display:** Styled error alerts with error count

### Datepicker Integration

All date inputs use the existing Flowbite datepicker implementation:
- `flowbite-datepicker` class on text inputs
- Format: dd/mm/yyyy (matches backend validation)
- Integrated with Alpine.js for reactive updates
- Located in: `create-regular.blade.php`, `create-advance.blade.php`, `create-running.blade.php`, `manage.blade.php`

---

## Files Modified/Created Summary

### Phase 1
| File | Action |
|------|--------|
| `app/Models/Bill.php` | Modified |
| `app/Models/Quotation.php` | Modified |
| `app/Models/BillPayment.php` | Created |
| `app/Models/BillAdvanceAdjustment.php` | Created |

### Phase 2
| File | Action |
|------|--------|
| `app/Exceptions/BillLockedException.php` | Created |
| `app/Models/Bill.php` | Modified |
| `app/Services/BillingService.php` | Modified |
| `app/Http/Requests/ApplyAdvanceCreditRequest.php` | Created |
| `app/Http/Requests/RecordPaymentRequest.php` | Created |
| `app/Policies/BillPolicy.php` | Modified |

### Phase 3
| File | Action |
|------|--------|
| `app/helpers.php` | Created |
| `app/Http/Controllers/Tenant/BillController.php` | Modified |
| `app/Http/Controllers/Tenant/BillPaymentController.php` | Created |
| `app/Http/Controllers/Tenant/BillApiController.php` | Created |
| `routes/web.php` | Modified |
| `composer.json` | Modified |

### Phase 4
| File | Action |
|------|--------|
| `resources/views/tenant/bills/create-regular.blade.php` | Verified/Existing |
| `resources/views/tenant/bills/create-advance.blade.php` | Verified/Existing |
| `resources/views/tenant/bills/create-running.blade.php` | Verified/Existing |
| `resources/views/tenant/bills/partials/bill-timeline.blade.php` | Created |
| `resources/views/tenant/bills/manage.blade.php` | Created |

---

## Phase 5: Frontend Advance Credits

### Blade Views Implemented

#### Advance Credit Banner Component (`resources/views/tenant/bills/partials/advance-credit-banner.blade.php`)
- **Features:**
  - Reusable Blade component for displaying advance credit status
  - Shows total advance, total received, total applied, and available balance
  - Expandable details showing individual advance bills with remaining balances
  - "Apply to Bill" button (conditionally shown based on `showApplyButton` prop)
  - Success banner when all credit has been applied
  - Alpine.js integration for expand/collapse functionality
  - Dark mode support with appropriate color classes

#### Advance Credit Management View (`resources/views/tenant/bills/advance-credit.blade.php`)
- **Features:**
  - Summary cards showing total advance, received, applied, and available balance
  - List of all advance bills for a quotation with expandable details
  - Credit applications listed per advance bill
  - Modal for applying credit to regular bills
  - Remove credit functionality for draft bills
  - Bill selection dropdown with existing credit info
  - Amount input with max button for quick filling
  - Preview section showing net payable after credit application
  - Responsive design with Tailwind CSS

### Blade Component Created

#### AdvanceCreditBanner (`app/View/Components/AdvanceCreditBanner.php`)
- **Properties:**
  - `quotationId` - The quotation ID to fetch advance bills for
  - `showApplyButton` - Whether to show the "Apply to Bill" button
- **Render:** Returns the `tenant.bills.partials.advance-credit-banner` view

### Controller Method Added

#### BillController::advanceCreditManagement (`app/Http/Controllers/Tenant/BillController.php`)
- **Method:** `advanceCreditManagement(Quotation $quotation)`
- **Authorization:** Uses `view` policy on quotation
- **Data Retrieved:**
  - All advance bills for the quotation (excluding cancelled)
  - Draft regular bills that can receive credit
  - Summary calculations (total advance, received, applied, available)
- **Returns:** `tenant.bills.advance-credit` view

### Routes Registered

#### Web Routes (`routes/web.php`)
- `GET /quotations/{quotation}/advance-credit` - Advance credit management view
  - Route name: `tenant.quotations.advance-credit`

---

## Files Modified/Created Summary

### Phase 1
| File | Action |
|------|--------|
| `app/Models/Bill.php` | Modified |
| `app/Models/Quotation.php` | Modified |
| `app/Models/BillPayment.php` | Created |
| `app/Models/BillAdvanceAdjustment.php` | Created |

### Phase 2
| File | Action |
|------|--------|
| `app/Exceptions/BillLockedException.php` | Created |
| `app/Models/Bill.php` | Modified |
| `app/Services/BillingService.php` | Modified |
| `app/Http/Requests/ApplyAdvanceCreditRequest.php` | Created |
| `app/Http/Requests/RecordPaymentRequest.php` | Created |
| `app/Policies/BillPolicy.php` | Modified |

### Phase 3
| File | Action |
|------|--------|
| `app/helpers.php` | Created |
| `app/Http/Controllers/Tenant/BillController.php` | Modified |
| `app/Http/Controllers/Tenant/BillPaymentController.php` | Created |
| `app/Http/Controllers/Tenant/BillApiController.php` | Created |
| `routes/web.php` | Modified |
| `composer.json` | Modified |

### Phase 4
| File | Action |
|------|--------|
| `resources/views/tenant/bills/create-regular.blade.php` | Verified/Existing |
| `resources/views/tenant/bills/create-advance.blade.php` | Verified/Existing |
| `resources/views/tenant/bills/create-running.blade.php` | Verified/Existing |
| `resources/views/tenant/bills/partials/bill-timeline.blade.php` | Created |
| `resources/views/tenant/bills/manage.blade.php` | Created |

### Phase 5
| File | Action |
|------|--------|
| `resources/views/tenant/bills/partials/advance-credit-banner.blade.php` | Created |
| `app/View/Components/AdvanceCreditBanner.php` | Created |
| `resources/views/tenant/bills/advance-credit.blade.php` | Created |
| `resources/views/tenant/quotations/show.blade.php` | Modified |
| `app/Http/Controllers/Tenant/BillController.php` | Modified |
| `routes/web.php` | Modified |

---

## Phase 6: Frontend Correction Flow

### Database Changes

#### Migration Created (`database/migrations/2026_03_16_000001_add_reissue_tracking_to_bills.php`)
- **New columns:**
  - `reissued_from_id` - Foreign key to the original cancelled bill
  - `reissued_to_id` - Foreign key to the new reissued bill
- **Purpose:** Track the chain of cancelled and reissued bills for audit purposes

### Models Updated

#### Bill Model (`app/Models/Bill.php`)
- **Added fillable fields:**
  - `reissued_from_id`, `reissued_to_id` (reissue tracking fields)
- **New relationships:**
  - `reissuedFrom()` - BelongsTo relationship to the original cancelled bill
  - `reissuedTo()` - BelongsTo relationship to the new reissued bill
  - `childBills()` - HasMany relationship for running bills from advance

### Policy Enhanced

#### BillPolicy (`app/Policies/BillPolicy.php`)
- **New method:** `reissue()` - Check if user can reissue a cancelled bill
- **Authorization:** Requires `edit_bills` permission and bill must be cancelled

### Service Enhanced

#### BillingService (`app/Services/BillingService.php`)
- **New methods:**
  - `reissueBill()` - Create a new bill from a cancelled one with tracking
  - `replicateBillItems()` - Copy bill items from cancelled bill to new bill
- **Features:**
  - Transaction-based creation for data integrity
  - Automatic tracking of reissue chain
  - Activity logging for audit trail
  - Support for all bill types (advance, regular, running)

### Controllers Enhanced

#### BillController (`app/Http/Controllers/Tenant/BillController.php`)
- **New methods:**
  - `cancelled()` - Display cancelled bill success page
  - `showReissueForm()` - Show reissue form for cancelled bill
  - `reissue()` - Process bill reissuance
  - `reissued()` - Display reissued bill success page
- **Updated methods:**
  - `cancel()` - Now redirects to cancelled success view

### Routes Registered

#### Web Routes (`routes/web.php`)
- `GET /bills/{bill}/cancelled` - Cancelled bill success page
- `GET /bills/{bill}/reissue` - Reissue form view
- `POST /bills/{bill}/reissue` - Process reissuance
- `GET /bills/{bill}/reissued` - Reissued bill success page

### Blade Views Created

#### Cancelled Bill Success View (`resources/views/tenant/bills/cancelled.blade.php`)
- **Features:**
  - Success header with cancelled indicator
  - Bill details summary (invoice, customer, amount, date)
  - Reissue button for cancelled bills (policy-gated)
  - Info card explaining next steps

#### Reissue Form View (`resources/views/tenant/bills/reissue.blade.php`)
- **Features:**
  - Original bill details summary
  - New invoice number input (auto-suggested)
  - Bill date and payment date inputs with Flowbite datepicker
  - Notes textarea
  - Warning info card about reissuance

#### Reissued Bill Success View (`resources/views/tenant/bills/reissued.blade.php`)
- **Features:**
  - Success header with reissued indicator
  - Reissue chain visualization (original → new)
  - Bill details summary
  - Draft status badge
  - Edit, Issue, and View action buttons (policy-gated)

#### Correction History Component (`resources/views/tenant/bills/partials/correction-history.blade.php`)
- **Features:**
  - Timeline visualization of cancelled → reissued chain
  - Color-coded status indicators (red for cancelled, green for current)
  - View links to related bills
  - Reissue link for cancelled bills
  - Only displays when bill is part of reissue chain

### Integration

#### Manage View Updated (`resources/views/tenant/bills/manage.blade.php`)
- Added correction-history component display
- Shows when bill has `reissued_from_id` or `reissued_to_id`

---

## Files Modified/Created Summary

### Phase 6
| File | Action |
|------|--------|
| `database/migrations/2026_03_16_000001_add_reissue_tracking_to_bills.php` | Created |
| `app/Models/Bill.php` | Modified |
| `app/Policies/BillPolicy.php` | Modified |
| `app/Services/BillingService.php` | Modified |
| `app/Http/Controllers/Tenant/BillController.php` | Modified |
| `routes/web.php` | Modified |
| `resources/views/tenant/bills/cancelled.blade.php` | Created |
| `resources/views/tenant/bills/reissue.blade.php` | Created |
| `resources/views/tenant/bills/reissued.blade.php` | Created |
| `resources/views/tenant/bills/partials/correction-history.blade.php` | Created |
| `resources/views/tenant/bills/manage.blade.php` | Modified |

---

## Next Steps

1. **Testing:** Write feature tests for bill cancellation and reissue workflows
2. **Documentation:** Update user documentation with correction flow features
3. **Migration:** Run `php artisan migrate` to apply reissue tracking columns

---

## Notes

- The `currentTenantId()` helper function is used throughout for multi-tenancy support
- All monetary calculations use `bcmath` functions for precision
- Activity logging is implemented via Laravel's logging facade
- The locking system prevents accidental modification of bills in invalid states
- All forms use existing Flowbite datepicker (no re-implementation needed)
- Alpine.js is used for reactive UI without additional framework overhead
- The advance credit banner component can be reused in multiple views (quotation show, bill create/edit)
- Credit can only be applied to or removed from draft bills
