<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Challan;
use App\Models\Expense;
use App\Models\Quotation;
use App\Models\ReceivedBill;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboradController extends Controller
{
    public function index()
    {

        if (Auth::user()->hasRole('admin')) {
            return $this->adminDasboard();
        } else {

            $userId = Auth::id();

            $bill = Bill::select(
                DB::raw('SUM(payable) as total_bill'),
                DB::raw('SUM(paid) as total_paid'),
                DB::raw('SUM(due) as total_due'),
                //paid percentage
                DB::raw('SUM(paid) / SUM(payable) * 100 as paid_percentage'),
            )
                ->where('due', '>', 0)
                    ->whereHas('challan.quotation', function ($query) use ($userId) {
                        $query->where('user_id', $userId);
                    })
                ->get();

            return view('dashboard.index', compact('bill'));
        }

    }

    private function adminDasboard()
    {
        return view('dashboard.admin');
    }

    public function getProfitSummary(Request $request)
    {
        // Validate that start and end dates are present
        $request->validate([
            'start' => 'required|date_format:Y-m-d',
            'end' => 'required|date_format:Y-m-d',
        ]);

        $startDate = Carbon::parse($request->start)->startOfDay();
        $endDate = Carbon::parse($request->end)->endOfDay();

        // 1. Get ReceivedBills for fully paid bills within the date range
        $receivedBills = ReceivedBill::whereBetween('received_date', [$startDate, $endDate])
            ->whereHas('bill', function ($query) {
                $query->where('paid', '<>', 0);
            })
            ->with('bill') // Eager load the bill to get buying_price efficiently
            ->get();

        // return $receivedBills;

        // 2. Calculate total received amount from these bills
        $totalReceived = $receivedBills->sum('amount');

        // 3. Calculate total purchase price from the related bills
        $totalPurchasePrice = $receivedBills->sum('bill.buying_price') ?? 0;

        $totalVat = $receivedBills->sum('bill.vat');
        $totalAtt = $receivedBills->sum('bill.att');
        $totalDelivery = $receivedBills->sum('bill.delivery_cost') ?? 0;

        // 4. Calculate total expense within the same date range
        $totalExpense = Expense::whereBetween('date', [$startDate, $endDate])->sum('amount');

        // 5. Calculate the final profit
        // $profit = $totalReceived - $totalExpense - $totalVat -$totalAtt - $totalDelivery - $totalPurchasePrice;
        $profit = $totalReceived - $totalVat -$totalAtt - $totalDelivery ;

        // Return the results as JSON
        return response()->json([
            'totalReceived' => $this->formatBanglaNumber($totalReceived),
            'totalExpense' => $this->formatBanglaNumber($totalExpense),
            'totalPurchasePrice' => $this->formatBanglaNumber($totalPurchasePrice),
            'totalVat' => $this->formatBanglaNumber($totalVat),
            'totalAtt' => $this->formatBanglaNumber($totalAtt),
            'totalDelivery' => $this->formatBanglaNumber($totalDelivery),
            'profit' => $this->formatBanglaNumber($profit),
        ]);
    }

    public function getTopSummary(Request $request)
    {
        // Set the default date range to the last 3 months if not provided
        $startDate = $request->start ? Carbon::parse($request->start)->startOfDay() : Carbon::now()->subMonths(2)->startOfMonth();
        $endDate = $request->end ? Carbon::parse($request->end)->endOfDay() : Carbon::now()->endOfMonth();

        $sell = Bill::whereBetween('created_at', [$startDate, $endDate])->sum('payable');

        $received = Bill::whereBetween('created_at', [$startDate, $endDate])->sum('paid');

        $due = Bill::whereBetween('created_at', [$startDate, $endDate])->sum('due');

        $buyingPrice = Bill::whereBetween('created_at', [$startDate, $endDate])->sum('buying_price');

        // Count all the products with no buying price within the date range
        $productsWithoutBuyingPrice = Bill::where('buying_price', '0')->count();


        $totalExpense = Expense::whereBetween('created_at', [$startDate, $endDate])->sum('amount');



        return response()->json([
            'sellRevenue' => $this->formatBanglaNumber($sell),
            'buyingPriceWarning' => $productsWithoutBuyingPrice,
            'buyingPrice' => $this->formatBanglaNumber($buyingPrice),
            'collectableBill' => $this->formatBanglaNumber($sell),
            'totalPaid' => $this->formatBanglaNumber($received),
            'totalDue' => $this->formatBanglaNumber($due),
            'totalExpense' => $this->formatBanglaNumber($totalExpense),
        ]);
    }

    public function getExpenseData(Request $request)
    {
        $filter = $request->input('filter', 'this_month');

        // Start the query on the Expense model
        $query = Expense::query();

        // Apply the date filter based on the 'filter' parameter
        switch ($filter) {
            case 'last_month':
                $query->whereBetween('expenses.date', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                break;
            case 'this_year':
                $query->whereYear('expenses.date', Carbon::now()->year);
                break;
            case 'last_year':
                $query->whereYear('expenses.date', Carbon::now()->subYear()->year);
                break;
            case 'last_90_days':
                $query->where('expenses.date', '>=', Carbon::now()->subDays(90)->startOfDay());
                break;
            case 'last_6_months':
                $query->where('expenses.date', '>=', Carbon::now()->subMonths(6)->startOfDay());
                break;
            case 'this_month':
            default:
                $query->whereBetween('expenses.date', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                break;
        }

        // Clone the query to get the total expense for the period before grouping
        $totalExpenseQuery = clone $query;
        $totalExpense = $totalExpenseQuery->sum('amount');

        // Get the detailed breakdown of expenses per user and type
        $expenseDetails = $query->join('users', 'expenses.user_id', '=', 'users.id')
            ->select(
                'users.name as user_name',
                'expenses.type',
                DB::raw('SUM(expenses.amount) as amount_per_type')
            )
            ->groupBy('users.id', 'users.name', 'expenses.type')
            ->get();

        // Process the flat data into the desired nested structure
        $usersData = [];
        foreach ($expenseDetails as $detail) {
            // If the user is not yet in our results array, initialize them
            if (!isset($usersData[$detail->user_name])) {
                $usersData[$detail->user_name] = [
                    'user_name' => $detail->user_name,
                    'total_expense' => 0,
                    'type' => new \stdClass(), // Use stdClass for an empty JSON object {}
                ];
            }

            // Add the amount for the current type to the 'type' object
            $usersData[$detail->user_name]['type']->{$detail->type} = (float)$detail->amount_per_type;

            // Add to the user's total_expense
            $usersData[$detail->user_name]['total_expense'] += (float)$detail->amount_per_type;
        }

        // Convert the associative array to a simple indexed array for the JSON response
        $expenses = array_values($usersData);

        // Optional: Sort the final array by total_expense in descending order
        usort($expenses, function ($a, $b) {
            return $b['total_expense'] <=> $a['total_expense'];
        });

        return response()->json([
            'total_expense' => $totalExpense,
            'expenses' => $expenses,
        ]);
    }

    public function getDueData(Request $request)
    {
        // Updated the default filter to 'this_month'
        $filter = $request->input('filter', 'this_month');

        $query = Bill::query();

        // --- UPDATED SWITCH STATEMENT ---
        switch ($filter) {
            case 'this_month':
                // Where created_at is within the current month
                $query->whereBetween('bills.created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                break;

            case 'last_month':
                // Where created_at is within the previous month
                $query->whereBetween('bills.created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                break;

            case 'this_year':
                // Where the year of created_at is the current year
                $query->whereYear('bills.created_at', Carbon::now()->year);
                break;

            case 'last_year':
                // Where the year of created_at is the previous year
                $query->whereYear('bills.created_at', Carbon::now()->subYear()->year);
                break;

            case 'last_90_days':
                $query->where('bills.created_at', '>=', Carbon::now()->subDays(90)->startOfDay());
                break;

            case 'last_6_months':
                $query->where('bills.created_at', '>=', Carbon::now()->subMonths(6)->startOfDay());
                break;

            default:
                // Fallback to 'this_month' if an unknown filter is passed
                $query->whereBetween('bills.created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                break;
        }
        // --- END OF UPDATED SWITCH STATEMENT ---


        $totalDue = (clone $query)->sum('due');

        $usersDue = $query
            ->join('challans', 'bills.challan_id', '=', 'challans.id')
            ->join('quotations', 'challans.quotation_id', '=', 'quotations.id')
            ->join('users', 'quotations.user_id', '=', 'users.id')
            ->select(
                'users.name as user_name',
                DB::raw('SUM(bills.due) as total_due'),
                DB::raw('SUM(bills.payable) as total_payable')
            )
            ->groupBy('users.id', 'users.name')
            ->havingRaw('SUM(bills.payable) > 0')
            ->orderBy('total_due', 'desc')
            ->get()
            ->map(function ($user) {
                $paidAmount = $user->total_payable - $user->total_due;
                $percentage = ($user->total_payable > 0) ? floor((($paidAmount / $user->total_payable) * 100) * 10) / 10 : 0;

                return [
                    'name' => $user->user_name,
                    'due' => $user->total_due,
                    'percentage' => $percentage,
                ];
            });

        return response()->json([
            'total_due' => $totalDue,
            'users' => $usersDue,
        ]);
    }

    // app/Http/Controllers/DashboardController.php

    public function getTargetData(Request $request)
    {
        $filter = $request->input('filter', 'this_year');
        $year = Carbon::now()->year;

        switch ($filter) {
            case 'last_year':
                $year = Carbon::now()->subYear()->year;
                break;
            case 'this_year':
            default:
                $year = Carbon::now()->year;
                break;
        }

        $achievedSales = Challan::join('quotations', 'challans.quotation_id', '=', 'quotations.id')
            ->whereYear('challans.created_at', $year)
            ->groupBy('quotations.user_id')
            ->select(
                'quotations.user_id',
                DB::raw('SUM(quotations.total) as achieved_amount')
            )->pluck('achieved_amount', 'user_id');

        $usersWithTargets = User::with(['saleTarget' => function ($query) use ($year) {
            $query->where('year', $year);
        }])
            ->whereHas('saleTarget', function ($query) use ($year) {
                $query->where('year', 'like', $year);
            })
            ->get();

        // 3. Combine the data for the chart
        $chartData = $usersWithTargets->map(function ($user) use ($achievedSales) {
            // --- FIX: Access the first item in the collection before getting the property ---
            $target = $user->saleTarget->first()?->target_amount ?? 0;
            $achieved = $achievedSales[$user->id] ?? 0;

            return [
                'x' => $user->name,
                'y' => $achieved,
                'goals' => [
                    [
                        'name' => 'Target',
                        'value' => $target,
                        'strokeWidth' => 5,
                        'strokeHeight' => 10,
                        'strokeColor' => '#775DD0'
                    ]
                ]
            ];
        });

        // 4. Calculate overall summary stats
        // --- FIX: Apply the same logic here for the total calculation ---
        $totalTarget = $usersWithTargets->sum(fn ($user) => $user->saleTarget->first()?->target_amount ?? 0);
        $totalAchieved = $achievedSales->sum();

        return response()->json([
            'year' => $year,
            'stats' => [
                'target' => $totalTarget,
                'achieved' => $totalAchieved,
                'remaining' => $totalTarget - $totalAchieved,
            ],
            'chartData' => $chartData,
        ]);
    }

    public function getQuotationData(Request $request)
    {
        // Start with the base query and eager load the user relationship
        // Only select the columns you need from the user table for efficiency
        $query = Quotation::query()->with('user:id,name');

        // Apply filters based on the request parameter
        switch ($request->input('filter', 'this_month')) {
            case 'this_month':
                $query->whereMonth('created_at', Carbon::now()->month)
                      ->whereYear('created_at', Carbon::now()->year);
                break;

            case 'this_year':
                $query->whereYear('created_at', Carbon::now()->year);
                break;

            case 'last_year':
                $query->whereYear('created_at', Carbon::now()->subYear()->year);
                break;

            case 'all':
                // No additional date constraints for 'all'
                break;
        }

        // Execute the query and select only necessary fields
        $quotations = $query->select('id', 'user_id', 'quotation_no', 'total', 'created_at')->get();

        // Return the data as a JSON response
        return response()->json($quotations);
    }

    public function getMyQuotationYears()
    {
        $years = Quotation::where('user_id', Auth::id())
            ->select(DB::raw('YEAR(created_at) as year'))
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

        // Fetch data from the database
        $monthlyData = Quotation::where('user_id', Auth::id())
            ->whereYear('created_at', $year)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total) as total_amount'),
                DB::raw('COUNT(id) as quotation_count')
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

}
