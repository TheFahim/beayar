<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // App/Http/Controllers/CustomerController.php

    public function index()
    {
        $customers = Customer::with('company')
            ->withCount('quotations')
            ->latest()
            ->get();

        return view('dashboard.customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboard.customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'attention' => 'nullable|string|max:255',
            'customer_no' => 'required|string|max:255|unique:customers,customer_no',
            'customer_name' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
        ]);

        $customer = Customer::create($validated);

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        $customer->load(['quotations']);

        $hasQuotation = count($customer->quotations) > 0 ? true : false;

        return view('dashboard.customers.edit', compact('customer', 'hasQuotation'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'attention' => 'nullable|string|max:255',
            'customer_no' => 'required|string|max:255|unique:customers,customer_no,'.$customer->id,
            'customer_name' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {

        $customer->load(['quotations']);
        $hasQuotation = count($customer->quotations) > 0 ? true : false;

        if ($hasQuotation) {
            abort(403);
        }

        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer Deleted successfully.');

    }
}
