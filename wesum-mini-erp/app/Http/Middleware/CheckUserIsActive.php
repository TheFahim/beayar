<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserIsActive
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

            // If user is inactive
            if (!$user->is_active) {
                // Log out the user
                Auth::logout();

                // Clear session data
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                // Redirect to login with error
                return redirect()->route('login')
                    ->withErrors(['username' => 'Your account is inactive. Please contact administrator.']);
            }
        }
        return $next($request);
    }
}
