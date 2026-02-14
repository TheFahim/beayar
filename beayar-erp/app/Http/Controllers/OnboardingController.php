<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Module;
use App\Models\Subscription;
use App\Models\TenantCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OnboardingController extends Controller
{
    /**
     * Show the plan selection page.
     */
    public function index()
    {
        // If user already has a subscription, redirect to company creation or dashboard
        if (Auth::user()->subscription) {
            return redirect()->route('onboarding.company');
        }

        return view('onboarding.plan_selection');
    }

    /**
     * Store the selected plan.
     */
    public function storePlan(Request $request)
    {
        $validated = $request->validate([
            'plan_type' => 'required|in:free,custom',
            // Custom plan fields
            'company_count' => 'required_if:plan_type,custom|integer|min:1',
            'separate_modules' => 'nullable|boolean',
            'modules' => 'nullable|array',
            'quotation_volume' => 'required_if:plan_type,custom|integer|min:0',
            'total_employees' => 'required_if:plan_type,custom|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();

            // Create Tenant if not exists
            if (! $user->tenant) {
                $tenant = \App\Models\Tenant::create([
                    'user_id' => $user->id,
                    'name' => $user->name."'s Account",
                ]);
            } else {
                $tenant = $user->tenant;
            }

            // Calculate limits based on input
            $limits = $this->calculateLimits($validated);
            $price = $this->calculatePrice($validated);

            // Create/Update Subscription
            $plan = Plan::where('slug', $validated['plan_type'])->first();

            if (! $plan) {
                // Fallback: Create a default free plan if missing (e.g. first run)
                $plan = Plan::firstOrCreate(
                    ['slug' => 'free'],
                    [
                        'name' => 'Free Plan',
                        'description' => 'Default free plan',
                        'base_price' => 0,
                        'billing_cycle' => 'monthly',
                        'is_active' => true,
                        'limits' => [
                            'company_limit' => 1,
                            'user_limit_per_company' => 2,
                            'quotation_limit_per_month' => 5,
                        ],
                        'module_access' => ['basic_crm', 'quotations', 'challans', 'billing', 'finance', 'products'],
                    ]
                );
            }

            $subscription = new Subscription;
            $subscription->tenant_id = $tenant->id;
            $subscription->user_id = $user->id;
            $subscription->plan_id = $plan->id;
            $subscription->plan_type = $validated['plan_type'];
            $subscription->status = 'active';
            $subscription->starts_at = now();
            $subscription->price = $price;

            // Set limits
            $subscription->company_limit = $limits['company_limit'];
            $subscription->user_limit_per_company = $limits['user_limit_per_company'];
            $subscription->quotation_limit_per_month = $limits['quotation_limit_per_month'];
            $subscription->module_access = $limits['module_access'];

            // Populate custom_limits JSON for backward compatibility / existing logic
            $subscription->custom_limits = [
                'companies' => $limits['company_limit'],
                'users_per_company' => $limits['user_limit_per_company'],
                'quotations_monthly' => $limits['quotation_limit_per_month'],
            ];

            $subscription->save();

            DB::commit();

            return redirect()->route('onboarding.company');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Plan selection failed: '.$e->getMessage());

            return back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Show the company creation page.
     */
    public function createCompany()
    {
        // Ensure user has a plan
        if (! Auth::user()->subscription) {
            return redirect()->route('onboarding.plan');
        }

        // If user already has a company, redirect to dashboard
        if (Auth::user()->ownedCompanies()->exists()) {
            return redirect()->route('tenant.dashboard');
        }

        return view('onboarding.create_company');
    }

    /**
     * Store the first company.
     */
    public function storeCompany(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $user = Auth::user();

            // Ensure Tenant exists (should be created in plan step, but double check)
            if (! $user->tenant) {
                $tenant = \App\Models\Tenant::create([
                    'user_id' => $user->id,
                    'name' => $user->name."'s Account",
                ]);
            } else {
                $tenant = $user->tenant;
            }

            // Create Company
            $company = TenantCompany::create([
                'tenant_id' => $tenant->id,
                'owner_id' => $user->id,
                'name' => $request->name,
                'address' => $request->address,
                'phone' => $request->phone,
                'organization_type' => TenantCompany::TYPE_INDEPENDENT,
                'status' => 'active',
            ]);

            // Add user as admin member
            $company->members()->attach($user->id, [
                'role' => 'company_admin',
                'is_active' => true,
            ]);

            // Assign Spatie Role
            if (! $user->hasRole('tenant_admin')) {
                $user->assignRole('tenant_admin');
            }

            // Set current context
            $user->current_tenant_company_id = $company->id;
            $user->save();

            // Set session
            session(['tenant_id' => $company->id]);

            DB::commit();

            return redirect()->route('tenant.dashboard');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Company creation failed: '.$e->getMessage());

            return back()->with('error', 'Failed to create company.');
        }
    }

    /**
     * Calculate subscription limits based on input.
     */
    private function calculateLimits(array $data): array
    {
        if ($data['plan_type'] === 'free') {
            // Fetch module access from the Plan model if available
            $plan = Plan::where('slug', 'free')->first();

            // Get all available modules from database
            $allModules = Module::pluck('slug')->toArray();

            // Default core modules
            $coreModules = ['basic_crm', 'quotations', 'challans', 'billing', 'finance', 'products'];

            // Combine all modules
            $modules = array_unique(array_merge($coreModules, $allModules));

            return [
                'company_limit' => 1,
                'user_limit_per_company' => 2,
                'quotation_limit_per_month' => 5,
                'module_access' => $modules,
            ];
        }

        // Custom Plan Logic
        // Simple mapping for demonstration

        // Distribute total employees across companies (average) or per company limit
        // Logic: "Total number of employees you need to manage across all companies?"
        // If 10 employees and 2 companies -> 5 per company? Or 10 global?
        // Let's assume the input is "User limit per company" for simplicity or calculated.
        // Prompt says: "Total number of employees... across all companies"

        $companyCount = (int) $data['company_count'];
        $totalEmployees = (int) $data['total_employees'];
        $userLimitPerCompany = ceil($totalEmployees / max($companyCount, 1));

        return [
            'company_limit' => $companyCount,
            'user_limit_per_company' => $userLimitPerCompany,
            'quotation_limit_per_month' => (int) $data['quotation_volume'],
            'module_access' => $data['modules'] ?? [],
        ];
    }

    /**
     * Calculate price based on input.
     */
    private function calculatePrice(array $data): float
    {
        if ($data['plan_type'] === 'free') {
            return 0.00;
        }

        // Dummy pricing logic
        $basePrice = 10;
        $companyPrice = $data['company_count'] * 10;
        $userPrice = $data['total_employees'] * 5;

        return $basePrice + $companyPrice + $userPrice;
    }
}
