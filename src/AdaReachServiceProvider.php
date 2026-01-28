<?php

namespace AdaReach\Sms;

use Illuminate\Support\ServiceProvider;

class AdaReachServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/adarearch.php', 'adarearch'
        );

        $this->app->singleton('adarearch', function ($app) {
            return new AdaReachClient(
                config('adarearch.username'),
                config('adarearch.password'),
                config('adarearch.base_url')
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/adarearch.php' => config_path('adarearch.php'),
            ], 'adarearch-config');
        }
    }
}
