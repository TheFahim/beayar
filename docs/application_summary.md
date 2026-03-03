# Beayar ERP — Application Summary

> **Purpose of this document:** This summary is the single source of truth for copywriters, designers, and developers building the **Beayar promo / marketing website**. It covers product positioning, feature inventory, target audience, subscription model, visual concepts, and technical differentiators — everything needed to craft compelling landing pages, feature tours, and pricing pages.

---

## 1. Product Identity

| Field | Detail |
|---|---|
| **Product Name** | Beayar ERP |
| **Tagline (suggested)** | *"One platform. Every business operation. Zero chaos."* |
| **Category** | Cloud-based, Multi-Tenant SaaS ERP |
| **Primary Market** | Small-to-Medium Enterprises (SMEs) in South Asia and emerging markets |
| **Industry Focus** | Trading, manufacturing, engineering services, and service-based businesses that manage quotations, deliveries, and invoicing |
| **Origin Story** | Born from the merger of two battle-tested billing systems — **Optimech** (sophisticated quotation & revision management) and **Wesum** (financial tracking & expense control) — Beayar unifies the best of both worlds into a single, modern platform. |

---

## 2. The Problem We Solve

Most small and mid-sized businesses juggle **disconnected tools** — spreadsheets for quotations, separate apps for invoicing, manual payment tracking, no visibility across branches. The result:

- ❌ Quotations lost in email threads
- ❌ No audit trail for revisions or price changes
- ❌ Billing tied to a single person's laptop
- ❌ No real-time view of outstanding receivables
- ❌ Teams can't collaborate on the same accounts
- ❌ Zero visibility for business owners across branches

**Beayar eliminates all of this.** It gives every team member — from the sales rep drafting a quote to the owner checking monthly revenue — a single, shared source of truth.

---

## 3. Product Positioning

### Who It's For

| Audience | Pain Point | How Beayar Helps |
|---|---|---|
| **Business Owners** | Can't see real-time financials across branches | Dashboard with live revenue, expenses, and sales targets |
| **Sales Teams** | Quotation creation is slow and error-prone | Multi-currency quotation builder with automatic calculations, tax, and PDF export |
| **Operations Managers** | Delivery and billing are disconnected | Challan-to-bill linking with partial delivery support |
| **Accountants** | Payment tracking is manual and scattered | Advance, running, and regular billing with payment recording |
| **Growing Teams** | Adding employees creates permission chaos | Role-based access control with fine-grained feature gating |

### Competitive Differentiators

1. **Quotation Revision Management** — Unlike generic invoicing tools, Beayar tracks every revision of a quotation, lets you compare versions, and keeps a full audit trail. Clients see professionalism; you see accountability.

2. **Flexible Billing Workflows** — Support for **advance bills** (pay before delivery), **running bills** (progressive billing), and **regular bills** (standard invoicing) — all in one system. Most competitors only handle one type.

3. **True Multi-Tenancy** — One login, multiple companies. Business owners with branches or multiple ventures switch between companies in one click. Data is completely isolated; employees only see what they should.

4. **Dynamic Feature Gating** — The platform's subscription system doesn't just limit counts — it controls feature access at the granular level. Admins can dynamically assign which features each plan tier includes, with no code changes needed.

5. **Built for Emerging Markets** — Multi-currency support with live exchange rates, BDT/INR/USD/EUR/CNY currencies, and localized date formats. Designed for businesses that trade across borders.

---

## 4. Complete Feature Inventory

### 4.1 Quotation Management ⭐ (Core Module)

| Feature | Details |
|---|---|
| **Quotation Builder** | Dynamic multi-line-item form with real-time subtotal, tax, and grand total calculation |
| **Multi-Currency Support** | Create quotations in USD, EUR, BDT, INR, CNY with live exchange rate conversion and custom exchange rate overrides |
| **Quotation Revisions** | Create multiple revisions of a quotation, activate the best version, and maintain a full version history |
| **Status Pipeline** | Track quotation lifecycle: Draft → Sent → Accepted → Rejected → Converted |
| **PDF Export** | Generate professional PDF quotations with company branding, ready to share with clients |
| **Product Auto-Fill** | Select products from catalog and auto-populate price, specifications, and brand/origin |
| **Smart Numbering** | Configurable quotation number formats (e.g., `{PREFIX}-{YYYY}-{SEQUENCE}`, `{CUSTOMER_NO}-{YY}-{SEQ}`) |
| **Quick Product Create** | Add new products inline while building a quotation — no page switching |
| **Terms & Conditions** | Rich text editor for customizable terms embedded in each quotation |

