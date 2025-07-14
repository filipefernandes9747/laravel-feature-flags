<?php

namespace FilipeFernandes\FeatureFlags;

use FilipeFernandes\FeatureFlags\Models\FeatureFlag;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class FeatureFlags
{
    public function isEnabled(string $key, $context = null, ?string $environment = null, ?\Closure $closure = null): bool
    {
        $context ??= Auth::user();
        $environment ??= app()->environment();

        $dbFlag = FeatureFlag::where('key', $key)->first();

        if ($dbFlag) {
            // Check environment-specific override first
            if ($dbFlag->environments && isset($dbFlag->environments[$environment])) {
                if (! $dbFlag->environments[$environment]) {
                    return false;
                }
            } elseif (! $dbFlag->enabled) {
                return false;
            }

            if ($closure) {
                return (bool) $closure($context);
            }

            $config = config("feature-flags.flags.$key");
            if (is_array($config) && is_callable($config['closure'] ?? null)) {
                return (bool) $config['closure']($context);
            }

            return true;
        }

        // Config fallback
        $config = config("feature-flags.flags.$key");

        if (is_array($config)) {
            if (isset($config['environments'][$environment])) {
                if (! $config['environments'][$environment]) {
                    return false;
                }
            } elseif (! ($config['enabled'] ?? false)) {
                return false;
            }

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
