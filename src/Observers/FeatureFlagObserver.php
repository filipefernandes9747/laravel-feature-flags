<?php

namespace FilipeFernandes\FeatureFlags\Observers;

use FilipeFernandes\FeatureFlags\Models\FeatureFlag;
use FilipeFernandes\FeatureFlags\Models\FeatureFlagHistory;

class FeatureFlagObserver
{
    public function created(FeatureFlag $featureFlag)
    {
        $this->logChange($featureFlag, 'created');
    }

    public function updated(FeatureFlag $featureFlag)
    {
        $this->logChange($featureFlag, 'updated');
    }

    public function deleted(FeatureFlag $featureFlag)
    {
        $this->logChange($featureFlag, 'deleted');
    }

    protected function logChange(FeatureFlag $featureFlag, string $event)
    {
        FeatureFlagHistory::create([
            'key' => $featureFlag->key,
            'enabled' => $featureFlag->enabled,
            'metadata' => $featureFlag->metadata,
            'environments' => $featureFlag->environments,
            'changed_at' => now(),
            'changed_by' => auth()->user()?->email ?? 'system',
            'event' => $event,
        ]);
    }
}
