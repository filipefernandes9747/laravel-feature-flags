<?php

namespace FilipeFernandes\FeatureFlags\Commands;

use Illuminate\Console\Command;

class InstallFeatureFlagsCommand extends Command
{
    protected $signature = 'feature:install {--migrate : Run the database migrations}';

    protected $description = 'Install the Feature Flags package (publish config, migrations, etc)';

    public function handle(): int
    {
        $this->info('🔧 Installing Feature Flags...');

        $this->callSilent('vendor:publish', [
            '--tag' => 'feature-flags-config',
            '--force' => true,
        ]);
        $this->info('✅ Config published');

        $this->callSilent('vendor:publish', [
            '--tag' => 'feature-flags-migrations',
            '--force' => true,
        ]);
        $this->info('✅ Migrations published');

        if ($this->option('migrate')) {
            $this->call('migrate');
        }

        $this->info('🎉 Feature Flags installed successfully.');

        return self::SUCCESS;
    }
}
