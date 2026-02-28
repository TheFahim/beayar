# Frontend Design Flaws Report

Scope:
- `beayar-erp/resources/js`
- `beayar-erp/resources/views`

This report lists potential and existing frontend design flaws found during a quick structural review. Items are categorized by severity and include file references and suggested remediation.

---

## 1) Critical

### 1.1 Unescaped HTML rendering in Blade (stored/DB HTML -> XSS risk)
- **What**
  - Multiple templates render database/user-provided rich-text using Blade raw output tags `{!! ... !!}`.
  - If these fields are not strictly sanitized on the server, this is a direct XSS vector.
- **Examples**
  - `resources/views/tenant/quotations/show.blade.php`
    - `{!! $quotationProduct->specification->description !!}`
    - `{!! $activeRevision->terms_conditions !!}` / `{!! $quotation->terms_conditions !!}`
  - `resources/views/tenant/bills/show.blade.php`
    - `{!! $qp->specification->description ?? '' !!}`
  - `resources/views/tenant/challans/show.blade.php`
    - `{!! $cp->quotationProduct->specification->description !!}`
    - `{!! $cp->quotationProduct->product->description !!}`
  - `resources/views/tenant/products/index.blade.php`
    - `{!! $spec->description !!}`
  - `resources/views/tenant/payments/show.blade.php`
    - `{!! $product->specs ?? '' !!}`
- **Why it matters**
  - Any injected `<script>` / event handler attributes will execute in the user’s session.
- **Fix direction**
  - Prefer `{{ ... }}` escaped output.
  - If rich text must be allowed, sanitize server-side (e.g., HTMLPurifier) and store sanitized HTML; also consider CSP.

### 1.2 Inline scripts + global `window.*` configuration objects (CSP hardening blocked; wider attack surface)
- **What**
  - Multiple pages define `<script>` blocks inside Blade templates and assign configuration to `window.*`.
  - This prevents strong CSP (`script-src 'self'` without `unsafe-inline`) and increases risk of DOM clobbering / accidental overwrites.
- **Examples**
  - `resources/views/tenant/quotations/create.blade.php` defines `window.quotationFormConfig` in an inline `<script>`.
  - `resources/views/tenant/quotations/edit.blade.php` defines `window.quotationFormConfig` + multiple DOM event listeners inline.
- **Fix direction**
  - Move page scripts to Vite modules and pass config via:
    - `data-*` attributes on root nodes, or
    - a single JSON script tag (`<script type="application/json" id="...">...</script>`) that you parse.

---

## 2) Medium

### 2.1 Broken / incomplete module left in production bundle
- **What**
  - `resources/js/quotations/modules/excel-exporter.js` defines `ExcelExporter.generate()` but it’s clearly incomplete and contains “refactor” notes in code logic.
  - Risk: dead/incomplete code shipped to users, future dev confusion, potential runtime errors if instantiated.
- **Fix direction**
  - Either remove unused class or complete implementation; ensure only the working export path is used.

### 2.2 Hard-coded company identity data in export module
- **What**
  - `resources/js/quotations/modules/excel-exporter.js` hardcodes address/email/phone/website and an authorized person name.
- **Why it matters**
  - Multi-tenant mismatch: wrong company data exported.
  - Maintenance risk: changes require code deploy.
- **Fix direction**
  - Use `data.company` fields consistently (address/contact/website/signatory) from backend.

### 2.3 Inconsistent API usage and CSRF handling patterns
- **What**
  - You set up `axios` in `resources/js/bootstrap.js`, but much of the code uses `fetch()` directly.
  - Some `fetch` calls include CSRF token manually (e.g., quotations status update), others don’t.
- **Examples**
  - `resources/js/quotations/quotation.js` uses `fetch()` for multiple endpoints; CSRF token is stored in component config but not consistently applied.
  - `resources/js/quotationsPage.js` uses `fetch('/quotations/...')` with `X-CSRF-TOKEN`.
- **Fix direction**
  - Standardize on either `axios` or a small wrapper around `fetch()` that:
    - always adds `X-Requested-With`, `Accept`, and CSRF
    - centralizes error handling

