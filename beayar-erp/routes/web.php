<?php

use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\Admin\TenantController as AdminTenantController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\Tenant\CustomerController;
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

// Tenant Routes (Protected by auth in real app)
Route::group(['middleware' => ['web', 'auth']], function () {
    Route::get('/dashboard', function () {
        return view('tenant.dashboard');
    })->name('tenant.dashboard');

    Route::get('/billing', function () {
        return view('tenant.billing.index');
    })->name('tenant.billing.index');

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
    Route::get('/products/search', [\App\Http\Controllers\Tenant\QuotationController::class, 'searchProduct'])->name('tenant.products.search');
    Route::get('/products/{product}/specifications', [\App\Http\Controllers\Tenant\QuotationController::class, 'getProductSpecifications'])->name('tenant.products.specifications');

    // Products Routes
    Route::resource('products', \App\Http\Controllers\Tenant\ProductController::class)->names('tenant.products');

    // Customer Routes
    Route::get('/companies/search', [CustomerController::class, 'searchCompanies'])->name('companies.search');
    Route::get('/companies/{company}/next-customer-serial', [CustomerController::class, 'nextCustomerSerial'])->name('companies.next-serial');
    Route::get('/customers/search', [CustomerController::class, 'searchCustomers'])->name('tenant.customers.search');
    Route::resource('customers', CustomerController::class)->names('tenant.customers');
    Route::resource('companies', \App\Http\Controllers\Api\V1\CompanyController::class)->names('companies'); // For modal creation

    // Quotation Routes
    Route::get('/quotations/exchange-rate', [\App\Http\Controllers\Tenant\QuotationController::class, 'getExchangeRate'])->name('exchange.rate');
    Route::get('/quotations/next-number', [\App\Http\Controllers\Tenant\QuotationController::class, 'getNextQuotationNo'])->name('tenant.quotations.next-number');
    Route::post('/quotations/product', [\App\Http\Controllers\Tenant\QuotationController::class, 'createProduct'])->name('tenant.quotations.create-product');
    Route::patch('/quotations/{quotation}/status', [\App\Http\Controllers\Tenant\QuotationController::class, 'updateStatus'])->name('tenant.quotations.status');
    Route::delete('/quotations/{quotation}/revisions/{revision}', [\App\Http\Controllers\Tenant\QuotationController::class, 'destroyRevision'])->name('tenant.quotations.revisions.destroy');
    Route::resource('quotations', \App\Http\Controllers\Tenant\QuotationController::class)->names('tenant.quotations');

    // Brand Origins
    Route::get('/brand-origins/search', [\App\Http\Controllers\Tenant\BrandOriginController::class, 'search'])->name('tenant.brand-origins.search');
    Route::post('/brand-origins', [\App\Http\Controllers\Tenant\BrandOriginController::class, 'store'])->name('tenant.brand-origins.store');
    Route::put('/brand-origins/{brandOrigin}', [\App\Http\Controllers\Tenant\BrandOriginController::class, 'update'])->name('tenant.brand-origins.update');
    Route::delete('/brand-origins/{brandOrigin}', [\App\Http\Controllers\Tenant\BrandOriginController::class, 'destroy'])->name('tenant.brand-origins.destroy');
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
