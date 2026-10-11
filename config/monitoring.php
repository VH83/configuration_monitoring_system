<?php

return [
    'root' => env('WATCH_ROOT', base_path('watched')),
    'debounce_seconds' => (int) env('WATCH_DEBOUNCE_SECONDS', 2),
    'poll_seconds' => (int) env('WATCH_POLL_SECONDS', 2),
    'webhook_url' => env('WEBHOOK_URL'),
    'webhook_token' => env('WEBHOOK_TOKEN'),
    'webhook_timeout' => (int) env('WEBHOOK_TIMEOUT', 10),
];