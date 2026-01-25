<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        if (Auth::getUser()->hasRole('admin')) {
            $expenses = Expense::with('user')->latest()->get();
        } else {
            $expenses = Expense::where('user_id', Auth::getUser()->id)->latest()->get();
        }
        return view('dashboard.expenses.index', compact('expenses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard.expenses.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date_format:d/m/Y',
            'expense' => 'required|array|min:1',
            'expense.*.type' => [
                'required'
            ],
            'expense.*.amount' => 'required|numeric|gt:0', // Must be > 0
            'expense.*.remarks' => 'nullable|string',
            'expense.*.voucher' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,bmp,svg,pdf,doc,docx|max:2048'
        ]);

        foreach ($request->expense as $data) {

            if ($data['voucher'] ?? false) {
                $data['voucher'] = $this->fileSave($data['voucher'], 'uploads/voucher', 'voucher');
            }

            Expense::create([
                'date' => Carbon::createFromFormat('d/m/Y', $validated['date'])->format('Y-m-d'),
                'type' => strtolower($data['type']),
                'amount' => $data['amount'],
                'remarks' => $data['remarks'],
                'voucher' => $data['voucher'] ?? '',
                'user_id' => Auth::getUser()->id
            ]);
        }

        return redirect()->back()->with('success', 'Expense Saved');
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Expense $expense)
    {
        return view('dashboard.expenses.edit', compact('expense'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expense $expense)
    {
        $validated = $request->validate([
            "date" => ['required', 'date_format:d/m/Y'],
            "type" => ['required'],
            'amount' => 'required|numeric|gt:0', // Must be > 0
            'remarks' => 'nullable|string',
            'voucher' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,bmp,svg,pdf,doc,docx|max:2048'
        ]);

        $validated['voucher'] = $this->fileUpdate($request->voucher, 'uploads/voucher', $expense->voucher, 'voucher');

        $validated['date'] = Carbon::createFromFormat('d/m/Y', $validated['date'])->format('Y-m-d');

        $expense->update($validated);

        return redirect()->route('expense.index')->with('success', 'Expense Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense)
    {
        if (File::exists($expense->voucher)) {
            File::delete($expense->voucher);
        }

        $expense->delete();

        return redirect()->route('expense.index')->with('success', 'Expense Deleted');
    }

    public function getChartData(Request $request)
    {
        // Default to 'last_month' if no period is provided
        $period = $request->input('period', 'last_month');

        $now = Carbon::now();
        $startDate = $now->copy()->startOfDay();
        $endDate = $now->copy()->endOfDay();

        switch ($period) {
            case 'today':
                // Start and end date are already set to today
                break;
            case 'last_7_days':
                $startDate = $now->copy()->subDays(6)->startOfDay();
                break;
            case 'this_month':
                $startDate = $now->copy()->startOfMonth();
                // $endDate is already set to today, which is correct for this period
                break;
            case 'last_month':
                $startDate = $now->copy()->subMonthNoOverflow()->startOfMonth();
                $endDate = $now->copy()->subMonthNoOverflow()->endOfMonth();
                break;
            case 'last_6_month':
                $startDate = $now->copy()->subMonths(6)->startOfDay();
                // End date remains today
                break;
            case 'this_year':
                $startDate = $now->copy()->startOfYear();
                // End date remains today
                break;
            case 'last_year': // <-- ADDED THIS MISSING CASE
                $startDate = $now->copy()->subYear()->startOfYear();
                $endDate = $now->copy()->subYear()->endOfYear();
                break;
            default:
                // Fallback to a sensible default, e.g., last 7 days
                $startDate = $now->copy()->subDays(6)->startOfDay();
                break;
        }

        // Query the data
        $expenseData = Expense::where('user_id', Auth::id())->whereBetween('date', [$startDate, $endDate])
            ->select('type', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('type')
            ->get();

        // Format for ApexCharts
        $labels = $expenseData->pluck('type')->map(function ($type) {
            return ucfirst(str_replace('_', ' ', $type)); // Format for display
        });

        $series = $expenseData->pluck('total_amount');
        $total = $series->sum();

        return response()->json([
            'series' => $series,
            'labels' => $labels,
            'total' => number_format($total, 2)
        ]);
    }

}
