# Backup and restore

Production stack uses **PostgreSQL** as the source of truth and **local disk** for media.

## Backup bundle

1. **Database** — `pg_dump` of the `luma` database
2. **Storage** — tar archive of `apps/api/storage/app` (includes `public/` symlink target content)
3. **Secrets** — export `luma-cms/apps/api/.env` separately (never commit to git)

## Make helpers (outer workspace)

From the LumaCMS workspace root:

```bash
make backup
```

Creates timestamped files under `backups/`:

- `luma-YYYYMMDD-HHMMSS.sql`
- `luma-storage-YYYYMMDD-HHMMSS.tar.gz`
- Symlinks `backups/latest.sql` and `backups/latest-storage.tar.gz`

Requires prod profile running (`make prod-setup` or `make prod-up`).

## Manual backup

```bash
docker compose --profile prod exec -T postgres \
  pg_dump -U luma luma > backups/luma-manual.sql

tar -czf backups/luma-storage-manual.tar.gz -C luma-cms/apps/api storage/app
```

Copy `.env` to a secure location outside the repo.

## Restore

1. Start prod stack with empty or target volumes
2. Restore database, then storage, then env secrets
3. Run migrations if schema drifted since backup

```bash
make restore BACKUP=backups/luma-YYYYMMDD-HHMMSS.sql
```

The Makefile restores matching `*-storage.tar.gz` when present and runs `migrate --force`.

4. Restart workers:

```bash
docker compose restart queue scheduler php
```

## Upgrade path

After pulling new code:

```bash
docker compose --profile prod up -d --build
docker compose exec php bash -c "cd apps/api && php artisan migrate --force"
docker compose restart queue scheduler
```