---

### 4.2 Billing & Invoicing 💰

| Feature | Details |
|---|---|
| **Three Billing Modes** | **Advance Bills** (prepayment), **Running Bills** (progressive billing), **Regular Bills** (post-delivery) |
| **Quotation-to-Bill Conversion** | One-click conversion from approved quotation to bill |
| **Challan-Linked Billing** | Link one or more delivery challans to a bill for accurate invoicing |
| **Bill Items Tracking** | Granular line-item tracking linked to challan products |
| **Invoice Numbering** | Auto-generated, sequential invoice numbers per company |
| **Payment Recording** | Record partial or full payments against any bill |
| **Received Bills** | Track payments received with date, amount, and reference |
| **Bill Search & Filter** | Search across all bills by type, status, customer, or date range |

---

### 4.3 Delivery Management (Challans) 🚚

| Feature | Details |
|---|---|
| **Challan Creation** | Generate delivery challans from accepted quotations |
| **Partial Delivery** | Support single-complete and partial-multiple delivery modes |
| **Product Tracking** | Track delivered quantities per product across multiple challans |
| **Bill Linking** | Associate challans with bills for accurate financial tracking |

---

### 4.4 Customer Relationship Management (CRM) 👥

| Feature | Details |
|---|---|
| **Customer Companies** | Manage B2B client organizations with full profiles |
| **Customer Contacts** | Multiple contact persons per client company |
| **Customer Search** | Instant search and filtering across your client base |
| **Customer Serial Numbers** | Auto-generated, unique identifiers for each customer |
| **Quick Company Create** | Add client companies from a modal without leaving your current workflow |
| **Quotation History** | View complete quotation history per customer |

---

### 4.5 Product Catalog 📦

| Feature | Details |
|---|---|
| **Product Management** | Full CRUD with name, description, pricing, and categorization |
| **Specifications** | Attach detailed technical specifications to each product |
| **Brand & Origin Tracking** | Track product brands and countries of origin with inline create/edit |
| **Image Library** | Centralized media manager with drag-and-drop upload, search, and reuse across products |
| **Product Search** | AJAX-powered instant search used across quotations and challans |

---

### 4.6 Financial Overview & Reports 📊

| Feature | Details |
|---|---|
| **Finance Dashboard** | Visual overview of income vs. expenses with interactive charts |
| **Expense Tracking** | Record and categorize company expenses |
| **Expense Categories** | Customizable expense categories for organized cost tracking |
| **Sales Targets** | Set monthly sales targets per user and track actual vs. target performance |
| **Payment Overview** | See all outstanding receivables and collected payments at a glance |
| **Revenue Charts** | ApexCharts-powered interactive graphs for revenue trends |

---

### 4.7 Multi-Company & Team Management 🏢

| Feature | Details |
|---|---|
| **Multiple Companies** | Manage multiple business entities under a single account |
| **Company Switcher** | Instant context-switching between companies via the sidebar |
| **Team Invitations** | Invite employees by email — they join immediately or create an account |
| **Role-Based Access** | Assign roles: **Owner**, **Admin**, **Employee** with distinct permission levels |
| **Custom Roles** | Create custom roles with specific permission sets (e.g., "Sales Manager" with quotation access but no billing) |
| **Member Management** | Activate, deactivate, or remove team members without losing their data |
| **Activity Logs** | Full audit trail — see who did what, and when |

---

### 4.8 Company Settings & Customization ⚙️

| Feature | Details |
|---|---|
| **Company Profile** | Logo, name, address, BIN number, and contact details |
| **Date Format** | Choose your preferred date display format (DD-MM-YYYY, YYYY-MM-DD, etc.) |
| **Currency Preference** | Set default currency and symbol for your company |
| **Custom Exchange Rates** | Override live exchange rates with your own custom rates |
| **Quotation Number Format** | Configure quotation numbering patterns using dynamic tags |
| **Workspace Theming** | Company logo displayed throughout the interface |

---

### 4.9 Subscription & Plans (Business Model) 💳

