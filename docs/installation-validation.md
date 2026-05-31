# Installation validation (clean-room smoke)

Use this checklist after building a shared release (`make release-shared`) to confirm packaging and first-run flows on a host with no prior Luma install.

## Prerequisites

- PHP 8.3+ with extensions required by `/luma-requirements.php`
- MySQL/MariaDB or SQLite (shared example uses MySQL)
- Web server document root pointing at `apps/api/public`
- Optional: `LUMA_SETUP_TOKEN` set in `.env` before exposing the installer

## Checklist

| Step | Action | Expected |
|------|--------|----------|
| 1 | `make release-shared` and extract zip to a clean directory | No missing `vendor/`, `apps/studio/dist/`, `deploy/` |
| 2 | `cp apps/api/.env.shared.example apps/api/.env`, set `APP_KEY`, DB credentials | `.env` readable by PHP-FPM only |
| 3 | Open `/luma-requirements.php` | All required checks pass; link to `/admin/setup` |
| 4 | Complete setup wizard at `/admin/setup` | Install finishes; redirect to `/admin/login` |
| 5 | Log in as owner | Dashboard loads; Settings and Updates accessible |
| 6 | Create page, upload media, publish | Public URL renders |
| 7 | Replace release files (simulate FTP update), keep `.env` and `storage/` | Files updated in place |
| 8 | Run update as owner: Studio **Settings → Updates → Run database update** or `php artisan luma:update` | Migrations apply; no 500 on API |
| 9 | (Optional) Configure webhook + trigger publish | Delivery row created; retries on failure |

## Notes

- Setup API routes accept optional header `X-Luma-Setup-Token` when `LUMA_SETUP_TOKEN` is configured.
- Only the **owner** role may run system updates via the web UI.
- After update, restart queue workers if your host runs them outside PHP.

Record date, release version, and any failures when running this checklist in production prep.
