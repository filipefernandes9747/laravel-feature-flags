<?php

use FilipeFernandes\FeatureFlags\FeatureFlags;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/feature-flags', function (Request $request) {
    $env = $request->query('env'); // nullable string

    /** @var FeatureFlags $featureFlags */
    $featureFlags = app(FeatureFlags::class);

    $flags = $featureFlags->all($env);

    return response()->json($flags);
});
