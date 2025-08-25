<?php

namespace FilipeFernandes\FeatureFlags\Observers;

use FilipeFernandes\FeatureFlags\FeatureFlags;
use FilipeFernandes\FeatureFlags\Models\FeatureFlag;
use FilipeFernandes\FeatureFlags\Models\FeatureFlagHistory;
use Illuminate\Support\Facades\Cache;

class FeatureFlagObserver
{
    public function __construct(protected FeatureFlags $service) {}

    public function created(FeatureFlag $featureFlag)
    {
        $this->logChange($featureFlag, 'created');
        $this->service->clearCache();
    }

    public function updated(FeatureFlag $featureFlag)
    {
        $this->logChange($featureFlag, 'updated');
        $this->service->clearCache();
    }

    public function deleted(FeatureFlag $featureFlag)
    {
        $this->logChange($featureFlag, 'deleted');
        $this->service->clearCache();
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
