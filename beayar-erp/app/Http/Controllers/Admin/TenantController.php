<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSubscriptionRequest;
use App\Models\Module;
use App\Models\Plan;
use App\Models\UserCompany;
use App\Services\SuperAdmin\AdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function __construct(public AdminService $adminService) {}

    public function index(): View
    {
        $tenants = UserCompany::with(['owner.subscription.plan'])->latest()->paginate(10);

        return view('admin.tenants.index', compact('tenants'));
    }

    public function show(UserCompany $company): View
    {
        $company->load(['owner.subscription.plan', 'owner.subscription.usages', 'tenant', 'members']);

        $subscription = $company->owner?->subscription;
        $plans = Plan::where('is_active', true)->get();
        $modules = Module::orderBy('name')->get();

        return view('admin.tenants.show', compact('company', 'subscription', 'plans', 'modules'));
    }

    public function updateSubscription(UpdateSubscriptionRequest $request, UserCompany $company): RedirectResponse
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

        return back()->with('success', 'Subscription updated successfully.');
    }

    public function suspend(Request $request, UserCompany $company): RedirectResponse
    {
        $newStatus = $request->input('status', 'suspended');
        $company->update(['status' => $newStatus]);

        return back()->with('success', 'Tenant status updated successfully.');
    }

    public function impersonate(UserCompany $company): RedirectResponse
    {
        $owner = $company->owner;
        $this->adminService->impersonateTenant($owner);

        return redirect()->route('tenant.dashboard')->with('success', 'Impersonating '.$company->name);
    }
}
