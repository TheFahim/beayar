<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AdminLoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AdminAuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth.login');
    }

    public function login(AdminLoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        Log::info('Admin login attempt', ['email' => $credentials['email']]);

        if (Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            Log::info('Admin login successful', ['email' => $credentials['email']]);

            return redirect()->intended(route('admin.dashboard'));
        }

        Log::warning('Admin login failed', ['email' => $credentials['email']]);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        $email = Auth::guard('admin')->user()->email ?? 'unknown';

        Auth::guard('admin')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        Log::info('Admin logout', ['email' => $email]);

        return redirect()->route('admin.login');
    }
}
