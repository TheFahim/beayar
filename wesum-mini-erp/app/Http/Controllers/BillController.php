<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Challan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        if (Auth::user()->hasRole('admin')) {
            $bills = Bill::with(['challan', 'challan.quotation', 'challan.quotation.customer'])
                ->latest()
                ->get();
        } else {
            $bills = Bill::with(['challan', 'challan.quotation', 'challan.quotation.customer'])
                ->whereHas('challan.quotation', function ($query) {
                    $query->where('user_id', Auth::id());
                })
                ->latest()
                ->get();
        }

        $bills->load('challan.quotation.products');


        return view('dashboard.bills.index', compact('bills'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $challan_id = request()->query('challan_id');

        $challan = Challan::find($challan_id);

        if (!$challan) {
            abort(404, 'Challan not found');
        }

        $challan->load('quotation', 'quotation.products');

        if (!Auth::user()->hasRole('admin') && $challan->quotation->user_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }

        return view('dashboard.bills.create', compact('challan'));
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bill_no' => 'required|string|max:255|unique:bills,bill_no',
            'challan_id' => 'required|exists:challans,id',
        ]);

        $total = Challan::find($validated['challan_id'])
            ->quotation->total;

        $subtotal = Challan::find($validated['challan_id'])
            ->quotation->subtotal;

        $vat = Challan::find($validated['challan_id'])
            ->quotation->vat;


        $validated['payable'] = $total;
        $validated['paid'] = 0;
        $validated['due'] = $total;
        $validated['att'] = $subtotal * 0.05;
        $validated['vat'] = $subtotal * $vat/100;
        $validated['delivery_cost'] = 0;
        $validated['buying_price'] = 0;
        $validated['profit'] = $total - $validated['att'] - $validated['vat'];
        $bill = Bill::create($validated);

        return redirect()->route('bills.show', $bill->id)->with('success', 'Bill created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Bill $bill)
    {
        $bill->load('challan.quotation');

        if (!Auth::user()->hasRole('admin') && $bill->challan->quotation->user_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }

        return view('dashboard.bills.show', compact('bill'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bill $bill)
    {
        $bill->load('challan', 'challan.quotation');
        if (!Auth::user()->hasRole('admin') && $bill->challan->quotation->user_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }
        return view('dashboard.bills.edit', compact('bill'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bill $bill)
    {
        $validated = $request->validate([
            'bill_no' => ['required', 'string', 'max:255',Rule::unique('bills', 'bill_no')->ignore($bill->id)],
        ]);

        $bill->load('challan.quotation');

        if (!Auth::user()->hasRole('admin') && $bill->challan->quotation->user_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }

        if (Auth::user()->hasRole('admin')) {
            $validated['buying_price'] = $request->buying_price;
            $validated['delivery_cost'] = $request->delivery_cost;
            $validated['profit'] = $bill->payable - $request->buying_price - $request->delivery_cost - $bill->vat - $bill->att;
        }

        $bill->update($validated);

        return redirect()->route('bills.index')->with('success', 'Bill No updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bill $bill)
    {
        if (!Auth::user()->hasRole('admin') && $bill->challan->quotation->user_id != Auth::id()) {
            abort(403, 'Unauthorized');
        }

        $bill->delete();

        return redirect()->route('bills.index')->with('success', 'Bill deleted successfully.');
    }

    public function search(Request $request)
    {

        $bills = Bill::select('id', 'bill_no')->limit(10)->get();

        return response()->json($bills);
    }

    public function getBillingData(Request $request)
    {


        $bill = Bill::select(
            DB::raw('SUM(payable) as total_bill'),
            DB::raw('SUM(paid) as total_paid'),
            DB::raw('SUM(due) as total_due')
        )
            ->where('due', '>', 0)->get();


    }

}
