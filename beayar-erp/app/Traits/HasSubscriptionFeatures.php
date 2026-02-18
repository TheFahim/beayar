<?php

namespace App\Traits;

use App\Models\Subscription;

trait HasSubscriptionFeatures
{
    /**
     * Get the subscription for the tenant company.
     * This assumes the company belongs to a tenant which has a subscription.
     */
    public function subscription()
    {
        // Access the subscription through the tenant relation
        // We use the 'tenant' relation defined in TenantCompany model
        return $this->tenant->subscription();
    }

    /**
     * Check if tenant has access to a module (Boolean)
     */
    public function hasFeature(string $featureSlug): bool
    {
        // Eager load subscription and plan features if not already loaded to avoid N+1
        // However, in a trait method, we usually rely on what's available or lazy load.
        
        $subscription = $this->tenant->subscription;

        if (!$subscription) {
            return false;
        }

        // 1. Check Subscription Overrides (if any)
        if (isset($subscription->feature_access[$featureSlug])) {
            return $subscription->feature_access[$featureSlug];
        }

        // 2. Check Plan Features
        // We need to check if the plan has this feature.
        // Assuming subscription->plan is loaded or we load it.
        $plan = $subscription->plan;
        
        if (!$plan) {
            return false;
        }

        // Check if the feature exists in the plan's features
        // We use the relation 'features' on the Plan model
        // To optimize, we should probably check the loaded relation if available, 
        // otherwise query it.
        
        // For performance, we might want to cache this or use the loaded relation.
        // If 'features' relation is loaded:
        if ($plan->relationLoaded('features')) {
            return $plan->features->contains('slug', $featureSlug);
        }

        // If not loaded, we can query existence efficiently
        return $plan->features()->where('slug', $featureSlug)->exists();
    }

    /**
     * Get the numeric limit for a feature
     * Returns -1 for unlimited, 0 for none, or the integer limit.
     */
    public function getFeatureLimit(string $featureSlug): int
    {
        $subscription = $this->tenant->subscription;

        if (!$subscription) {
            return 0;
        }

        // 1. Check Subscription Custom Limits
        if (isset($subscription->custom_limits[$featureSlug])) {
            return (int) $subscription->custom_limits[$featureSlug];
        }

        // 2. Check Plan Configuration (pivot table)
        $plan = $subscription->plan;

        if (!$plan) {
            return 0;
        }

        // We need to get the config from the pivot table for this feature
        // Ideally we should eagerly load plans.features
        
        $feature = null;
        if ($plan->relationLoaded('features')) {
            $feature = $plan->features->firstWhere('slug', $featureSlug);
        } else {
            $feature = $plan->features()->where('slug', $featureSlug)->first();
        }

        if ($feature && isset($feature->pivot->config['limit'])) {
            return (int) $feature->pivot->config['limit'];
        }

        return 0; // Default: No access
    }
}
