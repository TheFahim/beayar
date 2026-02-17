<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboardingComplete
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

            // Step 1: Check Subscription or Membership
            // If the user has a subscription (Owner) OR is a member of at least one company (Employee)
            if (! $user->subscription && ! $user->companies()->exists()) {
                if (! $request->routeIs('onboarding.plan') && ! $request->routeIs('onboarding.plan.store')) {
                    return redirect()->route('onboarding.plan');
                }
            }
            // Step 2: Check Company (only if subscription exists or skipped)
            // If user has subscription but no owned company AND no member company
            elseif ($user->subscription && ! $user->ownedCompanies()->exists() && ! $user->companies()->exists()) {
                // Check if user is already attempting to create company
                if (! $request->routeIs('onboarding.company') && ! $request->routeIs('onboarding.company.store')) {
                    return redirect()->route('onboarding.company');
                }
            }
        }

        return $next($request);
    }
}
