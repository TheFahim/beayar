<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
        }

        $customers = $query->latest()->paginate(10);

        return view('tenant.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('tenant.customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'tax_number' => 'nullable|string|max:50',
        ]);

        // BelongsToCompany trait should handle user_company_id automatically if using create() on model? 
        // No, usually it's a global scope or we manually set it. 
        // Looking at ProductController, it manually sets 'user_company_id'.
        
        $validated['user_company_id'] = auth()->user()->current_user_company_id;

        Customer::create($validated);

        return redirect()->route('tenant.customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function edit(Customer $customer)
    {
        if ($customer->user_company_id !== auth()->user()->current_user_company_id) {
            abort(403);
        }
        return view('tenant.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        if ($customer->user_company_id !== auth()->user()->current_user_company_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'tax_number' => 'nullable|string|max:50',
        ]);

        $customer->update($validated);

        return redirect()->route('tenant.customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->user_company_id !== auth()->user()->current_user_company_id) {
            abort(403);
        }

        $customer->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Customer deleted successfully']);
        }

        return redirect()->route('tenant.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }
}
