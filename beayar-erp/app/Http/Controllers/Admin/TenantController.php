<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSubscriptionRequest;
use App\Models\Module;
use App\Models\Plan;
use App\Models\TenantCompany;
use App\Models\User;
use App\Services\SuperAdmin\AdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function __construct(public AdminService $adminService) {}

    public function index(): View
    {
        $tenants = TenantCompany::with(['owner.subscription.plan'])->latest()->paginate(10);

        return view('admin.tenants.index', compact('tenants'));
    }
    
    public function create(): View
    {
        $plans = Plan::where('is_active', true)->get();
        return view('admin.tenants.create', compact('plans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'company_name' => 'required|string|max:255',
            'plan_id' => 'required|exists:plans,id',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            $tenant = \App\Models\Tenant::create([
                'user_id' => $user->id,
                'name' => $validated['company_name']
            ]);

            $company = TenantCompany::create([
                'tenant_id' => $tenant->id,
                'owner_id' => $user->id,
                'name' => $validated['company_name'],
                'status' => 'active',
                'organization_type' => 'independent',
                'address' => 'N/A', 
            ]);

            $plan = Plan::find($validated['plan_id']);
            
            \App\Models\Subscription::create([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'plan_type' => $plan->slug,
                'status' => 'active',
                'starts_at' => now(),
                'price' => $plan->base_price,
                'company_limit' => $plan->limits['company_limit'] ?? 1,
                'user_limit_per_company' => $plan->limits['user_limit_per_company'] ?? 1,
                'quotation_limit_per_month' => $plan->limits['quotation_limit_per_month'] ?? 10,
                'module_access' => $plan->module_access,
            ]);
            
            activity()
               ->performedOn($company)
               ->causedBy(auth()->guard('admin')->user())
               ->log('created tenant');
        });

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant created successfully.');
    }

    public function show(TenantCompany $company): View
    {
        $company->load(['owner.subscription.plan', 'owner.subscription.usages', 'tenant', 'members']);

        $subscription = $company->owner?->subscription;
        $plans = Plan::where('is_active', true)->get();
        $modules = Module::orderBy('name')->get();

        return view('admin.tenants.show', compact('company', 'subscription', 'plans', 'modules'));
    }

    public function updateSubscription(UpdateSubscriptionRequest $request, TenantCompany $company): RedirectResponse
    {
        $subscription = $company->owner?->subscription;

        if (! $subscription) {
            return back()->with('error', 'This tenant has no active subscription.');
        }

        $validated = $request->validated();
        $updateData = [];

        if (isset($validated['plan_id'])) {
            $updateData['plan_id'] = $validated['plan_id'];
        }

        if (isset($validated['status'])) {
            $updateData['status'] = $validated['status'];
        }

        if (array_key_exists('custom_limits', $validated)) {
            $updateData['custom_limits'] = $validated['custom_limits'];
        }

        if (array_key_exists('module_access', $validated)) {
            $updateData['module_access'] = $validated['module_access'];
        }

        $subscription->update($updateData);
        
        activity()
           ->performedOn($subscription)
           ->causedBy(auth()->guard('admin')->user())
           ->withProperties($validated)
           ->log('updated subscription');

        return back()->with('success', 'Subscription updated successfully.');
    }

    public function suspend(Request $request, TenantCompany $company): RedirectResponse
    {
        $newStatus = $request->input('status', 'suspended');
        $company->update(['status' => $newStatus]);
        
        activity()
           ->performedOn($company)
           ->causedBy(auth()->guard('admin')->user())
           ->withProperties(['status' => $newStatus])
           ->log('updated tenant status');

        return back()->with('success', 'Tenant status updated successfully.');
    }

    public function impersonate(TenantCompany $company): RedirectResponse
    {
        $owner = $company->owner;
        
        activity()
           ->performedOn($company)
           ->causedBy(auth()->guard('admin')->user())
           ->log('impersonated tenant');
           
        $this->adminService->impersonateTenant($owner);

        return redirect()->route('tenant.dashboard')->with('success', 'Impersonating '.$company->name);
    }
    
    public function destroy(TenantCompany $company): RedirectResponse
    {
        activity()
           ->performedOn($company)
           ->causedBy(auth()->guard('admin')->user())
           ->log('deleted tenant');
           
        $company->delete();

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant deleted successfully.');
    }
}
