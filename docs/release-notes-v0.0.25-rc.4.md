# Luma CMS v0.0.25-rc.4 — Root entry in project (WordPress-style)

## Summary

`index.php` and `install.php` now live in the **Luma CMS project root** (same folder as `apps/`). Release zip extracts flat — no extra wrapper folder.

## Install flow

1. Extract zip into site document root
2. Open `/` → `index.php` → `install.php` → `/admin/setup` wizard
3. After setup completes, **`install.php` is deleted automatically**
4. Only `index.php` remains as the public entry point

## Deploy checklist

After extract, you must see in the **same folder** as `apps/`:

- `index.php`
- `install.php`
- `.htaccess` (enable hidden files in panel)
- `luma-requirements.php`

Download: `luma-cms-0.0.25-rc.4-shared.zip`
