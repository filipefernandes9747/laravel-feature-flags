# 🔀 Laravel Feature Flags

A robust, extensible, and testable **feature flag system** for Laravel 10+ with Vue/SPA frontend support. Designed as a reusable package for modern Laravel apps.

## ✨ Features

- ✅ Configurable flags (in config file or database)
- ✅ Boolean or closure-based evaluation
- ✅ Environment-specific overrides
- ✅ Persisted flags in DB (with optional validation)
- ✅ Blade directive: `@feature('flag')`
- ✅ Livewire & Inertia integration
- ✅ Middleware for routes or groups
- ✅ Artisan commands to list and manage flags
- ✅ Ui for managing feature flags and history
- ✅ Built with testability & reusability in mind

---

## 📦 Installation

```bash
composer require filipefernandes/laravel-feature-flags
```

### 1. Publish config and migration

```bash
php artisan feature-flag:install {--force: if you want to replace the current files}
```

---

## ⚙️ Configuration

The main config lives in:

```php
config/feature-flags.php
```

Example:

```php
return [
    'flags' => [
        'flag_1' => [
            'enabled' => true,
            'closure' => fn () => auth()->check() && auth()->user()->is_beta,
        ],
        'flag_2' => [
            'enabled' => [
                'dev' => true,
                'prod' => false
            ],
        ],
        'flag_3' => [
            'enabled' => [
                'dev' => fn () => auth()->check() && auth()->user()->is_beta,
                'prod' => true
            ],
        ],
    ],

    'ui' => [
        'enabled' => true,
        'middleware' => [],
        'route_prefix' => 'admin/flags',
        'options' => [
            'beta testers' => fn ($context) => $context->is_beta_testers,
            'admin' => fn (User $user) => $user->is_admin,
            ...
        ]
    ],
];
```

You can define static booleans or closures. If the flag is enabled in DB and has a closure, it will be resolved dynamically or can use the ui for defining the conditionals.

---

## 🧪 Usage

### ✅ Check if a flag is enabled

```php
use FeatureFlags;

// Simple check if a feature flag is enabled for the current user and environment
if (FeatureFlags::isEnabled('new_dashboard')) {
    // Show the new dashboard
}
```

---

### 🔄 Check if a flag is enabled with a custom context and environment

```php
$user = User::find(123);
$environment = 'staging';

if (FeatureFlags::isEnabled(key: 'beta_feature',context: $user, environment: $environment)) {
    // Enable beta feature for this user in staging environment
}
```

---

### ⚙️ Check a flag with a custom callback

```php
FeatureFlags::isEnabled(key:'complex_feature', closure: function ($user) {
    return $user->isAdmin() && $user->created_at->diffInDays(now()) > 30;
});
```

---

### 📋 Check multiple flags

```php
// Check if all flags are enabled
$allEnabled = FeatureFlags::allAreEnabled(['feature_a', 'feature_b']);

// Check if some flags are enabled
$someEnabled = FeatureFlags::someAreEnabled(['feature_c', 'feature_d']);
```

---

### 📋 Get all active (enabled) feature flags

Retrieve an array of all currently enabled feature flags, considering both the database and config settings.

```php
use FeatureFlags;

// Get all active flags for the current environment (default)
$activeFlags = FeatureFlags::all();

// Get all active flags for a specific environment (e.g., 'staging', 'production')
$activeFlagsInStaging = FeatureFlags::all('staging');
```

---

### ⚙️ Behavior notes

- By default, the method uses the current app environment (`app()->environment()`).
- If your `feature-flags.environments` config is defined and non-empty, the method respects environment-specific overrides on each flag.
- If the global environments config is empty or missing, environment checks are ignored, and only the flag’s `enabled` status is used.
- Flags defined in the database override config flags.
- The result is cached for the time setting the config file for performance.

---

### 🔄 Clear cache

```php
FeatureFlags::clearCache();
```

### ✅ Blade directive

```blade
@feature('new_dashboard')
    <x-new-dashboard />
@else
    <x-old-dashboard />
@endfeature
```

### ✅ Inertia support

`featureFlags` are automatically shared with frontend (if configured):

```js
import { usePage } from "@inertiajs/vue3";

const featureFlags = usePage().props.featureFlags;
```

### ✅ Livewire support

Available in views and component logic:

```php
if (FeatureFlags::isEnabled('experimental_widget')) {
    // ...
}
```

### ✅ Middleware

Apply to a route or group:

```php
Route::middleware(['feature:some_flag'])->get('/beta', fn () => 'Beta content');
```

---

## 🛠 CLI Commands

### List all feature flags

```bash
php artisan feature:flags
```

### Enable/disable a flag

```bash
php artisan feature:enable new_dashboard
php artisan feature:disable new_dashboard
```

## ✅ Requirements

- PHP 8.1+
- Laravel 10+
- Optional: Livewire, Inertia

---

## 📜 License

MIT © [Filipe Fernandes](https://github.com/filipefernandes)
