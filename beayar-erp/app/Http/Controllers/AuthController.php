<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->hasRole('super_admin')) {
                return redirect()->route('admin.dashboard');
            }

            return redirect()->route('tenant.dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'company_name' => ['required', 'string', 'max:255'],
        ]);

        return \Illuminate\Support\Facades\DB::transaction(function () use ($validated) {
            // Step 1: Create User
            $user = \App\Models\User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
            ]);

            // Step 2: Create Tenant
            $tenant = \App\Models\Tenant::create([
                'user_id' => $user->id,
                'name' => $validated['company_name'] . ' Account',
            ]);

            // Step 2b: Create Subscription (Default 'Free')
            $plan = \App\Models\Plan::where('slug', 'free')->first() ?? \App\Models\Plan::first();
            
            \App\Models\Subscription::create([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id, // Optional legacy
                'plan_id' => $plan ? $plan->id : 1,
                'status' => 'active',
                'starts_at' => now(),
                'plan_type' => 'free',
            ]);
            
            // Step 3: Create Company
            $company = \App\Models\UserCompany::create([
                'tenant_id' => $tenant->id,
                'owner_id' => $user->id,
                'name' => $validated['company_name'],
                'organization_type' => \App\Models\UserCompany::TYPE_INDEPENDENT,
                'status' => 'active',
            ]);

            // Step 4: Attach User to Company as Super Admin
            $company->members()->attach($user->id, [
                'role' => 'company_admin',
                'is_active' => true,
            ]);

            // Set context
            $user->update(['current_user_company_id' => $company->id]);

            Auth::login($user);

            return redirect()->route('tenant.dashboard');
        });
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
                    ? back()->with(['status' => __($status)])
                    : back()->withErrors(['email' => __($status)]);
    }

    public function showResetPassword(Request $request)
    {
        return view('auth.reset-password', ['request' => $request, 'token' => $request->route('token')]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => \Illuminate\Support\Facades\Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $status === Password::PASSWORD_RESET
                    ? redirect()->route('login')->with('status', __($status))
                    : back()->withErrors(['email' => __($status)]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
