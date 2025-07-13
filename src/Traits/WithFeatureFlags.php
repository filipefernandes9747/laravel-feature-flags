<?php

namespace FilipeFernandes\FeatureFlags\Traits;

use FilipeFernandes\FeatureFlags\FeatureFlags;

trait WithFeatureFlags
{
    public function featureEnabled(string $key): bool
    {
        return app(FeatureFlags::class)->isEnabled($key);
    }
}
