<?php

namespace FilipeFernandes\FeatureFlags;

use FilipeFernandes\FeatureFlags\Commands\InstallFeatureFlagsCommand;
use FilipeFernandes\FeatureFlags\Commands\SetFlagCommand;
use FilipeFernandes\FeatureFlags\Http\Middleware\EnsureFeatureEnabled;
use FilipeFernandes\FeatureFlags\Models\FeatureFlag;
use FilipeFernandes\FeatureFlags\Observers\FeatureFlagObserver;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class FeatureFlagsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/feature-flags.php', 'feature-flags');
        $this->app->singleton(FeatureFlags::class, fn () => new FeatureFlags);
    }

    public function boot(): void
    {
        foreach (glob(__DIR__.'/../routes/*.php') as $routeFile) {
            $this->loadRoutesFrom($routeFile);
        }
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'feature-flags');

        $this->publishes([
            __DIR__.'/../config/feature-flags.php' => config_path('feature-flags.php'),
        ], 'feature-flags-config');

        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'feature-flags-migrations');

        $this->publishes([
            __DIR__.'/../resources/assets/css' => public_path('vendor/feature-flags/css'),
        ], 'feature-flags-public');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/feature-flags'),
        ], 'feature-flags-views');

        Blade::if('feature', fn ($key) => app(FeatureFlags::class)->isEnabled($key));

        if (class_exists(\Inertia\Inertia::class)) {
            \Inertia\Inertia::share('featureFlags', fn () => app(FeatureFlags::class)->all());
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                SetFlagCommand::class,
                InstallFeatureFlagsCommand::class,
            ]);
        }

        $this->app['router']->aliasMiddleware('feature', EnsureFeatureEnabled::class);

        FeatureFlag::observe(FeatureFlagObserver::class);
    }
}
