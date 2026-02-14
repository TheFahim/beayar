<?php

namespace App\Http\Middleware;

use App\Models\TenantCompany;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOperationalCompany
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only enforce on write operations
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $tenantId = session('tenant_id');

            if ($tenantId) {
                $company = TenantCompany::find($tenantId);

                if ($company && $company->isHolding()) {
                    abort(403, 'Please switch to a subsidiary company to enter data.');
                }
            }
        }

        return $next($request);
    }
}
