<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Database\Eloquent\Model;

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
        // Force HTTPS in production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
        
        // Performance optimizations for Eloquent
        $isProduction = $this->app->environment('production');
        Model::preventLazyLoading(!$isProduction);
        Model::preventSilentlyDiscardingAttributes(!$isProduction);
        Model::shouldBeStrict(!$isProduction);
    }
}
