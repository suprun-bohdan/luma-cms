# Luma CMS

Open-source, AI-native CMS with structured content, visual editing (planned), and a clean modular architecture.

> **Status: Pre-alpha.** Not production-ready.

## What is Luma CMS?

Luma CMS is a modular content platform for developers, editors, agencies, and content teams.

It is **not** a WordPress clone. The goal is a small core, strong module boundaries, secure extensions, and developer-first APIs — not plugin chaos.

## Core principles

- **Small core** — official features live in modules
- **Structured content** — collections, fields, and entries; not raw HTML as source of truth
- **Modular monolith** — clear boundaries between Core, modules, API, and Studio
- **Secure extensions** — plugins declare capabilities; default deny
- **Versioned API** — public REST under `/api/v1/`
- **Engineering discipline** — thin controllers, Actions, Policies, Form Requests, API Resources

## Current progress

| Area | Status |
|------|--------|
| Monorepo layout (`apps/`, `packages/`) | Done |
| Laravel API (`apps/api/`) | Scaffold + Content module started |
| Collections API | Done |
| Fields / Entries API | Planned |
| Auth (Sanctum) | Planned |
| React Studio (`apps/studio/`) | Scaffold + API health check |
| Plugin system | Planned |

### Available API (pre-alpha)

```http
GET  /api/v1/health
GET  /api/v1/collections
POST /api/v1/collections
GET  /api/v1/collections/{slug}
PUT  /api/v1/collections/{slug}
DELETE /api/v1/collections/{slug}
```

## Architecture (target)

```text
Core Kernel
  → Modules (Auth, Content, Media, SEO, Plugins, Settings, …)
  → Plugin runtime (capabilities + extension points)
  → Luma Studio (React / TypeScript)
  → Public API (/api/v1/)
  → Future: AI Gateway, Render Engine
```

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
```

## Local development

This repository is the product source. Some contributors use an optional outer workspace with Docker, Nginx, and Makefile for local orchestration — that layer is not required to work on the CMS code.

**API** (with outer Docker stack on port 8080):

```bash
curl http://localhost:8080/api/v1/health
curl http://localhost:8080/api/v1/collections
```

**API tests:**

```bash
docker compose exec php bash -c "cd apps/api && php artisan test"
```

**Studio:**

```bash
cd apps/studio
npm install
npm run dev
```

Open http://localhost:5173 (proxies `/api` to the backend when Docker is running).

See [apps/api/README.md](apps/api/README.md) and [apps/studio/README.md](apps/studio/README.md) for app-specific details.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md), [SECURITY.md](SECURITY.md), and [CODE_OF_CONDUCT.md](CODE_OF_CONDUCT.md).

## License

[MIT License](LICENSE)
