# Luma CMS

Open-source AI-native CMS with visual editing, structured content, and clean modular architecture.

> **Status: Pre-alpha.** Not production-ready. No runnable release is available yet.

## What is Luma CMS?

Luma CMS is a modular content platform for developers, editors, agencies, and content teams. It focuses on structured content, secure extensibility, developer-first APIs, and AI-assisted workflows — without copying legacy CMS patterns or plugin chaos.

Luma CMS is **not** a WordPress clone.

## Core principles

- **Small core** — minimal kernel, official features as modules
- **Structured content** — collections, fields, and entries; not raw HTML as source of truth
- **Clean module boundaries** — thin controllers, business logic in Actions/Services/Policies
- **Secure extensions** — plugins declare capabilities; default deny
- **Typed APIs** — versioned REST from the start
- **Developer-first** — Laravel backend, React TypeScript admin studio

## MVP roadmap

The first milestone is **Content Core (MVP 0.1)**: authentication, users, roles, collections, fields, entries, draft/published status, basic SEO metadata, REST API v1, and a basic admin studio.

See [docs/roadmap.md](docs/roadmap.md) for the full phased plan.

## Planned architecture

```
Core Kernel → Modules (Auth, Content, Media, SEO, Plugins, Settings)
           → Plugin Runtime (capabilities + extension points)
           → Admin Studio (React/TypeScript)
           → Public API (/api/v1/)
           → Future: AI Gateway, Render Engine
```

Details: [docs/architecture.md](docs/architecture.md)

## Repository structure

This repository is the clean open-source product. Planned layout:

```text
luma-cms/
  apps/
    api/          # Laravel backend
    studio/       # React TypeScript admin
  packages/
    sdk/          # Public TypeScript SDK
    plugin-sdk/   # Plugin development kit
    ui/           # Shared UI components
  docs/           # Public documentation
```

## Development status

Early development. Laravel API scaffold is available at `apps/api/`. React Studio is not yet created.

Installation instructions will be added once the first runnable development version is available.

Contributors: see [CONTRIBUTING.md](CONTRIBUTING.md) and [docs/development.md](docs/development.md).

## Documentation

| Document | Description |
|----------|-------------|
| [docs/vision.md](docs/vision.md) | Product vision and goals |
| [docs/architecture.md](docs/architecture.md) | System architecture |
| [docs/roadmap.md](docs/roadmap.md) | Phased development plan |
| [docs/content-model.md](docs/content-model.md) | Collections, fields, entries |
| [docs/permissions.md](docs/permissions.md) | Permission model |
| [docs/extensions.md](docs/extensions.md) | Plugin system (planned) |
| [docs/versioning.md](docs/versioning.md) | Versioning strategy |
| [docs/security.md](docs/security.md) | Security principles |
| [docs/development.md](docs/development.md) | Contributor workflow |

## License

[MIT License](LICENSE)
