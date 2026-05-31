# Luma CMS Extensions

Internal plugin API (Phase 5 MVP). Third-party plugins are not supported yet.

## Plugin directory

Place plugins under `plugins/` at the repository root:

```text
plugins/
  luma.demo/
    luma.plugin.json
    src/Plugin.php
```

Override the scan path with `LUMA_PLUGINS_PATH` in `.env`.

## Manifest

Every plugin requires `luma.plugin.json`. Use the template in the governance repo:

`.cursor/templates/plugin-manifest-template.json`

## Lifecycle

1. **Discover** — scan `plugins/` for manifests (`GET /api/v1/plugins/discover`)
2. **Install** — record plugin in SQLite (`POST /api/v1/plugins/install`)
3. **Enable** — load backend entrypoint, call `register()` + `boot()` (`POST /api/v1/plugins/{id}/enable`)
4. **Disable** — unregister runtime hooks (`POST /api/v1/plugins/{id}/disable`)
5. **Uninstall** — remove DB record (`DELETE /api/v1/plugins/{id}`)

Dangerous capabilities (see governance rules) require explicit admin approval before enable.

## Plugin backend

Implement `App\Modules\Plugins\Contracts\PluginContract`:

```php
public function register(PluginContext $context): void;
public function boot(PluginContext $context): void;
```

Plugins receive `PluginContext` only — not the Laravel application container.

## Extension points (MVP)

- `system.booted` — dispatched after enabled plugins boot

Register listeners in `register()`:

```php
$context->listen('system.booted', function () use ($context): void {
    $context->log('info', 'Ready.');
});
```

## Permissions

| Permission | Purpose |
|------------|---------|
| `plugins.manage` | discover, install, enable, disable, uninstall, approve capabilities |
| `plugins.audit` | read audit log |

Effective plugin action permission:

```
user_permission ∩ plugin_capability ∩ system_policy
```

## Audit log

Lifecycle and capability approval events are stored in `audit_logs` and exposed at `GET /api/v1/audit-logs`.
