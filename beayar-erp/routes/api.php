<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\TenantManagementController;
use App\Http\Controllers\Admin\PlatformRevenueController;
use App\Http\Controllers\Admin\GlobalCouponController;
use App\Http\Controllers\Api\V1\SubscriptionController;
use App\Http\Controllers\Api\V1\CompanyController;
use App\Http\Controllers\Api\V1\QuotationController;
use App\Http\Controllers\Api\V1\BillController;
use App\Http\Controllers\Api\V1\FinanceController;
use App\Http\Controllers\Api\V1\CouponController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->group(function () {
    // Auth
    Route::post('login', [AdminAuthController::class, 'login']);

    // Protected Admin Routes
    Route::middleware(['auth:sanctum', 'admin.auth'])->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout']);
        Route::get('me', [AdminAuthController::class, 'me']);

        // Tenants
        Route::get('tenants', [TenantManagementController::class, 'index']);
        Route::get('tenants/{company}', [TenantManagementController::class, 'show']);
        Route::post('tenants/{company}/suspend', [TenantManagementController::class, 'suspend']);
        Route::post('tenants/{company}/impersonate', [TenantManagementController::class, 'impersonate']);

        // Revenue
        Route::get('revenue', [PlatformRevenueController::class, 'index']);
        Route::get('invoices', [PlatformRevenueController::class, 'invoices']);

        // Global Coupons
        Route::apiResource('coupons', GlobalCouponController::class);
    });
});

/*
|--------------------------------------------------------------------------
| Tenant API V1 Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->middleware(['auth:sanctum', 'tenant.scope'])->group(function () {

    // Subscriptions
    Route::get('subscription/plans', [SubscriptionController::class, 'plans']);
    Route::get('subscription/current', [SubscriptionController::class, 'current']);
    Route::post('subscription/purchase', [SubscriptionController::class, 'purchase']);

    // Companies (Sub-companies)
    Route::apiResource('companies', CompanyController::class);

    // Quotations
    Route::apiResource('quotations', QuotationController::class);
    Route::post('quotations/{quotation}/revisions', [QuotationController::class, 'createRevision']);
    Route::get('quotations/{quotation}/pdf', [QuotationController::class, 'pdf']);

    // Bills
    Route::apiResource('bills', BillController::class);

    // Finance
    Route::get('finance/dashboard', [FinanceController::class, 'dashboard']);
    Route::get('finance/expenses', [FinanceController::class, 'expenses']);
    Route::get('finance/payments', [FinanceController::class, 'payments']);

    // Coupons (Tenant level)
    Route::apiResource('coupons', CouponController::class);
    Route::post('coupons/validate', [CouponController::class, 'validateCoupon']);

});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
