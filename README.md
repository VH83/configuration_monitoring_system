<<<<<<< HEAD
# SyncWorks Configuration Change Monitor

SyncWorks watches `.env`, JSON, YAML/YML, and Java properties files, stores valid snapshots, calculates structured dot-notation diffs, and delivers durable change events through a queue and webhook.

## Flow and architecture

`config:watch` checks active files, debounces rapid writes, safely reads and parses them, stores a snapshot, creates a `ChangeEvent` with structured added/removed/updated/type-change data, and dispatches `PublishChangeEvent`. The worker calls `WebhookNotifier`; successful events become `processed`, while failed jobs use 5, 15, 30, 60, and 120 second backoffs and are retained in `failed_jobs`.

See [docs/architecture.md](docs/architecture.md) for the component diagram.

## Local setup

```powershell
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
```

For the included SQLite demo, set `DB_CONNECTION=sqlite`, `DB_DATABASE=database/database.sqlite`, `QUEUE_CONNECTION=database`, `WATCH_ROOT=watched`, and `WEBHOOK_URL=http://127.0.0.1:8000/api/demo/webhook`.

Start the application, worker, and watcher in separate terminals:

```powershell
php artisan serve
php artisan queue:work database --tries=5 --timeout=30
php artisan config:watch
```

Use `php artisan config:watch --once` for a single scan. The dashboard is at `http://127.0.0.1:8000/`.

## Demo

1. Add `watched/.env` from the dashboard or `POST /api/watch`.
2. Run one watcher scan to create the initial snapshot.
3. Change `APP_ENV=local` to `APP_ENV=production` and `APP_DEBUG=true` to `APP_DEBUG=false`.
4. Run the watcher continuously or once. A change event is queued and delivered to the local demo webhook.
5. Refresh the dashboard to see old and new values, event status, and delivery time.
6. Set an unavailable `WEBHOOK_URL` to demonstrate retries and `failed_jobs`, then restore it and use the event Retry button or `POST /api/events/{event}/retry`.

## API

`GET /api/health` checks the application database and checks Redis when Redis is the active queue driver. `GET /api/files`, `GET /api/events`, and `GET /api/metrics` expose monitoring state. `POST /api/watch` accepts `{ "path": "watched/.env" }`; `DELETE /api/watch/{file}` stops monitoring. `POST /api/demo/webhook` is the local receiver. Event operations are available at `POST /api/events/{event}/replay`, `/retry`, `/acknowledge`, and `/resolve`.

## Data model

`monitored_files` stores the allowlisted path and current hash. `file_snapshots` stores every valid content and parsed representation. `change_events` stores durable audit events and delivery status. `demo_webhook_events` stores received demo payloads. Laravel's `jobs` and `failed_jobs` tables provide queue durability and dead-letter handling.

## Security and operations

Paths are normalized and restricted to `WATCH_ROOT`; traversal, system paths, unsupported extensions, and hidden files other than `.env` are rejected. Tokens and values are never written to logs. Webhook authentication uses `WEBHOOK_TOKEN`. API payloads are validated by Laravel. Configure an internal or allowlisted webhook URL in production to reduce SSRF risk.

Metrics cover detected changes, delivered notifications, failures, and pending events. Hashes and modification times avoid unnecessary parsing, while database indexes and independent watcher/worker processes support larger monitored sets.

## Tests and Docker

```powershell
php artisan test
php artisan route:list
docker compose config
docker compose up -d
```

Docker runs the application, MySQL, Redis, queue worker, and watcher, with `./watched` mounted into the watcher and application. `docker compose logs -f worker watcher` shows operational progress.

## Trade-offs and future work

The watcher uses portable polling instead of OS-specific filesystem events, which is simple and container-friendly but adds polling latency. Future improvements could add filesystem notifications, authentication and authorization for management APIs, webhook URL allowlists, encrypted snapshot content, and Prometheus/OpenTelemetry exporters.
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
=======
# configuration_monitoring_system
>>>>>>> d763f4c02ecb5fe5f313166df47023a4d4c9f4a3
