<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->current_tenant_company_id) {
            // The BelongsToCompany trait will automatically pick this up from the user model
            // But we can also explicitly set a context if needed
        }

        return $next($request);
    }
}
