<?php

use Illuminate\Support\Facades\Route;
use FilipeFernandes\FeatureFlags\FeatureFlags;

Route::get('/feature-flags', fn() => response()->json(app(FeatureFlags::class)->all(true)));
