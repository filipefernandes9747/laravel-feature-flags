<?php

namespace FilipeFernandes\FeatureFlags\Tests\Inertia;

use FilipeFernandes\FeatureFlags\FeatureFlags;
use Illuminate\Http\Request;
use Inertia\Middleware;

class TestInertiaMiddleware extends Middleware
{
    public function version(Request $request): ?string
    {
        return null;
    }

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'featureFlags' => app(FeatureFlags::class)->all(true),
        ]);
    }
}
