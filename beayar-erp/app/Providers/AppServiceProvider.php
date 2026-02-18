<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::if('feature', function (string $feature) {
            $user = Auth::user();
            if (!$user) {
                return false;
            }

            // Use currentCompany if available and it has the trait method
            if ($user->currentCompany && method_exists($user->currentCompany, 'hasFeature')) {
                return $user->currentCompany->hasFeature($feature);
            }

            // Fallback to user method if available (legacy support)
            if (method_exists($user, 'hasFeatureAccess')) {
                return $user->hasFeatureAccess($feature);
            }

            return false;
        });
    }
}
