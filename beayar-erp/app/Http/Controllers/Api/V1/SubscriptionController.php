<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subscription\SubscriptionPurchaseRequest;
use App\Models\Plan;
use App\Services\Subscription\PlanManagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    protected $planManager;

    public function __construct(PlanManagerService $planManager)
    {
        $this->planManager = $planManager;
    }

    public function plans(): JsonResponse
    {
        return response()->json(Plan::all());
    }

    public function current(Request $request): JsonResponse
    {
        $subscription = $request->user()->subscription()->with('plan')->first();
        return response()->json($subscription);
    }

    public function purchase(SubscriptionPurchaseRequest $request): JsonResponse
    {
        $plan = Plan::findOrFail($request->plan_id);
        $user = $request->user();

        // In a real scenario, we would process payment here via a Payment Gateway Service
        // For now, we simulate success and upgrade the plan

        // Assuming payment success...
        
        if ($user->subscription) {
            $this->planManager->changePlan($user->subscription, $plan);
        } else {
            // Create new subscription logic would go here if not handled by registration
        }

        return response()->json(['message' => 'Subscription updated successfully']);
    }
}
