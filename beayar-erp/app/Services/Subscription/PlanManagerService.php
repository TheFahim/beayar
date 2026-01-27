<?php

namespace App\Services\Subscription;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\SuperAdmin\PlatformBillingService;

class PlanManagerService
{
    protected $billingService;

    public function __construct(PlatformBillingService $billingService)
    {
        $this->billingService = $billingService;
    }

    public function subscribe(User $user, Plan $plan): Subscription
    {
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'pending', // Pending until payment
            'starts_at' => now(),
            'ends_at' => now()->addMonth(), // Assuming monthly
        ]);

        // Generate Invoice
        $this->billingService->generateInvoice($subscription);

        return $subscription;
    }

    public function changePlan(User $user, Plan $newPlan)
    {
        // Logic to upgrade/downgrade
        // Calculate prorated amount
        // Update subscription
    }
}
