<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
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

        // if (app()->environment('local')) {
        //     URL::forceScheme('https');
        // }

        Model::unguard();


        Gate::before(function ($user, $ability) {
            if ($user->username === 'developer') {
                return true; // grant all abilities
            }
            
        });

    }
}
