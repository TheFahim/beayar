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
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Role;

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

        if (Plan::count() === 0) {
            Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PlansSeeder']);
        }

        $plans = Plan::where('is_active', true)->get();

        return view('onboarding.plan_selection', compact('plans'));
    }

    /**
     * Store the selected plan.
     */
    public function storePlan(Request $request)
    {
        // Get all active plan slugs
        $planSlugs = Plan::where('is_active', true)->pluck('slug')->toArray();
        // Allow 'custom' and 'free' (for fallback creation)
        $allowedSlugs = implode(',', array_unique(array_merge($planSlugs, ['custom', 'free'])));

        $validated = $request->validate([
            'plan_type' => 'required|in:' . $allowedSlugs,
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

            $planType = $validated['plan_type'];

            if ($planType === 'custom') {
                 // Calculate limits based on input for Custom Plan
                $limits = $this->calculateLimits($validated);
                $price = $this->calculatePrice($validated);
                $plan = Plan::where('slug', 'custom')->first(); // Ensure custom plan exists in DB

                // If custom plan doesn't exist in DB, fallback or create it (optional)
                 if (!$plan) {
                     // Log warning or handle error
                 }

                 $moduleAccess = $limits['module_access'];

            } else {
                // Standard Plan
                $plan = Plan::where('slug', $planType)->first();

                if (! $plan && $planType === 'free') {
                    // Fallback: Create a default free plan if missing (e.g. first run)
                    $plan = Plan::create([
                        'slug' => 'free',
                        'name' => 'Free Plan',
                        'description' => 'Default free plan',
                        'base_price' => 0,
                        'billing_cycle' => 'monthly',
                        'is_active' => true,
                        'limits' => [
                            'sub_companies' => 1,
                            'employees' => 2,
                            'quotations' => 5,
                        ],
                        'module_access' => ['basic_crm', 'quotations', 'challans', 'billing', 'finance', 'products'],
                    ]);
                }

                if (! $plan) {
                    abort(404, 'Plan not found.');
                }

                $price = $plan->base_price;

                // Use plan limits
                // Map Plan limits structure to Subscription limits structure if needed
                // Plan limits: ['sub_companies' => X, 'employees' => Y, 'quotations' => Z]
                // Subscription/Controller limits keys: 'company_limit', 'user_limit_per_company', 'quotation_limit_per_month'

                $planLimits = $plan->limits ?? [];

                $limits = [
                    'company_limit' => $planLimits['sub_companies'] ?? 1,
                    'user_limit_per_company' => $planLimits['employees'] ?? 1, // Note: 'employees' might mean total users? Seeder says 'employees'
                    'quotation_limit_per_month' => $planLimits['quotations'] ?? 0,
                ];

                // For standard plans, assume they get access to all modules or defined modules
                // If Plan has module_access, use it. Otherwise default to all/core.
                if (!empty($plan->module_access)) {
                    $moduleAccess = $plan->module_access;
                } else {
                     // Fallback to core + all (like free plan logic was)
                     $allModules = Module::pluck('slug')->toArray();
                     $coreModules = ['basic_crm', 'quotations', 'challans', 'billing', 'finance', 'products'];
                     $moduleAccess = array_unique(array_merge($coreModules, $allModules));
                }
            }

            $subscription = new Subscription;
            $subscription->tenant_id = $tenant->id;
            $subscription->user_id = $user->id;
            $subscription->plan_id = $plan->id;
            $subscription->plan_type = $planType;
            $subscription->status = 'active';
            $subscription->starts_at = now();
            $subscription->price = $price;

            // Set limits
            $subscription->company_limit = $limits['company_limit'];
            $subscription->user_limit_per_company = $limits['user_limit_per_company'];
            $subscription->quotation_limit_per_month = $limits['quotation_limit_per_month'];
            $subscription->module_access = $moduleAccess;

            // Populate custom_limits JSON for backward compatibility / existing logic
            $subscription->custom_limits = [
                'sub_companies' => $limits['company_limit'], // Updated key to match CheckSubscriptionLimits usage?
                // Wait, CheckSubscriptionLimits checks keys like 'sub_companies' (from PlanSeeder) or 'companies' (old code)?
                // Let's stick to what Subscription model uses.
                // Subscription::getLimit checks custom_limits first, then plan limits.
                // PlanSeeder uses: sub_companies, quotations, employees.
                // So custom_limits should use these keys to override plan limits if needed.

                'sub_companies' => $limits['company_limit'],
                'employees' => $limits['user_limit_per_company'],
                'quotations' => $limits['quotation_limit_per_month'],
            ];

            $subscription->save();

            DB::commit();

            return redirect()->route('onboarding.company');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Plan selection failed: '.$e->getMessage());

            return back()->with('error', 'Something went wrong. Please try again: ' . $e->getMessage());
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

        $subscription = Auth::user()->subscription;
        return view('onboarding.create_company', compact('subscription'));
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

            // Set current context
            $user->current_tenant_company_id = $company->id;
            $user->save();

            // Set session
            session(['tenant_id' => $company->id]);

            // Set Spatie Team ID global context
            setPermissionsTeamId($company->id);

            // Assign Spatie Role
            $guardName = config('auth.defaults.guard');
            Role::findOrCreate('tenant_admin', $guardName);
            if (! $user->hasRole('tenant_admin')) {
                $user->assignRole('tenant_admin');
            }

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
