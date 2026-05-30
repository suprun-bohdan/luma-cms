# Luma Studio

React TypeScript admin interface for Luma CMS.

> **Status:** Not scaffolded yet. Directory reserved for Phase 1C (React Studio scaffold).

## Planned stack

- React
- TypeScript
- Vite
- TanStack Query
- Zod
- Tailwind CSS

## Planned structure

```text
apps/studio/
  src/
    api/            # Typed API client
    hooks/          # TanStack Query hooks
    components/     # UI components
    pages/          # Route pages
    schemas/        # Zod form schemas
```

## Rules

- Strict TypeScript; no `any` without documented reason
- Server state via TanStack Query, not global state
- Small, focused components

See [../../docs/architecture.md](../../docs/architecture.md).
