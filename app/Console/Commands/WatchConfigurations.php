<?php

namespace App\Console\Commands;

use App\Services\Monitoring\ConfigurationWatcher;
use Illuminate\Console\Command;

class WatchConfigurations extends Command
{
    protected $signature = 'config:watch {--once : Run one scan and exit}';
    protected $description = 'Continuously monitor active configuration files.';

    public function handle(ConfigurationWatcher $watcher): int
    {
        do {
            $count = $watcher->scan();
            if ($count > 0) $this->info("Created {$count} change event(s).");
            if ($this->option('once')) break;
            sleep((int) config('monitoring.poll_seconds', 2));
        } while (true);
        return self::SUCCESS;
    }
}