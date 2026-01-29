<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\TenantController as AdminTenantController;
use App\Http\Controllers\Admin\PlanController as AdminPlanController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
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

    Route::get('/quotations', function () {
        return view('tenant.quotations.create');
    })->name('tenant.quotations.create');

    Route::get('/billing', function () {
        return view('tenant.billing.index');
    })->name('tenant.billing.index');

    Route::get('/finance', function () {
        return view('tenant.finance.index');
    })->name('tenant.finance.index');

    Route::get('/subscription', function () {
        return view('tenant.subscription.index');
    })->name('tenant.subscription.index');

    Route::get('/images', [\App\Http\Controllers\ImageController::class, 'index'])->name('tenant.images.index');
    Route::get('/images/search', [\App\Http\Controllers\ImageController::class, 'search'])->name('tenant.images.search');
    Route::post('/images', [\App\Http\Controllers\ImageController::class, 'store'])->name('tenant.images.store');
    Route::put('/images/{id}', [\App\Http\Controllers\ImageController::class, 'update'])->name('tenant.images.update');
    Route::delete('/images/{id}', [\App\Http\Controllers\ImageController::class, 'destroy'])->name('tenant.images.destroy');

    // Products Routes
    Route::resource('products', \App\Http\Controllers\ProductController::class)->names('tenant.products');
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
