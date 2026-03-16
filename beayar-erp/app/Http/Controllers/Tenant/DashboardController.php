<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Bill;
use App\Models\BillPayment;
use App\Models\Challan;
use App\Models\Quotation;
use App\Models\SaleTarget;
use App\Models\TenantCompany;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $tenantId = $user->current_tenant_company_id;
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;
        $previousMonthDate = $now->copy()->subMonth();

        // 1. Revenue (Bill Payments)
        $currentRevenue = BillPayment::join('bills', 'bills.id', '=', 'bill_payments.bill_id')
            ->where('bills.tenant_company_id', $tenantId)
            ->whereMonth('bill_payments.payment_date', $currentMonth)
            ->whereYear('bill_payments.payment_date', $currentYear)
            ->sum('bill_payments.amount');

        $previousRevenue = BillPayment::join('bills', 'bills.id', '=', 'bill_payments.bill_id')
            ->where('bills.tenant_company_id', $tenantId)
            ->whereMonth('bill_payments.payment_date', $previousMonthDate->month)
            ->whereYear('bill_payments.payment_date', $previousMonthDate->year)
            ->sum('bill_payments.amount');

        $revenueTrend = $this->calculateTrend($currentRevenue, $previousRevenue);

        // 2. Invoices (Bills Count)
        $currentInvoices = Bill::where('tenant_company_id', $tenantId)
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->count();

        $previousInvoices = Bill::where('tenant_company_id', $tenantId)
            ->whereMonth('created_at', $previousMonthDate->month)
            ->whereYear('created_at', $previousMonthDate->year)
            ->count();

        $invoicesTrend = $this->calculateTrend($currentInvoices, $previousInvoices);

        // 3. Pending Invoices (Not paid or due > 0)
        // Interpreting "Trend" as comparing count of pending bills created this month vs last month
        $currentPending = Bill::where('tenant_company_id', $tenantId)
            ->where(function ($query) {
                $query->where('status', '!=', 'paid')
                      ->orWhere('due', '>', 0);
            })
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->count();

        $previousPending = Bill::where('tenant_company_id', $tenantId)
            ->where(function ($query) {
                $query->where('status', '!=', 'paid')
                      ->orWhere('due', '>', 0);
            })
            ->whereMonth('created_at', $previousMonthDate->month)
            ->whereYear('created_at', $previousMonthDate->year)
            ->count();

        $pendingTrend = $this->calculateTrend($currentPending, $previousPending);

        // 4. Teams (Active Members)
        $activeTeams = 0;
        $company = TenantCompany::find($tenantId);
        if ($company) {
            $activeTeams = $company->members()
                ->wherePivot('is_active', 1)
                ->count();
        }

        // 5. Total Quotations
        $currentQuotations = Quotation::where('tenant_company_id', $tenantId)
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->count();

        $previousQuotations = Quotation::where('tenant_company_id', $tenantId)
            ->whereMonth('created_at', $previousMonthDate->month)
            ->whereYear('created_at', $previousMonthDate->year)
            ->count();

        $quotationsTrend = $this->calculateTrend($currentQuotations, $previousQuotations);

        // 6. Challans from Quotations
        $currentChallans = Challan::where('tenant_company_id', $tenantId)
            ->whereNotNull('quotation_id')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->count();

        $previousChallans = Challan::where('tenant_company_id', $tenantId)
            ->whereNotNull('quotation_id')
            ->whereMonth('created_at', $previousMonthDate->month)
            ->whereYear('created_at', $previousMonthDate->year)
            ->count();

        $challansTrend = $this->calculateTrend($currentChallans, $previousChallans);

        // 7. Bills from Quotations
        $currentBillsFromQuotations = Bill::where('tenant_company_id', $tenantId)
            ->whereNotNull('quotation_id')
            ->whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear)
            ->count();

        $previousBillsFromQuotations = Bill::where('tenant_company_id', $tenantId)
            ->whereNotNull('quotation_id')
            ->whereMonth('created_at', $previousMonthDate->month)
            ->whereYear('created_at', $previousMonthDate->year)
            ->count();

        $billsFromQuotationsTrend = $this->calculateTrend($currentBillsFromQuotations, $previousBillsFromQuotations);

        // 8. Revenue Overview Chart (Monthly for current year)
        $monthlyRevenue = [];
        $maxRevenue = 0;

        for ($m = 1; $m <= 12; $m++) {
            $revenue = BillPayment::join('bills', 'bills.id', '=', 'bill_payments.bill_id')
                ->where('bills.tenant_company_id', $tenantId)
                ->whereMonth('bill_payments.payment_date', $m)
                ->whereYear('bill_payments.payment_date', $currentYear)
                ->sum('bill_payments.amount');

            $monthlyRevenue[] = [
                'month' => Carbon::createFromDate($currentYear, $m, 1)->format('M'),
                'amount' => $revenue,
                'percentage' => 0, // Will calculate later
            ];

            if ($revenue > $maxRevenue) {
                $maxRevenue = $revenue;
            }
        }

        // Calculate percentage height relative to max month
        foreach ($monthlyRevenue as &$data) {
            if ($maxRevenue > 0) {
                $data['percentage'] = ($data['amount'] / $maxRevenue) * 100;
            }
        }

        // 9. Recent Quotations
        $recentQuotations = Quotation::with('revisions')
            ->where('tenant_company_id', $tenantId)
            ->latest()
            ->take(3)
            ->get();

        // 10. Cash Flow
        // Collected: (Total amount from bill_payments / Total bill_amount from bills) * 100
        // This is cumulative total, not just this month, based on wording.
        $totalReceived = BillPayment::join('bills', 'bills.id', '=', 'bill_payments.bill_id')
            ->where('bills.tenant_company_id', $tenantId)
            ->sum('bill_payments.amount');

        $totalBilled = Bill::where('tenant_company_id', $tenantId)->sum('bill_amount');

        $collectedPercentage = $totalBilled > 0 ? ($totalReceived / $totalBilled) * 100 : 0;

        // Target: Fetch from sale_targets for current month
        // Assuming 'month' column stores 'Y-m' format
        $targetMonth = $now->format('Y-m');
        $saleTarget = SaleTarget::where('tenant_company_id', $tenantId)
            ->where('month', $targetMonth)
            ->first();

        $targetPercentage = 0;
        if ($saleTarget && $saleTarget->target_amount > 0) {
            $targetPercentage = ($saleTarget->achieved_amount / $saleTarget->target_amount) * 100;
        }

        return view('tenant.dashboard', compact(
            'currentRevenue', 'revenueTrend',
            'currentInvoices', 'invoicesTrend',
            'currentPending', 'pendingTrend',
            'activeTeams',
            'currentQuotations', 'quotationsTrend',
            'currentChallans', 'challansTrend',
            'currentBillsFromQuotations', 'billsFromQuotationsTrend',
            'monthlyRevenue',
            'recentQuotations',
            'collectedPercentage', 'targetPercentage',
            'currentYear'
        ));
    }

    private function calculateTrend($current, $previous): array
    {
        if ($previous == 0) {
            return [
                'value' => $current > 0 ? 100 : 0,
                'direction' => $current > 0 ? 'up' : 'neutral',
            ];
        }

        $percentage = (($current - $previous) / $previous) * 100;

        return [
            'value' => abs(round($percentage, 1)),
            'direction' => $percentage > 0 ? 'up' : ($percentage < 0 ? 'down' : 'neutral'),
        ];
    }
}
