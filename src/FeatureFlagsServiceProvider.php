<?php

namespace FilipeFernandes\FeatureFlags;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Illuminate\Support\Facades\Blade;
use FilipeFernandes\FeatureFlags\Commands\SetFlagCommand;
use FilipeFernandes\FeatureFlags\Http\Middleware\EnsureFeatureEnabled;

class FeatureFlagsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/feature-flags.php', 'feature-flags');
        $this->app->singleton(FeatureFlags::class, fn() => new FeatureFlags());
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->publishes([
            __DIR__ . '/../config/feature-flags.php' => config_path('feature-flags.php'),
        ], 'feature-flags-config');

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'feature-flags-migrations');

        Blade::if('feature', fn($key) => app(FeatureFlags::class)->isEnabled($key));
        Inertia::share('featureFlags', fn() => app(FeatureFlags::class)->all(true));


        if ($this->app->runningInConsole()) {
            $this->commands([SetFlagCommand::class]);
        }

        $this->app['router']->aliasMiddleware('feature', EnsureFeatureEnabled::class);
    }
}
