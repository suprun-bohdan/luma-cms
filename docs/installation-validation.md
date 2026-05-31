# Installation validation (clean-room smoke)

Use this checklist before go-live to confirm Luma CMS installs and runs from production-like artifacts — not only from a developer workspace.

## Terminology

| Flow | URL | When |
|------|-----|------|
| **Setup** | `/admin/setup` | Technical installer **before first login** — database, `.env`, admin account |
| **Onboarding** | `/admin/onboarding` | Product wizard **after login** — site type, starter content, preferences |

Legacy `/studio/` URLs must redirect to `/admin/` (see deploy samples). Canonical admin UI path is **`/admin/`**; product name in UI is **Luma Studio**.

---

## Section A — Shared release zip (shared hosting)

Run from the outer `LumaCMS/` workspace: `make release-shared VERSION=x.y.z`

### Manual release packaging checks

After building the zip, extract to a clean directory and confirm:

| Check | Expected |
|-------|----------|
| `apps/api/vendor/` present | Composer deps bundled (no Composer on host required) |
| `apps/studio/dist/` present | Built with `VITE_BASE_PATH=/admin/` |
| `deploy/` present | nginx/apache samples |
| `INSTALL.txt`, `luma-manifest.json` | Present |
| `apps/api/.env.shared.example` | Present; documents optional `LUMA_SETUP_TOKEN` |
| No `apps/api/.env` | Release script excludes local secrets |
| No `apps/api/tests/` | Test suite excluded from shared artifact |
| Zip does not contain `.git` or `node_modules` | Per `scripts/release-shared.sh` rsync excludes |

### Shared hosting checklist

**Prerequisites:** PHP 8.3+, web root = `apps/api/public`, MySQL/MariaDB or SQLite.

| Step | Action | Expected |
|------|--------|----------|
| A1 | Extract `luma-cms-*-shared.zip` to a clean directory | Packaging checks above pass |
| A2 | `cp apps/api/.env.shared.example apps/api/.env`, set `APP_KEY`, DB credentials | `.env` not world-readable |
| A3 | (Recommended) Set `LUMA_SETUP_TOKEN`; rebuild Studio with matching `VITE_LUMA_SETUP_TOKEN` | Setup API rejects requests without `X-Luma-Setup-Token` |
| A4 | Open `/luma-requirements.php` | Checks pass; link targets `/admin/setup` |
| A5 | Complete **Setup** at `/admin/setup` | Redirect to `/admin/login`; first user has **owner** role |
| A6 | Log in as owner | Dashboard, Settings, Updates accessible at `/admin/` |
| A7 | Create page, upload media, publish | Public page URL renders |
| A8 | Submit a public form (if starter site includes contact form) | Submission stored; optional webhook delivery row |
| A9 | **Plugins** (optional): Discover → install demo plugin → approve capabilities → enable | CMS stays up; warning about trusted PHP visible in Studio |
| A10 | Log in as **admin** (non-owner) if seeded separately | **Updates → Run database update** returns owner-permission error (403) |
| A11 | Replace release files (simulate FTP), keep `.env` and `storage/` | Files updated in place |
| A12 | Run update as **owner**: Studio **Settings → Updates** or `php artisan luma:update` | Migrations apply; audit log entry |
| A13 | (Optional) Webhook + publish | Delivery row created; retries on failure |

---

## Section B — Docker production profile

Run from outer `LumaCMS/` workspace:

```bash
make prod-setup   # build-studio + PostgreSQL + luma:install --force
```

This path uses **CLI install** (`luma:install`), not the web Setup wizard. Use Section A for web Setup validation.

| Step | Action | Expected |
|------|--------|----------|
| B1 | `make prod-setup` completes without error | Stack up with `--profile prod` |
| B2 | Open `http://localhost:<APP_PORT>/admin/` | Luma Studio loads (static assets from `apps/studio/dist`) |
| B3 | Legacy `/studio/` request | Redirects to `/admin/` (nginx in `docker/nginx/default.conf`) |
| B4 | Log in with seeded admin credentials | Dashboard at `/admin/dashboard` |
| B5 | Complete **Onboarding** at `/admin/onboarding` if not skipped | Progress saved; starter content optional |
| B6 | Smoke: page create/publish, media upload | Same as A7 |
| B7 | `make update` or Studio Updates as owner | Migrations/cache refresh succeeds |

---

## Notes

- Setup API routes accept header `X-Luma-Setup-Token` when `LUMA_SETUP_TOKEN` is configured.
- Only the **owner** role has `system.update.run`; **admin** can check updates but not run them via web UI.
- Production install rejects weak/default passwords when enforcement is active.
- After update, restart queue workers if your host runs them outside PHP.

## Validation log

Record date, release version, environment (shared zip / Docker prod), and any failures when running this checklist.

| Date | Version | Path | Result | Notes |
|------|---------|------|--------|-------|
| | | | | |
