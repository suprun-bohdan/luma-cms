# Luma Studio

React TypeScript admin interface for Luma CMS.

> **Status:** Pre-alpha scaffold (Phase 1C). Content workflows not implemented yet.

## Stack

- React 19
- TypeScript
- Vite
- TanStack Query
- Zod
- Tailwind CSS
- React Router

## Structure

```text
apps/studio/
  src/
    api/            # Typed API client
    hooks/          # TanStack Query hooks
    components/     # UI components
    pages/          # Route pages
    schemas/        # Zod validation schemas
```

## Local development

Requires the Laravel API running (outer Docker workspace on port 8080):

```bash
cd apps/studio
npm install
npm run dev
```

Open http://localhost:5173 — the dev server proxies `/api` to the backend.

Optional env:

```bash
# .env.local
VITE_API_BASE_URL=
```

Leave empty to use the Vite proxy (recommended for local dev).

## Build

```bash
npm run build
```

## Rules

- Strict TypeScript
- Server state via TanStack Query
- Zod for API response validation
- No decorative dashboard widgets before core content workflows exist
