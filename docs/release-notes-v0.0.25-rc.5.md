# Luma CMS v0.0.25-rc.5 — Install redirect fix (nginx / BrainyCP)

Fixes wrong redirect to **`/admin/install.php`** during fresh install on pure nginx hosts.

## Root cause

- rc.4 zip shipped `index.php` with **relative** `Location: install.php`
- When `/admin/setup` falls through to root `index.php` (missing nginx `location ^~ /admin/`), the browser resolves it as `/admin/install.php` → 404

## Fix

- Absolute redirect: `/install.php`
- Before install, root `index.php` also:
  - serves Studio for `/admin/*` (setup wizard works even without nginx alias)
  - boots Laravel for `/api/*` (setup API)

## Deploy on stage.bsc.cv.ua

1. Replace site files with this zip (or at minimum upload new `index.php`)
2. Add nginx snippet from `deploy/nginx/brainycp-luma-snippet.conf` (HTTP + HTTPS)
3. Remove old rewrite: `rewrite ^(.+)$ /index.php?q=$1`

Download: `luma-cms-0.0.25-rc.5-shared.zip`
