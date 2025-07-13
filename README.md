# 🔀 Laravel Feature Flags

A robust, extensible, and testable **feature flag system** for Laravel 10+ with Vue/SPA frontend support. Designed as a reusable package for modern Laravel apps.

## ✨ Features

- ✅ Configurable flags (in config file or database)
- ✅ Boolean or closure-based evaluation
- ✅ Persisted flags in DB (with optional validation)
- ✅ Blade directive: `@feature('flag')`
- ✅ Livewire & Inertia integration
- ✅ Middleware for routes or groups
- ✅ Artisan commands to list and manage flags
- ✅ Built with testability & reusability in mind

---

## 📦 Installation

```bash
composer require filipefernandes/feature-flags
```

### 1. Publish config and migration

```bash
php artisan vendor:publish --tag=feature-flags-config
php artisan vendor:publish --tag=feature-flags-migrations
php artisan migrate
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
        'new_dashboard' => true,
        'beta_mode' => fn () => auth()->check() && auth()->user()->is_beta,
    ],

    'middleware' => [
        'web' => ['auth'],
        'ui_guard' => 'auth',
    ],
];
```

You can define static booleans or closures. If the flag is enabled in DB and has a closure, it will be resolved dynamically.

---

## 🧪 Usage

### ✅ Check if a flag is enabled

```php
use FeatureFlags;

FeatureFlags::isEnabled('new_dashboard');
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

---

## 🧪 Testing

- Package includes built-in tests using [Orchestra Testbench](https://github.com/orchestral/testbench)
- Supports testing Blade, Livewire, Inertia, and middleware integration

Example:

```php
$this->get('/test-blade')
     ->assertSee('Feature is enabled');
```

---

## 🧱 Database Table

The published migration creates a `feature_flags` table:

```php
Schema::create('feature_flags', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique();
    $table->boolean('enabled')->default(false);
    $table->timestamps();
});
```

---

## 📂 Project Structure

```
src/
├── FeatureFlagsServiceProvider.php
├── Facades/FeatureFlags.php
├── Http/Middleware/FeatureMiddleware.php
├── Commands/
├── Directives/
├── Models/FeatureFlag.php
├── Support/helpers.php
tests/
```

---

## 🧩 Frontend Integration (Vue)

Make sure you have:

- Inertia middleware sharing `featureFlags`
- Your frontend uses `usePage().props.featureFlags` or API-based flags

---

## ✅ Requirements

- PHP 8.1+
- Laravel 10+
- Optional: Livewire, Inertia

---

## 📜 License

MIT © [Filipe Fernandes](https://github.com/filipefernandes)
