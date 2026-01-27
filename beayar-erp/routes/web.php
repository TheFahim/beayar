<?php

use Illuminate\Support\Facades\Route;

// Auth
Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/register', function () {
    return view('auth.register');
});

// Tenant Routes (Protected by auth in real app)
Route::group(['middleware' => ['web']], function () {
    Route::get('/dashboard', function () {
        return view('tenant.dashboard');
    });

    Route::get('/quotations', function () {
        return view('tenant.quotations.create');
    });

    Route::get('/billing', function () {
        return view('tenant.billing.index');
    });

    Route::get('/finance', function () {
        return view('tenant.finance.index');
    });

    Route::get('/subscription', function () {
        return view('tenant.subscription.index');
    });
});

// Admin Routes
Route::prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    });

    Route::get('/tenants', function () {
        return view('admin.tenants.index');
    });

    Route::get('/plans', function () {
        return view('admin.plans.index');
    });

    Route::get('/coupons', function () {
        return view('admin.coupons.index');
    });
});