| Feature | Details |
|---|---|
| **Free Plan** | Ideal for freelancers — 1 company, 20 quotations/month, 3 employees |
| **Pro Plan** | For small businesses — 5 companies, 100 quotations/month, 10 employees |
| **Pro Plus Plan** | For growing teams — 15 companies, unlimited quotations, 50 employees |
| **Custom Plan** | Enterprise — build your own plan with a dynamic pricing calculator |
| **Dynamic Pricing** | `Base Price + (Companies × $5) + (Employees × $2) + Module Add-ons` |
| **Module Add-ons** | Purchase additional modules (inventory, accounting, HR) à la carte |
| **Usage Tracking** | Real-time quota monitoring with upgrade prompts when approaching limits |
| **Feature Gating** | Granular feature-level access control per plan — admin-configurable |
| **Onboarding Flow** | Guided setup: Choose Plan → Create Company → Start Working |

---

### 4.10 Platform Administration (Super Admin) 🛡️

| Feature | Details |
|---|---|
| **Admin Dashboard** | Platform-wide analytics: MRR, tenant growth, active subscriptions |
| **Tenant Management** | View all companies, suspend accounts, impersonate tenants for support |
| **Plan Management** | Create and configure subscription plans with feature assignments |
| **Module Management** | Manage purchasable feature modules and their pricing |
| **Feature Management** | Dynamic feature CRUD — create features and associate them with plans |
| **Global Coupons** | Create platform-wide discount campaigns (percentage or fixed) |
| **Permission Management** | Manage the granular permission system across the platform |
| **Tenant Impersonation** | Log in as any tenant for troubleshooting — one click |

---

### 4.11 Security & Data Isolation 🔒

| Feature | Details |
|---|---|
| **Tenant Data Isolation** | Automatic query scoping — every database query is filtered by company context |
| **CSRF Protection** | All forms and AJAX requests include CSRF tokens |
| **Role-Based Authorization** | Policies and middleware enforce access at the route level |
| **Soft Deletes** | Deleted records are archived, not destroyed — recoverable by admins |
| **Activity Logging** | Spatie Activitylog tracks all significant actions with actor and timestamp |
| **Session-Based Auth** | Laravel Sanctum with session-based authentication for web |

---

### 4.12 User Experience & Interface 🎨

| Feature | Details |
|---|---|
| **Modern UI** | Clean, minimal design built with Tailwind CSS and Flowbite components |
| **Responsive Layout** | Desktop-first with mobile-friendly sidebar and navigation |
| **Dark Mode Ready** | DataTables and chart components support dark mode themes |
| **Alpine.js Interactivity** | Snappy client-side interactions without heavy framework overhead |
| **Toast Notifications** | Real-time success/error feedback via SweetAlert2 |
| **Print-Optimized Views** | Quotation and bill views designed for clean printing and PDF |
| **Drag & Drop Uploads** | Image and file uploads with drag-and-drop support |
| **Rich Text Editor** | SunEditor integration for terms, descriptions, and notes |
| **Searchable Dropdowns** | AJAX-powered searchable selects for customers, products, and companies |
| **Dynamic Forms** | Add/remove line items, inline create entities, and real-time calculations |

---

## 5. User Journey (End-to-End Workflow)

```
┌───────────────┐     ┌──────────────────┐     ┌──────────────────┐
│  1. SIGN UP   │ ──▶ │  2. CHOOSE PLAN  │ ──▶ │ 3. CREATE COMPANY│
│  (Register)   │     │  (Free/Pro/Plus)  │     │ (Name, Logo, BIN)│
└───────────────┘     └──────────────────┘     └──────────────────┘
                                                        │
        ┌───────────────────────────────────────────────┘
        ▼
┌───────────────┐     ┌──────────────────┐     ┌──────────────────┐
│ 4. ADD CLIENTS│ ──▶ │ 5. ADD PRODUCTS  │ ──▶ │ 6. CREATE QUOTE  │
│  (CRM)        │     │ (Catalog)        │     │ (Builder)        │
└───────────────┘     └──────────────────┘     └──────────────────┘
                                                        │
        ┌───────────────────────────────────────────────┘
        ▼
┌───────────────┐     ┌──────────────────┐     ┌──────────────────┐
│ 7. SEND QUOTE │ ──▶ │ 8. CLIENT ACCEPTS│ ──▶ │ 9. CREATE CHALLAN│
│  (PDF/Email)  │     │ (Status Update)  │     │ (Delivery)       │
└───────────────┘     └──────────────────┘     └──────────────────┘
                                                        │
        ┌───────────────────────────────────────────────┘
        ▼
┌───────────────┐     ┌──────────────────┐     ┌──────────────────┐
│ 10. CREATE    │ ──▶ │ 11. RECORD       │ ──▶ │ 12. VIEW REPORTS │
│  BILL/INVOICE │     │  PAYMENT         │     │ (Dashboard)      │
└───────────────┘     └──────────────────┘     └──────────────────┘
```

