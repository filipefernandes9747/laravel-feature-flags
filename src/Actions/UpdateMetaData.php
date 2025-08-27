<?php

namespace FilipeFernandes\FeatureFlags\Actions;

use FilipeFernandes\FeatureFlags\Models\FeatureFlag;

class UpdateMetaData
{
    public function handle(string|FeatureFlag $key, array $data): bool
    {
        $flag = $this->setFlag($key);

        return (bool) $flag->update([
            'metadata' => $data
        ]);
    }

    private function setFlag(string|FeatureFlag $flag): FeatureFlag
    {
        return ($flag instanceof FeatureFlag) ? $flag : FeatureFlag::where(['key' => $flag])->firstOrFail();
    }
}
