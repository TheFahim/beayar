<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckCompanyRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = Auth::user();

        if (! $user) {
            abort(401);
        }

        // Determine current company context
        // Priority: Session -> User's Default -> Abort
        $companyId = session('tenant_id') ?? $user->current_user_company_id;

        if (! $companyId) {
            abort(403, 'No company context selected.');
        }

        $userRole = $user->roleInCompany($companyId);

        if (! $userRole) {
            abort(403, 'You are not a member of this company.');
        }

        // Super Admin Bypass
        if ($userRole === 'company_admin') {
            return $next($request);
        }

        // Check specific role
        if ($userRole !== $role) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
