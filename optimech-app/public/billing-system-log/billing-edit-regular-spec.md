# Technical Specification — Edit Regular Bill

## Scope
- Implement `resources/views/dashboard/bills/edit-regular.blade.php` aligned with `create-regular.blade.php`.
- Add backend update flow for regular bills, including validation, authorization, and integrity rules.
- Integrate routes and API request/response contracts.
- Provide testing and deployment guidance.

## References
- Consolidated design: `public/billing-system-log/BILLING_SYSTEM_CONSOLIDATED.md` (Last updated 2025-11-21)
  - Service layer and rules: `app/Services/BillingService.php:78`–`88`, `app/Services/BillingService.php:97`–`116`, `app/Services/BillingService.php:284`–`335`
  - Controller smart workflow and endpoints: `app/Http/Controllers/BillController.php:104`–`157`, `:162`–`189`, `:531`–`547`
  - Business rules: Advance/Regular/Running constraints at lines 180–193
- UI patterns to mirror: `resources/views/dashboard/bills/create-regular.blade.php`
  - Sticky action bar: `resources/views/dashboard/bills/create-regular.blade.php:13`–`41`
  - Client validation: `resources/views/dashboard/bills/create-regular.blade.php:205`–`234`
  - Form contract and hidden fields: `resources/views/dashboard/bills/create-regular.blade.php:236`–`244`
  - Challan products table and quantity handling: `resources/views/dashboard/bills/create-regular.blade.php:343`–`475`
  - Items payload synthesis: `resources/views/dashboard/bills/create-regular.blade.php:634`–`650`

---

## 1. Frontend Requirements

### View: `resources/views/dashboard/bills/edit-regular.blade.php`
- Breadcrumb and sticky action bar
  - Mirror styles and structure from `create-regular.blade.php:13`–`41`.
  - Replace primary CTA text with `Update Regular Bill`; submit form via button targeting `#regularBillEditForm`.
- Error and success messaging
  - Reuse validation error banner: list server-side errors via `$errors->any()` as in `create-regular.blade.php:43`–`84`.
  - Show success flash via layout’s existing flash component (consistent with bills index usage).
- Alpine state (`x-data`) prefilled from existing `Bill`
  - `bill`, `quotation`, `activeRevision`, `challans`, `expandedChallans`, `selectedProducts`, `selectedQuantity`, `items`, `subtotal`, `discount`, `shipping`, `total`, `clientSideErrors`.
  - Prepopulate `selectedProducts` and `items` by reading current bill’s `items` joined via pivot. Use server-prepared JSON in the view (controller supplies `bill`, `challans` with `remaining_quantity` computed excluding this bill’s current quantities; see Backend).
  - Maintain discount/shipping toggle logic consistent with `create-regular.blade.php:197`–`204`.
- Form structure
  - `form#regularBillEditForm` method `POST`, include `@method('PUT')`, action `route('bills.update-regular', $bill)`.
  - Hidden inputs
    - `bill_type=regular`, `quotation_id`, `quotation_revision_id` identical to create.
    - `discount`/`shipping` bound to Alpine and applied per toggles.
  - Basic information fields
    - `invoice_no` (string, unique except current bill), `bill_date` (`d/m/Y`), `payment_received_date` (`d/m/Y|nullable`), `notes` (<=1000).
    - Match markup and classes in `create-regular.blade.php:582`–`631`.
  - Challan products section
    - Render per-challan expandable tables mirroring `create-regular.blade.php:343`–`475`.
    - For each challan product, display `remaining_quantity` derived from global billed-to-date excluding the current bill’s own quantities (see Backend) to prevent over-billing while editing.
    - Inputs:
      - `Bill Qty` text input with `x-model.number` and `@input`/`@blur` handlers identical to `onBillQtyInput/onBillQtyBlur` (`create-regular.blade.php:147`–`176`).
      - `Select` checkbox toggles inclusion; keep `formatRemaining` status UI.
  - Computed Summary
    - Reuse subtotal, discount, shipping, total tiles and toggles (`create-regular.blade.php:480`–`561`).
  - Items payload
    - Emit hidden fields for `items[*]` with `quotation_product_id`, `quantity`, `allocations[*]` (`challan_product_id`, `billed_quantity`) exactly as in `create-regular.blade.php:634`–`650`.
- Client-side validation
  - Keep `clientValidateAndSubmit` (`create-regular.blade.php:205`–`234`), updated to allow unchanged items (must still be non-empty).
- UI consistency
  - Use same Tailwind classes, gradients, icons, spacing as create view.
  - Maintain dark mode support.

