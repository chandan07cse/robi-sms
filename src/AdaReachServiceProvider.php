<?php

namespace AdaReach\Sms;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use AdaReach\Sms\Storage\SmsRepository;

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

        $this->app->singleton(SmsRepository::class, function ($app) {
            return new SmsRepository();
        });

        $this->app->singleton(AdaReachClient::class, function ($app) {
            return app('adarearch');
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'adarearch');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Register middleware
        $router = $this->app['router'];
        $router->aliasMiddleware('adarearch.auth', \AdaReach\Sms\Http\Middleware\DashboardAuth::class);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/adarearch.php' => config_path('adarearch.php'),
            ], 'adarearch-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/adarearch'),
            ], 'adarearch-views');

            $this->publishes([
                __DIR__.'/../public' => public_path('vendor/adarearch'),
            ], 'adarearch-assets');

            $this->commands([
                \AdaReach\Sms\Console\Commands\DashboardServeCommand::class,
                \AdaReach\Sms\Console\Commands\CleanupCommand::class,
                \AdaReach\Sms\Console\Commands\GeneratePasswordCommand::class,
            ]);
        }
    }
}
