<?php

namespace FilipeFernandes\FeatureFlags\Actions;

use FilipeFernandes\FeatureFlags\Models\FeatureFlag;
use Illuminate\Support\Str;

class CreateFlag
{
    public function handle(array $data): FeatureFlag
    {
        $key = Str::slug($data['name']);
        $environment = $data['environment'] ?? null;

        $environments = [];
        if ($environment) {
            $environments[$environment] = true;
        }

        return FeatureFlag::create([
            'key' => $key,
            'enabled' => array_unique(array_values($environments)) === [true],
            'environments' => $environments,
            'metadata' => $data['metadata'] ?? [],
        ]);
    }
}
