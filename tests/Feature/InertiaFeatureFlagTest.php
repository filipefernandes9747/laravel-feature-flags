<?php

use Inertia\Testing\AssertableInertia as Assert;

it('shares feature flags with inertia', function () {
    \FilipeFernandes\FeatureFlags\Models\FeatureFlag::create([
        'key' => 'new_dashboard',
        'enabled' => true,
    ]);

    $response = $this->get('/test-inertia')
        ->assertInertia(
            fn(Assert $page) =>
            $page->has('featureFlags')
                ->where('featureFlags.new_dashboard', true)
        );
});
