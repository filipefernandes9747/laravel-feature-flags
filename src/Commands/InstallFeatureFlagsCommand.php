<?php

namespace FilipeFernandes\FeatureFlags\Commands;

use Illuminate\Console\Command;

class InstallFeatureFlagsCommand extends Command
{
    protected $signature = 'feature-flag:install {--migrate : Run the database migrations} {--force}';

    protected $description = 'Install the Feature Flags package (publish config, migrations, etc)';

    public function handle(): int
    {
        $this->info('🔧 Installing Feature Flags...');

        $this->callSilent('vendor:publish', [
            '--tag' => 'feature-flags-config',
            '--force' => $this->option('force'),
        ]);
        $this->info('✅ Config published');

        $this->callSilent('vendor:publish', [
            '--tag' => 'feature-flags-migrations',
            '--force' => $this->option('force'),
        ]);
        $this->info('✅ Migrations published');

        $this->callSilent('vendor:publish', [
            '--tag' => 'feature-flags-public',
            '--force' => $this->option('force'),
        ]);
        $this->callSilent('vendor:publish', [
            '--tag' => 'feature-flags-views',
            '--force' => $this->option('force'),
        ]);
        $this->info('✅ Resources published');

        if ($this->option('migrate')) {
            $this->call('migrate');
        }

        $this->info('🎉 Feature Flags installed successfully.');

        return self::SUCCESS;
    }
}
