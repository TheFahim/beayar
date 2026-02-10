<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CompanyContextController extends Controller
{
    /**
     * Switch the current company context.
     */
    public function switch(Request $request, $companyId)
    {
        $user = Auth::user();

        // Check if user belongs to this company (either as owner or member)
        $isMember = $user->companies()->where('user_companies.id', $companyId)->exists();
        $isOwner = $user->ownedCompanies()->where('id', $companyId)->exists();

        if (! $isMember && ! $isOwner) {
            abort(403, 'You do not have access to this company.');
        }

        // Set the session
        Session::put('tenant_id', $companyId);

        // Update user's default company for next login
        $user->update(['current_user_company_id' => $companyId]);

        return redirect()->back()->with('success', 'Switched company context.');
    }
}
