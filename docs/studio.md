# Luma Studio

**Luma Studio** is the admin UI for Luma CMS. It is a React SPA built with Vite and served as static files from the API public directory.

## URL

Production default: **`/admin/`**

Build:

```bash
cd apps/studio
npm ci
VITE_BASE_PATH=/admin/ npm run build
```

Output: `apps/studio/dist/` — copy or symlink into `apps/api/public/admin/` per your deploy sample.

## Flows

| Route | Purpose |
|-------|---------|
| `/admin/setup` | **Technical installer** — one-time database and admin setup |
| `/admin/login` | Authentication |
| `/admin/onboarding` | **First-login onboarding** — site type and starter content |
| `/admin/dashboard` | Workspace overview |
| `/admin/settings/updates` | Post-release database update (owner) |

Setup and onboarding are separate by design: setup is infrastructure; onboarding is product configuration after login.

## Design system

Component and token documentation: `apps/studio/docs/studio-design-system.md`.

## Development

```bash
cd apps/studio
npm run dev
```

Proxy API requests to your local Laravel instance (see `apps/studio/README.md`).
