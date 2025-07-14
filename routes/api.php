<?php

use FilipeFernandes\FeatureFlags\FeatureFlags;
use Illuminate\Support\Facades\Route;

Route::get('/feature-flags', fn () => response()->json(app(FeatureFlags::class)->all(true)));
