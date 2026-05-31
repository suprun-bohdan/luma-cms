# Security

## Roles

- **owner** — first user created during install; can run system updates and view setup logs
- **admin** — content and settings management; cannot run web-based system updates by default
- Additional roles follow RBAC permissions in `RolesAndPermissionsSeeder`

## Installer

- Setup routes are blocked after install (`EnsureNotInstalled`).
- Rate limits: `setup` (read) and `setup-write` (mutations).
- Optional `LUMA_SETUP_TOKEN` requires header `X-Luma-Setup-Token` on setup API calls.
- Production rejects weak/default passwords (`ProductionPasswordGuard`).

## Environment writes

During install, only allowlisted keys are written to `.env` via `EnvFileWriter` (file lock + atomic rename).

## System updates

- Permissions: `system.update.check`, `system.update.run` (owner only for run).
- Update actions are audit-logged.
- Prefer CLI `php artisan luma:update` on locked-down hosts where the web UI is disabled.

## Plugins

Plugins are **trusted PHP** — no sandbox. Code runs in the same process as the CMS.

- Install only from trusted sources.
- Dangerous capabilities require explicit approval in Studio.
- Failed plugin boot is contained (`PluginStatus::Failed`); the CMS continues running.

See [plugins.md](./plugins.md) and `plugins/README.md`.

## Webhooks

- HMAC signatures (`X-Luma-Signature`) on outbound deliveries.
- Retries use Laravel queue (`DeliverWebhookJob` with backoff).
- Publish events do not fail if webhook dispatch throws.

## Integration tokens

Scoped machine tokens for read-only integration API routes. Rotate and revoke unused tokens.

## Reporting

Report security issues responsibly to the project maintainers; do not disclose publicly before a fix is available.
