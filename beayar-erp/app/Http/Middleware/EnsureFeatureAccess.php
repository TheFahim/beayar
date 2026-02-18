<?php

namespace App\Http\Middleware;

use App\Enums\FeatureEnum;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeatureAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();

        if (!$user || !$user->currentTenant) {
            // If no user or no tenant context, deny access or redirect
            return redirect()->route('login')->with('error', 'Authentication required.');
        }

        if (!$user->currentTenant->hasFeature($feature)) {
            // You might want to redirect to a specific 'upgrade' page
            // or return a 403 Forbidden response.
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Feature not included in your plan.'], 403);
            }

            return redirect()->back()->with('error', 'Feature not included in your plan. Please upgrade.');
        }

        return $next($request);
    }
}
