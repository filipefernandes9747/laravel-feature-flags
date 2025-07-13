<?php

use Livewire\Livewire;
use FilipeFernandes\FeatureFlags\Models\FeatureFlag;
use FilipeFernandes\FeatureFlags\Tests\Livewire\TestComponent;

it('renders livewire feature flag', function () {
    FeatureFlag::create(['key' => 'new_dashboard', 'enabled' => true]);

    Livewire::test(TestComponent::class)
        ->assertSee('Livewire feature enabled')
        ->assertDontSee('Livewire feature disabled');
});
