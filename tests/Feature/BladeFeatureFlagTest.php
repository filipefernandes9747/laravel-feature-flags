<?php

use FilipeFernandes\FeatureFlags\Models\FeatureFlag;

it('shows blade section when flag is enabled', function () {
    FeatureFlag::create([
        'key' => 'new_dashboard',
        'enabled' => true,
    ]);

    $this->get('/test-blade')
        ->assertSee('Feature is enabled')
        ->assertDontSee('Feature is disabled');
});
