# Luma CMS v0.0.25-rc.7 — Setup API before database

Fixes HTTP 500 on `/api/v1/setup/status` and `/api/v1/setup/logs` when MySQL is not configured yet (fresh upload, before Database step).

Also fix **Version: Unknown** (status 500) and stop log polling on Welcome step.

Download: `luma-cms-0.0.25-rc.7-shared.zip`

**On stage:** ensure writable paths (requirements step):

```bash
chmod -R u+rwX storage bootstrap/cache apps/api/storage apps/api/bootstrap/cache
```
