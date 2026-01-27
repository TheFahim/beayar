<?php

namespace App\Services\Subscription;

use App\Models\User;
use App\Models\SubscriptionUsage;

class SubscriptionService
{
    public function checkLimit(User $user, string $metric): bool
    {
        if (!$user->subscription) {
            return false;
        }
        return $user->subscription->checkLimit($metric);
    }

    public function recordUsage(User $user, string $metric, int $quantity = 1): void
    {
        if ($user->subscription) {
            $user->subscription->recordUsage($metric, $quantity);
        }
    }

    public function getUsage(User $user, string $metric): int
    {
        if (!$user->subscription) {
            return 0;
        }
        return $user->subscription->usages()->where('metric', $metric)->value('used') ?? 0;
    }
}
