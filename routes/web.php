<?php

use FilipeFernandes\FeatureFlags\Http\Controllers\FeatureFlagController;
use Illuminate\Support\Facades\Route;

$middleware = config('feature-flags.ui.middleware', []);
$routeEndpoint = config('feature-flags.ui.route_prefix', 'admin/flags');

Route::prefix($routeEndpoint)
    ->middleware($middleware)
    ->name('feature-flags.')
    ->group(function () {
        Route::get('/', [FeatureFlagController::class, 'index'])->name('index');
        Route::post('/', [FeatureFlagController::class, 'store'])->name('store');
        Route::post('/{flag}/toggle', [FeatureFlagController::class, 'toggle'])->name('toggle');

        Route::get('/history', [FeatureFlagController::class, 'indexHistory'])->name('history');
    });
