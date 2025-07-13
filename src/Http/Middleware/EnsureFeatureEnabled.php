<?php

namespace FilipeFernandes\FeatureFlags\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use FilipeFernandes\FeatureFlags\FeatureFlags;

class EnsureFeatureEnabled
{
    public function handle(Request $request, Closure $next, string $flag)
    {
        if (!app(FeatureFlags::class)->isEnabled($flag)) {
            throw new HttpException(403, "Feature [$flag] is not enabled.");
        }

        return $next($request);
    }
}
