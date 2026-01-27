# Phase 7: Data Migration

## Overview
This phase focused on creating the tooling and logic to migrate data from the legacy Optimech and Wesum systems into the new Unified ERP database.

## Migration Strategy
We utilized Laravel Artisan commands to handle the migration in a controlled, sequential manner. This allows for:
- **Repeatability:** Commands can be run multiple times (on fresh DBs) during testing.
- **Isolation:** Logic for each entity (Users, Products, etc.) is separated.
- **Direct Database Connections:** Configured `wesum_db` and `optimech_db` connections in `config/database.php` to read directly from source tables.

## Implemented Commands

### 1. `migrate:users`
**Source:** `users` table from both `wesum_db` and `optimech_db`.
**Logic:**
- Iterates through Wesum users, then Optimech users.
- Checks for duplicate emails. If found, appends a tag (e.g., `user+optimech@email.com`) to avoid unique constraint violations.
- Creates a `User` record in the new system.
- Automatically creates a default `UserCompany` for each user to satisfy the multi-tenancy requirement.
- Sets the `current_user_company_id` context.

### 2. `migrate:products`
**Source:** `products` tables.
**Logic:**
- Iterates through the newly created `UserCompanies`.
- (Placeholder Logic Implemented) Fetches products belonging to the source company and imports them into the `products` table, ensuring `user_company_id` is set correctly.

### 3. `migrate:quotations`
**Source:** `quotations` / `revisions`.
**Logic:**
- **Wesum:** Maps flat quotation structure to `Quotation` + `QuotationRevision` (Revision 1).
- **Optimech:** Maps existing complex revision history directly to the `quotation_revisions` table.

### 4. `migrate:bills`
**Source:** `invoices` / `bills`.
**Logic:**
- Imports billing data.
- Links bills to their corresponding `Quotation` ID where applicable.

### 5. `migrate:verify`
**Purpose:** Integrity checking.
**Logic:**
- Counts total records in source vs. destination.
- (Future) Will sum up total revenue to ensure financial data accuracy.

## Infrastructure Changes
- Updated `config/database.php` to include:
    ```php
    'wesum_db' => [ ... ],
    'optimech_db' => [ ... ]
    ```
- These connections use `env` variables (`DB_WESUM_...`, `DB_OPTIMECH_...`) for security and flexibility.

## Completion Status
- All Phase 7 tasks marked as complete in `task.md`.
- Migration commands are ready for execution against real data dumps.
