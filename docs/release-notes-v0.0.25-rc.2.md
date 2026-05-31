# Luma CMS v0.0.25-rc.2 — Shared-hosting root entry

## Summary

Patch release candidate adding a **single web entry point** at the archive root. Extract the zip into your subdomain/site folder, open the URL, and complete the browser installer — no document root change to `apps/api/public` and no custom nginx/apache config on typical shared panels.

## What changed since rc.1

- Root `index.php`, `.htaccess`, and `luma-requirements.php` in the release zip
- Auto-copy `.env.shared.example` → `.env` on first visit when `apps/api/` is writable
- `.htaccess` serves `/admin/` from Studio dist, routes Laravel via root front controller, blocks `/apps/` and sensitive paths
- `deploy/nginx/luma-root.conf.example` for pure-nginx hosts without `.htaccess`

## Installation path

1. Download `luma-cms-0.0.25-rc.2-shared.zip`
2. Extract into your site document root (e.g. subdomain folder)
3. Open `https://your-domain/` → `/admin/setup`
4. Complete wizard → `/admin/login` → first published page

See `INSTALL.txt` in the archive.

## Known limitations

- Requires Apache `AllowOverride` or nginx snippet (BrainyCP/HestiaCP usually OK)
- Subdirectory installs (`/folder/` on same domain) not supported in this release
- `open_basedir` restrictions may block bootstrap on some hosts
- All rc.1 limitations still apply (trusted plugin PHP, no marketplace, pre-alpha)

## Not production-stable yet

Pre-alpha release candidate for dogfooding and hosting validation only.
