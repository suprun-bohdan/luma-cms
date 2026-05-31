# Luma Studio

React TypeScript admin interface for Luma CMS.

> **Status:** Pre-alpha `[0.0.25-rc.1]`. Content admin, visual pages, plugins, integrations, web setup wizard, first-run onboarding, site settings, and release updater UI.

[![Studio CI](https://github.com/suprun-bohdan/luma-cms/actions/workflows/studio.yml/badge.svg)](https://github.com/suprun-bohdan/luma-cms/actions/workflows/studio.yml)

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
  app/              # Router, auth layout, onboarding gate
  shared/           # API client, auth, layout, UI primitives, hooks
  features/         # auth, collections, pages, media, forms, plugins, setup, onboarding, settings, updates, …
  pages/            # Dashboard, NotFound
  styles/           # SCSS entry, tokens (extend for themes)
```

## Auth

- Bearer token auth against `POST /api/v1/auth/login`
- Session stored in `sessionStorage` (token + user)
- Protected routes redirect to `/login` when unauthenticated
- First login may redirect to `/onboarding` until completed (skip locally with `LUMA_SKIP_ONBOARDING=true` on the API)
- Dev credentials: `admin@luma.test` / `password` (see `apps/api/README.md`)

## Routes

| Route | Description |
|-------|-------------|
| `/setup` | Technical web installer (database, admin user — before app is installed) |
| `/login` | Sign in |
| `/onboarding` | First-run product wizard after login (site profile, starter preset) |
| `/dashboard` | Overview, API health, setup history |
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
| `/pages/new` | Create page (section templates, blocks, live preview before save) |
| `/pages/:slug/edit` | Visual page editor (drag-and-drop blocks, preview, inspector, plugin blocks from API) |
| `/menus` | Navigation hub (header + footer) |
| `/forms` | Forms list |
| `/forms/:slug/edit` | Edit form fields |
| `/forms/:slug/submissions` | Form submissions inbox |
| `/integrations/webhooks` | Outbound webhooks |
| `/integrations/tokens` | Integration API tokens |
| `/settings` | Global site title and tagline |
| `/settings/updates` | Release version + run database update after FTP deploy |
| `/plugins` | Discover, install, enable/disable plugins; approve capabilities |
| `/plugins/audit-logs` | Plugin lifecycle audit log (requires `plugins.audit`) |
| `/seo/redirects` | URL redirects list |
| `/seo/redirects/new` | Create redirect |

Production builds are served under `/admin/` (Vite `base` / router basename from `VITE_BASE_PATH`). The product name in the UI remains **Luma Studio**; legacy `/studio/` URLs redirect to `/admin/` in sample deploy configs.

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
# Optional: must match API LUMA_SETUP_TOKEN when installer is token-protected
# VITE_LUMA_SETUP_TOKEN=
```

Leave empty to use the Vite proxy (recommended for local dev). For non-proxy deployments, set the full API origin.

## Build & CI

```bash
npm run lint
npm run build
```

Production bundle (same as CI and release packaging):

```bash
VITE_BASE_PATH=/admin/ npm run build
```

GitHub Actions [`.github/workflows/studio.yml`](../../.github/workflows/studio.yml) runs on changes to `apps/studio/**`: Node 22, `npm ci`, lint, build. GitLab CI: `studio:build` job in [`.gitlab-ci.yml`](../../.gitlab-ci.yml).

## Manual smoke test

1. Fresh install: open `/setup`, complete database + admin steps (or use `php artisan luma:install`)
2. Sign in at `/login` with dev credentials
3. Complete `/onboarding` or skip via API env `LUMA_SKIP_ONBOARDING=true`
4. Upload an image at `/media`
5. Create a page at `/pages/new` — pick a section template, add blocks, confirm live preview
6. Publish and open public URL `/p/{slug}`
7. Edit header menu at `/menus` — link to your page slug
8. Check `/forms/contact/submissions` after a form submit
9. At `/plugins` — discover and install `luma.demo`, enable; add the **Quote** plugin block on a page
10. At `/settings/updates` — confirm version and run database update after a release file replace
11. Sign out — protected routes redirect to login; API returns 401 without token

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
