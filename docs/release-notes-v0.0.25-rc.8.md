# Luma CMS v0.0.25-rc.8 — Writable storage in release zip

Release packaging now runs `scripts/ensure-writable-paths.sh` before zipping:

- Creates full `apps/api/storage/` skeleton
- Sets `apps/api/storage` and `apps/api/bootstrap/cache` to **0777**

After unzip on shared hosting, Requirements → **Writable directories** should pass without manual chmod.

Download: `luma-cms-0.0.25-rc.8-shared.zip`
