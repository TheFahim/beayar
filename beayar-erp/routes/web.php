<?php

use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FeatureController as AdminFeatureController;
use App\Http\Controllers\Admin\ModuleController as AdminModuleController;
use App\Http\Controllers\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\Admin\TenantController as AdminTenantController;
use App\Http\Controllers\Api\V1\CompanyController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\Tenant\BillController;
use App\Http\Controllers\Tenant\BrandOriginController;
use App\Http\Controllers\Tenant\ChallanController;
use App\Http\Controllers\Tenant\CustomerController;
use App\Http\Controllers\Tenant\ProductController;
use App\Http\Controllers\Tenant\QuotationController;
use App\Http\Controllers\Tenant\ReceivedBillController;
use Illuminate\Support\Facades\Route;

// Auth
Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Password Reset Routes
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.store');

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Onboarding Routes (Protected by auth)
Route::group(['middleware' => ['web', 'auth'], 'prefix' => 'onboarding', 'as' => 'onboarding.'], function () {
    Route::get('/plan', [\App\Http\Controllers\OnboardingController::class, 'index'])->name('plan');
    Route::post('/plan', [\App\Http\Controllers\OnboardingController::class, 'storePlan'])->name('plan.store');
    Route::get('/company', [\App\Http\Controllers\OnboardingController::class, 'createCompany'])->name('company');
    Route::post('/company', [\App\Http\Controllers\OnboardingController::class, 'storeCompany'])->name('company.store');
});

// Context Switching
Route::post('/companies/{company}/switch', [\App\Http\Controllers\CompanyContextController::class, 'switch'])
    ->middleware(['web', 'auth'])
    ->name('companies.switch');

// Tenant Routes (Protected by auth in reazl app)
Route::group(['middleware' => ['web', 'auth', 'onboarding.complete', 'tenant.context']], function () {
    Route::get('/dashboard', function () {
        return view('tenant.dashboard');
    })->name('tenant.dashboard');

    // Tenant Profile (Only for Tenant Admin/Owner)
    Route::get('/profile', [\App\Http\Controllers\Tenant\ProfileController::class, 'show'])->name('tenant.profile.show');
    Route::get('/profile/edit', [\App\Http\Controllers\Tenant\ProfileController::class, 'edit'])->name('tenant.profile.edit');
    Route::put('/profile', [\App\Http\Controllers\Tenant\ProfileController::class, 'update'])->name('tenant.profile.update');

    // Company Members
    Route::resource('company-members', \App\Http\Controllers\CompanyMemberController::class)->names('company-members');

    // User Companies (Workspaces)
    Route::resource('user-companies', \App\Http\Controllers\Tenant\TenantCompanyController::class)->names('tenant.user-companies');

    // Billing Routes
    Route::get('/bills/search', [BillController::class, 'search'])->name('tenant.bills.search');
    Route::get('/bills/data', [BillController::class, 'getBillingData'])->name('tenant.bills.data');
    Route::get('/quotations/{quotation}/bill', [BillController::class, 'createFromQuotation'])->name('tenant.quotations.bill');
    Route::post('/quotations/{quotation}/bills/advance', [BillController::class, 'storeAdvanceBill'])->name('tenant.quotations.bills.advance.store');
    Route::post('/quotations/{quotation}/bills/running', [BillController::class, 'storeRunningBill'])->name('tenant.quotations.bills.running.store');
    Route::put('/bills/{bill}/advance', [BillController::class, 'updateAdvance'])->name('tenant.bills.advance.update');
    Route::put('/bills/{bill}/regular', [BillController::class, 'updateRegular'])->name('tenant.bills.regular.update');
    Route::put('/bills/{bill}/running', [BillController::class, 'updateRunning'])->name('tenant.bills.running.update');
    Route::resource('bills', BillController::class)->names('tenant.bills')->middleware('ensure.operational');

    // Received Bills (Payments)
    Route::resource('received-bills', ReceivedBillController::class)->names('tenant.received-bills')->middleware('ensure.operational');

    Route::get('/finance', function () {
        return view('tenant.finance.index');
    })->name('tenant.finance.index');

    Route::get('/subscription', function () {
        return view('tenant.subscription.index');
    })->name('tenant.subscription.index');

    Route::get('/images', [ImageController::class, 'index'])->name('tenant.images.index');
    Route::get('/images/search', [ImageController::class, 'search'])->name('tenant.images.search');
    Route::post('/images', [ImageController::class, 'store'])->name('tenant.images.store');
    Route::put('/images/{id}', [ImageController::class, 'update'])->name('tenant.images.update');
    Route::delete('/images/{id}', [ImageController::class, 'destroy'])->name('tenant.images.destroy');

    // Product Specifications
    Route::get('/products/search', [QuotationController::class, 'searchProduct'])->name('tenant.products.search');
    Route::get('/products/{product}/specifications', [QuotationController::class, 'getProductSpecifications'])->name('tenant.products.specifications');

    // Products Routes
    Route::resource('products', ProductController::class)->names('tenant.products')->middleware('ensure.operational');

    // Customer Routes
    Route::get('/companies/search', [CustomerController::class, 'searchCompanies'])->name('companies.search');
    Route::get('/companies/{company}/next-customer-serial', [CustomerController::class, 'nextCustomerSerial'])->name('companies.next-serial');
    Route::get('/customers/search', [CustomerController::class, 'searchCustomers'])->name('tenant.customers.search');
    Route::resource('customers', CustomerController::class)->names('tenant.customers');
    Route::resource('companies', CompanyController::class)->names('companies'); // For modal creation

    // Quotation Routes
    Route::get('/quotations/exchange-rate', [QuotationController::class, 'getExchangeRate'])->name('exchange.rate');
    Route::get('/quotations/next-number', [QuotationController::class, 'getNextQuotationNo'])->name('tenant.quotations.next-number');
    Route::post('/quotations/product', [QuotationController::class, 'createProduct'])->name('tenant.quotations.create-product');
    Route::patch('/quotations/{quotation}/status', [QuotationController::class, 'updateStatus'])->name('tenant.quotations.status');
    Route::get('/quotations/revisions/{revision}/activate', [QuotationController::class, 'activateRevision'])->name('tenant.quotations.revisions.activate');
    Route::delete('/quotations/{quotation}/revisions/{revision}', [QuotationController::class, 'destroyRevision'])->name('tenant.quotations.revisions.destroy');
    Route::resource('quotations', QuotationController::class)->names('tenant.quotations')->middleware('ensure.operational');

    // Brand Origins
    Route::get('/brand-origins/search', [BrandOriginController::class, 'search'])->name('tenant.brand-origins.search');
    Route::post('/brand-origins', [BrandOriginController::class, 'store'])->name('tenant.brand-origins.store');
    Route::put('/brand-origins/{brandOrigin}', [BrandOriginController::class, 'update'])->name('tenant.brand-origins.update');
    Route::delete('/brand-origins/{brandOrigin}', [BrandOriginController::class, 'destroy'])->name('tenant.brand-origins.destroy');

    // Challan Routes
    Route::get('/challans/products', [ChallanController::class, 'getProductsByChallanIds'])->name('tenant.challans.products');
    Route::resource('challans', ChallanController::class)->names('tenant.challans');
});

