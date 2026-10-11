<?php

namespace App\Services\Notification;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class WebhookNotifier
{
    public function send(array $payload): void
    {
        $url = config('monitoring.webhook_url');
        if (! $url) {
            throw new RuntimeException('WEBHOOK_URL is not configured.');
        }

        $request = Http::timeout((int) config('monitoring.webhook_timeout', 10))
            ->acceptJson()
            ->asJson();
        $token = config('monitoring.webhook_token');
        if ($token) {
            $request = $request->withToken($token);
        }

        $response = $request->post($url, $payload);
        if ($response->failed()) {
            Log::warning('Configuration webhook failed', ['event_id' => $payload['event_id'] ?? null, 'status' => $response->status()]);
            throw new RuntimeException('Webhook returned HTTP '.$response->status());
        }

        Log::info('Configuration webhook delivered', ['event_id' => $payload['event_id'] ?? null]);
    }
}