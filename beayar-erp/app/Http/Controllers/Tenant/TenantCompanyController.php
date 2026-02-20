<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TenantCompanyController extends Controller
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
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $user = Auth::user();

        // Ensure user has a tenant record (should have from registration)
        $tenant = $user->tenant;
        if (! $tenant) {
            // Fallback: Create tenant if missing (legacy users)
            $tenant = Tenant::create([
                'user_id' => $user->id,
                'name' => $user->name."'s Account",
            ]);
        }

        // Check subscription limits (company limit)
        if ($tenant->subscription) {
            $limit = $tenant->subscription->getLimit('sub_companies');
            if ($limit !== -1 && $tenant->companies()->count() >= $limit) {
                return back()->withErrors(['limit' => 'You have reached your company limit. Upgrade your plan.']);
            }
        }

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('companies/logos', 'public');
        }

        $company = TenantCompany::create([
            'tenant_id' => $tenant->id,
            'owner_id' => $user->id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'logo' => $logoPath,
            'organization_type' => TenantCompany::TYPE_INDEPENDENT,
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
        $company = TenantCompany::findOrFail($id);
        $user = Auth::user();

        // Check if user is owner or admin of the company
        if ($company->owner_id !== $user->id) {
             // Or check if user is an admin member
             $member = $company->members()->where('user_id', $user->id)->first();
             if (!$member || $member->pivot->role !== 'company_admin') {
                 abort(403, 'Unauthorized action.');
             }
        }

        return view('tenant.companies.edit', compact('company'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $company = TenantCompany::findOrFail($id);
        $user = Auth::user();

        // Check permission
        if ($company->owner_id !== $user->id) {
             $member = $company->members()->where('user_id', $user->id)->first();
             if (!$member || $member->pivot->role !== 'company_admin') {
                 abort(403, 'Unauthorized action.');
             }
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ];

        if ($request->hasFile('logo')) {
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }
            $data['logo'] = $request->file('logo')->store('companies/logos', 'public');
        }

        $company->update($data);

        return redirect()->route('tenant.user-companies.index')->with('success', 'Company updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $company = TenantCompany::findOrFail($id);
        $user = Auth::user();

        // Only owner should be able to delete the company
        if ($company->owner_id !== $user->id) {
            abort(403, 'Unauthorized action. Only the owner can delete the company.');
        }

        $company->delete();

        // If the user was currently in this company, handle session/context
        if (session('tenant_id') == $id) {
            session()->forget('tenant_id');
            // Find another company
            $otherCompany = $user->companies()->where('tenant_companies.id', '!=', $id)->first();
            if ($otherCompany) {
                session(['tenant_id' => $otherCompany->id]);
                $user->update(['current_tenant_company_id' => $otherCompany->id]);
            } else {
                $user->update(['current_tenant_company_id' => null]);
            }
        }

        return redirect()->route('tenant.user-companies.index')->with('success', 'Company deleted successfully.');
    }
}
