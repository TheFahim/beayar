<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePlanRequest;
use App\Http\Requests\Admin\UpdatePlanRequest;
use App\Models\Feature;
use App\Models\Module;
use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlanController extends Controller
{
    public function index(): View
    {
        $plans = Plan::withCount('subscriptions')->with('features')->get();
        $modules = Module::orderBy('name')->get();
        $features = Feature::with('module')->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.plans.index', compact('plans', 'modules', 'features'));
    }

    public function store(StorePlanRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $plan = Plan::create([
            'name' => $validated['name'],
            'slug' => $validated['slug'],
            'description' => $validated['description'] ?? null,
            'base_price' => $validated['base_price'],
            'billing_cycle' => $validated['billing_cycle'],
            'is_active' => $validated['is_active'] ?? true,
            'limits' => $validated['limits'] ?? null,
            'module_access' => $validated['module_access'] ?? [],
        ]);

        $this->syncPlanFeatures($plan, $request);

        activity()
            ->performedOn($plan)
            ->causedBy(auth()->guard('admin')->user())
            ->log('created plan');

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

        $this->syncPlanFeatures($plan, $request);

        activity()
            ->performedOn($plan)
            ->causedBy(auth()->guard('admin')->user())
            ->log('updated plan');

        return back()->with('success', 'Plan updated successfully.');
    }

    public function syncFeatures(Request $request, Plan $plan): RedirectResponse
    {
        $request->validate([
            'feature_ids' => 'nullable|array',
            'feature_ids.*' => 'exists:features,id',
            'feature_limits' => 'nullable|array',
        ]);

        $this->syncPlanFeatures($plan, $request);

        activity()
            ->performedOn($plan)
            ->causedBy(auth()->guard('admin')->user())
            ->log('synced plan features');

        return back()->with('success', 'Plan features updated successfully.');
    }

    private function syncPlanFeatures(Plan $plan, Request $request): void
    {
        $features = $request->input('feature_ids', []);
        $limits = $request->input('feature_limits', []);
        $syncData = [];

        foreach ($features as $featureId) {
            $config = null;
            // Check if there is a limit for this feature
            if (isset($limits[$featureId]) && $limits[$featureId] !== null) {
                // Ensure limit is an integer
                $config = ['limit' => (int) $limits[$featureId]];
            }
            
            // We store the config as an array, which will be cast to JSON by the PlanFeature pivot model
            $syncData[$featureId] = ['config' => $config];
        }

        $plan->features()->sync($syncData);
    }

    public function destroy(Plan $plan): RedirectResponse
    {
        if ($plan->subscriptions()->where('status', 'active')->exists()) {
            return back()->with('error', 'Cannot delete a plan with active subscriptions.');
        }

        $plan->update(['is_active' => false]);

        activity()
            ->performedOn($plan)
            ->causedBy(auth()->guard('admin')->user())
            ->log('deactivated plan');

        return back()->with('success', 'Plan deactivated successfully.');
    }
}