### The Complete Business Cycle — Inside One Platform

1. **Sign Up** → Register with email, verify, and you're in.
2. **Choose a Plan** → Free to start, upgrade when you grow.
3. **Create Your Company** → Set up company profile, logo, and preferences.
4. **Add Your Clients** → Import or create client companies and contacts.
5. **Build Your Product Catalog** → Add products with specs, images, and pricing.
6. **Create Quotations** → Use the dynamic builder with revision management.
7. **Send & Track** → Export PDFs, track status (Draft → Sent → Accepted).
8. **Deliver** → Create challans to track partial or full deliveries.
9. **Invoice** → Convert quotations to advance, running, or regular bills.
10. **Get Paid** → Record partial or full payments, track outstanding amounts.
11. **Analyze** → View financial dashboards, expense reports, and sales targets.
12. **Scale** → Add branches, invite team members, upgrade your plan.

---

## 6. Subscription Plans — Pricing Page Content

### Plan Comparison Table

| Feature | Free | Pro | Pro Plus | Custom |
|---|:---:|:---:|:---:|:---:|
| **Monthly Price** | $0 | $29/mo | $79/mo | Calculated |
| **Companies / Branches** | 1 | 5 | 15 | User-defined |
| **Monthly Quotations** | 20 | 100 | Unlimited | User-defined |
| **Team Members** | 3 | 10 | 50 | User-defined |
| **Quotation Revisions** | ❌ | ✅ | ✅ | ✅ |
| **PDF Export** | ❌ | ✅ | ✅ | ✅ |
| **Advance Billing** | ❌ | ✅ | ✅ | ✅ |
| **Running Bills** | ❌ | ✅ | ✅ | ✅ |
| **Finance Dashboard** | ❌ | ✅ | ✅ | ✅ |
| **Custom Roles** | ❌ | ✅ | ✅ | ✅ |
| **Multiple Companies** | ❌ | ✅ | ✅ | ✅ |
| **Module Add-ons** | ❌ | Basic | Advanced | Selectable |
| **Priority Support** | ❌ | ❌ | ✅ | ✅ |

### Custom Plan Builder

> *"Build your perfect plan."*

Businesses with specific needs can configure their own plan by selecting:
- Number of companies / branches
- Monthly quotation limit
- Number of team members
- Specific feature modules

**Pricing formula:**
`Total = Base Fee + (Companies × $5) + (Employees × $2) + (Quotation Volume Tier) + (Module Add-ons)`

---

## 7. Technical Architecture — Trust Signals for the Promo Site

> Useful for an "Under the Hood" or "Built with Enterprise-Grade Technology" section.

### Technology Stack

| Layer | Technology |
|---|---|
| **Backend** | Laravel 10+ (PHP 8.2) |
| **Frontend** | Blade Templates + Alpine.js |
| **Styling** | Tailwind CSS + Flowbite |
| **Charts** | ApexCharts |
| **Database** | MySQL 8.0+ |
| **Authentication** | Laravel Sanctum (session-based) |
| **Authorization** | Spatie Laravel Permission (RBAC) |
| **Audit Trail** | Spatie Activitylog |
| **Rich Text** | SunEditor |
| **Build Tool** | Vite |

### Architecture Highlights

- **Single-Database Multi-Tenancy** — All tenants share one database with automatic query scoping. Zero risk of data leakage between companies.
- **Global Scope Enforcement** — A `TenantScope` is applied to every Eloquent query on tenant-specific models, ensuring users only ever see their own company's data.
- **Service Layer Architecture** — Business logic lives in dedicated service classes (`QuotationService`, `BillingService`, `CompanySettingsService`), keeping controllers thin and testable.
- **Feature Gating Engine** — Features are dynamically assigned to plans via a `features` / `plan_features` database system. The admin can change what any plan includes — no code deployment needed.
- **Middleware Stack** — Layered security: `SetTenantContext` → `EnsureOnboardingComplete` → `CheckCompanyRole` → `CheckSubscriptionLimits` → `CheckFeatureAccess`.
- **Automated Testing** — PHPUnit/Pest test suite covering subscription logic, quotation calculations, multi-tenancy isolation, and critical workflows.

---

## 8. Key Statistics (for Social Proof / Hero Section)

> These are architectural facts you can translate into marketing copy.

