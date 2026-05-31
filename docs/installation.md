# Installation

Luma CMS ships as a Laravel API (`apps/api`) and a static **Luma Studio** admin UI (`apps/studio/dist`).

## Primary path — browser setup (shared hosting)

This is the main install flow for real CMS users. No Composer, npm, terminal, or custom vhost required on typical shared panels.

1. Download `luma-cms-*-shared.zip` (built with `make release-shared` from the outer workspace).
2. Extract into your **site document root** (subdomain folder or domain root).
3. Select PHP 8.3+ in the hosting panel.
4. Open your site URL in a browser — you should reach `/admin/setup` automatically.
5. Optionally open `/luma-requirements.php` first to verify PHP extensions.
6. Complete the visual installer (database, site settings, owner account).
7. Sign in at `/admin/login`, complete onboarding, publish your first page.

The release archive includes root `index.php`, `.htaccess`, and `luma-requirements.php` so document root can be the **extract folder** — you do not need to point the panel at `apps/api/public`.

See **`INSTALL.txt`** in the repository root for a printable, user-facing guide.

If Luma CMS is not installed yet, `/`, `/admin`, and `/admin/login` redirect to `/admin/setup`. After installation, `/admin/setup` is disabled.

### `.env` on first visit

If `apps/api/` is writable, root `index.php` copies `.env.shared.example` → `.env` automatically. Otherwise copy manually before or during setup.

## Advanced path — document root = `apps/api/public`

Optional hardening for VPS or custom nginx/apache vhosts. See `deploy/nginx/luma.conf` and `deploy/apache/luma-vhost.conf.example`.

## Setup token (production)

Before exposing `/admin/setup` to the internet, set a secret in `.env`:

```env
LUMA_SETUP_TOKEN=your-long-random-token
```

Build Studio with the same value:

```env
VITE_LUMA_SETUP_TOKEN=your-long-random-token
```

The setup wizard sends `X-Luma-Setup-Token` on setup API requests.

## Pure nginx (no `.htaccess`)

Use `deploy/nginx/luma-root.conf.example` when the panel does not run Apache rewrite rules.

## Secondary path — CLI install (developers / automation)

For Docker production profiles, CI, or local automation:

```bash
cd apps/api
php artisan luma:install --force
php artisan luma:update
```

Web and CLI installers share `InstallService`. **Do not** present CLI install as the primary user-facing path.

## Docker (development / production profile)

Use the outer `LumaCMS/` workspace:

```bash
make up          # development stack
make prod-setup  # production profile with PostgreSQL, queue, scheduler
```

Studio is served at `/admin/` with same-origin API. Docker prod uses CLI install, not the web wizard.

## Validation

Run the clean-room checklist in [installation-validation.md](./installation-validation.md) before go-live.
