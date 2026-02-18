<?php

namespace App\Services;

use App\Models\TenantCompany;

class SubscriptionManager
{
    /**
     * Check if a tenant can consume a specific amount of a feature.
     *
     * @param TenantCompany $tenant
     * @param string $featureSlug
     * @param int $amount
     * @return bool
     */
    public function canConsume(TenantCompany $tenant, string $featureSlug, int $amount = 1): bool
    {
        $limit = $tenant->getFeatureLimit($featureSlug);

        // -1 represents Unlimited
        if ($limit === -1) {
            return true;
        }

        // If limit is 0, it means no access or 0 limit
        if ($limit === 0) {
            return false;
        }

        // Get current usage
        // We use firstOrCreate to ensure a record exists
        $usageRecord = $tenant->subscription->usages()
            ->firstOrCreate(
                ['metric' => $featureSlug],
                ['used' => 0]
            );

        return ($usageRecord->used + $amount) <= $limit;
    }

    /**
     * Consume a feature (increment usage).
     *
     * @param TenantCompany $tenant
     * @param string $featureSlug
     * @param int $amount
     * @throws \Exception
     */
    public function consume(TenantCompany $tenant, string $featureSlug, int $amount = 1): void
    {
        if (!$this->canConsume($tenant, $featureSlug, $amount)) {
            throw new \Exception("Limit reached for {$featureSlug}");
        }

        $tenant->subscription->usages()
            ->where('metric', $featureSlug)
            ->increment('used', $amount);
    }

    /**
     * Reduce usage (decrement).
     *
     * @param TenantCompany $tenant
     * @param string $featureSlug
     * @param int $amount
     */
    public function reduce(TenantCompany $tenant, string $featureSlug, int $amount = 1): void
    {
        $tenant->subscription->usages()
            ->where('metric', $featureSlug)
            ->decrement('used', $amount);
    }
}
