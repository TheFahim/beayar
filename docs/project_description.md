# Beayar ERP - Project Documentation

## 1. Project Overview

**Beayar ERP** is a unified, multi-tenant SaaS application designed to streamline business operations for small to medium enterprises. Born from the merger of two legacy systems ("Optimech" and "Wesum"), it combines robust billing and quotation capabilities with comprehensive financial tracking and multi-user collaboration tools.

The platform operates on a **Single Database, Multi-Tenant** architecture, allowing users to manage multiple companies (tenants) under a single login. It features a tiered subscription model (Free, Pro, Pro Plus, Custom) to cater to different business sizes.

---

## 2. Tech Stack

### Backend
- **Framework:** Laravel 10+
- **Language:** PHP 8.2+
- **Database:** MySQL 8.0+
- **Authentication:** Laravel Sanctum
- **Authorization:** Spatie Laravel Permission (RBAC)
- **Image Handling:** Intervention Image
- **Audit:** Spatie Activitylog

### Frontend
- **Templating:** Blade Components
- **CSS Framework:** Tailwind CSS
- **UI Components:** Flowbite
- **Interactivity:** Alpine.js
- **Charts:** ApexCharts

### Infrastructure
- **Web Server:** Nginx / Apache
- **Dependency Management:** Composer (PHP), NPM (JS)

---

## 3. System Architecture

### 3.1 Multi-Tenancy Strategy
The system uses a **Tenant Scope** approach within a single database.
- **Data Isolation:** All tenant-specific tables (e.g., `quotations`, `products`, `customers`) include a `user_company_id` foreign key.
- **Global Scope:** A `TenantScope` is automatically applied to Eloquent models to ensure users only access data belonging to their active company context.
- **Context Switching:** Users can belong to multiple companies and switch between them via the UI, which updates the session-based tenant context.

### 3.2 User Hierarchy
1.  **User:** The individual account holder.
2.  **User Company (Tenant):** The primary business entity owned by a User. This is the "billing unit" for subscriptions.
3.  **Customer Company:** A client of the User Company (B2B relationship).

### 3.3 Subscription Model
- **Plans:** Managed via the `plans` table.
    - **Free:** Basic access, limited quotas.
    - **Pro:** Increased limits (Sub-companies, Quotations, Employees).
    - **Pro Plus:** High volume/unlimited limits.
    - **Custom:** Tailored enterprise solutions.
- **Enforcement:** Middleware checks usage against plan limits before allowing actions (e.g., creating a new quotation).

---

## 4. Key Features

### 4.1 Super Admin Dashboard
*Accessible only to system administrators.*
- **Tenant Management:** View, suspend, or impersonate tenant accounts.
- **Platform Analytics:** Monitor MRR (Monthly Recurring Revenue), tenant growth, and active subscriptions.
- **Plan Management:** Configure plan features, limits, and pricing.
- **Global Coupons:** Create and manage marketing campaigns and discount codes.
- **System Settings:** Manage global configurations and feature flags.

### 4.2 Tenant Dashboard (User Workspace)
*The core ERP interface for business owners and employees.*

#### **Sales & CRM**
- **Quotations:** Create multi-revision quotations with product selection, tax calculations, and PDF generation.
- **Customers:** Manage client profiles (`CustomerCompany` and `Customer` contacts).
- **CRM Pipeline:** Track quotation status (Draft, Sent, Accepted, Rejected).

#### **Inventory & Products**
- **Product Catalog:** Manage products with specifications, brands, and origins.
- **Image Library:** Centralized media manager for product images.
- **Stock Tracking:** (Planned/In-progress) Basic inventory level monitoring.

#### **Billing & Finance**
- **Challans:** Generate delivery challans linked to approved quotations.
- **Billing:**
    - **Advance Bills:** Request payments before delivery.
    - **Running Bills:** Progressive billing based on work completion/delivery.
    - **Regular Bills:** Standard post-delivery invoices.
- **Payments:** Record partial or full payments against bills.
- **Expenses:** Track company expenses with categorization.
- **Financial Reports:** View income vs. expense charts and sales targets.

#### **Administration**
- **Team Management:** Invite employees and assign roles (Admin vs. Employee).
- **Subscription:** View usage, upgrade plans, and manage billing details.
- **Settings:** Configure company profile, logo, and preferences.

---

## 5. Database Schema Overview

### **Core Identity & Access**
- `users`: Global user accounts.
- `user_companies`: Tenant entities.
- `company_users`: Many-to-Many pivot for company membership + roles.
- `roles`, `permissions`: Spatie RBAC tables.

### **Subscriptions**
- `plans`: Definitions of subscription tiers.
- `subscriptions`: Links User -> Plan with start/end dates.
- `subscription_usage`: Tracks quota consumption (e.g., "quotations_generated").
- `modules`: Add-on features available for purchase.

### **Business Entities**
- `customers`, `customer_companies`: Clients of the tenant.
- `products`, `brand_origins`, `specifications`: Catalog data.
- `quotations`, `quotation_revisions`, `quotation_products`: Sales documents.
- `challans`, `challan_products`: Delivery documents.
- `bills`, `bill_challans`, `bill_items`: Invoices.
- `received_bills`, `payments`: Financial transactions.
- `expenses`, `expense_categories`: Cost tracking.

### **Platform Administration**
- `admins`: Super admin accounts.
- `platform_invoices`, `platform_payments`: Revenue tracking for the SaaS itself.
- `coupons`, `coupon_usage`: Discount system.

---

## 6. Directory Structure Highlights

```
/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/          # Super Admin controllers
│   │   │   ├── Tenant/         # Core business logic controllers
│   │   │   └── Api/            # API endpoints
│   │   ├── Middleware/
│   │   │   ├── SetTenantContext.php # Applies tenant scope
│   │   │   └── CheckSubscription.php # Enforces limits
│   │   └── Requests/           # Form validation classes
│   ├── Models/                 # Eloquent models (User, Quotation, etc.)
│   ├── Services/               # Business logic layer (SubscriptionService, etc.)
│   └── Traits/                 # Shared behaviors (BelongsToTenant)
├── database/
│   ├── migrations/             # Schema definitions
│   └── seeders/                # Demo data and initial setup
├── resources/
│   ├── views/
│   │   ├── admin/              # Admin panel Blade templates
│   │   ├── tenant/             # Tenant dashboard Blade templates
│   │   └── components/         # Reusable UI components
│   └── css/                    # Tailwind source
├── routes/
│   ├── web.php                 # Web routes (Auth, Dashboard)
│   └── api.php                 # API routes
└── tests/                      # Feature and Unit tests
```

---

## 7. Getting Started

1.  **Prerequisites:** PHP 8.2, Composer, Node.js, MySQL.
2.  **Installation:**
    ```bash
    composer install
    npm install && npm run build
    cp .env.example .env
    php artisan key:generate
    ```
3.  **Database Setup:**
    ```bash
    # Configure .env with DB credentials first
    php artisan migrate --seed
    ```
    *Note: The seeder populates plans, roles, and a default Super Admin.*
4.  **Access:**
    - **Admin Panel:** `/admin/dashboard` (Default: `admin@beayar.com` / `password`)
    - **Tenant App:** `/login` (Register a new account to start).

---

*Documentation generated on 2026-02-10.*
