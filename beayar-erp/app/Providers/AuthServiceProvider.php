<?php

namespace App\Providers;

use App\Models\Feedback;
use App\Models\FeedbackImage;
use App\Policies\FeedbackImagePolicy;
use App\Policies\FeedbackPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Gate::policy(Feedback::class, FeedbackPolicy::class);
        Gate::policy(FeedbackImage::class, FeedbackImagePolicy::class);
    }
}
