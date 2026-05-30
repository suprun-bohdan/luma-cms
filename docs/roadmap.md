# Roadmap

> **Status:** Pre-alpha. Phase 0 complete; Phase 1 next.

## Phase 0: Foundation

**Goal:** Establish the open-source product foundation.

- [x] License (MIT)
- [x] Product documentation
- [x] Monorepo skeleton (`apps/`, `packages/`)
- [x] Contribution and security docs

## Phase 1: Content Core (MVP 0.1)

**Goal:** First usable CMS core.

A developer can define a collection. An editor can create an entry. A client can read published content via the API.

### Features

- Authentication
- Users and roles
- Collections
- Fields
- Entries
- Draft / published status
- Basic SEO metadata
- REST API v1
- Basic React admin studio (Luma Studio)

### API endpoints (planned)

```
GET/POST   /api/v1/collections
GET/PUT/DELETE /api/v1/collections/{collection}
GET/POST   /api/v1/collections/{collection}/fields
GET/POST   /api/v1/collections/{collection}/entries
GET/PUT/DELETE /api/v1/entries/{entry}
```

### Done means

- Backend tests pass
- Admin can create a collection and add fields
- Admin can create, edit, and publish an entry
- Published entry is readable through the public API

## Phase 2: Media Core

- Media upload and metadata
- Image variants
- Alt text
- Storage abstraction

## Phase 3: Extension Foundation

- Plugin manifest (`luma.plugin.json`)
- Plugin lifecycle (discover → install → enable → …)
- Plugin capabilities and extension points
- Audit logs

See [extensions.md](extensions.md).

## Phase 4: Editor

- Rich text editor
- Block-based content
- Preview
- Revisions

## Phase 5: AI Gateway

- Provider abstraction
- Prompt registry
- AI permissions and audit logs
- Usage limits
- Content assistance

## Phase 6: Rendering

- Public content API enhancements
- Preview renderer
- Static rendering
- SSR adapters

## Not now

Do **not** build these before Phase 1 (Content Core) is working:

- Plugin marketplace
- SaaS billing
- Full visual page builder
- Advanced AI agents
- E-commerce
- Multi-tenancy
- GraphQL

Building these prematurely creates architectural debt and distracts from the core content model.
