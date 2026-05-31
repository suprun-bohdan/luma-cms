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
| `index.php`, `.htaccess`, `luma-requirements.php` at archive root | Root entry for shared hosting |
| `apps/api/vendor/` present | Composer deps bundled (no Composer on host required) |
| `apps/studio/dist/` present | Built with `VITE_BASE_PATH=/admin/` |
| `deploy/` present | nginx/apache samples |
| `INSTALL.txt`, `luma-manifest.json` | Present |
| `apps/api/.env.shared.example` | Present; documents optional `LUMA_SETUP_TOKEN` |
| No `apps/api/.env` | Release script excludes local secrets |
| No `apps/api/tests/` | Test suite excluded from shared artifact |
| Zip does not contain `.git` or `node_modules` | Per `scripts/release-shared.sh` rsync excludes |

### Shared hosting checklist

**Prerequisites:** PHP 8.3+, document root = **extracted archive folder** (default) or `apps/api/public` (advanced), MySQL/MariaDB or SQLite.

| Step | Action | Expected |
|------|--------|----------|
| A0 | Point panel document root at extract folder **without** changing to `apps/api/public` | `/` redirects to `/admin/setup` when not installed |
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

## Manual clean-room validation log

| Date | Environment | Command / Flow | Result | Notes |
|------|-------------|----------------|--------|-------|
| 2026-05-31 | Shared release package | `make release-shared VERSION=0.0.24` + zip inspection | **Pass** | `dist/luma-cms-0.0.24-shared.zip` (31M): `vendor/`, `studio/dist/`, `INSTALL.txt`, `.env.shared.example`, `deploy/` present; no `.env`, `node_modules`, or `apps/api/tests`; `INSTALL.txt` links `/admin/setup` |
| 2026-05-31 | Docker production profile | `make prod-setup` (after fixes below) | **Pass** | PostgreSQL (`DB_CONNECTION=pgsql`), prod containers up, `luma:install --force` OK, `/admin/` HTTP 200, `/studio/` → 301 `/admin/`, `/api/v1/health` OK, `/luma-requirements.php` → `/admin/setup`, `/admin/login` OK when installed |
| 2026-05-31 | Setup token | `LUMA_SETUP_TOKEN` + `VITE_LUMA_SETUP_TOKEN` end-to-end | **Not run** | Optional production hardening; covered by automated `SetupApiTest` |
| 2026-05-31 | Post-install smoke | Owner login → media → page → publish → form → update RBAC | **Pass** | API smoke: owner update 200, admin update 403, owner journal 200 / admin journal 403, media 201, page publish + public `/p/` 200, form submit 302 (with CSRF); plugin/update Studio copy verified via `DistributionStudioCopyTest` |

### Issues found and resolved during this run

1. **`make prod-setup` host `cp .env` permission denied** when `.env` was root-owned from Docker. Fixed outer [`Makefile`](../Makefile) to copy via `docker compose exec -u root php`.
2. **`luma:install` rejected default production password** (`change-me-in-production`). Fixed Makefile to pass `--admin-password` (12+ chars); updated [`apps/api/.env.production.example`](apps/api/.env.production.example) placeholder.
3. **Nginx routing stale until reload** after config changes — run `docker compose exec nginx nginx -s reload` if `/admin/` or `/studio/` redirect misbehaves.

No open blockers remain for Phase 8.3 planning.

---

## Phase 8.4 — Real user install rehearsal

Simulates a shared-hosting user path: extract `luma-cms-0.0.25-shared.zip` to an isolated directory (not the git workspace), configure `.env` from `.env.shared.example`, web document root = `apps/api/public`, then Setup → login → onboarding → first published page.

Automated smoke (same HTTP/API steps as manual R1–R7): outer [`scripts/rehearsal-8.4-smoke.sh`](../../scripts/rehearsal-8.4-smoke.sh) with [`docker-compose.rehearsal.yml`](../../docker-compose.rehearsal.yml) on port `8081`.

| Step | Action | Result | Notes |
|------|--------|--------|-------|
| R0 | `make release-shared VERSION=0.0.25` + packaging checks | **Pass** | 31M zip; `vendor/`, `studio/dist/`, no `.env`/tests |
| R1 | `/luma-requirements.php` | **Pass** | Links to `/admin/setup` |
| R2 | Web Setup (API: requirements → database → finish) | **Pass** | SQLite DB path; owner account created |
| R3 | Owner login | **Pass** | `owner` role |
| R4 | Onboarding wizard (welcome → site-type → starter → integrations → finish) | **Pass** | Business preset + starter content |
| R5 | Studio `/admin/dashboard` | **Pass** | `Luma Studio` shell loads |
| R6 | Create page `hello` | **Pass** | Hero block |
| R7 | Publish + public `/p/hello` | **Pass** | HTTP 200, headline visible |
| R8 | Friction review | **Pass** | See items below; one blocker fixed in product |

