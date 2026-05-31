# Luma API

Laravel backend for Luma CMS.

> **Status:** Pre-alpha scaffold (Phase 1B). Content Core API not implemented yet.

## Stack

- PHP 8.3+
- Laravel 13
- SQLite (local dev default); PostgreSQL planned for production

## Structure

```text
apps/api/
  app/
    Core/           # Kernel, contracts, bootstrapping
    Modules/
      Auth/
      Content/
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

Versioned public routes:

```
GET /api/v1/health
```

Laravel health check:

```
GET /up
```

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
- API Resources for all public responses (when endpoints exist)

See [../../docs/architecture.md](../../docs/architecture.md).
