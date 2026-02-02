<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::with(['customerCompany'])->withCount('quotations');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('customer_no', 'like', "%{$search}%");
        }

        $customers = $query->latest()->paginate(10);

        return view('tenant.customers.index', compact('customers'));
    }

    public function create()
    {
        // Generate a customer number
        $count = Customer::where('user_company_id', auth()->user()->current_user_company_id)->count();
        $customerNo = '';

        return view('tenant.customers.create', compact('customerNo'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_company_id' => 'required|exists:customer_companies,id',
            'customer_name' => 'required|string|max:255', // Maps to 'name'
            'customer_no' => 'required|string|max:255', // We'll verify uniqueness manually or rely on generated
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'attention' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
        ]);

        // Map customer_name to name
        $data = [
            'user_company_id' => auth()->user()->current_user_company_id,
            'customer_company_id' => $validated['customer_company_id'],
            'name' => $validated['customer_name'],
            'customer_no' => $validated['customer_no'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'attention' => $validated['attention'],
            'designation' => $validated['designation'],
            'department' => $validated['department'],
        ];

        // Ensure uniqueness of customer_no within tenant? Or globally?
        // Optimech has global unique.
        if (Customer::where('customer_no', $data['customer_no'])->exists()) {
             // Regenerate or fail?
             // Let's just append random string if exists
             $data['customer_no'] .= '-' . Str::random(4);
        }

        Customer::create($data);

        return redirect()->route('tenant.customers.index')
            ->with('success', 'Customer created successfully.');
    }

    public function edit(Customer $customer)
    {
        if ($customer->user_company_id !== auth()->user()->current_user_company_id) {
            abort(403);
        }

        $customer->load('quotations');
        $hasQuotation = $customer->quotations()->count() > 0;

        return view('tenant.customers.edit', compact('customer', 'hasQuotation'));
    }

    public function update(Request $request, Customer $customer)
    {
        // return $request;
        if ($customer->user_company_id !== auth()->user()->current_user_company_id) {
            abort(403);
        }

        $validated = $request->validate([
            'customer_company_id' => 'required|exists:customer_companies,id',
            'customer_name' => 'required|string|max:255',
            'customer_no' => 'required|string|max:255', // Should exclude current
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'attention' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
        ]);

         $data = [
            'customer_company_id' => $validated['customer_company_id'],
            'name' => $validated['customer_name'],
            'customer_no' => $validated['customer_no'], // Usually don't update customer_no
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'address' => $validated['address'],
            'attention' => $validated['attention'],
            'designation' => $validated['designation'],
            'department' => $validated['department'],
        ];

        $customer->update($data);

        return redirect()->route('tenant.customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->user_company_id !== auth()->user()->current_user_company_id) {
            abort(403);
        }

        if (!$customer->is_deletable) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete customer because it has existing quotations.',
                ], 422);
            }
            return back()->with('error', 'Cannot delete customer because it has existing quotations.');
        }

        $customer->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Customer deleted successfully']);
        }

        return redirect()->route('tenant.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    // API for searching customer companies (to replace Optimech's companies.search)
    public function searchCompanies(Request $request)
    {
        $user = auth()->user();
        // Fallback to first owned company if current_user_company_id is not set
        $companyId = $user->current_user_company_id ?? $user->ownedCompanies()->first()?->id;

        if (!$companyId) {
            return response()->json([]);
        }

        // Use withoutGlobalScope to ensure we query based on the resolved $companyId
        // This handles cases where the global scope might fail if current_user_company_id is null
        $query = CustomerCompany::withoutGlobalScope('user_company_id')
            ->where('user_company_id', $companyId);

        if ($request->has('query') && $request->get('query')) {
            $search = $request->get('query');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company_code', 'like', "%{$search}%");
            });
        }

        $companies = $query->orderBy('name')->get(['id', 'name', 'company_code', 'address']);

        return response()->json($companies);
    }

    // API for searching customers
    public function searchCustomers(Request $request)
    {
        $query = Customer::where('user_company_id', auth()->user()->current_user_company_id)
            ->with('customerCompany:id,name');

        if ($request->has('query') && $request->get('query')) {
            $search = $request->get('query');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('customer_no', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('customerCompany', function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $customers = $query->orderBy('name')->limit(20)->get();

        return response()->json($customers);
    }
}
