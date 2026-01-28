<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Auth
Route::get('/', function () {
    return redirect('/login');
});



Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

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
    Route::post('/images', [\App\Http\Controllers\ImageController::class, 'store'])->name('tenant.images.store');
});

// Admin Routes
Route::prefix('admin')->middleware(['web', 'auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

    Route::get('/tenants', function () {
        return view('admin.tenants.index');
    })->name('admin.tenants.index');

    Route::get('/plans', function () {
        return view('admin.plans.index');
    })->name('admin.plans.index');

    Route::get('/coupons', function () {
        return view('admin.coupons.index');
    })->name('admin.coupons.index');
});
