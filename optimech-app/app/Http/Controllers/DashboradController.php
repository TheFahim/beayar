<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Challan;
use App\Models\Quotation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboradController extends Controller
{
    public function index()
    {
        // Get current month's bill summary data for the dashboard cards
        $bill = Bill::selectRaw('
            SUM(bills.due) as total_due,
            SUM(bills.total_amount) as total_bill,
            SUM(bills.total_amount - bills.due) as total_paid,
            CASE WHEN SUM(bills.total_amount) > 0 THEN ROUND((SUM(bills.total_amount - bills.due) / SUM(bills.total_amount) * 100), 1) ELSE 0 END as paid_percentage
        ')
            ->whereMonth('bills.created_at', Carbon::now()->month)
            ->whereYear('bills.created_at', Carbon::now()->year)
            ->get();

        // Ensure we have at least one record to avoid undefined index errors
        if ($bill->isEmpty()) {
            $bill = collect([(object) [
                'total_due' => 0,
                'total_bill' => 0,
                'total_paid' => 0,
                'paid_percentage' => 0,
            ]]);
        }

        // Get quotation statistics for the current month
        $quotationStats = $this->getCurrentMonthQuotationStats();

        // Get challan statistics for the current month
        $challanStats = $this->getCurrentMonthChallanStats();

        // Get comprehensive bill management statistics
        $billStats = $this->getComprehensiveBillStats();

        // Get conversion rate statistics
        $conversionRateStats = $this->getConversionRateStats();

        // Get historical trend data for charts
        $historicalTrends = $this->getHistoricalTrends();

        $financialSummary = $this->getFinancialSummary();

        return view('dashboard.index', compact('bill', 'quotationStats', 'challanStats', 'billStats', 'conversionRateStats', 'historicalTrends', 'financialSummary'));
    }

    public function getMyQuotationYears()
    {
        $years = Quotation::join('quotation_revisions', 'quotations.id', '=', 'quotation_revisions.quotation_id')
            ->where('quotation_revisions.created_by', Auth::id())
            ->select(DB::raw('YEAR(quotations.created_at) as year'))
            ->distinct()
            ->orderBy('year', 'desc') // Show most recent years first
            ->pluck('year');

        return response()->json($years);
    }

    /**
     * Get the authenticated user's monthly quotation summary for a given year.
     */
    public function getMyQuotationSummary(Request $request)
    {
        // Validate that the year is provided and is an integer
        $request->validate(['year' => 'required|integer']);
        $year = $request->input('year');

        // Fetch data from the database by joining with quotation_revisions
        $monthlyData = Quotation::join('quotation_revisions', 'quotations.id', '=', 'quotation_revisions.quotation_id')
            ->where('quotation_revisions.created_by', Auth::id())
            ->whereYear('quotations.created_at', $year)
            ->select(
                DB::raw('MONTH(quotations.created_at) as month'),
                DB::raw('SUM(quotation_revisions.total) as total_amount'),
                DB::raw('COUNT(DISTINCT quotations.id) as quotation_count')
            )
            ->groupBy('month')
            ->get()
            ->keyBy('month'); // Key the collection by month number for easy lookup

        // Create a full 12-month summary to ensure the chart axis is always complete
        $summary = [];
        for ($month = 1; $month <= 12; $month++) {
            if (isset($monthlyData[$month])) {
                $summary[] = [
                    'month' => $month,
                    'total_amount' => (float) $monthlyData[$month]->total_amount,
                    'quotation_count' => (int) $monthlyData[$month]->quotation_count,
                ];
            } else {
                // If no data for a month, add a zero-value entry
                $summary[] = [
                    'month' => $month,
                    'total_amount' => 0,
                    'quotation_count' => 0,
                ];
            }
        }

        return response()->json($summary);
    }

    /**
     * Get challan statistics for the current month
     */
    private function getCurrentMonthChallanStats()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Get current month challan statistics
        $monthlyStats = Challan::whereMonth('challans.created_at', $currentMonth)
            ->whereYear('challans.created_at', $currentYear)
            ->join('quotation_revisions', 'challans.quotation_revision_id', '=', 'quotation_revisions.id')
            ->select(
                DB::raw('COUNT(challans.id) as total_count'),
                DB::raw('COUNT(DISTINCT quotation_revisions.quotation_id) as quotation_count')
            )
            ->first();

        // Get overall statistics (all time)
        $overallStats = Challan::join('quotation_revisions', 'challans.quotation_revision_id', '=', 'quotation_revisions.id')
            ->select(
                DB::raw('COUNT(challans.id) as total_count'),
                DB::raw('COUNT(DISTINCT quotation_revisions.quotation_id) as quotation_count')
            )
            ->first();

        // Get challans by status (delivered vs pending based on delivery_date)
        $deliveredStats = Challan::whereNotNull('delivery_date')
            ->where('delivery_date', '<=', Carbon::now())
            ->select(DB::raw('COUNT(*) as delivered_count'))
            ->first();

        $pendingStats = Challan::where(function ($query) {
            $query->whereNull('delivery_date')
                ->orWhere('delivery_date', '>', Carbon::now());
        })
            ->select(DB::raw('COUNT(*) as pending_count'))
            ->first();

        return [
            'current_month' => [
                'count' => $monthlyStats->total_count ?? 0,
                'quotation_count' => $monthlyStats->quotation_count ?? 0,
            ],
            'overall' => [
                'count' => $overallStats->total_count ?? 0,
                'quotation_count' => $overallStats->quotation_count ?? 0,
            ],
            'delivered' => $deliveredStats->delivered_count ?? 0,
            'pending' => $pendingStats->pending_count ?? 0,
        ];
    }

    /**
     * Get comprehensive bill management statistics
     */
    private function getComprehensiveBillStats()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Get current month bill statistics
        $monthlyStats = Bill::whereMonth('bills.created_at', $currentMonth)
            ->whereYear('bills.created_at', $currentYear)
            ->select(
                DB::raw('COUNT(*) as total_count'),
                DB::raw('SUM(bill_amount) as total_amount'),
                DB::raw('SUM(due) as total_due'),
                DB::raw('SUM(total_amount - due) as total_paid')
            )
            ->first();

        // Get overall statistics (all time)
        $overallStats = Bill::select(
            DB::raw('COUNT(*) as total_count'),
            DB::raw('SUM(total_amount) as total_amount'),
            DB::raw('SUM(due) as total_due'),
            DB::raw('SUM(total_amount - due) as total_paid')
        )
            ->first();

        // Get overdue bills (due > 0 and created more than 30 days ago)
        $overdueStats = Bill::where('due', '>', 0)
            ->where('created_at', '<', Carbon::now()->subDays(30))
            ->select(
                DB::raw('COUNT(*) as overdue_count'),
                DB::raw('SUM(due) as overdue_amount')
            )
            ->first();

        // Get bills by type for current month
        // $billsByType = Bill::whereMonth('bills.created_at', $currentMonth)
        //     ->whereYear('bills.created_at', $currentYear)
        $billsByType = Bill::select('bill_type', DB::raw('COUNT(*) as count'), DB::raw('SUM(total_amount) as total_amount'))
            ->groupBy('bill_type')
            ->get()
            ->keyBy('bill_type');

        return [
            'current_month' => [
                'count' => $monthlyStats->total_count ?? 0,
                'total_amount' => $monthlyStats->total_amount ?? 0,
                'total_due' => $monthlyStats->total_due ?? 0,
                'total_paid' => $monthlyStats->total_paid ?? 0,
            ],
            'overall' => [
                'count' => $overallStats->total_count ?? 0,
                'total_amount' => $overallStats->total_amount ?? 0,
                'total_due' => $overallStats->total_due ?? 0,
                'total_paid' => $overallStats->total_paid ?? 0,
            ],
            'overdue' => [
                'count' => $overdueStats->overdue_count ?? 0,
                'amount' => $overdueStats->overdue_amount ?? 0,
            ],
            'by_type' => [
                'advance' => $billsByType->get('advance')?->count ?? 0,
                'regular' => $billsByType->get('regular')?->count ?? 0,
                'running' => $billsByType->get('running')?->count ?? 0,
            ],
        ];
    }

    /**
     * Financial summary metrics for dashboard cards, consistent with BillController index
     */
    public function getFinancialSummary()
    {
        try {
            $bills = Bill::with(['quotation'])->latest()->get();

            $latestByQuotation = Bill::select('id', 'quotation_id', 'bill_date')
                ->orderBy('quotation_id')
                ->orderBy('bill_date', 'desc')
                ->orderBy('id', 'desc')
                ->get()
                ->groupBy('quotation_id')
                ->map(function ($group) {
                    return optional($group->first())->id;
                });

            $totalAmountUniqueByQuotation = $bills->groupBy('quotation_id')->map(function ($group) use ($latestByQuotation) {
                $qid = optional($group->first())->quotation_id;
                $latestId = $qid !== null ? ($latestByQuotation[$qid] ?? null) : null;
                $latest = $latestId ? $group->firstWhere('id', $latestId) : $group->sortByDesc('bill_date')->sortByDesc('id')->first();

                return (float) ($latest->total_amount ?? 0);
            })->sum();

            $totalDueUniqueByQuotation = $bills->groupBy('quotation_id')->map(function ($group) use ($latestByQuotation) {
                $qid = optional($group->first())->quotation_id;
                $latestId = $qid !== null ? ($latestByQuotation[$qid] ?? null) : null;
                $latest = $latestId ? $group->firstWhere('id', $latestId) : $group->sortByDesc('bill_date')->sortByDesc('id')->first();

                return (float) ($latest->due ?? 0);
            })->sum();

            return [
                'total_bills' => (int) $bills->count(),
                'total_amount' => (float) $bills->sum('total_amount'),
                'total_paid' => (float) $bills->sum('bill_amount'),
                'total_due' => (float) $bills->sum('due'),
                'total_amount_unique_by_quotation' => (float) $totalAmountUniqueByQuotation,
                'total_due_unique_by_quotation' => (float) $totalDueUniqueByQuotation,
            ];
        } catch (\Throwable $e) {
            return [
                'total_bills' => 0,
                'total_amount' => 0.0,
                'total_paid' => 0.0,
                'total_due' => 0.0,
                'total_amount_unique_by_quotation' => 0.0,
                'total_due_unique_by_quotation' => 0.0,
            ];
        }
    }

    /**
     * API endpoint: return financial summary metrics as JSON
     */
    public function getFinancialSummaryApi()
    {
        return response()->json($this->getFinancialSummary());
    }

    /**
     * Get quotation statistics for the current month
     */
    private function getCurrentMonthQuotationStats()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        // Get total quotation count and value for current month
        $monthlyStats = Quotation::whereMonth('quotations.created_at', $currentMonth)
            ->whereYear('quotations.created_at', $currentYear)
            ->join('quotation_revisions', 'quotations.id', '=', 'quotation_revisions.quotation_id')
            ->where('quotation_revisions.is_active', true)
            ->select(
                DB::raw('COUNT(DISTINCT quotations.id) as total_count'),
                DB::raw('SUM(CASE WHEN quotation_revisions.type = \'via\' THEN quotation_revisions.total * quotation_revisions.exchange_rate ELSE quotation_revisions.total END) as total_value')
            )
            ->first();

        // Get overall statistics (all time)
        $overallStats = Quotation::join('quotation_revisions', 'quotations.id', '=', 'quotation_revisions.quotation_id')
            ->where('quotation_revisions.is_active', true)
            ->select(
                DB::raw('COUNT(DISTINCT quotations.id) as total_count'),
                DB::raw('SUM(CASE WHEN quotation_revisions.type = \'via\' THEN quotation_revisions.total * quotation_revisions.exchange_rate ELSE quotation_revisions.total END) as total_value')
            )
            ->first();

        return [
            'current_month' => [
                'count' => $monthlyStats->total_count ?? 0,
                'value' => $monthlyStats->total_value ?? 0,
            ],
            'overall' => [
                'count' => $overallStats->total_count ?? 0,
                'value' => $overallStats->total_value ?? 0,
            ],
        ];
    }

    /**
     * Get quotation to bill conversion rate statistics
     */
    private function getConversionRateStats()
    {
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        $userId = Auth::id();

        // Get current month quotation count
        $currentMonthQuotations = Quotation::whereMonth('quotations.created_at', $currentMonth)
            ->whereYear('quotations.created_at', $currentYear)
            ->join('quotation_revisions', 'quotations.id', '=', 'quotation_revisions.quotation_id')
            // ->where('quotation_revisions.created_by', $userId)
            ->select(DB::raw('COUNT(DISTINCT quotations.id) as count'))
            ->first();

        // Get current month bill count (Parent bills only, matching user's quotations)
        $currentMonthBills = Bill::whereMonth('bills.created_at', $currentMonth)
            ->whereYear('bills.created_at', $currentYear)
            ->whereNull('bills.parent_bill_id') // Exclude child bills
            ->join('quotations', 'bills.quotation_id', '=', 'quotations.id')
            ->join('quotation_revisions', 'quotations.id', '=', 'quotation_revisions.quotation_id')
            // ->where('quotation_revisions.created_by', $userId)
            ->select(DB::raw('COUNT(DISTINCT bills.id) as count'))
            ->first();

        // Get overall quotation count (all time)
        $overallQuotations = Quotation::join('quotation_revisions', 'quotations.id', '=', 'quotation_revisions.quotation_id')
            // ->where('quotation_revisions.created_by', $userId)
            ->select(DB::raw('COUNT(DISTINCT quotations.id) as count'))
            ->first();

        // Get overall bill count (all time, Parent bills only)
        $overallBills = Bill::whereNull('bills.parent_bill_id') // Exclude child bills
            ->join('quotations', 'bills.quotation_id', '=', 'quotations.id')
            ->join('quotation_revisions', 'quotations.id', '=', 'quotation_revisions.quotation_id')
            // ->where('quotation_revisions.created_by', $userId)
            ->select(DB::raw('COUNT(DISTINCT bills.id) as count'))
            ->first();

        // Calculate conversion rates
        $currentMonthQuotationsCount = $currentMonthQuotations->count ?? 0;
        $currentMonthBillsCount = $currentMonthBills->count ?? 0;
        $overallQuotationsCount = $overallQuotations->count ?? 0;
        $overallBillsCount = $overallBills->count ?? 0;

        return [
            'current_month' => [
                'quotation_count' => $currentMonthQuotationsCount,
                'bill_count' => $currentMonthBillsCount,
                'conversion_rate' => $currentMonthQuotationsCount > 0 ? round(($currentMonthBillsCount / $currentMonthQuotationsCount) * 100, 2) : 0,
            ],
            'overall' => [
                'quotation_count' => $overallQuotationsCount,
                'bill_count' => $overallBillsCount,
                'conversion_rate' => $overallQuotationsCount > 0 ? round(($overallBillsCount / $overallQuotationsCount) * 100, 2) : 0,
            ],
        ];
    }

    /**
     * Get historical trend data for charts
     */
    public function getHistoricalTrends()
    {
        // Get last 12 months of quotation data
        $quotationTrends = Quotation::where('quotations.created_at', '>=', Carbon::now()->subMonths(12))
            ->join('quotation_revisions', 'quotations.id', '=', 'quotation_revisions.quotation_id')
            ->where('quotation_revisions.created_by', Auth::id())
            ->select(
                DB::raw('DATE_FORMAT(quotations.created_at, "%Y-%m") as month'),
                DB::raw('COUNT(DISTINCT quotations.id) as count'),
                DB::raw('SUM(quotation_revisions.total) as total_value')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Get last 12 months of bill data
        $billTrends = Bill::where('created_at', '>=', Carbon::now()->subMonths(12))
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(total_amount) as total_amount'),
                DB::raw('SUM(due) as total_due')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return [
            'quotations' => $quotationTrends,
            'bills' => $billTrends,
        ];
    }

    /**
     * Format number in Bangla format
     */
    public function formatBanglaNumber($number)
    {
        return number_format($number, 0, '.', ',');
    }

    /**
     * Get user quotation statistics for admin dashboard.
     *
     * Returns quotation counts and total values per user, grouped by the specified filter period.
     * This endpoint is protected by admin middleware.
     *
     * @api {GET} /dashboard/api/user-quotation-stats
     *
     * @apiParam {string} filter - Filter period: 'this_month' | 'this_year' | 'all' (default: 'this_month')
     *
     * @apiSuccess {number} total_count - Total number of quotations across all users
     * @apiSuccess {number} total_value - Total value of all quotations
     * @apiSuccess {Array} users - Array of user stats with:
     *   - user_id: User ID
     *   - user_name: User display name
     *   - quotation_count: Number of quotations created
     *   - total_value: Sum of quotation values
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserQuotationStats(Request $request)
    {
        $filter = $request->input('filter', 'this_month');

        // Validate filter parameter
        if (! in_array($filter, ['this_month', 'this_year', 'all'])) {
            $filter = 'this_month';
        }

        $query = Quotation::query()
            ->join('quotation_revisions', 'quotations.id', '=', 'quotation_revisions.quotation_id')
            ->join('users', 'quotation_revisions.created_by', '=', 'users.id')
            ->where('quotation_revisions.is_active', true);

        // Apply date filter
        if ($filter === 'this_month') {
            $query->whereMonth('quotations.created_at', Carbon::now()->month)
                ->whereYear('quotations.created_at', Carbon::now()->year);
        } elseif ($filter === 'this_year') {
            $query->whereYear('quotations.created_at', Carbon::now()->year);
        }
        // 'all' requires no additional filtering

        $userStats = $query
            ->select(
                'users.id as user_id',
                'users.name as user_name',
                DB::raw('COUNT(DISTINCT quotations.id) as quotation_count'),
                DB::raw("SUM(CASE WHEN quotation_revisions.type = 'via' THEN quotation_revisions.total * quotation_revisions.exchange_rate ELSE quotation_revisions.total END) as total_value")
            )
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('quotation_count')
            ->get();

        $totalCount = $userStats->sum('quotation_count');
        $totalValue = $userStats->sum('total_value');

        return response()->json([
            'total_count' => (int) $totalCount,
            'total_value' => (float) $totalValue,
            'users' => $userStats->map(function ($stat) {
                return [
                    'user_id' => (int) $stat->user_id,
                    'user_name' => $stat->user_name,
                    'quotation_count' => (int) $stat->quotation_count,
                    'total_value' => (float) $stat->total_value,
                ];
            }),
        ]);
    }
}
