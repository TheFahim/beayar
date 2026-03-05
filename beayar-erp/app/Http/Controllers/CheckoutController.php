<?php

namespace App\Http\Controllers;

use App\Models\Module;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class CheckoutController extends Controller
{
    /**
     * Show the checkout/registration form for a given plan.
     */
    public function show(string $planSlug)
    {
        $plan = Plan::where('slug', $planSlug)->where('is_active', true)->firstOrFail();

        return view('landing.checkout', compact('plan'));
    }

    /**
     * Process the mock checkout: create user, tenant, subscription, then redirect.
     */
    public function process(Request $request, string $planSlug)
    {
        $plan = Plan::where('slug', $planSlug)->where('is_active', true)->firstOrFail();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();

            // 1. Create the User
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            // 2. Create the Tenant
            $tenant = Tenant::create([
                'user_id' => $user->id,
                'name' => $user->name . "'s Account",
            ]);

            // 3. Build subscription data from plan
            $planLimits = $plan->limits ?? [];
            $limits = [
                'company_limit' => $planLimits['sub_companies'] ?? 1,
                'user_limit_per_company' => $planLimits['employees'] ?? 1,
                'quotation_limit_per_month' => $planLimits['quotations'] ?? 0,
            ];

            // Get module access
            if (!empty($plan->module_access)) {
                $moduleAccess = $plan->module_access;
            } else {
                $allModules = Module::pluck('slug')->toArray();
                $coreModules = ['basic_crm', 'quotations', 'challans', 'billing', 'finance', 'products'];
                $moduleAccess = array_unique(array_merge($coreModules, $allModules));
            }

            // 4. Create the Subscription (mock — no real payment)
            $subscription = new Subscription();
            $subscription->tenant_id = $tenant->id;
            $subscription->user_id = $user->id;
            $subscription->plan_id = $plan->id;
            $subscription->plan_type = $plan->slug;
            $subscription->status = 'active';
            $subscription->starts_at = now();
            $subscription->price = $plan->base_price;
            $subscription->module_access = $moduleAccess;
            $subscription->custom_limits = [
                'sub_companies' => $limits['company_limit'],
                'employees' => $limits['user_limit_per_company'],
                'quotations' => $limits['quotation_limit_per_month'],
            ];
            $subscription->save();

            // 5. Log the user in
            Auth::login($user);

            DB::commit();

            // 6. Redirect to company creation (onboarding step 2)
            return redirect()->route('onboarding.company');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout failed: ' . $e->getMessage());

            return back()->withInput()->with('error', 'Something went wrong. Please try again.');
        }
    }
}
