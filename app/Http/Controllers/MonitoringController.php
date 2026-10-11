<?php

namespace App\Http\Controllers;

use App\Jobs\PublishChangeEvent;
use App\Models\ChangeEvent;
use App\Models\DemoWebhookEvent;
use App\Models\MonitoredFile;
use App\Services\Monitoring\PathGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class MonitoringController extends Controller
{
    public function files(): JsonResponse
    {
        return response()->json(MonitoredFile::latest()->get());
    }

    public function events(): JsonResponse
    {
        return response()->json(ChangeEvent::with('monitoredFile')->latest()->paginate(50));
    }

    public function watch(Request $request, PathGuard $guard): JsonResponse
    {
        $path = $request->validate([
            'path' => ['required', 'string'],
        ])['path'];
        $normalized = $guard->normalize($path);
        $name = basename($normalized);
        $extension = strtolower(pathinfo($normalized, PATHINFO_EXTENSION));
        $file = MonitoredFile::updateOrCreate(['path' => $normalized], ['file_type' => $name === '.env' ? 'env' : $extension, 'is_active' => true]);
        return response()->json($file, 201);
    }

    public function unwatch(string $file, PathGuard $guard): JsonResponse
    {
        $normalized = $guard->normalize($file);
        $record = MonitoredFile::where('path', $normalized)->firstOrFail();
        $record->update(['is_active' => false]);
        return response()->json(['status' => 'ok']);
    }

    public function replay(ChangeEvent $event): JsonResponse
    {
        $event->update(['status' => 'pending', 'processed_at' => null]);
        PublishChangeEvent::dispatch($event->id);
        return response()->json(['status' => 'queued', 'event_id' => $event->id]);
    }

    public function retry(ChangeEvent $event): JsonResponse
    {
        return $this->replay($event);
    }

    public function acknowledge(ChangeEvent $event): JsonResponse
    {
        $event->update(['status' => 'acknowledged', 'processed_at' => now()]);
        return response()->json($event);
    }

    public function resolve(ChangeEvent $event): JsonResponse
    {
        $event->update(['status' => 'resolved', 'processed_at' => now()]);
        return response()->json($event);
    }

    public function demoWebhook(Request $request): JsonResponse
    {
        $data = $request->validate(['event_id' => ['required', 'integer'], 'event_type' => ['required', 'string'], 'file' => ['required', 'string'], 'changes' => ['required', 'array'], 'timestamp' => ['required', 'string']]);
        DemoWebhookEvent::create(['event_id' => $data['event_id'], 'payload' => $data]);
        Log::info('Demo webhook received', ['event_id' => $data['event_id']]);
        return response()->json(['status' => 'received', 'event_id' => $data['event_id']]);
    }

    public function metrics(): JsonResponse
    {
        return response()->json([
            'changes_detected' => ChangeEvent::where('event_type', 'configuration.changed')->count(),
            'notifications_sent' => ChangeEvent::where('status', 'processed')->count(),
            'notification_failures' => ChangeEvent::where('status', 'failed')->count(),
            'pending_events' => ChangeEvent::where('status', 'pending')->count(),
        ]);
    }

    public function health(): JsonResponse
    {
        $database = 'ok';
        $redis = config('queue.default') === 'redis' ? 'ok' : 'not_required';
        try { DB::connection()->getPdo(); } catch (\Throwable) { $database = 'unavailable'; }
        if ($redis === 'ok') {
            try { Redis::connection()->ping(); } catch (\Throwable) { $redis = 'unavailable'; }
        }
        $healthy = $database === 'ok' && $redis !== 'unavailable';
        return response()->json(['status' => $healthy ? 'ok' : 'degraded', 'database' => $database, 'redis' => $redis, 'timestamp' => now()->toIso8601String()], $healthy ? 200 : 503);
    }

    public function dashboard()
    {
        return view('dashboard', [
            'files' => MonitoredFile::latest()->get(),
            'events' => ChangeEvent::with('monitoredFile')->latest()->limit(25)->get(),
            'metrics' => ['changes' => ChangeEvent::count(), 'sent' => ChangeEvent::where('status', 'processed')->count(), 'failed' => ChangeEvent::where('status', 'failed')->count(), 'pending' => ChangeEvent::where('status', 'pending')->count()],
        ]);
    }
}