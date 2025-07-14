<?php

namespace FilipeFernandes\FeatureFlags\Tests;

use FilipeFernandes\FeatureFlags\FeatureFlagsServiceProvider;
use FilipeFernandes\FeatureFlags\Tests\Inertia\TestInertiaMiddleware;
use FilipeFernandes\FeatureFlags\Tests\Livewire\TestComponent;
use FilipeFernandes\FeatureFlags\Tests\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Inertia\Inertia;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            FeatureFlagsServiceProvider::class,
            \Inertia\ServiceProvider::class,
            \Livewire\LivewireServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'base64:'.base64_encode(random_bytes(32)));

        $app['config']->set('feature-flags.flags', [
            'new_dashboard' => [
                'enabled' => true,
            ],
        ]);

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        $app['router']->aliasMiddleware('inertia', TestInertiaMiddleware::class);

        // Set User model for auth
        $app['config']->set('auth.providers.users.model', User::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->artisan('migrate')->run();

        // Add this to allow Laravel to load the view
        $this->app['view']->addLocation(__DIR__.'/resources/views');
        // Define the test route
        $this->app['router']->get('/test-blade', function () {
            return view('test'); // will render tests/resources/views/test.blade.php
        });

        // Register Livewire test route
        $this->app['router']->get('/test-livewire', TestComponent::class);

        // Register Inertia test route
        $this->app['router']->get('/test-inertia', function () {
            return Inertia::render('TestPage', []);
        });

        // Add Laravel helper functions if not present
        if (! function_exists('config_path')) {
            function config_path($path = '')
            {
                return __DIR__.'/../vendor/orchestra/testbench-core/laravel/config'.($path ? '/'.$path : '');
            }
        }

        if (! function_exists('database_path')) {
            function database_path($path = '')
            {
                return __DIR__.'/../vendor/orchestra/testbench-core/laravel/database'.($path ? '/'.$path : '');
            }
        }

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'FilipeFernandes\\FeatureFlags\\Tests\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }
}
