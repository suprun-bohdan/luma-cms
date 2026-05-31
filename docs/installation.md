# Installation

Luma CMS ships as a Laravel API (`apps/api`) and a static **Luma Studio** admin UI (`apps/studio/dist`).

## Primary path — browser setup (shared hosting)

This is the main install flow for real CMS users. No Composer, npm, or terminal required.

1. Download `luma-cms-*-shared.zip` (built with `make release-shared` from the outer workspace).
2. Upload and extract on your host.
3. Set document root to **`apps/api/public`** (never the archive root).
4. Copy `apps/api/.env.shared.example` to `apps/api/.env`. Set `APP_URL` and DB credentials if needed.
5. Ensure `storage/` and `bootstrap/cache/` are writable.
6. Open **`/luma-requirements.php`** in your browser — fix any failed checks.
7. Complete the visual installer at **`/admin/setup`** (database, site settings, owner account).
8. Sign in at **`/admin/login`**, complete onboarding, and publish your first page.

See **`INSTALL.txt`** in the repository root for a printable, user-facing guide.

If Luma CMS is not installed yet, `/`, `/admin`, and `/admin/login` redirect to `/admin/setup`. After installation, `/admin/setup` is disabled.

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

## Secondary path — CLI install (developers / automation)

For Docker production profiles, CI, or local automation:

```bash
cd apps/api
php artisan luma:install --force
php artisan luma:update
```

Web and CLI installers share `InstallService`. **Do not** present CLI install as the primary user-facing path in product docs.

## Docker (development / production profile)

Use the outer `LumaCMS/` workspace:

```bash
make up          # development stack
make prod-setup  # production profile with PostgreSQL, queue, scheduler
```

Studio is served at `/admin/` with same-origin API. Docker prod uses CLI install, not the web wizard. For web Setup validation, see [installation-validation.md](./installation-validation.md) Section A.

## Validation

Run the clean-room checklist in [installation-validation.md](./installation-validation.md) before go-live.
