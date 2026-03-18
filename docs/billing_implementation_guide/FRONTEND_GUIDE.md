# Frontend Billing Implementation Guide

**Date:** 2026-03-16  
**Version:** 1.0  
**Purpose:** Guide for frontend developers and stakeholders to understand frontend-visible billing features and workflows

---

## Executive Summary

This document describes all frontend changes, new features, user interface modifications, and user experience improvements resulting from the billing module implementation. The billing system now supports:

- **Three Bill Types:** Advance, Running, and Regular bills
- **6-Rule Locking System:** Prevents editing of bills in protected states
- **Advance Credit Management:** Apply advance payments to final bills
- **Payment Tracking:** Full payment recording and status management
- **Correction Workflow:** Cancel and reissue bills when corrections are needed

---

## Table of Contents

1. [Bill Types Overview](#bill-types-overview)
2. [Bill Statuses](#bill-statuses)
3. [Bill Creation Workflows](#bill-creation-workflows)
4. [Bill Management Interface](#bill-management-interface)
5. [Payment Recording](#payment-recording)
6. [Advance Credit System](#advance-credit-system)
7. [Correction Flow (Cancel/Reissue)](#correction-flow-cancelreissue)
8. [UI Components](#ui-components)
9. [User Experience Improvements](#user-experience-improvements)
10. [Testing Checklist](#testing-checklist)

---

## Bill Types Overview

### 1. Advance Bills (Amber/Orange Theme)

**Purpose:** Upfront payment before delivery

**Characteristics:**
- Created before any delivery occurs
- Typically represents a percentage of the total project value
- Can receive payments to build up credit balance
- Credit can be applied to Regular bills later
- Color theme: Amber/Orange (#F59E0B)

**Example Use Case:**
A customer agrees to pay 30% upfront for a project. An Advance bill is created for 30% of the quotation total.

---

### 2. Running Bills (Purple/Indigo Theme)

**Purpose:** Interim bills during project progress

**Characteristics:**
- Linked to a parent Advance bill
- Represents progress-based billing
- Multiple running bills can be created against one advance
- Shows remaining advance balance
- Color theme: Purple/Indigo (#8B5CF6)

**Example Use Case:**
During a long project, running bills are created for each milestone payment (e.g., 30% after design, 30% after development, 40% after delivery).

---

### 3. Regular Bills (Green/Teal Theme)

**Purpose:** Final bill after all deliveries complete

**Characteristics:**
- Created after all deliveries/challans are complete
- Can have advance credit applied to reduce payable amount
- Contains itemized products from linked challans
- Color theme: Green/Teal (#10B981)

**Example Use Case:**
After all products are delivered, a Regular bill is created for the remaining balance, optionally reduced by any advance credit.

---

## Bill Statuses

| Status | Description | Editable | Can Issue | Can Cancel | Can Record Payment |
|--------|-------------|----------|-----------|------------|-------------------|
| **Draft** | Newly created, not yet finalized | ✅ Yes | ✅ Yes | ❌ No | ❌ No |
| **Issued** | Sent to customer, awaiting payment | ❌ No | ❌ No | ✅ Yes | ✅ Yes |
| **Partially Paid** | Some payment received | ❌ No | ❌ No | ✅ Yes | ✅ Yes |
| **Paid** | Fully paid by customer | ❌ No | ❌ No | ❌ No | ❌ No |
| **Cancelled** | Cancelled (can be reissued) | ❌ No | ❌ No | ❌ No | ❌ No |
| **Adjusted** | Adjusted (e.g., credit applied) | ❌ No | ❌ No | ❌ No | ❌ No |

### Status Badge Colors

- **Draft:** Gray (`bg-gray-100`)
- **Issued:** Blue (`bg-blue-100`)
- **Partially Paid:** Yellow (`bg-yellow-100`)
- **Paid:** Green (`bg-green-100`)
- **Cancelled:** Red (`bg-red-100`)
- **Adjusted:** Indigo (`bg-indigo-100`)

---

## Bill Creation Workflows

### Creating an Advance Bill

**Route:** `/bills/create?type=advance&quotation_id={id}`

**Steps:**
1. Navigate to Bills → Create New Bill
2. Select "Advance Bill" type
3. Choose the quotation
4. Enter the advance amount or percentage
5. System calculates the total (amount + tax)
6. Select bill date and due date (optional)
7. Add notes and terms (optional)
8. Click "Create Advance Bill"

**Frontend Features:**
- Percentage input with automatic amount calculation
- Amount-to-percentage reverse calculation
- Due amount computation
- Currency type support from quotation revision
- Flowbite datepicker integration

---

### Creating a Running Bill

**Route:** `/bills/create?type=running&parent_bill_id={id}`

**Steps:**
1. Navigate to an existing Advance bill
2. Click "Create Running Bill" (if advance is paid/issued)
3. Enter the running bill amount or percentage
4. System shows remaining balance after this bill
5. Select bill date
6. Add notes (optional)
7. Click "Create Running Bill"

**Frontend Features:**
- Parent advance bill details displayed
- Running percentage/amount inputs with sync
- Remaining balance calculation after this bill
- Bill history timeline showing all previous bills
- Auto-generated invoice number (parent-A, parent-B pattern)

---

### Creating a Regular Bill

**Route:** `/bills/create?type=regular&quotation_id={id}`

**Steps:**
1. Navigate to Bills → Create New Bill
2. Select "Regular Bill" type
3. Choose the quotation
4. Select challans to include (checkbox selection)
5. System loads billable products from selected challans
6. Adjust quantities if needed (cannot exceed delivered)
7. Enter tax amount and shipping (optional)
8. Optionally apply advance credit
9. Select bill date and due date
10. Add notes and terms (optional)
11. Click "Create Regular Bill"

**Frontend Features:**
- Challan selection with expandable accordion UI
- Dynamic bill items table with quantity input
- Real-time subtotal/total calculation
- Advance credit banner (if credit available)
- Advance credit application modal
- Discount, VAT, shipping inputs
- Round-up calculation option

**Important Change:** Regular bills are NO LONGER blocked by existing advance bills. Credit can be applied during creation or after.

---

## Bill Management Interface

**Route:** `/bills` or `/bills/{id}/manage`

### Action Buttons (Policy-Gated)

| Button | Visibility Condition | Action |
|--------|---------------------|--------|
| **Print** | Always | Opens print-friendly invoice view |
| **Edit** | `@can('update', $bill)` - Draft only | Opens edit form |
| **Issue Bill** | `@can('issue', $bill)` - Draft only | Issues the bill |
| **Record Payment** | `@can('recordPayment', $bill)` - Issued/Partially Paid | Opens payment modal |
| **Cancel** | `@can('cancel', $bill)` - Issued/Partially Paid | Opens cancellation modal |
| **Reissue** | `@can('reissue', $bill)` - Cancelled only | Reissues as new draft |

### Bill Summary Section

Displays financial breakdown:
- Subtotal
- Discount (if applicable)
- Shipping
- Tax Amount
- **Advance Applied** (highlighted in green)
- **Net Payable** (bold, calculated)
- Paid Amount
- **Remaining Balance** (red if unpaid, green if paid)

### Bill Items Table (Regular Bills)

Shows itemized products:
| Column | Description |
|--------|-------------|
| Product | Product name from quotation |
| Quantity | Billed quantity (editable in draft) |
| Unit Price | Price per unit |
| Total | Line total |

### Payments List

Shows all recorded payments:
- Payment amount
- Date
- Method (Cash, Bank Transfer, Check, Credit Card, UPI, Other)
- Reference number
- Recorded by (user name)
- Void button (for non-fully-paid bills)

---

## Payment Recording

**Route:** `/bills/{id}/payments` (via modal)

### Payment Modal Fields

| Field | Type | Required | Validation |
|-------|------|----------|------------|
| Amount | Number | Yes | > 0, ≤ remaining balance |
| Payment Method | Dropdown | Yes | cash, bank_transfer, check, credit_card, upi, other |
| Payment Date | Date | Yes | Cannot be future date |
| Reference Number | Text | No | Max 100 characters |
| Notes | Textarea | No | Max 1000 characters |

### Payment Status Flow

```
Issued → Partially Paid → Paid
         (when partial payment received)
                    ↓
              (when fully paid)
                    ↓
                   Paid
```

### Payment Recording UX

1. Click "Record Payment" button
2. Modal opens with current remaining balance shown
3. Enter payment amount (default: remaining balance)
4. Select payment method
5. Select payment date (default: today)
6. Optionally enter reference number
7. Click "Record Payment"
8. Status updates automatically:
   - If amount < remaining → "Partially Paid"
   - If amount ≥ remaining → "Paid"

---

## Advance Credit System

### Advance Credit Banner

A banner displayed on quotations and bill creation pages showing:

```
┌─────────────────────────────────────────────────────────┐
│ 💳 Advance Credit Available                            │
│ 2 advance bill(s) with remaining balance               │
│                                                         │
│                    ৳ 15,000.00                          │
│                  Available Balance                      │
│                                                         │
│ [Show details ▼]                      [Apply to Bill]  │
└─────────────────────────────────────────────────────────┘
```

**Details (expandable):**
- Total Advance: Sum of all advance bills
- Total Received: Sum of all payments on advances
- Total Applied: Sum of all credit applied to regular bills
- **Available Balance:** Total Received - Total Applied
- List of individual advances with remaining balances

### Applying Advance Credit

**Route:** `/bills/{id}/apply-advance` (via modal or during creation)

**Process:**
1. Click "Apply Advance Credit" button
2. Modal shows available advance bills with balances
3. Select the advance bill to apply credit from
4. Enter amount to apply (or click "Max")
5. Preview shows:
   - Bill Total
   - Credit Applied (-)
   - **Net Payable** (result)
6. Click "Apply"
7. Bill updates:
   - `advance_applied_amount` increases
   - `net_payable_amount` decreases
   - Status badge shows "Adjusted" if applicable

### Credit Application Rules

- ✅ Credit can only be applied to **Regular bills**
- ✅ Credit can only be applied from **Advance bills** on the **same quotation**
- ✅ Credit can only be applied up to the **available balance** of the advance
- ✅ Credit can be applied during Regular bill creation OR after creation
- ✅ Credit can be **removed** from draft Regular bills
- ❌ Credit **cannot** be applied to Running bills
- ❌ Credit **cannot** be applied to Advance bills

### Advance Credit Management View

**Route:** `/quotations/{id}/advance-credit`

Shows:
- Summary cards (Total Advance, Received, Applied, Available)
- List of all advance bills with:
  - Status badges
  - Total, Received, Available amounts
  - Expandable details showing:
    - Credit applications to regular bills
    - Remove credit option for draft bills

---

## Correction Flow (Cancel/Reissue)

### Cancelling a Bill

**Route:** `/bills/{id}/cancel` (via modal)

**Cancellation Modal Shows:**

1. **Impact Warnings:**
   - Bill will be marked as "Cancelled"
   - If Regular bill with advance credit → Credit will be **reversed**
   - If bill has payments → Payment records preserved
   - If Advance bill with running bills → Warning displayed

2. **Confirmation Checkbox:**
   - "I understand this action cannot be undone"

3. **Reason Field:**
   - Optional textarea for cancellation reason

**Cancellation UX:**
1. Click "Cancel Bill" button
2. Modal shows impact warnings
3. Enter reason (optional)
4. Check confirmation checkbox
5. Click "Cancel Bill"
6. Redirected to success page

### Cancelled Bill Success Page

**Route:** `/bills/{id}/cancelled`

Shows:
- Success header with cancelled indicator
- Bill details summary
- Reversed advance credit amount (if applicable)
- **Reissue Bill** button (creates new draft)
- **View All Bills** link

---

### Reissuing a Bill

**Route:** `/bills/{id}/reissue`

**Process:**
1. Click "Reissue Bill" on a cancelled bill
2. Modal shows what will be copied:
   - Same bill type
   - Same line items and amounts
   - Same challan links
   - New bill number (auto-generated)
3. What won't be copied:
   - Payments (need to be re-recorded)
   - Advance credit (need to be re-applied)
4. Click "Create New Draft"
5. Redirected to reissued success page

### Reissued Bill Success Page

**Route:** `/bills/{id}/reissued`

Shows:
- Success header with reissued indicator
- Comparison: Old (cancelled) vs New (draft) bill
- Next steps checklist:
  1. Review the new draft bill
  2. Re-apply advance credit if needed
  3. Issue the bill when ready
- Action buttons:
  - **Edit & Issue** - Opens edit form
  - **View Draft** - Opens bill detail

### Correction History Component

Displayed on bill detail pages when a bill is part of a correction chain:

```
┌─────────────────────────────────────────────────────────┐
│ 🕐 Correction History                                    │
│                                                         │
│  ○ ──● ──● ──● (timeline)                            │
│   │    │    │                                          │
│  REG-001  REG-002  REG-003                           │
│  (cancelled)  (current)                              │
└─────────────────────────────────────────────────────────┘
```

Shows:
- Timeline of cancelled → reissued chain
- Links to each bill in the chain
- Current bill highlighted

---

## UI Components

### Bill Timeline Component

**File:** `resources/views/tenant/bills/partials/bill-timeline.blade.php`

Shows all bills for a quotation in chronological order:
- Color-coded icons by bill type
- Status badges
- Lock indicator for locked bills
- Expandable details (paid amount, remaining balance, parent/child bills)

### Advance Credit Banner Component

**File:** `resources/views/tenant/bills/partials/advance-credit-banner.blade.php`

Reusable component showing advance credit status:
- Summary totals
- Expandable individual advance details
- Apply button (optional)

### Correction History Component

**File:** `resources/views/tenant/bills/partials/correction-history.blade.php`

Shows the chain of cancelled → reissued bills:
- Visual timeline
- Links to related bills

---

## User Experience Improvements

### 1. Real-Time Calculations

All monetary calculations happen in real-time on the frontend:
- Subtotal updates as items change
- Tax and shipping automatically added
- Advance credit immediately reflected in net payable
- Remaining balance updates when payments are recorded

### 2. Visual Feedback

- **Loading states** during form submissions
- **Success messages** after actions complete
- **Error alerts** with specific field highlights
- **Disabled buttons** for unavailable actions with tooltips

### 3. Dark Mode Support

All billing views fully support dark mode:
- Appropriate background colors
- Contrast-compliant text colors
- Status badge color adjustments

### 4. Responsive Design

- Mobile-friendly layouts
- Collapsible sections on smaller screens
- Touch-friendly inputs

### 5. Policy-Based UI

Buttons and actions automatically show/hide based on user permissions:
- Only users with `bills.edit` can see Edit button
- Only users with `bills.issue` can see Issue button
- Only users with `bills.cancel` can see Cancel button

### 6. Datepicker Integration

All date fields use Flowbite datepicker:
- Format: dd/mm/yyyy
- Reactive to form state
- Accessible on mobile

---

## Frontend File Structure

### Views Directory

```
resources/views/tenant/bills/
├── create.blade.php              # Bill type selection
├── create-advance.blade.php     # Advance bill creation
├── create-regular.blade.php      # Regular bill creation
├── create-running.blade.php      # Running bill creation
├── edit.blade.php                # Edit type selection
├── edit-advance.blade.php        # Edit advance bill
├── edit-regular.blade.php        # Edit regular bill
├── edit-running.blade.php        # Edit running bill
├── index.blade.php               # Bills list
├── manage.blade.php              # Bill detail/management
├── show.blade.php                # Bill detail view
├── cancelled.blade.php           # Cancellation success
├── reissue.blade.php             # Reissue form
├── reissued.blade.php            # Reissue success
├── advance-credit.blade.php      # Advance credit management
└── partials/
    ├── advance-credit-banner.blade.php
    ├── bill-timeline.blade.php
    └── correction-history.blade.php
```

---

## API Endpoints for Frontend

### AJAX Endpoints

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/quotations/{id}/billable-challans` | Get unbilled challans |
| GET | `/api/quotations/{id}/available-advances` | Get advances with balance |
| GET | `/api/quotations/{id}/bill-summary` | Get all bills for quotation |
| GET | `/api/bills/{id}/advance-balance` | Get advance bill balance |
| GET | `/api/bills/{id}/status` | Get bill status info |
| GET | `/api/bills/search?q={query}` | Search bills |

---

## Testing Checklist

### Bill Creation

- [ ] Can create advance bill with valid data
- [ ] Percentage calculation works correctly
- [ ] Amount-to-percentage reverse calculation works
- [ ] Can create running bill linked to advance
- [ ] Parent advance details displayed correctly
- [ ] Running balance calculation is accurate
- [ ] Can create regular bill with challan selection
- [ ] Challan selection updates bill items
- [ ] Quantity validation prevents over-billing
- [ ] Regular bill creation NOT blocked by existing advance

### Bill Operations

- [ ] Can issue a draft bill
- [ ] Draft bill locks after issuing
- [ ] Edit button only visible for draft bills
- [ ] Issue button only visible for draft bills
- [ ] Cancel button only visible for issued bills
- [ ] Can cancel an issued bill
- [ ] Cancellation shows impact warnings
- [ ] Advance credit reverses on regular bill cancellation
- [ ] Can reissue a cancelled bill
- [ ] Reissued bill is a new draft
- [ ] Reissue chain is tracked

### Advance Credit

- [ ] Advance credit banner displays correctly
- [ ] Banner shows correct totals
- [ ] Can apply advance credit during bill creation
- [ ] Can apply advance credit from bill detail
- [ ] Over-application is rejected with error
- [ ] Can remove credit from draft bill
- [ ] Credit restores on bill cancellation
- [ ] Regular bills show net payable after credit

### Payments

- [ ] Can record payment on issued bill
- [ ] Amount validation prevents over-payment
- [ ] Status updates to partially_paid correctly
- [ ] Status updates to paid when fully paid
- [ ] Can void payment on partially paid bill
- [ ] Cannot void payment on fully paid bill

### UI/UX

- [ ] All forms have proper error handling
- [ ] Loading states display during submission
- [ ] Success messages display after actions
- [ ] Timeline shows all bills correctly
- [ ] Correction history displays correctly
- [ ] Status badges have correct colors
- [ ] Dark mode works correctly
- [ ] Responsive design works on mobile

---

## Migration Notes

### Key Database Changes

1. **bills table:**
   - `is_locked` - Boolean for quick lock check
   - `lock_reason` - Enum explaining why locked
   - `locked_at` - Timestamp when locked
   - `advance_applied_amount` - Credit applied
   - `net_payable_amount` - Final amount after credit
   - `reissued_from_id` - Original bill if reissued
   - `reissued_to_id` - New bill if reissued

2. **bill_payments table:**
   - New table for payment tracking
   - Links to bills with amount, method, date

3. **bill_advance_adjustments table:**
   - Tracks credit from advance → regular bills
   - Enables reversal tracking

4. **quotations table:**
   - `billing_stage` - Tracks billing progress
   - Values: none, advance_pending, advance_issued, running_in_progress, regular_pending, completed, cancelled

---

## Support and Troubleshooting

### Common Issues

**Issue:** "Bill cannot be edited" error
- **Cause:** Bill is locked (not in draft status)
- **Solution:** Check bill status and lock reason

**Issue:** "Advance credit exceeds available balance" error
- **Cause:** Trying to apply more credit than available
- **Solution:** Reduce amount to available balance or use different advance

**Issue:** "Bill is already cancelled" error
- **Cause:** Trying to cancel an already cancelled bill
- **Solution:** Use Reissue to create a new bill

**Issue:** Payment amount exceeds remaining balance
- **Cause:** Trying to over-pay a bill
- **Solution:** Reduce payment amount or use correct bill

---

## Conclusion

This frontend implementation provides a comprehensive billing system with:

- ✅ Clear bill type differentiation (Advance/Running/Regular)
- ✅ Intuitive bill creation workflows
- ✅ Flexible advance credit management
- ✅ Complete payment tracking
- ✅ Smooth correction flow (cancel/reissue)
- ✅ Policy-based access control
- ✅ Responsive, accessible UI

All features are implemented according to the billing module specification and tested for both functionality and user experience.
