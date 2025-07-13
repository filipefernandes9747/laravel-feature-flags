<?php

use FilipeFernandes\FeatureFlags\FeatureFlags;
use FilipeFernandes\FeatureFlags\Models\FeatureFlag;

it('returns false for unknown flags', function () {
    $flags = new FeatureFlags();
    expect($flags->isEnabled('nonexistent'))->toBeFalse();
});

it('returns true for known flags', function () {
    $flags = new FeatureFlags();
    expect($flags->isEnabled('new_dashboard'))->toBeTrue();
});


it('returns true if DB flag is enabled', function () {
    FeatureFlag::create(['key' => 'flag_1.0', 'enabled' => true]);

    $flags = new FeatureFlags();
    expect($flags->isEnabled('flag_1.0'))->toBeTrue();
});


it('returns true if DB flag is enabled but closure is false', function () {
    FeatureFlag::create(['key' => 'flag_1.0', 'enabled' => true]);

    $flags = new FeatureFlags();
    expect($flags->isEnabled('flag_1.0', null, fn() => false))->toBeFalse();
});
