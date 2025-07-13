<?php

namespace FilipeFernandes\FeatureFlags\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use FilipeFernandes\FeatureFlags\FeatureFlagsServiceProvider;
use FilipeFernandes\FeatureFlags\Tests\Inertia\TestInertiaMiddleware;
use FilipeFernandes\FeatureFlags\Tests\Livewire\TestComponent;
use Inertia\Inertia;

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
        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));


        $app['config']->set('feature-flags.flags', [
            'new_dashboard' => [
                'enabled' => true,
            ]
        ]);

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['router']->aliasMiddleware('inertia', TestInertiaMiddleware::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->artisan('migrate')->run();

        // Add this to allow Laravel to load the view
        $this->app['view']->addLocation(__DIR__ . '/resources/views');
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
    }
}
