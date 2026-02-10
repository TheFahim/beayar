<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    protected $fillable = [
        'user_id',
        'tenant_id',
        'plan_id',
        'custom_limits',
        'status',
        'starts_at',
        'ends_at',
        'trial_ends_at'
    ];

    protected $casts = [
        'custom_limits' => 'array',
        'module_access' => 'array',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function usages(): HasMany
    {
        return $this->hasMany(SubscriptionUsage::class);
    }

    // Check if subscription is active
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               ($this->ends_at === null || $this->ends_at->isFuture());
    }

    // Check limit for a metric
    public function checkLimit(string $metric): bool
    {
        $limit = $this->getLimit($metric);
        
        if ($limit === -1) { // Unlimited
            return true;
        }

        $usage = $this->usages()->where('metric', $metric)->value('used') ?? 0;

        return $usage < $limit;
    }

    // Get limit for a metric (Plan limit + Custom limit)
    public function getLimit(string $metric): int
    {
        // Check custom limits first
        if (isset($this->custom_limits[$metric])) {
            return $this->custom_limits[$metric];
        }

        // Fallback to plan limits
        $planLimits = $this->plan->limits ?? [];
        return $planLimits[$metric] ?? 0;
    }

    // Record usage
    public function recordUsage(string $metric, int $quantity = 1): void
    {
        $usage = $this->usages()->firstOrCreate(
            ['metric' => $metric],
            ['used' => 0, 'limit' => $this->getLimit($metric)]
        );

        $usage->increment('used', $quantity);
    }
}
