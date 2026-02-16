<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    /**
     * Show the form for editing the tenant profile.
     */
    public function edit()
    {
        $user = Auth::user();
        $company = $user->currentCompany;

        if (! $company) {
            return redirect()->route('tenant.dashboard')->with('error', 'No active company found.');
        }

        // Authorization check: Only owner (tenant admin) can edit
        if (! $user->isOwnerOf($company->id)) {
            abort(403, 'Unauthorized action. Only the company owner can edit the profile.');
        }

        return view('tenant.profile.edit', compact('company'));
    }

    /**
     * Update the tenant profile.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $company = $user->currentCompany;

        if (! $company) {
            return redirect()->route('tenant.dashboard')->with('error', 'No active company found.');
        }

        if (! $user->isOwnerOf($company->id)) {
            abort(403, 'Unauthorized action. Only the company owner can edit the profile.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $company->update($validated);

        return redirect()->route('tenant.profile.edit')->with('success', 'Tenant profile updated successfully.');
    }
}
