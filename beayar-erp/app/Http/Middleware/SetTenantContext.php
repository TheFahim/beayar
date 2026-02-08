<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetTenantContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            if (!Session::has('tenant_id')) {
                // Try to set the first company available
                $company = $user->companies->first() ?? $user->ownedCompanies->first();

                if ($company) {
                    Session::put('tenant_id', $company->id);
                }
            } else {
                // Verify access
                $tenantId = Session::get('tenant_id');
                // Check if user is owner OR member
                $isMember = $user->companies()->where('user_companies.id', $tenantId)->exists();
                $isOwner = $user->ownedCompanies()->where('id', $tenantId)->exists();

                if (!$isMember && !$isOwner) {
                    Session::forget('tenant_id');
                    return redirect()->route('dashboard')->with('error', 'You do not have access to this company context.');
                }
            }
        }

        return $next($request);
    }
}
