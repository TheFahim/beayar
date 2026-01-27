<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Subscription\SubscriptionService;

class CheckSubscriptionLimits
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function handle(Request $request, Closure $next, string $metric): Response
    {
        $user = $request->user();

        if (!$user || !$this->subscriptionService->checkLimit($user, $metric)) {
            return response()->json(['message' => 'Subscription limit exceeded for ' . $metric], 403);
        }

        return $next($request);
    }
}
