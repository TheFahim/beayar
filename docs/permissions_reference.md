# Permission & Access Control Reference

This document provides a comprehensive analysis of the permission system within the `beayar-erp` application. It details the available permissions, user roles, and how they map to specific modules.

## Overview of the Permission System

The application currently employs a dual-layer access control system:

1.  **Role-Based Access Control (RBAC)**: The primary enforcement mechanism used in `Policies` and `Middleware`. It checks for specific fixed roles (e.g., `company_admin`, `employee`) to grant access.
2.  **Granular Permissions (Configurable)**: A secondary layer built on `spatie/laravel-permission`. These permissions are stored in the database and can be assigned to custom roles via the `TenantRoleController`. While these permissions can be configured and assigned, current application logic primarily enforces access based on the fixed roles.

---

## Default Roles

The system is seeded with the following default roles:

| Role Name | Scope | Description |
| :--- | :--- | :--- |
| `super_admin` | Global | Manages the entire platform, tenants, and system-wide settings. Bypasses most checks. |
| `tenant_admin` | Tenant | The owner of the tenant account. Has full control over their companies and subscription. |
| `company_admin` | Company | Administrators within a specific company. Can manage employees and data. |
| `employee` | Company | Standard users with access to operational features (creating bills, quotations, etc.). |

---

## Configurable Permissions by Module

The following permissions are available in the system and can be assigned to custom roles. These definitions are sourced from `Database\Seeders\RolesAndPermissionsSeeder.php`.

### 1. Product Management
Controls access to the product catalog and inventory items.

| Permission Name | Description | Default Roles |
| :--- | :--- | :--- |
| `view_products` | View the list of products and details. | `company_admin`, `employee` |
| `create_products` | Add new products to the catalog. | `company_admin`, `employee` |
| `edit_products` | Modify existing product details. | `company_admin`, `employee` |
| `delete_products` | Remove products from the catalog. | `company_admin` |

### 2. Quotation Management
Controls the creation and management of sales quotations.

| Permission Name | Description | Default Roles |
| :--- | :--- | :--- |
| `view_quotations` | View created quotations. | `company_admin`, `employee` |
| `create_quotations` | Create new quotations for customers. | `company_admin`, `employee` |
| `edit_quotations` | Update existing quotations. | `company_admin`, `employee` |
| `delete_quotations` | Delete quotations. | `company_admin` |

### 3. Customer Management
Controls access to customer data and CRM features.

| Permission Name | Description | Default Roles |
| :--- | :--- | :--- |
| `view_customers` | View customer lists and profiles. | `company_admin`, `employee` |
| `create_customers` | Add new customers to the system. | `company_admin`, `employee` |
| `edit_customers` | Edit customer information. | `company_admin`, `employee` |
| `delete_customers` | Remove customers. | `company_admin` |

### 4. Billing & Finance
Controls access to invoicing, bills, and financial dashboards.

| Permission Name | Description | Default Roles |
| :--- | :--- | :--- |
| `view_bills` | View bills and invoices. | `company_admin`, `employee` |
| `create_bills` | Generate new bills. | `company_admin`, `employee` |
| `edit_bills` | Modify existing bills. | `company_admin`, `employee` |
| `view_finance` | Access financial reports and dashboards. | `company_admin` |

### 5. Settings & Administration
Controls access to company configuration and user management.

| Permission Name | Description | Default Roles |
| :--- | :--- | :--- |
| `manage_settings` | Access and modify company settings. | `company_admin` |
| `manage_members` | Add, edit, or remove company members. | `company_admin` |
| `manage_roles` | Create and assign custom roles. | `company_admin` |

---

## Implementation Details

### Database Schema
*   **Permissions Table**: Stores the list of available permissions (`view_products`, etc.).
*   **Roles Table**: Stores role definitions (`company_admin`, `custom_role_1`).
*   **Role_Has_Permissions**: Maps permissions to roles.
*   **Company_Members**: Maps users to companies and assigns them a primary role.

### Logic & Enforcement
*   **Policies (`App\Policies\*`)**: Currently check for `roleInCompany` returning `company_admin` or `employee`.
*   **Middleware (`CheckCompanyRole`)**: Enforces the primary role check.
*   **TenantRoleController**: Allows creation of custom roles and assignment of the permissions listed above.

> **Note**: To fully enable the granular permission system (e.g., allowing a user to `view_products` but NOT `create_products`), the application Policies need to be updated to check `$user->can('permission_name')` instead of just checking the role name.
