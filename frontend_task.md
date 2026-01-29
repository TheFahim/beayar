---

## Phase 4: Tenant Core Modules (CRM & Sales)
**Goal:** Implement core business logic views for Tenants.

### Week 4

#### 4.1 Backend Requirements (Tenant CRM)
- [x] **Create Web Controllers:**
    - `Tenant\CustomerController`: CRUD views.
    - `Tenant\ProductController`: CRUD views + Image handling.
    - `Tenant\QuotationController`: CRUD views + PDF generation.
- [x] **Internal API (AJAX):**
    - Ensure `GET /api/v1/products/{id}` is accessible for the Quotation form to fetch prices.

#### 4.2 CRM Modules
- [x] **Customer Management:**
    - [x] List View (Datatable).
    - [x] Create/Edit Modal or Page.
- [x] **Product Catalog:**
    - [x] Grid View with Images (Image Library implemented).
    - [x] Create/Edit Form (File Upload for images).

#### 4.3 Quotation System (Complex)
- [x] **Quotation List:** Datatable with Status filters.
- [x] **Quotation Builder (Form):**
    - [x] **Customer Select:** Searchable dropdown.
    - [x] **Line Items:** Dynamic Repeater (Add Row/Remove Row).
    - [x] **Product Select:** Fetch price/specs on change via Axios.
    - [x] **Calculations:** Alpine.js logic for `qty * price = subtotal`, `tax`, `discount`, `grand_total`.
- [x] **View/Print:** UI to display the generated Quotation PDF.

---
