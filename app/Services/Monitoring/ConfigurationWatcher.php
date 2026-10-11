<?php

namespace App\Services\Monitoring;

use App\Exceptions\InvalidConfigurationException;
use App\Jobs\PublishChangeEvent;
use App\Models\ChangeEvent;
use App\Models\FileSnapshot;
use App\Models\MonitoredFile;
use App\Services\Diff\ConfigurationDiffEngine;
use App\Services\Parsing\ParserResolver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ConfigurationWatcher
{
    public function __construct(
        private readonly ParserResolver $parsers,
        private readonly ConfigurationDiffEngine $diff,
    ) {}

    public function scan(): int
    {
        $created = 0;
        MonitoredFile::where('is_active', true)->each(function (MonitoredFile $file) use (&$created): void {
            try {
                $created += $this->process($file);
            } catch (\Throwable $exception) {
                Log::error('Configuration file check failed', ['file' => $file->path, 'error' => $exception->getMessage()]);
            }
        });
        return $created;
    }

    public function process(MonitoredFile $file): int
    {
        $exists = is_file($file->path);
        if (! $exists) {
            if ($file->last_hash === null) return 0;
            $event = $this->event($file, 'configuration.deleted', ['changes' => ['deleted' => true]]);
            $file->forceFill(['last_hash' => null, 'last_modified_at' => null])->save();
            PublishChangeEvent::dispatch($event->id);
            return 1;
        }

        $mtime = @filemtime($file->path) ?: 0;
        if ($file->last_modified_at?->timestamp === $mtime && $file->last_hash !== null) return 0;
        $hash = hash_file('sha256', $file->path);
        if ($hash === $file->last_hash) {
            $file->forceFill(['last_modified_at' => now()->setTimestamp($mtime)])->save();
            return 0;
        }
        $key = 'config-watch:'.$file->id;
        if (Cache::has($key)) return 0;
        Cache::put($key, true, (int) config('monitoring.debounce_seconds', 2));

        $content = @file_get_contents($file->path);
        if ($content === false) throw new InvalidConfigurationException('Unable to read monitored file.');
        $parsed = $this->parsers->forPath($file->path)->parse($content);
        $previous = $file->snapshots()->latest()->first();

        if (! $previous) {
            FileSnapshot::create(['monitored_file_id' => $file->id, 'hash' => $hash, 'content' => $content, 'parsed_content' => $parsed]);
            $file->forceFill(['last_hash' => $hash, 'last_modified_at' => now()->setTimestamp($mtime)])->save();
            return 0;
        }

        $changes = $this->diff->compare($previous->parsed_content, $parsed);
        FileSnapshot::create(['monitored_file_id' => $file->id, 'hash' => $hash, 'content' => $content, 'parsed_content' => $parsed]);
        $file->forceFill(['last_hash' => $hash, 'last_modified_at' => now()->setTimestamp($mtime)])->save();
        if (collect($changes)->flatten()->isEmpty()) return 0;

        $event = $this->event($file, 'configuration.changed', ['changes' => $changes]);
        PublishChangeEvent::dispatch($event->id);
        Log::info('Configuration change detected', ['event_id' => $event->id, 'file' => $file->path]);
        return 1;
    }

    private function event(MonitoredFile $file, string $type, array $data): ChangeEvent
    {
        return ChangeEvent::create([
            'monitored_file_id' => $file->id,
            'event_type' => $type,
            'payload' => ['event_type' => $type, 'file' => $file->path, 'timestamp' => now()->toIso8601String()] + $data,
            'status' => 'pending',
            'detected_at' => now(),
        ]);
    }
}