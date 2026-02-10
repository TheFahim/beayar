<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePlanRequest;
use App\Http\Requests\Admin\UpdatePlanRequest;
use App\Models\Module;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        $plans = Plan::withCount('subscriptions')->get();
        $modules = Module::orderBy('name')->get();

        return view('admin.plans.index', compact('plans', 'modules'));
    }

    public function store(StorePlanRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Plan::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'base_price' => $validated['base_price'],
            'billing_cycle' => $validated['billing_cycle'],
            'is_active' => $validated['is_active'] ?? true,
            'limits' => $validated['limits'] ?? null,
            'module_access' => $validated['module_access'] ?? [],
        ]);

        return back()->with('success', 'Plan created successfully.');
    }

    public function update(UpdatePlanRequest $request, Plan $plan): RedirectResponse
    {
        $validated = $request->validated();

        $plan->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'base_price' => $validated['base_price'],
            'billing_cycle' => $validated['billing_cycle'],
            'is_active' => $validated['is_active'] ?? $plan->is_active,
            'limits' => $validated['limits'] ?? null,
            'module_access' => $validated['module_access'] ?? [],
        ]);

        return back()->with('success', 'Plan updated successfully.');
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        if ($plan->subscriptions()->where('status', 'active')->exists()) {
            return back()->with('error', 'Cannot delete a plan with active subscriptions.');
        }

        $plan->update(['is_active' => false]);

        return back()->with('success', 'Plan deactivated successfully.');
    }
}
