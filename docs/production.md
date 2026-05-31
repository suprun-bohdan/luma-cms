# Production deployment

## Document root

Point the web server at `apps/api/public`. Nginx/Apache samples live in `deploy/nginx/` and `deploy/apache/`.

Studio static assets must be available under `/admin/` (built with `VITE_BASE_PATH=/admin/`). Legacy `/studio/` URLs should redirect to `/admin/`.

## Environment

- `APP_ENV=production`, `APP_DEBUG=false`
- Strong `APP_KEY`; never commit `.env`
- Database: MySQL/MariaDB or PostgreSQL for production; SQLite is acceptable for small single-node installs
- `QUEUE_CONNECTION=database` (or Redis when you add it later)
- Run queue worker and scheduler for webhooks and media jobs

## File permissions

- `storage/` and `bootstrap/cache/` writable by PHP
- `.env` not world-readable
- Do not expose `vendor/`, `apps/studio/src/`, or git metadata

## Updates

1. Back up database and `storage/`.
2. Replace application files from a new release zip; keep `.env` and `storage/`.
3. Run `php artisan luma:update` or use **Luma Studio → Settings → Updates** as **owner**.

## Cron (shared hosting)

```cron
* * * * * cd /path/to/apps/api && php artisan schedule:run >> /dev/null 2>&1
```

If your host does not run a persistent queue worker, configure a cron or panel job to process the queue.

## Hardening

See [security.md](./security.md) for installer tokens, RBAC, plugins, and webhooks.
