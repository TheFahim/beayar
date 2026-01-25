<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companies = Company::latest()->get();
        // return view('dashboard.companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // return view('dashboard.companies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Transform company_code to uppercase and remove invalid characters
        $request->merge([
            'company_code' => strtoupper(preg_replace('/[^A-Za-z]/', '', $request->company_code)),
        ]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company_code' => 'required|string|max:10|regex:/^[A-Z]+$/|unique:companies,company_code',
            'address' => 'nullable|string|max:255',
        ]);

        $company = Company::create($validated);

        return response()->json([
            'success' => true,
            'company' => $company,
            'message' => 'Company created successfully.',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company)
    {
        $company->load('customers');
        // return view('dashboard.companies.show', compact('company'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company)
    {
        // return view('dashboard.companies.edit', compact('company'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Company $company)
    {
        // Transform company_code to uppercase and remove invalid characters
        $request->merge([
            'company_code' => strtoupper(preg_replace('/[^A-Za-z]/', '', $request->company_code)),
        ]);

        // For modal updates, we only validate name and address.
        if ($request->ajax() || $request->wantsJson()) {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'company_code' => 'required|string|max:10|regex:/^[A-Z]+$/|unique:companies,company_code,'.$company->id,
                'address' => 'nullable|string|max:255',
            ]);
        } else {
            // For a full form submission, validate everything.
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'company_code' => 'required|string|max:10|regex:/^[A-Z]+$/|unique:companies,company_code,'.$company->id,
                'company_no' => 'required|string|max:255|unique:companies,company_no,'.$company->id,
                'address' => 'nullable|string|max:255',
            ]);
        }

        $company->update($validated);

        // Handle AJAX/JSON requests (from modal)
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'company' => $company->fresh(), // Get fresh data from database
                'message' => 'Company updated successfully.',
            ]);
        }

        return redirect()->route('companies.index')->with('success', 'Company updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        $company->load('customers');

        if ($company->customers->count() > 0) {
            // Handle AJAX/JSON requests
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete company with existing customers.',
                ], 422);
            }

            return redirect()->route('companies.index')
                ->with('error', 'Cannot delete company with existing customers.');
        }

        $company->delete();

        // Handle AJAX/JSON requests
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Company deleted successfully.',
            ]);
        }

        return redirect()->route('companies.index')
            ->with('success', 'Company deleted successfully.');
    }

    /**
     * Search companies for API endpoint
     */
    public function search(Request $request)
    {
        $companies = Company::select('id', 'name', 'address', 'company_code')
            ->when($request->search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('company_code', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->get();

        return response()->json($companies);
    }

    /**
     * Get next customer serial number for a company
     */
    public function getNextCustomerSerial(Request $request, Company $company)
    {
        $lastCustomer = $company->customers()
            ->where('customer_no', 'like', $company->company_code.'-%')
            ->orderByRaw('CAST(SUBSTRING(customer_no, LENGTH(?) + 2) AS UNSIGNED) DESC', [$company->company_code])
            ->first();

        $nextSerial = 1;
        if ($lastCustomer) {
            $lastSerial = (int) substr($lastCustomer->customer_no, strlen($company->company_code) + 1);
            $nextSerial = $lastSerial + 1;
        }

        $customerNo = $company->company_code.'-'.str_pad($nextSerial, 2, '0', STR_PAD_LEFT);

        return response()->json([
            'customer_no' => $customerNo,
            'serial' => $nextSerial,
        ]);
    }
}
