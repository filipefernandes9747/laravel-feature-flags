<?php

namespace FilipeFernandes\FeatureFlags;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use FilipeFernandes\FeatureFlags\Models\FeatureFlag;

class FeatureFlags
{
    public function isEnabled(string $key, $context = null, ?\Closure $overrideClosure = null): bool
    {
        $context ??= Auth::user();

        $dbFlag = FeatureFlag::where('key', $key)->first();

        if ($dbFlag) {
            if (!$dbFlag->enabled) return false;

            if ($overrideClosure) return (bool) $overrideClosure($context);

            $config = config("feature-flags.flags.$key");
            if (is_array($config) && is_callable($config['closure'] ?? null)) {
                return (bool) $config['closure']($context);
            }

            return true;
        }

        $config = config("feature-flags.flags.$key");

        if (is_array($config)) {
            if (!($config['enabled'] ?? false)) return false;

            if (is_callable($config['closure'] ?? null)) {
                return (bool) $config['closure']($context);
            }

            return true;
        }

        return false;
    }


    public function all(bool $onlyExposed = false): array
    {
        return Cache::remember('feature_flags_all', now()->addMinutes(5), function () use ($onlyExposed) {
            $config = collect(config('feature-flags.flags'))->map(function ($val) {
                return is_callable($val) ? (bool) $val(Auth::user()) : (bool) $val;
            });

            $query = FeatureFlag::query();
            if ($onlyExposed) {
                $query->whereJsonContains('metadata->expose', true);
            }

            return $config->merge($query->pluck('enabled', 'key'))->toArray();
        });
    }

    public function clearCache(): void
    {
        Cache::forget('feature_flags_all');
    }
}
