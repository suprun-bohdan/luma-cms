# Luma Studio

React TypeScript admin interface for Luma CMS.

> **Status:** Phase 2 Studio Core + Media + Pages + Navigation + Phase 3.2 Business Website Kit polish.

## Design system

See [docs/studio-design-system.md](./docs/studio-design-system.md) for tokens, layout primitives, UI kit, and migration rules.

```text
shared/
  components/   # UI primitives (Button, Input, Table, …)
  layout/       # AdminShell, ListPage, FormPage, SplitPane, …
styles/
  _tokens.scss  # SCSS design tokens
  _theme.scss   # :root CSS custom properties
```

## Stack

- React 19
- TypeScript
- Vite
- TanStack Query
- Zod
- Tailwind CSS
- SCSS (Sass) for tokens, theming, and component styles
- React Router

## Structure

```text
apps/studio/src/
  app/              # App shell, router, query client
  shared/           # API client, auth, layout, UI primitives, hooks
  features/         # auth, collections, fields, entries, preview, media, pages, navigation
  pages/            # Dashboard, NotFound
  styles/           # SCSS entry, tokens (extend for themes)
```

## Auth

- Bearer token auth against `POST /api/v1/auth/login`
- Session stored in `sessionStorage` (token + user)
- Protected routes redirect to `/login` when unauthenticated
- Dev credentials: `admin@luma.test` / `password` (see `apps/api/README.md`)

## Routes

| Route | Description |
|-------|-------------|
| `/login` | Sign in |
| `/dashboard` | Overview + API health |
| `/collections` | List collections |
| `/collections/new` | Create collection |
| `/collections/:slug/edit` | Edit collection |
| `/collections/:slug/fields` | Field builder |
| `/collections/:slug/entries` | Entry list + filters |
| `/collections/:slug/entries/new` | Create entry |
| `/entries/:id/edit` | Edit entry |
| `/entries/:id/preview` | Admin + public preview |
| `/media` | Media library (upload, alt text, delete) |
| `/pages` | Pages list |
| `/pages/new` | Create page (block templates + SEO preview) |
| `/pages/:slug/edit` | Edit page, publish, view public |
| `/menus` | Navigation hub (header + footer) |
| `/menus/footer` | Footer menu editor |
| `/seo/redirects` | URL redirects list |
| `/seo/redirects/new` | Create redirect |

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

Leave empty to use the Vite proxy (recommended for local dev). For non-proxy deployments, set the full API origin.

## Build

```bash
npm run build
npm run lint
```

## Manual smoke test

1. Sign in at `/login` with dev credentials
2. Upload an image at `/media`
3. Create a page at `/pages/new` — use block template buttons, fill SEO fields, check SEO preview pane
4. Save draft, publish, open public URL `/p/{slug}` (via API origin or Studio link)
5. Edit header menu at `/menus/header` — link to your page slug
6. Optional: run demo seeder (`DemoSiteSeeder`) and open `/p/home`
7. Sign out — protected routes redirect to login; API returns 401 without token

## Styling

- **Tailwind CSS** — utility classes in React components (primary)
- **SCSS** — `src/styles/` for design tokens, global rules, future themes
- Tokens: `src/styles/_tokens.scss` (also loadable via `@use 'tokens' as *` in component `.scss` files)
- Vite alias: `@styles` → `src/styles`

## Rules

- Strict TypeScript
- Server state via TanStack Query
- Zod for API response validation
- Minimal shared Tailwind components (no shadcn on MVP)