| Metric | Value |
|---|---|
| Eloquent Models | 41+ |
| Controllers | 24+ across Admin, Tenant, and API scopes |
| Service Classes | 17+ covering every business domain |
| Database Migrations | 80+ tables and columns |
| Blade Views | 160+ pages and components |
| Custom Middleware | 10 security & context layers |
| Test Files | 30+ automated test suites |
| Feature-Gated Capabilities | 16+ individually toggleable features |
| Supported Currencies | 5 (USD, EUR, BDT, INR, CNY) |
| Date Format Options | 5+ configurable formats |
| Billing Types | 3 (Advance, Running, Regular) |

---

## 9. Suggested Website Sections & Messaging

### Hero Section
**Headline:** *"Run Your Entire Business From One Dashboard"*
**Subheadline:** *"Quotations. Invoicing. Deliveries. Payments. Teams. All in one place."*
**CTA:** *"Start Free — No Credit Card Required"*

### Feature Tour Sections (Recommended Order)
1. **Quotation Builder** — "Create Professional Quotations in Minutes"
2. **Billing & Invoicing** — "From Quote to Payment — Seamlessly"
3. **Multi-Company Management** — "Multiple Branches. One Platform."
4. **Team Collaboration** — "Invite Your Team. Control Their Access."
5. **Financial Insights** — "See Your Numbers. Make Better Decisions."
6. **Customization** — "Your Company. Your Rules."

### Trust Bar
- "Enterprise-grade security"
- "99.9% uptime"
- "Your data is yours — always"
- "Built on Laravel — trusted by millions"

### Target Audience Callouts
- 🏭 **Manufacturing & Trading** — Track quotations, deliveries, and invoices
- 🔧 **Engineering Services** — Manage revisions and progressive billing
- 💼 **Small Business Owners** — See everything from one dashboard
- 👥 **Growing Teams** — Add branches, invite employees, assign roles

---

## 10. Glossary (for Promo Site Copy Accuracy)

| Term | Meaning in Beayar |
|---|---|
| **Tenant** | A company/business entity using the platform (the customer's organization) |
| **Quotation** | A price proposal sent to a client, can have multiple revisions |
| **Revision** | A version of a quotation — compare, select, and proceed with the best one |
| **Challan** | A delivery document that tracks what products were shipped |
| **Bill** | An invoice generated from a quotation or challan |
| **Advance Bill** | An invoice requesting payment before delivery |
| **Running Bill** | A progressive invoice based on partial deliveries or milestones |
| **Regular Bill** | A standard post-delivery invoice |
| **Received Bill** | A payment record logged against a bill |
| **Feature Gating** | Controlling which features are available based on the subscription plan |
| **Module** | An optional add-on capability (e.g., advanced reports, inventory) |
| **Company Switcher** | UI element that lets users switch between their companies |
| **Tenant Scope** | The automatic data filter that isolates each company's data |
| **Onboarding** | The guided setup process: Plan → Company → Start |

---

## 11. SEO Keywords (for Promo Site)

### Primary Keywords
- ERP software for small business
- Online quotation management software
- Multi-company billing software
- Invoice and quotation software
- SaaS ERP platform

### Secondary Keywords
- quotation revision tracking
- advance billing software
- running bill management
- multi-tenant ERP
- delivery challan software
- team management ERP
- expense tracking for businesses
- sales target tracking
- multi-currency quotation tool

### Long-tail Keywords
- "how to manage quotations and invoices in one app"
- "best ERP for small trading businesses"
- "quotation management with revision history"
- "billing software with advance and running bills"
- "multi-branch business management tool"

---

## 12. Content Assets Needed (Recommendations)

### For the Promo Website
- [ ] **Product screenshots** — Dashboard, Quotation Builder, Bill View, Company Settings
- [ ] **Feature comparison table** — Interactive toggle between plan tiers
- [ ] **User flow animation** — Animated GIF/video: Quotation → Challan → Bill → Payment
- [ ] **Testimonial section** — Placeholder for early adopters
- [ ] **Demo video** — 2-minute walkthrough of the main workflow
- [ ] **Interactive pricing calculator** — Let visitors build a custom plan and see the price

### Brand Assets
- [ ] Beayar logo (SVG, PNG at multiple sizes)
- [ ] Color palette and typography guide
- [ ] Illustration style guide

---

*Document generated on 2026-03-03. Based on complete codebase analysis of Beayar ERP (41 models, 24+ controllers, 17 services, 160+ views, 80+ migrations).*
