<?php

use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
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

// Tenant Routes (Protected by auth in reazl app)
Route::group(['middleware' => ['web', 'auth', 'onboarding.complete', 'tenant.context']], function () {
    Route::get('/dashboard', function () {
        return view('tenant.dashboard');
    })->name('tenant.dashboard');

    // Company Members
    Route::resource('company-members', \App\Http\Controllers\CompanyMemberController::class)->names('company-members');

    // Billing Routes
    Route::get('/bills/search', [BillController::class, 'search'])->name('tenant.bills.search');
    Route::get('/bills/data', [BillController::class, 'getBillingData'])->name('tenant.bills.data');
    Route::get('/quotations/{quotation}/bill', [BillController::class, 'createFromQuotation'])->name('tenant.quotations.bill');
    Route::post('/quotations/{quotation}/bills/advance', [BillController::class, 'storeAdvanceBill'])->name('tenant.quotations.bills.advance.store');
    Route::post('/quotations/{quotation}/bills/running', [BillController::class, 'storeRunningBill'])->name('tenant.quotations.bills.running.store');
    Route::put('/bills/{bill}/advance', [BillController::class, 'updateAdvance'])->name('tenant.bills.advance.update');
    Route::put('/bills/{bill}/regular', [BillController::class, 'updateRegular'])->name('tenant.bills.regular.update');
    Route::put('/bills/{bill}/running', [BillController::class, 'updateRunning'])->name('tenant.bills.running.update');
    Route::resource('bills', BillController::class)->names('tenant.bills');

    // Received Bills (Payments)
    Route::resource('received-bills', ReceivedBillController::class)->names('tenant.received-bills');

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
    Route::resource('products', ProductController::class)->names('tenant.products');

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
    Route::resource('quotations', QuotationController::class)->names('tenant.quotations');

    // Brand Origins
    Route::get('/brand-origins/search', [BrandOriginController::class, 'search'])->name('tenant.brand-origins.search');
    Route::post('/brand-origins', [BrandOriginController::class, 'store'])->name('tenant.brand-origins.store');
    Route::put('/brand-origins/{brandOrigin}', [BrandOriginController::class, 'update'])->name('tenant.brand-origins.update');
    Route::delete('/brand-origins/{brandOrigin}', [BrandOriginController::class, 'destroy'])->name('tenant.brand-origins.destroy');

    // Challan Routes
    Route::get('/challans/products', [ChallanController::class, 'getProductsByChallanIds'])->name('tenant.challans.products');
    Route::resource('challans', ChallanController::class)->names('tenant.challans');
});

// Admin Routes
Route::prefix('admin')->middleware(['web', 'auth', 'role:admin'])->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/tenants', [AdminTenantController::class, 'index'])->name('tenants.index');
    Route::post('/tenants/{company}/suspend', [AdminTenantController::class, 'suspend'])->name('tenants.suspend');
    Route::post('/tenants/{company}/impersonate', [AdminTenantController::class, 'impersonate'])->name('tenants.impersonate');

    Route::get('/plans', [AdminPlanController::class, 'index'])->name('plans.index');
    Route::put('/plans/{plan}', [AdminPlanController::class, 'update'])->name('plans.update');

    Route::get('/coupons', [AdminCouponController::class, 'index'])->name('coupons.index');
    Route::post('/coupons', [AdminCouponController::class, 'store'])->name('coupons.store');
    Route::delete('/coupons/{coupon}', [AdminCouponController::class, 'destroy'])->name('coupons.destroy');
});
