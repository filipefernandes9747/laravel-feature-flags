<?php

namespace FilipeFernandes\FeatureFlags;

use FilipeFernandes\FeatureFlags\Enums\OperationType;
use FilipeFernandes\FeatureFlags\Models\FeatureFlag;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class FeatureFlags
{
    private array $configCache = [];
    private ?Collection $dbFlags = null;

    public function isEnabled(string $key, $context = null, ?string $environment = null, ?\Closure $closure = null): bool
    {
        $context ??= Auth::user();
        $environment ??= app()->environment();
        $definedEnvironments = $this->getDefinedEnvironments();

        // Check database flag first (usually fewer items)
        $dbFlag = $this->getDbFlag($key);

        if ($dbFlag) {
            return $this->evaluateDbFlag($dbFlag, $environment, $definedEnvironments, $context, $closure, $key);
        }

        // Fallback to config
        return $this->evaluateConfigFlag($key, $environment, $definedEnvironments, $context);
    }

    public function all(?string $environment = null): array
    {
        $environment ??= app()->environment();
        $user = Auth::user();
        $userKey = $user?->id ?? 'guest';

        $cache = config('feature-flags.cache');
        if (!$cache['enabled']) {
            return $this->getAllActiveFlags($environment, $user);
        }

        return Cache::tags(['feature_flags', "user:{$userKey}"])
            ->remember(
                "all_active_{$environment}_user_{$userKey}",
                $cache['ttl'],
                fn() => $this->getAllActiveFlags($environment, $user)
            );
    }

    public function allAreEnabled(array $keys): bool
    {
        if (empty($keys)) {
            return true;
        }

        foreach ($keys as $key) {
            if (!$this->isEnabled($key)) {
                return false;
            }
        }
        return true;
    }

    public function someAreEnabled(array $keys): bool
    {
        foreach ($keys as $key) {
            if ($this->isEnabled($key)) {
                return true;
            }
        }
        return false;
    }

    public function inactive(string $key, ?string $environment = null): bool
    {
        return !$this->isEnabled($key, environment: $environment);
    }

    public function allAreInactive(array $keys): bool
    {
        foreach ($keys as $key) {
            if ($this->isEnabled($key)) {
                return false;
            }
        }
        return true;
    }

    public function someAreInactive(array $keys): bool
    {
        foreach ($keys as $key) {
            if (!$this->isEnabled($key)) {
                return true;
            }
        }
        return false;
    }

    public function getAllFlags(array $environments): array
    {
        $dbFlags = $this->getDbFlags()->keyBy('key');

        // Start with DB flags
        $flags = $dbFlags->map(fn($flag) => [
            'key' => $flag->key,
            'enabled' => !empty($environments) && !empty($flag->environments)
                ? $flag->environments
                : $flag->enabled,
            'updated_at' => $flag->updated_at,
            'db' => true
        ]);

        // Add config flags that aren't in DB
        $configFlags = $this->getConfigFlags();
        foreach ($configFlags as $key => $configFlag) {
            if (!$dbFlags->has($key)) {
                $flags->put($key, [
                    'key' => $key,
                    'enabled' => $configFlag['enabled'] ?? false,
                    'updated_at' => null,
                    'db' => false
                ]);
            }
        }

        return $flags->values()->toArray();
    }

    public function clearCache(): void
    {
        Cache::tags('feature_flags')->flush();
        // Also clear internal cache
        $this->configCache = [];
        $this->dbFlags = null;
    }

    // Private helper methods

    private function getDefinedEnvironments(): array
    {
        return $this->configCache['environments'] ??= config('feature-flags.environments', []);
    }

    private function getConfigFlags(): array
    {
        return $this->configCache['flags'] ??= config('feature-flags.flags', []);
    }

    private function getDbFlags(): Collection
    {
        return $this->dbFlags ??= FeatureFlag::all();
    }

    private function getDbFlag(string $key): ?FeatureFlag
    {
        return $this->getDbFlags()->firstWhere('key', $key);
    }

    private function evaluateDbFlag(
        FeatureFlag $dbFlag,
        string $environment,
        array $definedEnvironments,
        $context,
        ?\Closure $closure,
        string $key
    ): bool {
        // Check environment overrides
        if (!empty($definedEnvironments) && $dbFlag->environments && isset($dbFlag->environments[$environment])) {
            if (!$dbFlag->environments[$environment]) {
                return false;
            }
        } elseif (!$dbFlag->enabled) {
            return false;
        }

        // Custom closure takes precedence
        if ($closure) {
            return (bool) $closure($context);
        }

        //check conditions
        if (!empty($dbFlag->conditions)) {
            return (bool) $this->evaluateConditions($dbFlag->conditions, $context);
        }

        // Check config closure
        $config = $this->getConfigFlags()[$key] ?? null;
        if (is_array($config) && is_callable($config['closure'] ?? null)) {
            return (bool) $config['closure']($context);
        }

        return true;
    }

    private function evaluateConfigFlag(string $key, string $environment, array $definedEnvironments, $context): bool
    {
        $config = $this->getConfigFlags()[$key] ?? null;

        if (!is_array($config)) {
            return false;
        }

        // Check environment-specific settings
        if (!empty($definedEnvironments) && isset($config['environments'][$environment])) {
            $envValue = $config['environments'][$environment];

            return match (true) {
                is_bool($envValue) => $envValue,
                is_callable($envValue) => (bool) $envValue($context),
                default => false,
            };
        }

        // Check global enabled flag
        if (!($config['enabled'] ?? false)) {
            return false;
        }

        // Execute closure if present
        if (is_callable($config['closure'] ?? null)) {
            return (bool) $config['closure']($context);
        }

        return true;
    }

    private function getAllActiveFlags(string $environment, ?Authenticatable $user): array
    {
        $definedEnvironments = $this->getDefinedEnvironments();

        // Process config flags
        $configFlags = collect($this->getConfigFlags())
            ->mapWithKeys(fn($val, $key) => [
                $key => $this->evaluateConfigValue($val, $environment, $user, $definedEnvironments)
            ]);

        // Process DB flags
        $dbFlags = $this->getDbFlags()
            ->mapWithKeys(fn($flag) => [
                $flag->key => $this->evaluateDbFlagForAll($flag, $environment, $user, $definedEnvironments)
            ]);

        // Merge and filter active flags
        return array_filter(
            array_merge($configFlags->toArray(), $dbFlags->toArray()),
            fn($enabled) => $enabled === true
        );
    }

    private function evaluateConfigValue($val, string $environment, ?Authenticatable $user, array $definedEnvironments): bool
    {
        if (is_callable($val)) {
            return (bool) $val($user);
        }

        if (!is_array($val)) {
            return (bool) $val;
        }

        $enabled = false;

        if (!empty($definedEnvironments) && isset($val['environments'][$environment])) {
            $envValue = $val['environments'][$environment];
            $enabled = match (true) {
                is_bool($envValue) => $envValue,
                is_callable($envValue) => (bool) $envValue($user),
                default => false,
            };
        } else {
            $enabled = (bool) ($val['enabled'] ?? false);
        }

        // Apply closure if enabled and closure exists
        if ($enabled && is_callable($val['closure'] ?? null)) {
            $enabled = (bool) $val['closure']($user);
        }

        return $enabled;
    }

    private function evaluateDbFlagForAll(FeatureFlag $flag, string $environment, ?Authenticatable $user, array $definedEnvironments): bool
    {
        // Check environment settings
        if (!empty($definedEnvironments)) {
            if ($flag->environments && isset($flag->environments[$environment])) {
                if (!$flag->environments[$environment]) {
                    return false;
                }
            } elseif (!$flag->enabled) {
                return false;
            }
        } else {
            if (!$flag->enabled) {
                return false;
            }
        }

        // Check for config closure
        $config = $this->getConfigFlags()[$flag->key] ?? null;
        if (is_array($config) && is_callable($config['closure'] ?? null)) {
            return (bool) $config['closure']($user);
        }

        return true;
    }

    private function evaluateConditions(array $conditions, $context): bool
    {
        $andConditions = $conditions['and'] ?? [];
        $orConditions  = $conditions['or'] ?? [];

        // all "and" conditions must pass
        foreach ($andConditions as $condition) {
            if (!$this->evaluateCondition($condition, $context)) {
                return false;
            }
        }


        // if no OR, then only AND matters
        if (empty($orConditions)) {
            return true;
        }

        // at least one OR must pass
        foreach ($orConditions as $condition) {
            if ($this->evaluateCondition($condition, $context)) {
                return true;
            }
        }

        return false;
    }

    private function evaluateCondition(array $condition, $context): bool
    {
        if ($condition['context'] === 'user' && str_contains(strtolower(class_basename($context)), 'user')) {
            if ($condition['operation'] !== OperationType::in->name) {
                $attribute = $context->{$condition['key']} ?? null;

                if ($attribute === null) {
                    return false;
                }
            }

            return match ($condition['operation']) {
                OperationType::equals->name   => $attribute === $condition['value'],
                OperationType::contains->name => str_contains((string) $attribute, (string) $condition['value']),
                OperationType::in->name       => $this->evaluateInCondition($context, (string) $condition['value']),
                default => false,
            };
        }

        return true;
    }

    private function evaluateInCondition($context, string $option): bool
    {
        $options = config('feature-flags.user_list', []);

        if (empty($options) || empty($options[$option])) {
            return false;
        }

        return $options[$option]($context);
    }
}
