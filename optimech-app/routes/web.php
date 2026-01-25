<?php

use App\Http\Controllers\BillController;
use App\Http\Controllers\BrandOriginController;
use App\Http\Controllers\ChallanController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboradController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ReceivedBillController;
use App\Http\Controllers\SaleTargetController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\SpecificationController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckUserIsActive;
use App\Http\Middleware\CheckUserIsAdmin;
// use App\Http\Controllers\BillingController; // merged into BillController
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware(['auth', CheckUserIsActive::class])
    ->prefix('dashboard')
    ->group(function () {
        Route::get('/', [DashboradController::class, 'index'])->name('dashboard.index');

        // User Management Routes
        Route::get('users/{user}/disable', [UserController::class, 'disable'])->name('users.disable')->middleware(CheckUserIsAdmin::class);
        Route::resource('users', UserController::class)->middleware(CheckUserIsAdmin::class);

        // Expense Routes
        Route::get('/expenses-chart-data', [ExpenseController::class, 'getChartData'])->name('expenses.chart.data');

        // Image Routes
        Route::get('images/search', [ImageController::class, 'search'])->name('images.search');
        Route::resource('images', ImageController::class);

        // Product and Specification Routes
        Route::get('/products/search', [QuotationController::class, 'searchProduct'])->name('products.search');
        Route::resource('products', ProductController::class);
        Route::resource('specifications', SpecificationController::class);

        // Challan Routes
        Route::get('/challans/products', [ChallanController::class, 'getProductsByChallanIds'])->name('challans.products');
        Route::resource('challans', ChallanController::class);

        // Bill Routes
        Route::get('/search/bills', [BillController::class, 'search'])->name('bills.search');
        Route::get('/api/billing-data', [BillController::class, 'getBillingData']);
        Route::get('/api/target-chart-data', [SaleTargetController::class, 'getTargetChartData'])->name('targets.chart.data');
        Route::resource('bills', BillController::class);
        Route::get('bills/{bill}/edit-regular', [BillController::class, 'editRegular'])->name('bills.edit-regular');
        Route::put('bills/{bill}/update-regular', [BillController::class, 'updateRegular'])->name('bills.update-regular');
        Route::get('bills/{bill}/edit-advance', [BillController::class, 'editAdvance'])->name('bills.edit-advance');
        Route::put('bills/{bill}/update-advance', [BillController::class, 'updateAdvance'])->name('bills.update-advance');
        Route::get('bills/{bill}/edit-running', [BillController::class, 'editRunning'])->name('bills.edit-running');
        Route::put('bills/{bill}/update-running', [BillController::class, 'updateRunning'])->name('bills.update-running');
        Route::resource('received-bills', ReceivedBillController::class);

        // Billing Routes (Smart Bill Creation System via BillController)
        Route::prefix('billing')->name('bills.')->group(function () {
            Route::get('/quotations/{quotation}/create', [BillController::class, 'createFromQuotation'])->name('create-from-quotation');
            Route::post('/quotations/{quotation}/store', [BillController::class, 'storeAdvanceBill'])->name('store-from-quotation');
            Route::post('/quotations/{quotation}/store-advance-bill', [BillController::class, 'storeAdvanceBill'])->name('store-advance-bill');
            Route::post('/quotations/{quotation}/store-running-bill', [BillController::class, 'storeRunningBill'])->name('store-running-bill');
        });

        // Dashboard API Routes - Simplified to only core entities
        Route::get('/api/my-quotation-years', [DashboradController::class, 'getMyQuotationYears'])->name('api.my.quotation.years');
        Route::get('/api/my-quotation-summary', [DashboradController::class, 'getMyQuotationSummary'])->name('api.my.quotation.summary');
        Route::get('/api/financial-summary', [DashboradController::class, 'getFinancialSummaryApi'])->name('api.financial.summary');
        Route::get('/api/user-quotation-stats', [DashboradController::class, 'getUserQuotationStats'])
            ->name('api.user.quotation.stats')
            ->middleware(CheckUserIsAdmin::class);

        // Customer and Company Routes
        Route::get('/customers/search', [QuotationController::class, 'searchCustomer'])->name('customers.search');
        Route::get('/companies/search', [CompanyController::class, 'search'])->name('companies.search');
        Route::get('/companies/{company}/next-customer-serial', [CompanyController::class, 'getNextCustomerSerial'])->name('companies.next-customer-serial');
        Route::resource('customers', CustomerController::class);
        Route::resource('companies', CompanyController::class);

        // Product API Routes
        Route::get('/products/{product}/specifications', [QuotationController::class, 'getProductSpecifications'])->name('products.specifications');
        Route::get('/quotations/exchange-rate', [QuotationController::class, 'getExchangeRate'])->name('exchange.rate');
        // Quotation helper API Routes
        Route::get('/quotations/next-number', [QuotationController::class, 'getNextQuotationNo'])->name('quotations.next-number');
        Route::post('/quotations/create-product', [QuotationController::class, 'createProduct'])->name('quotations.create-product');
        Route::post('/quotations/upload-product-image', [QuotationController::class, 'uploadProductImage'])->name('quotations.upload-product-image');

        // ============ QUOTATION ROUTES WITH NESTED REVISIONS ============

        // Quotation Search/Filter Routes (must be before resource)
        Route::get('quotations/search', [QuotationController::class, 'search'])->name('quotations.search');

        Route::get('activate/{revision}/revisions', [QuotationController::class, 'activateRevision'])->name('revisions.activate');
        // Quotation Revision Routes (nested structure)
        Route::prefix('quotations/{quotation}')->name('quotations.')->group(function () {
            // Revision management routes
            Route::get('revisions', [QuotationController::class, 'showRevisions'])->name('revisions');
            Route::get('revisions/create', [QuotationController::class, 'createRevision'])->name('revisions.create');
            Route::post('revisions', [QuotationController::class, 'storeRevision'])->name('revisions.store');
            Route::get('revisions/{revision}', [QuotationController::class, 'showRevision'])->name('revisions.show');
            Route::get('revisions/{revision}/edit', [QuotationController::class, 'editRevision'])->name('revisions.edit');
            Route::put('revisions/{revision}', [QuotationController::class, 'updateRevision'])->name('revisions.update');
            Route::delete('revisions/{revision}', [QuotationController::class, 'destroyRevision'])->name('revisions.destroy');

            // Revision lock/unlock (for challan integration)
            Route::post('revisions/{revision}/lock', [QuotationController::class, 'lockRevision'])->name('revisions.lock');
            Route::post('revisions/{revision}/unlock', [QuotationController::class, 'unlockRevision'])->name('revisions.unlock');

            // Duplicate revision to create new one
            Route::post('revisions/{revision}/duplicate', [QuotationController::class, 'duplicateRevision'])->name('revisions.duplicate');

            // PDF export for revision
            Route::get('revisions/{revision}/pdf', [QuotationController::class, 'exportRevisionPdf'])->name('revisions.pdf');
        });

        Route::get('brand-origins/search', [BrandOriginController::class, 'search'])->name('brand-origins.search');
        Route::resource('brand-origins', BrandOriginController::class);
        // Additional Quotation Action Routes
        Route::post('quotations/{quotation}/duplicate', [QuotationController::class, 'duplicate'])->name('quotations.duplicate');
        Route::patch('quotations/{quotation}/status', [QuotationController::class, 'updateStatus'])->name('quotations.status.update');
        Route::post('quotations/{quotation}/send-email', [QuotationController::class, 'sendEmail'])->name('quotations.send-email');
        Route::get('quotations/{quotation}/print', [QuotationController::class, 'print'])->name('quotations.print');
        Route::get('quotations/{quotation}/download', [QuotationController::class, 'download'])->name('quotations.download');

        // Export routes for quotations
        Route::get('quotations/export/excel', [QuotationController::class, 'exportExcel'])->name('quotations.export.excel');
        Route::get('quotations/export/pdf', [QuotationController::class, 'exportPdf'])->name('quotations.export.pdf');

        // Main Quotation Resource Routes
        Route::resource('quotations', QuotationController::class);

        // ============ END QUOTATION ROUTES ============

        // Logout Route
        Route::delete('logout', [SessionController::class, 'logout'])->name('logout');
    });

// Authentication Routes
Route::get('login', [SessionController::class, 'login'])->name('login');
Route::post('signin', [SessionController::class, 'signIn'])->name('signIn');
// Route::get('register', [SessionController::class, 'register'])->name('register');
// Route::post('register', [SessionController::class, 'store'])->name('register.store');
