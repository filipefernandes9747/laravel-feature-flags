<?php

namespace FilipeFernandes\FeatureFlags\Actions;

use FilipeFernandes\FeatureFlags\Models\FeatureFlag;

class DeleteFlag
{
    public function handle(string|FeatureFlag $key): bool
    {
        $flag = $this->setFlag($key);

        return (bool) $flag->delete();
    }

    private function setFlag(string|FeatureFlag $flag): FeatureFlag
    {
        return ($flag instanceof FeatureFlag) ? $flag : FeatureFlag::where(['key' => $flag])->firstOrFail();
    }
}
