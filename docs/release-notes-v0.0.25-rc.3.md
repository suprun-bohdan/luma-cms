# Luma CMS v0.0.25-rc.3 — Flat shared-hosting zip

## Summary

Fixes release packaging so root entry files appear **directly** after unzip (no `staging-shared/` subfolder). Adds visible `install.php` shortcut.

## What changed since rc.2

- Zip layout: extract → `index.php`, `install.php`, `.htaccess` at top level next to `apps/`
- `install.php` → redirects to `/admin/setup` (or `/admin/login` if installed)
- Build script verifies root entry files after packaging

## Deploy on shared hosting

1. Download `luma-cms-0.0.25-rc.3-shared.zip`
2. **Delete old files** in site folder (or use clean folder)
3. Extract zip **into** document root — you must see `index.php` and `apps/` in the same folder
4. Open `https://your-domain/` or `https://your-domain/install.php`

If you only see `apps/` without `index.php`, you extracted rc.1/rc.2 nested layout or an old archive.

## Not production-stable yet

Pre-alpha release candidate.
