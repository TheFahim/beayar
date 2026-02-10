<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\UserCompany;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class UserCompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        // Show companies where user is owner or member
        $companies = $user->companies()->get();

        return view('tenant.companies.index', compact('companies'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('tenant.companies.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
        ]);

        $user = Auth::user();

        // Ensure user has a tenant record (should have from registration)
        $tenant = $user->tenant;
        if (!$tenant) {
            // Fallback: Create tenant if missing (legacy users)
            $tenant = Tenant::create([
                'user_id' => $user->id,
                'name' => $user->name . "'s Account",
            ]);
        }

        // Check subscription limits (company limit)
        // $limit = $tenant->subscription->plan->company_limit ?? 1;
        // if ($tenant->companies()->count() >= $limit) {
        //     return back()->withErrors(['limit' => 'You have reached your company limit. Upgrade your plan.']);
        // }

        $company = UserCompany::create([
            'tenant_id' => $tenant->id,
            'owner_id' => $user->id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'organization_type' => UserCompany::TYPE_INDEPENDENT,
            'status' => 'active',
        ]);

        // Attach user as Admin
        $company->members()->attach($user->id, [
            'role' => 'company_admin',
            'is_active' => true,
        ]);

        return redirect()->route('tenant.user-companies.index')->with('success', 'Company created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