// Admin Auth Routes
Route::prefix('admin')->middleware(['web'])->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login']);
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
});

// Admin Routes
Route::prefix('admin')->middleware(['web', 'auth:admin', 'admin.auth'])->name('admin.')->group(function () {
    Route::post('/tenants/stop-impersonation', [AdminTenantController::class, 'stopImpersonation'])->name('tenants.stop-impersonation');

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Tenants
    Route::get('/tenants', [AdminTenantController::class, 'index'])->name('tenants.index');
    Route::get('/tenants/create', [AdminTenantController::class, 'create'])->name('tenants.create');
    Route::post('/tenants', [AdminTenantController::class, 'store'])->name('tenants.store');
    Route::get('/tenants/{company}', [AdminTenantController::class, 'show'])->name('tenants.show');
    Route::put('/tenants/{company}/subscription', [AdminTenantController::class, 'updateSubscription'])->name('tenants.subscription.update');
    Route::post('/tenants/{company}/suspend', [AdminTenantController::class, 'suspend'])->name('tenants.suspend');
    Route::post('/tenants/{company}/impersonate', [AdminTenantController::class, 'impersonate'])->name('tenants.impersonate');
    Route::delete('/tenants/{company}', [AdminTenantController::class, 'destroy'])->name('tenants.destroy');

    // Plans
    Route::get('/plans', [AdminPlanController::class, 'index'])->name('plans.index');
    Route::post('/plans', [AdminPlanController::class, 'store'])->name('plans.store');
    Route::put('/plans/{plan}', [AdminPlanController::class, 'update'])->name('plans.update');
    Route::delete('/plans/{plan}', [AdminPlanController::class, 'destroy'])->name('plans.destroy');

    // Modules
    Route::get('/modules', [AdminModuleController::class, 'index'])->name('modules.index');
    Route::post('/modules', [AdminModuleController::class, 'store'])->name('modules.store');
    Route::put('/modules/{module}', [AdminModuleController::class, 'update'])->name('modules.update');
    Route::delete('/modules/{module}', [AdminModuleController::class, 'destroy'])->name('modules.destroy');

    // Features
    Route::get('/features', [AdminFeatureController::class, 'index'])->name('features.index');
    Route::post('/features', [AdminFeatureController::class, 'store'])->name('features.store');
    Route::put('/features/{feature}', [AdminFeatureController::class, 'update'])->name('features.update');
    Route::delete('/features/{feature}', [AdminFeatureController::class, 'destroy'])->name('features.destroy');

    // Plan Feature Sync
    Route::put('/plans/{plan}/features', [AdminPlanController::class, 'syncFeatures'])->name('plans.features.sync');

    // Coupons
    Route::get('/coupons', [AdminCouponController::class, 'index'])->name('coupons.index');
    Route::post('/coupons', [AdminCouponController::class, 'store'])->name('coupons.store');
    Route::delete('/coupons/{coupon}', [AdminCouponController::class, 'destroy'])->name('coupons.destroy');
});

