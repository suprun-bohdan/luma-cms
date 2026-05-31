# Production storage

Luma CMS stores uploads, logs, and framework files on disk by default.

## Default layout

| Path | Purpose |
|------|---------|
| `apps/api/storage/app` | Private uploads (media originals, variants) |
| `apps/api/storage/app/public` | Public files served via `/storage/*` after `storage:link` |
| `apps/api/storage/logs` | Application logs |
| `apps/api/storage/framework` | Cache, sessions, compiled views |

## Docker volumes (prod profile)

The outer `docker-compose.yml` mounts a named volume:

```text
luma-api-storage → /var/www/html/apps/api/storage
```

PostgreSQL data uses `luma-postgres-data`.

## Media

- Default driver: `FILESYSTEM_DISK=local`
- Thumbnails and variants are written under `storage/app/media/`
- Backup must include `storage/app` (see [backup-restore.md](backup-restore.md))

## S3 (documented only)

S3-compatible object storage is **not implemented** in the production MVP. When added, configure:

```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false
```

Until then, keep local disk and include `storage/app` in backups.

## Plugins

Plugins are loaded from the repo `plugins/` directory. Override path:

```env
LUMA_PLUGINS_PATH=/var/www/html/plugins
```

In Docker prod, the codebase is mounted read-only; keep plugins in the image or bind-mount `plugins/` read-only if you ship custom plugins.

## Permissions

The PHP entrypoint ensures `storage/` and `bootstrap/cache/` are writable by `www-data`. After restore or manual file copy, run:

```bash
docker compose exec php bash -c "cd apps/api && php artisan storage:link"
```
