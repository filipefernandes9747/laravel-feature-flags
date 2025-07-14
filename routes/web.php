<?php

use Illuminate\Support\Facades\Route;
use FilipeFernandes\FeatureFlags\Http\Controllers\FeatureFlagController;

Route::middleware(config('feature-flags.middleware.ui', ['web', 'auth']))
    ->prefix('feature-flags')
    ->name('feature-flags.')
    ->group(function () {
        Route::get('/', [FeatureFlagController::class, 'index'])->name('index');
        Route::post('/{flag}/toggle', [FeatureFlagController::class, 'toggle'])->name('toggle');
    });
