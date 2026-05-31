# Luma CMS v0.0.25-rc.11 — Shared hosting .env write fix

Found by the outer `hosting-sim` Docker stack (BrainyCP-like nginx + PHP-FPM, unzip-only deploy).

## Fixes

- `EnvFileWriter` stores lock and backup under `storage/framework/` and updates `.env` in place
- Setup write rate limit: 20/min (try multiple DB drivers in the wizard)

Download: `luma-cms-0.0.25-rc.11-shared.zip`

Tag: `v0.0.25-rc.11`
