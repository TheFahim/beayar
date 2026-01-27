# UI Migration Documentation

## Overview
This document outlines the plan to migrate the UI structure and components from `optimech-app` to `beayar-erp`. The goal is to adopt the modern, component-based UI architecture using Blade components and Tailwind CSS.

## Source Structure (`optimech-app`)
The source project uses a structured component architecture:
- `resources/views/components/dashboard/layout`: Main layout files.
- `resources/views/components/dashboard/common`: Common elements (Sidebar, Navbar, Footer).
- `resources/views/components/ui`: Reusable UI elements (Buttons, Inputs, Cards, SVGs).
- `resources/css/app.css`: Tailwind CSS configuration.
- `resources/js/app.js`: JavaScript assets including Alpine.js.

## Target Structure (`beayar-erp`)
We will replicate this structure in `beayar-erp`, replacing the existing `layouts` directory approach with the component-based approach where appropriate, or integrating them.

### Directory Mapping

| Source (`optimech-app`) | Target (`beayar-erp`) | Description |
| ----------------------- | --------------------- | ----------- |
| `components/dashboard/layout/` | `components/dashboard/layout/` | Main application layouts. |
| `components/dashboard/common/` | `components/dashboard/common/` | Sidebar, Navbar, etc. |
| `components/ui/` | `components/ui/` | Atomic UI components. |
| `css/app.css` | `css/app.css` | Tailwind styles. |
| `js/app.js` | `js/app.js` | Main JS entry point. |

## Migration Plan

### 1. Asset Migration
- Copy `resources/css/app.css` to `beayar-erp`.
- Copy `resources/js/app.js` and related modules to `beayar-erp`.
- Ensure `tailwind.config.js` and `vite.config.js` are updated to support the new structure.

### 2. Component Migration
- Create `resources/views/components` directory structure.
- Copy `dashboard/layout`, `dashboard/common`, and `ui` components.
- Refactor `sidebar.blade.php` to include `beayar-erp` specific routes (Admin vs Tenant).

### 3. Layout Update
- Replace existing `layouts/admin.blade.php` and `layouts/tenant.blade.php` content with usage of `<x-dashboard.layout.default>` or similar, or fully adopt the new layout component.

### 4. View Updates
- Update `admin/dashboard.blade.php` and `tenant/dashboard.blade.php` to extend the new layout.
- Update Authentication views (`auth/login.blade.php`, etc.) to use the new UI components.

## Implementation Details

### Sidebar Logic
The sidebar in `optimech-app` is static or role-based. In `beayar-erp`, we have separate contexts for Admin and Tenant. We will likely need:
- `<x-dashboard.common.admin-sidebar>`
- `<x-dashboard.common.tenant-sidebar>`
Or a single sidebar with conditional logic based on the user's scope.

### Dependencies
- Ensure `alpinejs` is installed/imported.
- Ensure `flowbite` or other UI libraries used in `optimech-app` are present.

## Next Steps
1.  Copy assets and configure build tools.
2.  Migrate UI components.
3.  Implement Layouts.
4.  Update Dashboard views.
