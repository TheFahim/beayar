**Objective:** Upgrade an existing single-tenant Laravel SaaS application to a multi-user, multi-tenant system by implementing the provided "Multi-User SaaS Upgrade Guide".

**Context:**
The application is an existing Laravel project. The goal is to refactor it to support multiple users per company account, with role-based access control and clear data separation between companies (tenants). You will be provided with a complete step-by-step guide. Your task is to process this guide and generate the necessary code, commands, and instructions to execute the upgrade.

**Primary Instructions:**

**Phase A: Feasibility Assessment & Planning**

Before generating any code, perform an assessment. Based on the provided guide, analyze a hypothetical standard Laravel application and answer the following:
1.  **Identify Potential Conflicts:** What are the most likely areas in a typical existing Laravel application (e.g., existing User model structure, hardcoded user ID checks in controllers, existing middleware) that might conflict with the changes outlined in this guide?
2.  **Required Modifications:** List the key modifications required. For example: "The existing `User` model will need new relationships (`ownedCompanies`, `companies`) and helper methods (`roleInCompany`). The `Quotation` model will need the `BelongsToTenant` trait and new columns."
3.  **Dependency Check:** Confirm if any new Composer packages are implicitly required (e.g., the guide mentions `spatie/laravel-permission` as a check, but doesn't add it. Note that no new packages are being added in this guide).
4.  **Confirmation:** State your readiness to proceed with code generation based on the plan.

**Phase B: Code Generation & Implementation**

Execute the following steps precisely as detailed in the "Multi-User SaaS Upgrade Guide". For each step, provide the complete, production-ready code block or the exact shell command to be run.

---

### **Phase 1: Create New Infrastructure Code**

**1.1: Create `app/Scopes/TenantScope.php`**
   - Generate the command to create the directory.
   - Generate the complete PHP code for the `TenantScope` class.

**1.2: Create `app/Traits/BelongsToTenant.php`**
   - Generate the command to create the directory.
   - Generate the complete PHP code for the `BelongsToTenant` trait.

**1.3: Create `app/Http/Middleware/SetTenantContext.php`**
   - Generate the `php artisan make:middleware` command.
   - Generate the complete PHP code for the `SetTenantContext` middleware.

**1.4: Create `app/Services/CompanyMemberService.php`**
   - Generate the command to create the directory.
   - Generate the complete PHP code for the `CompanyMemberService`.

---

### **Phase 2: Update Models**

**2.1: Update `app/Models/User.php`**
   - Generate the complete, updated code for the `User` model, incorporating all new relationships and helper methods as specified.

**2.2: Update `app/Models/UserCompany.php`**
   - Generate the complete, updated code for the `UserCompany` model.

**2.3: Update `app/Models/Quotation.php`**
   - Generate the updated code for the `Quotation` model, ensuring the `BelongsToTenant` trait is used and the `boot` method is included.

**2.4: Update `app/Models/Bill.php`**
   - Generate the updated code for the `Bill` model with the `BelongsToTenant` trait.

**2.5: Update `app/Models/Challan.php`**
   - Generate the updated code for the `Challan` model with the `BelongsToTenant` trait.

**2.6: Update Other Models (`Client`, `Product`, etc.)**
   - Provide a generic, reusable code template for updating other tenant-scoped models, clearly indicating where `[ModelName]` should be replaced.

---

### **Phase 3: Create Authorization Layer**

**3.1: Create `app/Policies/QuotationPolicy.php`**
   - Generate the `php artisan make:policy` command.
   - Generate the complete code for the `QuotationPolicy`.

**3.2-3.3: Create Bill and Challan Policies**
   - Provide the artisan commands to generate `BillPolicy` and `ChallanPolicy`. State that the implementation should follow the `QuotationPolicy` pattern.

**3.4: Create `app/Policies/CompanyMemberPolicy.php`**
   - Generate the `php artisan make:policy` command.
   - Generate the complete code for the `CompanyMemberPolicy`.

**3.5: Register Policies in `app/Providers/AuthServiceProvider.php`**
   - Generate the updated `$policies` array for the `AuthServiceProvider`.

---

### **Phase 4: Database Migration**

**4.1: Create Migration File**
   - Generate the `php artisan make:migration` command.
   - Generate the complete code for the `up()` and `down()` methods of the migration.

**4.2: Create Data Migration Seeder**
   - Generate the `php artisan make:seeder` command.
   - Generate the complete code for the `MigrateExistingOwnersSeeder` class.

---

### **Phase 5: Update Controllers**

**5.1: Register Middleware in `app/Http/Kernel.php`**
   - Show the line to be added to the `$middlewareAliases` array.

**5.2: Create `app/Http/Controllers/CompanyMemberController.php`**
   - Generate the `php artisan make:controller` command.
   - Generate the complete code for the `CompanyMemberController`.

**5.3: Update Existing Controllers**
   - Using `QuotationController` as the primary example, generate its complete updated code, showing how to use the authorization policies (`$this->authorize(...)`) and how data fetching is simplified by the `TenantScope`.
   - Provide instructions to apply the same authorization pattern to `BillController`, `ChallanController`, etc.

**5.4: Add Routes in `routes/web.php`**
   - Generate the PHP code snippet for the new company member management routes.

---

**Final Output Format:**
- Use clear headings for each phase and step, matching the guide.
- Use Markdown for code blocks with appropriate language identifiers (e.g., `php`, `bash`).
- Ensure the generated code is complete, correct, and ready to be copied and pasted.
- Add comments where necessary to clarify complex logic (e.g., in the `CompanyMemberService`).
