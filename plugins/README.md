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

## Extension points (Phase 5.1–5.2)

| Extension point | Required capability | Purpose |
|-----------------|-------------------|---------|
| `system.booted` | — | Run after enabled plugin boots |
| `content.afterCreate` | `content.create` | React to new entries |
| `content.afterUpdate` | `content.update` | React to entry updates |
| `content.afterPublish` | `content.publish` | React to entry/page publish |
| `admin.navigation` | `admin.extend` | Add Studio sidebar links |
| `render.block` | `editor.extend` | Register page block types |

Declare points in `extensionPoints[]`. Use `PluginContext::listen()`, `registerAdminNavigation()`, and `registerBlockType()`.

### Block types

Block ids are namespaced: `{plugin_id}/{local_type}` (e.g. `luma.demo/quote`).

```php
$context->registerBlockType(
    'quote',
    'Quote',
    'Pull quote with author',
    'content',
    ['quote' => '...', 'author' => '...'],
    [
        ['name' => 'quote', 'label' => 'Quote', 'type' => 'textarea'],
        ['name' => 'author', 'label' => 'Author', 'type' => 'text'],
    ],
    static fn (array $props): string => '<blockquote>...</blockquote>',
);
```

Escape all output in renderers (`e()` in Laravel).

### Plugin routes

Requires dangerous capability `routes.register` (approve in Studio before enable):

```php
$context->registerRoute('GET', 'ping', static fn () => ['ok' => true]);
```

Dispatched at `GET /api/v1/plugins/{plugin_id}/ping`.

## Demo plugin (v0.3.0)

`luma.demo`:

- Logs on `system.booted` and `content.afterPublish`
- Sidebar link **Demo insights** → `/plugins`
- **Quote** block type for the visual editor

Install via Studio **Plugins → Discover → Install → Enable**, or API.

**Manifest upgrades:** uninstall and reinstall to refresh the DB snapshot (auto-upgrade is not implemented yet).

Override scan path with `LUMA_PLUGINS_PATH` in API `.env`.

## Security model

Luma plugins are **trusted server-side PHP code**, not sandboxed extensions. The runtime validates backend entrypoint paths with `realpath()` and rejects path traversal, but a malicious plugin can still execute arbitrary PHP once enabled. Only install plugins from sources you trust, review capabilities before approval, and treat `routes.register` as a dangerous capability.
