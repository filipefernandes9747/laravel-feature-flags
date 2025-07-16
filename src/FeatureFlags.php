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

        // Get global environments config array or empty array if none
        $definedEnvironments = config('feature-flags.environments', []);

        $dbFlag = FeatureFlag::where('key', $key)->first();

        if ($dbFlag) {
            // Only check environment overrides if environments are defined globally and flag has env overrides
            if (! empty($definedEnvironments) && $dbFlag->environments && isset($dbFlag->environments[$environment])) {
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
            if (! empty($definedEnvironments) && isset($config['environments'][$environment])) {
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

    public function all(?string $environment = null): array
    {
        $user = Auth::user();
        $environment ??= app()->environment();
        $definedEnvironments = config('feature-flags.environments', []);

        $cache = config('feature-flags.cache');

        return Cache::remember('feature_flags_all_active_'.$environment, now()->addMinutes($cache['ttl']), function () use ($environment, $user, $definedEnvironments) {
            $configFlags = collect(config('feature-flags.flags'))->mapWithKeys(function ($val, $key) use ($environment, $user, $definedEnvironments) {
                $enabled = false;

                if (is_array($val)) {
                    if (! empty($definedEnvironments)) {
                        if (isset($val['environments'][$environment])) {
                            $enabled = (bool) $val['environments'][$environment];
                        } else {
                            $enabled = (bool) ($val['enabled'] ?? false);
                        }
                    } else {
                        // No environments defined globally, ignore env check
                        $enabled = (bool) ($val['enabled'] ?? false);
                    }

                    if ($enabled && is_callable($val['closure'] ?? null)) {
                        $enabled = (bool) $val['closure']($user);
                    }
                } elseif (is_callable($val)) {
                    $enabled = (bool) $val($user);
                } else {
                    $enabled = (bool) $val;
                }

                return [$key => $enabled];
            });

            $dbFlags = FeatureFlag::all()->mapWithKeys(function ($flag) use ($environment, $user, $definedEnvironments) {
                if (! empty($definedEnvironments)) {
                    if ($flag->environments && isset($flag->environments[$environment])) {
                        if (! $flag->environments[$environment]) {
                            return [$flag->key => false];
                        }
                    } elseif (! $flag->enabled) {
                        return [$flag->key => false];
                    }
                } else {
                    // No environments defined globally, ignore env check
                    if (! $flag->enabled) {
                        return [$flag->key => false];
                    }
                }

                $config = config("feature-flags.flags.{$flag->key}");
                if (is_array($config) && is_callable($config['closure'] ?? null)) {
                    return [$flag->key => (bool) $config['closure']($user)];
                }

                return [$flag->key => true];
            })->toArray();

            $allFlags = array_merge($configFlags->toArray(), $dbFlags);

            return array_filter($allFlags, fn ($enabled) => $enabled === true);
        });
    }

    public function allAreEnabled(array $keys = []): bool
    {
        foreach ($keys as $key) {
            if (! $this->isEnabled($key)) {
                return false;
            }
        }

        return true;
    }

    public function someAreEnabled(array $keys = []): bool
    {
        foreach ($keys as $key) {
            if ($this->isEnabled($key)) {
                return true;
            }
        }

        return false;
    }

    public function inative(string $key, ?string $environment = null): bool
    {
        return ! $this->isEnabled(key: $key, environment: $environment);
    }

    public function allAreInative(array $keys = []): bool
    {
        foreach ($keys as $key) {
            if ($this->isEnabled($key)) {
                return false;
            }
        }

        return true;
    }

    public function someAreInactive(array $keys = []): bool
    {
        foreach ($keys as $key) {
            if (! $this->isEnabled($key)) {
                return true;
            }
        }

        return false;
    }

    public function clearCache(): void
    {
        Cache::forget('feature_flags_all');
    }
}
