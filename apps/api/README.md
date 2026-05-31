# Luma API

Laravel backend for Luma CMS.

> **Status:** Pre-alpha (Phase 1D). Collections and Fields CRUD API with Sanctum auth and RBAC.

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
      Media/
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
| `admin` | content.view, content.create, content.update, content.delete |
| `editor` | content.view, content.create, content.update |

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

See [../../docs/architecture.md](../../docs/architecture.md).
