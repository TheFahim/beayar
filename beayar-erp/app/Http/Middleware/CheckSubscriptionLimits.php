<?php

namespace App\Http\Middleware;

use App\Services\Subscription\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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

        if (! $user || ! $this->subscriptionService->checkLimit($user, $metric)) {
            return response()->json(['message' => 'Subscription limit exceeded for '.$metric], 403);
        }

        return $next($request);
    }
}