### 2.4 Duplicate layout logic and duplicated toast blocks
- **What**
  - Similar layout templates exist with repeated markup and identical toast code blocks.
- **Examples**
  - `resources/views/components/layouts/app.blade.php`
  - `resources/views/components/dashboard/layout/default.blade.php`
- **Fix direction**
  - Extract toast component or a single base layout; keep one source of truth.

### 2.5 Overloaded Alpine components (too many responsibilities in one file)
- **What**
  - `resources/js/quotations/quotation.js` is ~1370 lines and mixes:
    - initialization
    - watchers
    - network calls
    - calculation logic
    - UI modals
    - data normalization
  - Even though some logic is split into modules, the main component is still very large.
- **Fix direction**
  - Split into smaller Alpine components per section (customer, products table, totals, modals).
  - Promote shared concerns into services (exchange rates, api client, validation).

### 2.6 Server-rendered HTML appended via `innerHTML` from fetch response
- **What**
  - `resources/js/imageLibrary.js` uses `tempDiv.innerHTML = data.html` and injects fetched HTML into the DOM.
- **Why it matters**
  - If the HTML response can contain untrusted content (or is affected by user input), it becomes an XSS surface.
- **Fix direction**
  - Prefer returning JSON data and render via template.
  - If you keep HTML fragments, ensure the server escapes content and consider DOMPurify before insertion.

### 2.7 Event listeners added without clear teardown on long-lived pages
- **What**
  - Various scripts attach `document.addEventListener(...)` globally (e.g., delete/save handlers).
- **Examples**
  - `resources/js/buttonAlerts.js` registers `click` and `submit` listeners for the whole document.
- **Why it matters**
  - In SPA-like navigation (Turbo/Inertia/PJAX), listeners can duplicate.
- **Fix direction**
  - Guard against multiple initialization, scope listeners to specific root container, or use Alpine actions.

---

## 3) Low

### 3.1 Reliance on jQuery while using Alpine/Vite
- **What**
  - Layouts include `jquery-3.7.1.min.js` even though most interaction uses Alpine + modern modules.
- **Examples**
  - `resources/views/components/layouts/app.blade.php`
  - `resources/views/components/dashboard/layout/default.blade.php`
- **Why it matters**
  - Extra payload and mixed paradigms.
- **Fix direction**
  - Remove jQuery if not required; if required, limit to specific pages.

### 3.2 Mixed styling approaches (inline CSS blocks within views)
- **What**
  - Print-specific CSS is embedded directly inside views.
- **Examples**
  - `resources/views/tenant/quotations/show.blade.php`
  - `resources/views/tenant/bills/show.blade.php`
- **Fix direction**
  - Extract print styles into dedicated CSS files via Vite so styles are consistent and maintainable.

### 3.3 Minor data bug in payments create date formatting
- **What**
  - `resources/views/tenant/payments/create.blade.php`: in `init()`, you check `row.received_date` but then format `row.date`.
- **Impact**
  - Could lead to incorrect date display when repopulating old values.
- **Fix direction**
  - Use `row.received_date = formatDateToDDMMYYYY(row.received_date)`.

### 3.4 Multiple sources of Alpine startup
- **What**
  - `resources/js/bootstrap.js` sets up Alpine but comments out `Alpine.start()`.
  - `resources/js/app.js` imports bootstrap and calls `Alpine.start()`.
- **Impact**
  - Not wrong, but easy to break with future refactors if additional bundles are introduced.
- **Fix direction**
  - Keep a single, explicit bootstrap entry that always starts Alpine.

---

## Quick recommendations (prioritized)
- **Critical first**
  - Remove/limit `{!! ... !!}` outputs or sanitize rich text server-side.
  - Reduce inline scripts + global config objects to enable CSP.
- **Medium**
  - Standardize API client + CSRF behavior.
  - De-duplicate layouts/toasts.
  - Refactor large Alpine components.
- **Low**
  - Remove jQuery if not used.
  - Extract print CSS.
  - Fix the payment date formatting bug.
