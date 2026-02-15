<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('admin')->check()) {
            Log::info('AdminAuth middleware: User not authenticated, redirecting to admin login', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);
            return redirect()->route('admin.login');
        }

        $user = Auth::guard('admin')->user();
        if (! $user->isSuperAdmin()) {
            Log::warning('AdminAuth middleware: User unauthorized (not super admin)', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role ?? 'unknown',
            ]);
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