**Environment:** PHP 8.4 (Docker), SQLite (minimal shared-hosting simulation), `LUMA_SETUP_TOKEN` not set, zip `0.0.25`, date 2026-05-31.

### Friction and fixes (Phase 8.4)

| Item | Severity | Resolution |
|------|----------|------------|
| Default `.env.shared.example` used `CACHE_STORE=database` before migrations exist | **Blocker** | Changed to `CACHE_STORE=file` and `SESSION_DRIVER=file`; note added to `INSTALL.txt` |
| Rehearsal Docker overlay replaced `storage/` with empty volume | Infra only | Removed separate storage volume from `docker-compose.rehearsal.yml` — real zip includes full `storage/` skeleton |
| MySQL shared-hosting path not re-run in this session | Nice-to-have | SQLite path validates Setup/onboarding; MySQL remains covered by Section A checklist + prior API tests |

**Phase 8.4 gate:** **Pass** (with blocker fix committed). Next: tag release candidate or plan Phase 9 — not started here.

---

## Phase 8.5 — Release candidate finalization (`0.0.25-rc.1`)

| Item | Result | Notes |
|------|--------|-------|
| Version sync | **Pass** | README, CHANGELOG `[0.0.25-rc.1]`, `bootstrap/luma-requirements.php`, `config/luma.php`, product READMEs |
| Shared env placeholder | **Pass** | `LUMA_SEED_ADMIN_PASSWORD=Set-a-strong-password-min-12-chars` in `.env.shared.example` |
| Plugin path containment | **Pass** | Trailing-separator root check; `PluginRuntimePathTest` sibling-prefix fixture |
| PHPUnit + Studio lint/build | **Pass** | Docker `make test-api`; `npm run lint`; `VITE_BASE_PATH=/admin/ npm run build` |
| Shared artifact | **Pass** | `make release-shared VERSION=0.0.25-rc.1` — manifest version matches |
| MySQL + setup token browser E2E | **Not run** | Deferred to post-tag validation (see Phase 8.4 friction table) |
| GitHub Actions | **Manual** | Verify workflow runs on GitHub after push |

**Phase 8.5 gate:** **Pass** — ready for tag `v0.0.25-rc.1`.

---

## v0.0.25-rc.1 release candidate validation (browser-first polish)

| Item | Result | Notes |
|------|--------|-------|
| Browser-first INSTALL.txt | **Pass** | User-facing guide; CLI secondary; document root warning |
| `/` redirect when not installed | **Pass** | PHPUnit `SetupApiTest::test_web_root_redirects_to_setup_when_not_installed` |
| Studio login → setup when not installed | **Pass** | `LoginPage` + `InstallGate` redirect |
| Setup wizard steps | **Pass** | Welcome, requirements, database, site, owner, install |
| Install marker for requirements page | **Pass** | `storage/app/.luma-installed`; `/luma-requirements.php` → login when present |
| MySQL + setup token browser E2E | **Not run** | Deferred (see Phase 8.4 friction table) |
| GitHub Actions post-push | **Manual** | Verify in GitHub UI after push |

**RC polish gate:** **Pass** — browser-first flow documented and wired; tag when ready.

---

## v0.0.25-rc.2 — Shared-hosting root entry point

| Item | Result | Notes |
|------|--------|-------|
| Root `index.php` + `.htaccess` in release zip | **Pass** | `deploy/shared-hosting-root/` copied by `release-shared.sh` |
| Document root = extract folder (no `apps/api/public` change) | **Pass** | `INSTALL.txt` primary path; advanced option documented |
| Security deny rules in root `.htaccess` | **Pass** | Blocks `/apps/`, dotfiles, repo markdown at web root |
| nginx pure-hosting sample | **Pass** | `deploy/nginx/luma-root.conf.example` |
| PHPUnit artifact tests | **Pass** | `DistributionArtifactsTest` extended |
| Live subdomain E2E (stage.bsc.cv.ua) | **Not run** | User manual validation pending |
| GitHub Release with zip asset | **Pending** | Tag `v0.0.25-rc.2` after push |

**RC2 gate:** **Pass** (automation) — ready for tag and live hosting test.

