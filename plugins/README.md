# Luma CMS Plugins

Internal plugin directory for Phase 5. See [apps/api/README.md](../apps/api/README.md#plugins) for API and lifecycle.

## Layout

```text
plugins/
  luma.demo/
    luma.plugin.json
    src/Plugin.php
```

## Manifest

Each plugin requires `luma.plugin.json`. Minimum fields match the governance template (schemaVersion, id, name, version, capabilities, entrypoints, …).

## Extension points (Phase 5.1)

| Extension point | Required capability | Purpose |
|-----------------|-------------------|---------|
| `system.booted` | — | Run after enabled plugin boots |
| `content.afterCreate` | `content.create` | React to new entries |
| `content.afterUpdate` | `content.update` | React to entry updates |
| `content.afterPublish` | `content.publish` | React to entry/page publish |
| `admin.navigation` | `admin.extend` | Add Studio sidebar links |

Declare points in `extensionPoints[]` and implement listeners via `PluginContext::listen()`. Use `PluginContext::registerAdminNavigation($label, $to, $sortOrder)` for sidebar items.

## Demo plugin (v0.2.0)

`luma.demo`:

- Logs on `system.booted` and `content.afterPublish`
- Registers sidebar link **Demo insights** → `/plugins`

Install via Studio **Plugins → Discover → Install → Enable**, or API:

```bash
curl -X POST http://localhost:8080/api/v1/plugins/install \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"plugin_id":"luma.demo"}'
```

**Manifest upgrades:** uninstall and reinstall to refresh the DB snapshot (auto-upgrade is not implemented yet).

Override scan path with `LUMA_PLUGINS_PATH` in API `.env`.
