# Luma CMS v0.0.25-rc.13 — /admin 404 after install

## Root cause

After setup completes, root `index.php` booted Laravel for all paths including `/admin/*`. Laravel has no `/admin/login` route → **404**.

During setup the same PHP fallback served Studio correctly; after install it stopped, so finishing the wizard redirected to a broken login URL and refresh showed "already installed".

## Fix

- `index.php` always serves Studio for `/admin/*` before Laravel (works without nginx alias)
- Nginx snippet: add `location = /admin` redirect and correct SPA `try_files` fallback
- Setup i18n: stop resetting wizard step when locale changes

## Deploy on stage.bsc.cv.ua

1. Upload at minimum **`index.php`** from this release (or full rc.13 zip)
2. Paste updated `deploy/nginx/brainycp-luma-snippet.conf` into HTTP + HTTPS server blocks
3. Open **https://stage.bsc.cv.ua/admin/login** — site is already installed (`installed: true`)

Download: `luma-cms-0.0.25-rc.13-shared.zip`

Tag: `v0.0.25-rc.13`
