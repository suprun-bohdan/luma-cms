# Architecture

Luma CMS uses a modular architecture with a small core, official modules, and a secure plugin runtime.

> **Status:** Planned architecture. Application code is not yet implemented.

## Conceptual components

| Component | Role |
|-----------|------|
| **Core Kernel** | Boot, config, module registry, permissions, events |
| **Modules** | Official system functionality (Auth, Content, Media, SEO, Plugins, Settings) |
| **Content Engine** | Collections, fields, entries, versions |
| **Media Engine** | Upload, metadata, variants (planned) |
| **SEO Engine** | Metadata resolution (planned) |
| **Plugin Runtime** | Manifest validation, capabilities, extension points, lifecycle |
| **Admin Studio** | React/TypeScript admin interface |
| **Public API** | Versioned REST API for content and admin operations |
| **AI Gateway** | AI provider abstraction (future) |
| **Render Engine** | Public rendering, preview, SSR adapters (future) |

## Core rules

1. **Core does not know about every feature.** Official features live in modules.
2. **Modules provide official system functionality.** They communicate through contracts, not implementation details.
3. **Plugins extend through declared extension points and capabilities.** They do not receive unrestricted access to internals.

## Backend structure

```
apps/api/app/
  Core/           # Kernel, contracts, bootstrapping
  Modules/
    Auth/
    Content/
    Media/
    Seo/
    Plugins/
    Settings/
  Support/        # Shared helpers, base classes
```

### Layering rules

- **Controllers** orchestrate only — no business logic
- **Actions / Services / Policies** contain business logic and authorization
- **Form Requests** validate input
- **API Resources** shape public responses
- **Models / repositories** isolate database access

### Dependency direction

Allowed:

- Core → Contracts
- Modules → Core Contracts
- Modules → their own models and services
- API Controllers → Module Actions

Avoid:

- Content → Plugin internals
- Plugins → Core internals
- Controllers → raw database queries
- Frontend → database assumptions

## Frontend structure

```
apps/studio/      # React + TypeScript + Vite admin interface
```

Stack (planned):

- React, TypeScript, Vite
- TanStack Query for server state
- Zod for form validation
- Tailwind CSS for styling

## Database

PostgreSQL-first design. Flexible content stored in JSON/JSONB-compatible columns with explicit schema defined by collections and fields.

## API

Public routes are versioned from the start:

```
/api/v1/...
```

Internal model structure is never exposed directly. API Resources define the public contract.

## Related documents

- [content-model.md](content-model.md) — data model
- [permissions.md](permissions.md) — authorization
- [extensions.md](extensions.md) — plugin system
- [versioning.md](versioning.md) — version axes
