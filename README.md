# Luma CMS

Open-source CMS for developers, agencies, and SMB — structured content, clean Laravel architecture, and a modern admin studio (in progress).

> **Status: Pre-alpha.** Phase 1 Content Core API is implemented; Studio UI and extensions are not ready for production.

[![PHP](https://github.com/suprun-bohdan/luma-cms/actions/workflows/php.yml/badge.svg)](https://github.com/suprun-bohdan/luma-cms/actions/workflows/php.yml)

## What is Luma CMS?

Luma CMS is a modular content platform — **not** a WordPress clone. Small core, structured content (collections → fields → entries), secure extensions, and developer-first APIs.

**Target wedge:** fast business websites with structured content, visual editing (planned), integrations, and clean extensibility.

## Core principles

- **Small core** — official features live in modules
- **Structured content** — collections, fields, entries; not raw HTML as source of truth
- **Modular monolith** — Core, modules, versioned API, Luma Studio
- **Secure extensions** — RBAC + plugin capabilities (plugins planned); default deny
- **Versioned API** — REST under `/api/v1/`
- **Engineering discipline** — Actions, Policies, Form Requests, API Resources, feature tests

## Current progress

| Area | Status |
|------|--------|
| Monorepo (`apps/`, `packages/`) | Done |
| Laravel API — auth (Sanctum + RBAC) | Done |
| Collections / Fields / Entries API | Done |
| Draft / publish + public read API | Done |
| CI (GitHub Actions + GitLab CI) | Done |
| React Studio (`apps/studio/`) | Scaffold + health check only |
| Studio content UI (Phase 2) | Planned |
| Plugin system | Planned |

Details: [CHANGELOG.md](CHANGELOG.md) · [apps/api/README.md](apps/api/README.md)

## API overview (pre-alpha)

Public (no auth):

```http
GET  /api/v1/health
POST /api/v1/auth/login
GET  /api/v1/public/collections/{slug}/entries
GET  /api/v1/public/entries/{id}
```

Authenticated (`Authorization: Bearer {token}`) — collections, fields, entries CRUD, publish/unpublish. See [apps/api/README.md](apps/api/README.md) for the full list and examples.

## Repository layout

```text
luma-cms/
  apps/
    api/          # Laravel 13 backend
    studio/       # React + TypeScript admin
  packages/
    sdk/          # Public TypeScript SDK (planned)
    plugin-sdk/   # Plugin development kit (planned)
    ui/           # Shared UI components (planned)
  .github/        # GitHub Actions (PHP build & test)
  .gitlab-ci.yml  # GitLab CI (PHP build & test)
```

## Local development

This repo is the product source. An optional outer workspace with Docker/Nginx may be used locally — not required to hack on the code.

**API** (with Docker on port 8080):

```bash
# migrate + seed (first time)
docker compose exec php bash -c "cd apps/api && php artisan migrate && php artisan db:seed"

curl http://localhost:8080/api/v1/health
```

**Tests:**

```bash
docker compose exec php bash -c "cd apps/api && php artisan test"
```

CI runs the same test suite on push/PR (PHP 8.4, Composer, SQLite in-memory).

**Studio:**

```bash
cd apps/studio && npm install && npm run dev
```

Open http://localhost:5173 (proxies `/api` when the backend is running).

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md), [SECURITY.md](SECURITY.md), and [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md).

## License

[MIT License](LICENSE)
