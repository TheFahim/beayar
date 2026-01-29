# Frontend Implementation Tasks

This document outlines the detailed tasks required to execute the [Frontend Implementation Plan](frontend_implementation_plan.md).

## Phase 1: Foundation & Design System
**Goal:** Establish the frontend infrastructure and migrate the atomic component library.

### Week 1

#### 1.1 Environment Setup
- [x] **Install Frontend Dependencies:** Run `npm install` to ensure all packages (Tailwind, Flowbite, Alpine.js, Vite) are ready.
- [x] **Configure Vite:** Update `vite.config.js` to process `resources/css/app.css` and `resources/js/app.js`.
- [x] **Configure Tailwind:** Update `tailwind.config.js` to include paths for all Blade files and enable Flowbite plugin.
- [x] **Asset Migration:**
    - [x] Copy fonts/images from `optimech-app/public` to `beayar-erp/public`.
    - [x] Migrate `resources/css/app.css` (custom utilities/theme).

#### 1.2 Component Migration (Atomic)
- [x] **Create Component Directory Structure:**
    - `resources/views/components/ui/form`
    - `resources/views/components/ui/svg`
    - `resources/views/components/dashboard/layout`
    - `resources/views/components/dashboard/common`
- [x] **Migrate UI Components:**
    - [x] `x-ui.input`, `x-ui.select`, `x-ui.textarea`, `x-ui.checkbox`.
    - [x] `x-ui.button` (primary, secondary, danger variants).
    - [x] `x-ui.card`.
    - [x] `x-ui.modal` (Alpine.js based).
    - [x] `x-ui.badge` (Status indicators).
- [x] **Migrate Icons:** Move all SVG Blade components.

#### 1.3 Verification
- [x] **Verify Build:** Run `npm run build` and check for errors.

---

## Phase 2: Authentication & Navigation Structure
**Goal:** Implement secure login flows and main application shells.

### Week 2

#### 2.1 Backend Requirements (Auth)
- [x] **Auth Controller:** Ensure `App\Http\Controllers\AuthController` handles:
    - Login (View + Post).
    - Logout.
    - Redirection logic (Admin -> `/admin/dashboard`, User -> `/dashboard`).

#### 2.2 Layout Implementation
- [x] **Guest Layout:** Create `components/layouts/guest.blade.php` (for Login/Register).
- [x] **App Layout:** Create `components/layouts/app.blade.php` (Sidebar + Topbar + Content).
- [x] **Sidebar Component:**
    - [x] Implement `x-dashboard.common.sidebar`.
    - [x] Add logic to toggle links based on `Auth::user()->hasRole('admin')`.
- [x] **Navbar Component:**
    - [x] Implement `x-dashboard.common.navbar`.
    - [x] Add User Profile dropdown and Company Switcher (for Tenants).

#### 2.3 Auth Views
- [x] **Login Page:** `resources/views/auth/login.blade.php`.
- [x] **Register Page:** `resources/views/auth/register.blade.php`.
- [x] **Forgot Password:** UI for password reset.

---

## Phase 3: Admin Portal Implementation
**Goal:** Build the Super Admin interface.

### Week 3

#### 3.1 Backend Requirements (Admin)
- [x] **Create Web Controllers:**
    - `Admin\DashboardController`: Return stats view.
    - `Admin\TenantController`: Return list view + handle actions.
    - `Admin\PlanController`: Return CRUD views.
    - `Admin\CouponController`: Return global coupon views.

#### 3.2 Dashboard & Tenants
- [x] **Admin Dashboard:**
    - [x] Stats Cards (Total Tenants, MRR).
    - [x] Recent Activity Table.
- [x] **Tenant Management:**
    - [x] **Index View:** Data table listing all companies.
    - [x] **Actions:** "Login as Tenant" button (Impersonation), "Suspend" toggle.

#### 3.3 Subscriptions & Marketing
- [x] **Plans UI:** Grid view of available plans with "Edit" modals.
- [x] **Global Coupons:**
    - [x] Create/Edit Form with validation.
    - [x] List view.

---

## Phase 4: Tenant Core Modules (CRM & Sales)
**Goal:** Implement core business logic views for Tenants.

### Week 4

#### 4.1 Backend Requirements (Tenant CRM)
- [ ] **Create Web Controllers:**
    - `Tenant\CustomerController`: CRUD views.
    - `Tenant\ProductController`: CRUD views + Image handling.
    - `Tenant\QuotationController`: CRUD views + PDF generation.
- [ ] **Internal API (AJAX):**
    - Ensure `GET /api/v1/products/{id}` is accessible for the Quotation form to fetch prices.

#### 4.2 CRM Modules
- [ ] **Customer Management:**
    - [ ] List View (Datatable).
    - [ ] Create/Edit Modal or Page.
- [ ] **Product Catalog:**
    - [x] Grid View with Images (Image Library implemented).
    - [ ] Create/Edit Form (File Upload for images).

#### 4.3 Quotation System (Complex)
- [ ] **Quotation List:** Datatable with Status filters.
- [ ] **Quotation Builder (Form):**
    - [ ] **Customer Select:** Searchable dropdown.
    - [ ] **Line Items:** Dynamic Repeater (Add Row/Remove Row).
    - [ ] **Product Select:** Fetch price/specs on change via Axios.
    - [ ] **Calculations:** Alpine.js logic for `qty * price = subtotal`, `tax`, `discount`, `grand_total`.
- [ ] **View/Print:** UI to display the generated Quotation PDF.

---

## Phase 5: Tenant Finance Modules
**Goal:** Build financial workflows and reporting.

### Week 5

#### 5.1 Backend Requirements (Finance)
- [ ] **Create Web Controllers:**
    - `Tenant\BillController`: Handle Bill generation from Quotations.
    - `Tenant\PaymentController`: Record payments.
    - `Tenant\FinanceController`: Dashboard stats.
- [ ] **Internal API (AJAX):**
    - `GET /api/v1/challans`: For linking Challans to Bills.

#### 5.2 Billing & Payments
- [ ] **Bill Management:**
    - [ ] Index View (Advance vs Regular bills).
    - [ ] Create Bill Form (Auto-fill from Quotation if applicable).
- [ ] **Payment Recording:**
    - [ ] Modal to record payment against a Bill.
    - [ ] Validation (Amount cannot exceed due).

#### 5.3 Reporting
- [ ] **Finance Dashboard:**
    - [ ] **Charts:** Integrate ApexCharts.
        - [ ] Monthly Revenue Bar Chart.
        - [ ] Expense Pie Chart.
    - [ ] **Date Picker:** Filter reports by date range.

---

## Phase 6: Integration, Polish & Testing
**Goal:** Finalize the application.

### Week 6

#### 6.1 UX Improvements
- [ ] **Feedback System:**
    - [ ] Implement `x-ui.toast` (SweetAlert2 wrapper) for flash messages.
    - [ ] Add `x-ui.loader` for AJAX loading states.
- [ ] **Error Handling:** Ensure server-side validation errors are displayed on all forms (`$errors`).

#### 6.2 Mobile Responsiveness
- [ ] **Sidebar:** Ensure it collapses correctly on mobile.
- [ ] **Tables:** Verify `simple-datatables` behavior on small screens.

#### 6.3 Final Testing
- [ ] **Manual E2E Test:**
    1. Register new Tenant.
    2. Create Customer.
    3. Create Product.
    4. Create Quotation.
    5. Convert to Bill.
    6. Record Payment.
    7. Verify Admin Dashboard shows revenue.