### CRUD interactions (frontend)
- Edit: prefill all fields; allow changing `invoice_no`, dates, `notes`, discount/shipping toggles, and allocations.
- Delete: continue using generic delete route (`route('bills.destroy', $bill)`) available from index; optional delete button may be provided in edit view for admins.
- Read: link to bills index; optionally a compact bill history card as in `create-regular.blade.php:297`–`319`.

---

## 2. Backend Requirements

### Controller changes: `app/Http/Controllers/BillController.php`
- Routing to the edit view
  - Update `edit(Bill $bill)` to return `dashboard.bills.edit-regular` when `$bill->isRegular()`.
  - Authorization: preserve existing check in `edit()` (`app/Http/Controllers/BillController.php:264`–`269`).
- New method: `updateRegular(Request $request, Bill $bill)`
  - Preconditions
    - Only latest bill per quotation can be edited (`isLatestBillForQuotation`) per `app/Http/Controllers/BillController.php:647`–`655`.
    - Authorization identical to `update()` (`app/Http/Controllers/BillController.php:297`–`299`).
  - Validation
    - Create `UpdateRegularBillRequest` mirroring `StoreRegularBillRequest` with changes:
      - `invoice_no`: unique, ignoring current bill ID.
      - `bill_date` and `payment_received_date`: `date_format:d/m/Y` (align with create).
      - `discount`, `shipping`, `notes` identical bounds.
      - `items[*]` structure identical; allocation checks must compute remaining quantities excluding current bill’s existing items to allow reallocation without false positives.
    - Alternatively, inline `validate()` if not using FormRequest; recommended FormRequest for consistency.
  - Business logic and persistence
    - Call a new service method `updateRegular(Bill $bill, array $data): Bill` in `BillingService`.
    - The service must:
      - Validate constraints centrally (reuse `validateBillConstraints` patterns referenced at `app/Services/BillingService.php:284`–`335`).
      - Recompute remaining quantities against delivered challan history excluding the current bill’s items.
      - Replace bill items snapshot:
        - Delete existing `bill_items` for this bill’s pivots.
        - Rebuild pivots (`bill_challans`) as needed and create new `BillItem` snapshots per `createBill` pipeline (`app/Services/BillingService.php:47`–`88`).
      - Recalculate totals and due for regular bills per `createBill` totals logic (`app/Services/BillingService.php:97`–`116`).
      - Persist `discount` and `shipping` and set `bill_amount` to `(sum(bill_price) - discount) + shipping`.
    - Use transactions to ensure atomicity.
  - Response
    - Redirect to `bills.index` with success flash; on validation/persistence errors, redirect back with `withInput()` and error messages (mirror `store()` behavior at `app/Http/Controllers/BillController.php:168`–`189`).

### Data preparation for edit view
- In `editRegular(Bill $bill)` (introduced or via `edit()` branch), load:
  - `quotation` and active revision (per `getActiveRevision()` logic `app/Http/Controllers/BillController.php:191`–`194`).
  - Challans for the revision `getRevisionChallans()` (`app/Http/Controllers/BillController.php:196`–`206`).
  - Compute `remaining_quantity` for challan products via `calculateRemainingQuantitiesForChallanProducts()` but exclude quantities from the current bill to permit reallocation within capacity.
    - Implementation: adjust the aggregate query in `calculateRemainingQuantitiesForChallanProducts()` (`app/Http/Controllers/BillController.php:531`–`547`) to filter out bill_items belonging to `$bill->id` during edit.
  - Provide JSON to the view for pre-populating `selectedProducts`/`items`.

### Service changes: `app/Services/BillingService.php`
- Add `updateRegular(Bill $bill, array $data): Bill`
  - Validate inputs and constraints (parent–child not applicable for regular; quantity remaining per challan applies).
  - Rebuild items snapshot consistent with `createBill()` implementation (`app/Services/BillingService.php:47`–`88`).
  - Recalculate totals/due as in regular bill section (`app/Services/BillingService.php:97`–`116`).
  - Protect against division by zero where applicable (see `calculateBillPercentage()` design in consolidated doc `BILLING_SYSTEM_CONSOLIDATED.md:108`).
  - Transactional safety.

### Models
- No schema changes required. Ensure `Bill` and `BillItem` casts and relations are used as in current implementation:
  - `app/Models/Bill.php:51`–`93` relations and `isRegular()` (`app/Models/Bill.php:112`–`116`).
  - `app/Models/BillItem.php:17`–`38` fillable and casts; `billChallan/items` relationships via `BillChallan`.

