# Luma CMS Plugins

Internal plugin directory for Phase 5 MVP. See [apps/api/README.md](../apps/api/README.md#plugins) for API and lifecycle.

## Layout

```text
plugins/
  luma.demo/
    luma.plugin.json
    src/Plugin.php
```

## Manifest

Each plugin requires `luma.plugin.json`. Minimum fields match the governance template (schemaVersion, id, name, version, capabilities, entrypoints, …).

## Demo plugin

`luma.demo` listens on the `system.booted` extension point. Install via Studio **Plugins → Discover → Install → Enable**, or API:

```bash
curl -X POST http://localhost:8080/api/v1/plugins/install \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"plugin_id":"luma.demo"}'
```

Override scan path with `LUMA_PLUGINS_PATH` in API `.env`.
