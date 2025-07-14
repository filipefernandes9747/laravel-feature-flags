<?php

use FilipeFernandes\FeatureFlags\Tests\Models\User;

it('shows the feature flags in the UI', function () {
    \FilipeFernandes\FeatureFlags\Models\FeatureFlag::create(['key' => 'dark_mode', 'enabled' => true]);

    $this->actingAs(User::factory()->create())
        ->get('/feature-flags')
        ->assertSee('new_dashboard');
});
