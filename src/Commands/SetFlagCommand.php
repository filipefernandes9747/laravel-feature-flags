<?php

namespace FilipeFernandes\FeatureFlags\Commands;

use FilipeFernandes\FeatureFlags\Models\FeatureFlag;
use Illuminate\Console\Command;

class SetFlagCommand extends Command
{
    protected $signature = 'feature-flag:set {key} {enabled=true}';

    protected $description = 'Enable or disable a feature flag';

    public function handle(): int
    {
        $enabled = filter_var($this->argument('enabled'), FILTER_VALIDATE_BOOLEAN);
        FeatureFlag::updateOrCreate(['key' => $this->argument('key')], ['enabled' => $enabled]);
        $this->info("Flag '{$this->argument('key')}' set to: ".($enabled ? 'enabled' : 'disabled'));

        return Command::SUCCESS;
    }
}
