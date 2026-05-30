# Extensions

Luma CMS extends through a **secure plugin system** with declared capabilities and named extension points.

> **Status:** Planned for Phase 3. Not yet implemented.

## Core principle

A plugin is a **guest with a pass**, not the owner of the house.

Plugins must not receive unrestricted access to Laravel internals, the full application container, or direct database writes.

## Plugin manifest

Every plugin requires a manifest file: `luma.plugin.json`

Minimum fields:

| Field | Description |
|-------|-------------|
| schemaVersion | Manifest schema version |
| id | Unique plugin identifier (e.g. `luma.example-plugin`) |
| name | Human-readable name |
| description | Short description |
| version | Plugin semver |
| type | `plugin` |
| author | Author name and URL |
| compatibility | Required Luma, plugin API, PHP, Node versions |
| capabilities | Declared capability list |
| extensionPoints | Extension points the plugin uses |
| entrypoints | Backend and admin entry files |
| migrations | Migration path (if any) |

## Capabilities

Plugins declare capabilities in the manifest. The system grants them at install/enable time. **Default deny** — a plugin has no capabilities unless explicitly granted.

Examples:

- `content.read`, `content.create`, `content.update`, `content.delete`, `content.publish`
- `media.read`, `media.upload`, `media.delete`
- `seo.analyze`, `seo.update`
- `admin.extend`, `editor.extend`
- `routes.register`, `jobs.dispatch`, `webhooks.send`

Dangerous capabilities require explicit admin approval and audit logging. See [permissions.md](permissions.md).

## Extension points

Named extension points replace chaotic global hooks.

Examples (planned):

| Extension point | Purpose |
|-----------------|---------|
| `admin.navigation` | Add admin menu items |
| `admin.entryPanel` | Add panels to entry editor |
| `editor.toolbar` | Extend editor toolbar |
| `content.afterCreate` | Run after entry creation |
| `content.afterPublish` | Run after publish |
| `media.afterUpload` | Run after media upload |
| `seo.metadata.resolve` | Resolve SEO metadata |
| `system.booted` | Run after system boot |

Phase 3 starts with a minimal subset. More extension points are added only when a concrete use case exists.

## Plugin API

Plugins interact through `PluginContext` — a restricted API surface.

**Good:**

```php
public function register(PluginContext $context): void
```

**Bad:**

```php
public function register(Application $app): void
```

Plugins must not:

- Receive the full Laravel container
- Execute raw SQL or run migrations without `database.migrate` capability
- Modify core database tables directly

## Lifecycle

```
discover → validate → install → enable → register → boot
                                              ↓
                                    disable → uninstall → upgrade
```

| Stage | Description |
|-------|-------------|
| discover | Scan plugin directory or accept upload |
| validate | Validate manifest schema and compatibility |
| install | Register plugin, store manifest snapshot |
| enable | Grant capabilities (admin approval for dangerous ones) |
| register | Call plugin `register()` via PluginContext |
| boot | Call plugin `boot()` on system startup |
| disable | Revoke runtime registration |
| uninstall | Remove plugin and optionally its data |
| upgrade | Migrate plugin to a new version |

## Events

- **Sync events** may block the operation (e.g. validation hooks)
- **Async events** must not break user workflow if a plugin fails

## Prerequisites

Phase 3 (Extension Foundation) requires Phase 1 (Content Core) to be working.

## Related documents

- [permissions.md](permissions.md) — permission formula and capability layers
- [security.md](security.md) — audit logging
- [versioning.md](versioning.md) — plugin API version
- [roadmap.md](roadmap.md) — Phase 3 timeline
