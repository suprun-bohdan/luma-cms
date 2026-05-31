# Content Model

Luma CMS stores content as **structured data**, not raw HTML. HTML is a render output, not the source of truth.

> **Status:** Planned model. Tables and API are not yet implemented.

## Concepts

| Term | Definition |
|------|------------|
| **Collection** | A content type (e.g. posts, pages, products, events) |
| **Field** | A schema field inside a collection (e.g. title, body, slug) |
| **Entry** | A content item created from a collection |
| **Entry Version** | A historical snapshot of an entry |

## MVP tables

Phase 1 (Content Core) introduces four tables:

### collections

| Column | Description |
|--------|-------------|
| id | Primary key |
| name | Human-readable name |
| slug | URL-safe identifier |
| description | Optional description |
| config | JSON configuration |
| schema_version | Content schema version for this collection |
| created_at, updated_at | Timestamps |

### fields

| Column | Description |
|--------|-------------|
| id | Primary key |
| collection_id | Foreign key to collections |
| name | Human-readable name |
| slug | Field identifier within collection |
| type | Field type (text, rich_text, number, etc.) |
| config | JSON field configuration |
| sort_order | Display order |
| required | Whether the field is required |
| created_at, updated_at | Timestamps |

### entries

| Column | Description |
|--------|-------------|
| id | Primary key |
| collection_id | Foreign key to collections |
| status | draft, published, archived (enum) |
| data | JSON structured content |
| created_by, updated_by | User references |
| published_at | Publication timestamp |
| created_at, updated_at | Timestamps |

### entry_versions

Historical snapshots of entry data for revision tracking.

## Design decisions

### Structured data, not raw HTML

Entries store JSON in the `data` column. The schema is defined by the collection's fields. Rendering layers produce HTML or other formats from structured data.

### Draft and published from day one

Every entry has a status. Publishing sets `published_at` and makes the entry available through the public API.

### Schema versioning from day one

Each collection carries a `schema_version`. When field definitions change, versioning allows safe migration of existing entries.

### Visual builder comes later

A full visual page builder is **not** part of MVP 0.1. Basic collection, field, and entry CRUD must work first.

## Related documents

- [architecture.md](architecture.md)
- [permissions.md](permissions.md) — collection-level access control (planned)
- [versioning.md](versioning.md) — content schema versioning