### Authorization
- Preserve existing guards:
  - Authenticated routes under `routes/web.php:25`–`27`.
  - Role-based checks preventing non-admin edits unless quotation owner (`app/Http/Controllers/BillController.php:297`–`299`).
  - Latest-bill-only edit rule (`app/Http/Controllers/BillController.php:647`–`655`).

---

## 3. Integration Requirements

### Routes
- Add specialized regular edit/update routes to match advance/running style:
  - `GET /dashboard/bills/{bill}/edit-regular` → `BillController@edit` branch or new `editRegular` method; name `bills.edit-regular`.
  - `PUT /dashboard/bills/{bill}/update-regular` → `BillController@updateRegular`; name `bills.update-regular`.
- Keep `Route::resource('bills', BillController::class)` as-is; `edit()` will route to the correct view based on `bill_type`.

### Request/Response contracts
- Request (PUT `update-regular`)
  - Body fields:
    - `bill_type='regular'` (hidden), `quotation_id`, `quotation_revision_id`.
    - `invoice_no` (string, unique except current), `bill_date` (`d/m/Y`), `payment_received_date` (`d/m/Y|nullable`).
    - `discount` (>=0), `shipping` (>=0), `notes` (<=1000).
    - `items`: array of objects each with `quotation_product_id`, `quantity`, and `allocations` array of `{challan_product_id, billed_quantity}`.
- Response
  - On success: redirect `bills.index` with `success` message.
  - On validation error: HTTP 302 back with `withErrors()` and `withInput()`; frontend renders the banner.

### Business logic nuances
- Regular bill edit must not cause over-billing:
  - Remaining checks per challan product computed as `delivered.quantity - (sum(billed_to_date excluding current bill) + new_allocation_quantity)`.
  - Keep rule “item quantity equals sum of allocations” (`StoreRegularBillRequest.php:123`–`126`).
- Due recomputation
  - `due_amount = total_amount(latest quotation context) - SUM(sibling regular bills bill_amount) - current bill_amount` (`app/Services/BillingService.php:103`–`114`).
  - Ensure status `cancelled` bills excluded.

---

## 4. Testing Requirements

### Unit tests (backend)
- `tests/Feature/Billing/UpdateRegularBillTest.php`
  - Validates update with unchanged allocations.
  - Validates update with changed allocations within remaining capacity.
  - Fails when billed quantities exceed remaining.
  - Enforces latest-bill-only edit rule.
  - Authorization: quotation owner vs non-owner; admin bypass.
- Service tests for `BillingService@updateRegular`
  - Snapshot rebuild correctness; totals and due recomputation.
  - Transaction rollback on partial failures.

### Integration tests
- End-to-end PUT flow from `edit-regular.blade.php` posting to controller, ensuring error banner renders on failures and success redirects.
- Validate JSON prefill of `selectedProducts` from controller.

### Browser compatibility
- Verify in latest Chrome, Firefox, Safari (macOS) and Edge.
- Inputs, Alpine reactivity, and Tailwind styles consistent across dark/light modes.

### Performance
- Large datasets: many challans/products per revision.
  - Measure client rendering time for expandable tables.
  - Server: aggregation queries for remaining quantities; ensure indexes per consolidated doc (composite indexes on `bills(quotation_id, bill_type, status)`).

---

## 5. Documentation

### Technical implementation details
- View mirrors `create-regular` structure and scripts; controller prepares prefill JSON and filtered remaining quantities; service updates snapshots and totals transactionally.

### API specifications
- Endpoints
  - `GET /dashboard/bills/{bill}/edit-regular` → returns edit view.
  - `PUT /dashboard/bills/{bill}/update-regular` → accepts the payload described above.
- Payload shapes match `StoreRegularBillRequest` with invoice/date uniqueness and format differences for update.

### Deployment instructions
- Add routes in `routes/web.php` under authenticated `dashboard` group.
- Implement controller methods and service `updateRegular`.
- Run tests: `php artisan test tests/Feature/Billing/` (per consolidated doc lines 218–220).

### Troubleshooting guide
- Validation errors: confirm dates formatted `dd/mm/YYYY` and that `items` include allocations; check uniqueness of `invoice_no`.
- Over-billing errors: verify `remaining_quantity` server-prepared values and ensure current bill’s items are excluded from remaining calculations during edit.
- Authorization failures: ensure user is admin or quotation owner, and bill is latest for its quotation.

---

## Implementation Checklist
- Frontend: new Blade view `edit-regular.blade.php` with Alpine init, form, client validation, and payload.
- Backend: routes, `BillController@updateRegular`, service `BillingService@updateRegular`, edit view data prep with filtered remaining quantities.
- Tests: unit and integration coverage for update flows.
- Docs: keep this spec under `public/billing-system-log/` and update consolidated doc references as needed.