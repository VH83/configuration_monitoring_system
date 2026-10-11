<?php

namespace App\Jobs;

use App\Models\ChangeEvent;
use App\Services\Notification\WebhookNotifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class PublishChangeEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    public int $tries = 5;
    public int $timeout = 30;

    public function __construct(public readonly int $eventId) {}

    public function backoff(): array
    {
        return [5, 15, 30, 60, 120];
    }

    public function handle(WebhookNotifier $notifier): void
    {
        $event = ChangeEvent::findOrFail($this->eventId);
        $notifier->send($event->payload + ['event_id' => $event->id]);
        $event->forceFill(['status' => 'processed', 'processed_at' => now()])->save();
    }

    public function failed(Throwable $exception): void
    {
        ChangeEvent::whereKey($this->eventId)->update(['status' => 'failed']);
        Log::error('Configuration webhook permanently failed', ['event_id' => $this->eventId, 'error' => $exception->getMessage()]);
    }
}