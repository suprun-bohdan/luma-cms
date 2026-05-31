# Installation

Luma CMS ships as a Laravel API (`apps/api`) and a static **Luma Studio** admin UI (`apps/studio/dist`).

## Shared hosting (recommended path)

1. Download or build `luma-cms-*-shared.zip` (`make release-shared` from the outer workspace).
2. Upload and extract so the web root is `apps/api/public`.
3. Copy `apps/api/.env.shared.example` to `apps/api/.env` and configure database credentials.
4. Visit `/luma-requirements.php` — fix any failed PHP extension checks.
5. Open `/admin/setup` and complete the installer (database, admin user, optional starter site).
6. Log in at `/admin/login`.

See `INSTALL.txt` in the repository root for a short printable guide.

## Docker (development / production profile)

Use the outer `LumaCMS/` workspace:

```bash
make up          # development stack
make prod-setup  # production profile with PostgreSQL, queue, scheduler
```

Studio is served at `/admin/` with same-origin API. For a full clean-room checklist including Docker prod, see [installation-validation.md](./installation-validation.md) Section B.

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

## CLI install (alternative)

```bash
cd apps/api
php artisan luma:install --force
php artisan luma:update
```

Web and CLI installers share `InstallService`.

## Validation

Run the clean-room checklist in [installation-validation.md](./installation-validation.md) before go-live.
