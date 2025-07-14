<?php

namespace FilipeFernandes\FeatureFlags\Actions;

use FilipeFernandes\FeatureFlags\Models\FeatureFlag;

class ToggleFlag
{
    private FeatureFlag $flag;
    private array $settings;


    public function handle(array $data): bool
    {
        $this->settings = $data;
        // Get or create the feature flag from the DB
        $this->setFlag();

        // Step 1: Check if the flag exists in the config and prefill environments if needed
        $this->prefillFlag();

        // Step 2: Apply the new toggle from the request
        $this->applyToggle();

        // Step 3: Save metadata
        $this->saveMetadata();

        return $this->flag->save();
    }

    private function setFlag(): void
    {
        $this->flag = FeatureFlag::firstOrNew(['key' => $this->settings['key']]);
    }


    private function prefillFlag(): void
    {
        $key = $this->settings['key'];
        $configFlags = config('feature-flags.flags', []);
        if (!$this->flag->exists && isset($configFlags[$key]['enabled']) && is_array($configFlags[$key]['enabled'])) {
            // Replicate environments from config
            $this->flag->environments = $configFlags[$key]['enabled'];
            $this->flag->enabled = collect($this->flag->environments)->contains(true);
        }
    }

    private function applyToggle(): void
    {
        $environment = $this->settings['environment'] ?? null;
        $enabled = $this->settings['enabled'];
        if ($environment) {
            $environments = $this->flag->environments ?? [];
            if (!is_array($environments)) {
                $environments = json_decode($environments, true) ?? [];
            }

            $environments[$environment] = $enabled;
            $this->flag->environments = $environments;
            $this->flag->enabled = !in_array(false, $environments, true);
        } else {
            $this->flag->enabled = (bool) $enabled;
        }
    }

    private function saveMetadata(): void
    {
        $metadata = $this->settings['metadata'] ?? [];
        $this->flag->metadata = $metadata;
    }
}
