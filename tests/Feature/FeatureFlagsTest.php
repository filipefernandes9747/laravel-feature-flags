<?php

use FilipeFernandes\FeatureFlags\FeatureFlags;
use FilipeFernandes\FeatureFlags\Models\FeatureFlag;
use Illuminate\Support\Facades\Config;

it('returns false for unknown flags', function () {
    $flags = new FeatureFlags;
    expect($flags->isEnabled('nonexistent'))->toBeFalse();
});

it('returns true for known flags', function () {
    $flags = new FeatureFlags;
    expect($flags->isEnabled('new_dashboard'))->toBeTrue();
});

it('returns true if DB flag is enabled', function () {
    FeatureFlag::create(['key' => 'flag_1.0', 'enabled' => true]);

    $flags = new FeatureFlags;
    expect($flags->isEnabled('flag_1.0'))->toBeTrue();
});

it('returns true if DB flag is enabled but closure is false', function () {
    FeatureFlag::create(['key' => 'flag_1.0', 'enabled' => true]);

    $flags = new FeatureFlags;
    expect($flags->isEnabled(
        key: 'flag_1.0',
        closure: fn() => false
    ))->toBeFalse();
});

it('returns only enabled flags from config and db for current environment by default', function () {
    Config::set('feature-flags.flags', [
        'flag_config_enabled' => ['enabled' => true],
        'flag_config_disabled' => ['enabled' => false],
        'flag_with_env' => [
            'environments' => ['testing' => true, 'production' => false],
            'enabled' => true,
        ],
    ]);
    FeatureFlag::create(['key' => 'flag_db_enabled', 'enabled' => true]);
    FeatureFlag::create(['key' => 'flag_db_disabled', 'enabled' => false]);
    FeatureFlag::create(['key' => 'flag_db_with_env', 'enabled' => true, 'environments' => ['testing' => true, 'production' => false]]);

    // Mock environment 'testing'
    app()->detectEnvironment(fn() => 'testing');

    $flags = new FeatureFlags;
    $result = $flags->all();

    expect($result)->toHaveKey('flag_config_enabled')
        ->and($result['flag_config_enabled'])->toBeTrue()
        ->and($result)->not->toHaveKey('flag_config_disabled')
        ->and($result)->toHaveKey('flag_with_env')
        ->and($result['flag_with_env'])->toBeTrue()
        ->and($result)->toHaveKey('flag_db_enabled')
        ->and($result['flag_db_enabled'])->toBeTrue()
        ->and($result)->not->toHaveKey('flag_db_disabled')
        ->and($result)->toHaveKey('flag_db_with_env')
        ->and($result['flag_db_with_env'])->toBeTrue();
});

it('respects given environment argument', function () {
    Config::set('feature-flags.flags', [
        'flag_env' => [
            'environments' => ['production' => true, 'testing' => false],
            'enabled' => true,
        ],
    ]);
    FeatureFlag::create(['key' => 'flag_env', 'enabled' => true, 'environments' => ['production' => true, 'testing' => false]]);

    $flags = new FeatureFlags;

    $prodFlags = $flags->all('production');
    $testingFlags = $flags->all('testing');

    expect($prodFlags)->toHaveKey('flag_env')->and($prodFlags['flag_env'])->toBeTrue();
    expect($testingFlags)->not->toHaveKey('flag_env');
});

it('returns true if all given flags are enabled', function () {
    FeatureFlag::create(['key' => 'flag_a', 'enabled' => true]);
    FeatureFlag::create(['key' => 'flag_b', 'enabled' => true]);

    $flags = new FeatureFlags;
    expect($flags->allAreEnabled(['flag_a', 'flag_b']))->toBeTrue();
});

it('returns false if any flag is disabled', function () {
    FeatureFlag::create(['key' => 'flag_a', 'enabled' => true]);
    FeatureFlag::create(['key' => 'flag_b', 'enabled' => false]);

    $flags = new FeatureFlags;
    expect($flags->allAreEnabled(['flag_a', 'flag_b']))->toBeFalse();
});

it('returns true if some flags are enabled', function () {
    FeatureFlag::create(['key' => 'flag_a', 'enabled' => false]);
    FeatureFlag::create(['key' => 'flag_b', 'enabled' => true]);

    $flags = new FeatureFlags;
    expect($flags->someAreEnabled(['flag_a', 'flag_b']))->toBeTrue();
});

it('returns false if none of the flags are enabled', function () {
    FeatureFlag::create(['key' => 'flag_a', 'enabled' => false]);
    FeatureFlag::create(['key' => 'flag_b', 'enabled' => false]);

    $flags = new FeatureFlags;
    expect($flags->someAreEnabled(['flag_a', 'flag_b']))->toBeFalse();
});

it('returns true if all given flags are inactive', function () {
    FeatureFlag::create(['key' => 'flag_a', 'enabled' => false]);
    FeatureFlag::create(['key' => 'flag_b', 'enabled' => false]);

    $flags = new FeatureFlags;
    expect($flags->allAreInactive(['flag_a', 'flag_b']))->toBeTrue();
});

it('returns false if any flag is enabled', function () {
    FeatureFlag::create(['key' => 'flag_a', 'enabled' => false]);
    FeatureFlag::create(['key' => 'flag_b', 'enabled' => true]);

    $flags = new FeatureFlags;
    expect($flags->allAreInactive(['flag_a', 'flag_b']))->toBeFalse();
});

it('returns true if some flags are inactive', function () {
    FeatureFlag::create(['key' => 'flag_a', 'enabled' => false]);
    FeatureFlag::create(['key' => 'flag_b', 'enabled' => true]);

    $flags = new FeatureFlags;
    expect($flags->someAreInactive(['flag_a', 'flag_b']))->toBeTrue();
});

it('returns false if none of the flags are inactive', function () {
    FeatureFlag::create(['key' => 'flag_a', 'enabled' => true]);
    FeatureFlag::create(['key' => 'flag_b', 'enabled' => true]);

    $flags = new FeatureFlags;
    expect($flags->someAreInactive(['flag_a', 'flag_b']))->toBeFalse();
});

it('returns false if flag is disabled for environment', function () {
    FeatureFlag::create([
        'key' => 'env_flag',
        'enabled' => true,
        'environments' => ['testing' => false, 'production' => true],
    ]);

    $flags = new FeatureFlags;
    expect($flags->isEnabled('env_flag', null, 'testing'))->toBeFalse();
    expect($flags->isEnabled('env_flag', null, 'production'))->toBeTrue();
});

it('return true feature flag on config with closure with enviroment', function () {
    Config::set('feature-flags.cache.enabled', false);
    Config::set('feature-flags.flags', [
        'flag_test_closure_true' => [
            'enabled' => true,
            'environments' => [
                'testing' => function ($context) {
                    return true;
                },
            ],
        ],
        'flag_test_closure_false' => [
            'enabled' => true,
            'environments' => [
                'testing' => function ($context) {
                    return false;
                },
            ],
        ],
    ]);

    $flags = new FeatureFlags;
    $result = $flags->all();

    expect($result)->toHaveKey('flag_test_closure_true')
        ->and($result['flag_test_closure_true'])->toBeTrue()
        ->and($result)->not->toHaveKey('flag_test_closure_false');
});
