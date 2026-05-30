# Luma API

Laravel backend for Luma CMS.

> **Status:** Not scaffolded yet. Directory reserved for Phase 1B (Laravel API scaffold).

## Planned stack

- PHP 8.3+
- Laravel
- PostgreSQL

## Planned structure

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
  routes/
  database/
  tests/
```

## Rules

- Thin controllers; business logic in Actions, Services, Policies
- Versioned public API: `/api/v1/...`
- API Resources for all public responses

See [../../docs/architecture.md](../../docs/architecture.md).
