# Luma CMS v0.0.25-rc.6 — Installer polish (themes, requirements, nginx)

## Highlights

- **Light / dark theme** in setup wizard and login (`Dark theme` / `Light theme` toggle)
- **Requirements step** fixed — uses public `/api/v1/system/requirements`
- **Version** shown on Welcome step via `/api/v1/setup/status`
- **Root `index.php`** — absolute install redirect, Studio/API bootstrap before install

## Deploy

1. Extract zip into site document root (flat — `index.php` next to `apps/`)
2. Apply nginx snippet: `deploy/nginx/brainycp-luma-snippet.conf`
3. Open `https://your-domain/` or `/install.php` → `/admin/setup`

## Optional: setup token

If `LUMA_SETUP_TOKEN` is set in `apps/api/.env`, build Studio with the same value:

```bash
VITE_LUMA_SETUP_TOKEN=your-token VITE_BASE_PATH=/admin/ npm run build
```

Or leave `LUMA_SETUP_TOKEN` empty during first install.

Download: `luma-cms-0.0.25-rc.6-shared.zip`
