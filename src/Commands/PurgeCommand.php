<?php

namespace FilipeFernandes\FeatureFlags\Commands;

use Carbon\Carbon;
use FilipeFernandes\FeatureFlags\FeatureFlags;
use FilipeFernandes\FeatureFlags\Models\FeatureFlag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class PurgeCommand extends Command
{
    protected $signature = 'feature-flag:purge
                            {--key=* : Only purge these keys}
                            {--except=* : Skip purging these keys}
                            {--dry-run : Show what would be deleted, but don\'t delete}
                            {--hours= : Only purge flags older than this many hours}
                            {--days= : Only purge flags older than this many days}';

    protected $description = 'Purge feature flags by key, with optional exceptions, dry-run mode, and age filter';

    public function handle(): int
    {
        $key = $this->option('key');
        $except = $this->option('except');
        $dryRun = $this->option('dry-run');
        $hours = $this->option('hours');
        $days = $this->option('days');

        $query = FeatureFlag::query();

        $service = new FeatureFlags;

        if (! empty($key)) {
            $query->whereIn('key', $key);
        }

        if (! empty($except)) {
            $query->whereNotIn('key', $except);
        }

        if (! empty($hours) && is_numeric($hours)) {
            $threshold = Carbon::now()->subHours((int) $hours);
            $query->where('updated_at', '<=', $threshold);
        }

        if (! empty($days) && is_numeric($days)) {
            $threshold = Carbon::now()->subDays((int) $days);
            $query->where('updated_at', '<=', $threshold);
        }

        $flags = $query->get();

        if ($dryRun) {
            if ($flags->isEmpty()) {
                $this->info('No flags would be deleted.');
            } else {
                $this->info('Dry run: the following flags would be deleted:');
                $this->table(['Key', 'Enabled', 'Updated At'], $flags->map(fn($f) => [
                    $f->key,
                    $f->enabled ? 'true' : 'false',
                    $f->updated_at->toDateTimeString(),
                ])->toArray());
            }
        } else {
            $deletedCount = $flags->count();
            FeatureFlag::whereIn('id', $flags->pluck('id'))->delete();
            $service->clearCache();
            $this->info("Deleted {$deletedCount} feature flag(s).");
        }

        return Command::SUCCESS;
    }
}
