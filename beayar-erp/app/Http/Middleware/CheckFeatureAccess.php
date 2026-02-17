<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureAccess
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasFeatureAccess($feature)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your plan does not include access to this feature.',
                    'feature' => $feature,
                ], 403);
            }

            return redirect()->back()->with(
                'error',
                'Your current plan does not include this feature. Please upgrade.'
            );
        }

        return $next($request);
    }
}
