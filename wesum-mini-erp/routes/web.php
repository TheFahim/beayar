<?php

use App\Http\Controllers\BillController;
use App\Http\Controllers\ChallanController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboradController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ReceivedBillController;
use App\Http\Controllers\SaleTargetController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\CheckUserIsActive;
use App\Http\Middleware\CheckUserIsAdmin;
use App\Models\SaleTarget;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware(['auth',CheckUserIsActive::class])
    ->prefix('dashboard')
    ->group(function () {
        Route::get('/', [DashboradController::class, 'index'])->name('dashboard.index');


        Route::get('users/{user}/disable', [UserController::class, 'disable'])->name('users.disable')->middleware(CheckUserIsAdmin::class);
        Route::resource('users', UserController::class)->middleware(CheckUserIsAdmin::class);


        Route::get('/expenses-chart-data', [ExpenseController::class, 'getChartData'])->name('expenses.chart.data');

        Route::resource('expense', ExpenseController::class);
        Route::resource('targets', SaleTargetController::class);
        Route::resource('challans', ChallanController::class);

        Route::get('/search/bills', [BillController::class, 'search'])->name('bills.search');

        Route::get('/api/billing-data', [BillController::class, 'getBillingData']);
        Route::get('/api/target-chart-data', [SaleTargetController::class, 'getTargetChartData'])->name('targets.chart.data');

        Route::get('/api/top-summary', [DashboradController::class, 'getTopSummary'])->name('dashboard.top.summary');
        Route::get('/api/profit-summary', [DashboradController::class, 'getProfitSummary'])->name('dashboard.profit.summary');

        Route::get('/api/expense', [DashboradController::class, 'getExpenseData'])->name('dashboard.expense.data');

        Route::get('/api/due', [DashboradController::class, 'getDueData'])->name('dashboard.due.data');

        Route::get('/api/target', [DashboradController::class, 'getTargetData'])->name('dashboard.target.data');

        Route::get('/api/quotation', [DashboradController::class, 'getQuotationData'])->name('dashboard.quotation.data');

        Route::get('/api/my-quotation-years', [DashboradController::class, 'getMyQuotationYears'])->name('api.my.quotation.years');

        // Route to get the user's own monthly summary for a given year
        Route::get('/api/my-quotation-summary', [DashboradController::class, 'getMyQuotationSummary'])->name('api.my.quotation.summary');


        Route::resource('bills', BillController::class);
        Route::resource('received-bills', ReceivedBillController::class);


        Route::get('/customers/search', [QuotationController::class, 'searchCustomer'])->name('customers.search');
        Route::get('/pre-quotation', [QuotationController::class, 'preQuotation'])->name('pre.quotation');

        Route::resource('quotations', QuotationController::class);
        Route::resource('customers', CustomerController::class);

        // Route::resource('permissions', PermissionController::class);
        // Route::resource('roles', RoleController::class);

        Route::delete('logout', [SessionController::class, 'logout'])->name('logout');
    });

Route::get('login', [SessionController::class, 'login'])->name('login');
Route::post('signin', [SessionController::class, 'signIn'])->name('signIn');
// Route::get('register', [SessionController::class, 'register'])->name('register');
// Route::post('register', [SessionController::class, 'store'])->name('register.store');
