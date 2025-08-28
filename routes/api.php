<?php

use FilipeFernandes\FeatureFlags\FeatureFlags;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

$externalRoute = config('feature-flags.external_route');

if ($externalRoute['enabled'] ?? false) {
    $route = $externalRoute['endpoint'] ?? 'feature-flags';

    Route::get(
        $route,
        fn(Request $request, FeatureFlags $featureFlags) =>
        response()->json($featureFlags->all($request->query('env')))
    );
}
