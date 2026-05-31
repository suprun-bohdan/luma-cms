# Luma API

Laravel backend for Luma CMS.

> **Status:** Pre-alpha. Content Core + Media Core API with auth/RBAC.

## Stack

- PHP 8.3+
- Laravel 13
- Laravel Sanctum (API tokens)
- SQLite (local dev default); PostgreSQL planned for production

## Structure

```text
apps/api/
  app/
    Core/           # Kernel, contracts, bootstrapping
    Modules/
      Auth/         # Login, logout, me
      Users/        # Roles, permissions, RBAC
      Content/      # Collections, fields, entries
      Media/        # Upload, storage, variants
      Seo/
      Plugins/
      Settings/
    Support/
    Http/Controllers/
  routes/
    api.php         # Versioned API routes
  database/
  tests/
```

## API

Public routes:

```
GET  /api/v1/health
POST /api/v1/auth/login
GET  /api/v1/public/collections/{slug}/entries
GET  /api/v1/public/entries/{id}
GET  /api/v1/public/media/{uuid}
```

Authenticated routes (`Authorization: Bearer {token}`):

```
POST   /api/v1/auth/logout
GET    /api/v1/auth/me
GET    /api/v1/collections
POST   /api/v1/collections
GET    /api/v1/collections/{slug}
PUT    /api/v1/collections/{slug}
DELETE /api/v1/collections/{slug}
GET    /api/v1/collections/{slug}/fields
POST   /api/v1/collections/{slug}/fields
GET    /api/v1/collections/{slug}/fields/{field}
PUT    /api/v1/collections/{slug}/fields/{field}
DELETE /api/v1/collections/{slug}/fields/{field}
GET    /api/v1/collections/{slug}/entries          ?status=draft|published|archived
POST   /api/v1/collections/{slug}/entries
GET    /api/v1/entries/{id}
PUT    /api/v1/entries/{id}
DELETE /api/v1/entries/{id}
POST   /api/v1/entries/{id}/publish
POST   /api/v1/entries/{id}/unpublish
GET    /api/v1/media
POST   /api/v1/media                         multipart: file, optional alt_text
GET    /api/v1/media/{uuid}
PUT    /api/v1/media/{uuid}                  { "alt_text": "..." }
DELETE /api/v1/media/{uuid}
```

Laravel health check:

```
GET /up
```

### Auth (local dev)

After migrate + seed:

```bash
docker compose exec php bash -c "cd apps/api && php artisan migrate && php artisan db:seed"
```

Default admin (override via `.env`):

| Variable | Default |
|----------|---------|
| `LUMA_SEED_ADMIN_EMAIL` | `admin@luma.test` |
| `LUMA_SEED_ADMIN_PASSWORD` | `password` |

Login:

```bash
curl -s -X POST http://localhost:8080/api/v1/auth/login \
  -H 'Content-Type: application/json' \
  -d '{"email":"admin@luma.test","password":"password"}'
```

### RBAC

| Role | Permissions |
|------|-------------|
| `admin` | all content.* and media.* permissions |
| `editor` | content.view/create/update; media.read/upload/update (no delete) |

### Media

Upload (multipart):

```bash
curl -s -X POST http://localhost:8080/api/v1/media \
  -H 'Authorization: Bearer {token}' \
  -F 'file=@/path/to/photo.jpg' \
  -F 'alt_text=Hero image'
```

Public read:

```bash
curl -s http://localhost:8080/api/v1/public/media/{uuid}
```

Storage:

- Files on the `public` disk under `storage/app/public/media/{uuid}/`
- Run once after migrate: `php artisan storage:link`
- Outer Docker workspace mounts `luma-api-storage` volume on `apps/api/storage` for persistent uploads

Config: `config/media.php` (max size, allowed mime types, thumbnail width).

### Fields

Supported field types: `text`, `textarea`, `number`, `boolean`, `datetime`, `json`.

Create field example:

```bash
curl -s -X POST http://localhost:8080/api/v1/collections/pages/fields \
  -H 'Authorization: Bearer {token}' \
  -H 'Content-Type: application/json' \
  -d '{"name":"Title","type":"text","required":true}'
```

Adding, updating (schema-affecting attrs), or deleting a field increments the parent collection `schema_version`.

### Entries

Entry `data` is validated against the collection field schema (strict keys, required fields, type checks).

Create draft entry:

```bash
curl -s -X POST http://localhost:8080/api/v1/collections/pages/entries \
  -H 'Authorization: Bearer {token}' \
  -H 'Content-Type: application/json' \
  -d '{"data":{"title":"Hello"}}'
```

Publish (creates `entry_versions` snapshot with current `schema_version`):

```bash
curl -s -X POST http://localhost:8080/api/v1/entries/1/publish \
  -H 'Authorization: Bearer {token}'
```

Public read (published entries only, no auth):

```bash
curl -s http://localhost:8080/api/v1/public/collections/pages/entries
```

Note: single-entry JSON responses return fields at the root (no outer `data` wrapper) because the payload attribute is also named `data`. List responses use a standard `data` array wrapper.

## CI

On push/PR to `main`, PHP 8.4 build and tests run automatically:

- **GitHub Actions:** `.github/workflows/php.yml`
- **GitLab CI:** `.gitlab-ci.yml`

Steps: `composer validate` → `composer install` → Laravel `.env` bootstrap → `php artisan test` (SQLite in-memory).

## Local development

From the outer dev workspace (Docker):

```bash
make up
# API: http://localhost:8080/api/v1/health
```

Run tests inside the PHP container:

```bash
docker compose exec php bash -c "cd apps/api && php artisan test"
```

## Rules

- Thin controllers; business logic in Actions, Services, Policies
- Public API uses `/api/v1/` prefix
- API Resources for all public responses
- SQLite is source of truth; policies enforce RBAC permissions
