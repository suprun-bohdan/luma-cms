# Plugins

Luma plugins extend the CMS with blocks, admin navigation, migrations, and PHP hooks.

## Layout

Plugins live under `plugins/<plugin-id>/` with a `luma.plugin.json` manifest. Configure path via `LUMA_PLUGINS_PATH` if needed.

## Lifecycle

1. **Discover** — scan disk for manifests
2. **Install** — register plugin record
3. **Approve capabilities** — dangerous capabilities require owner/admin approval
4. **Enable** — boot plugin service provider and run migrations
5. **Disable / uninstall** — stop boot or remove registration

## Security model

- Plugins run as trusted PHP in-process (no sandbox).
- Path traversal is blocked via `realpath` containment in `PluginRuntimeService`.
- Boot failures are recorded and do not crash the application.

## Studio

Manage plugins at **Luma Studio → Plugins**. A warning banner reminds operators to trust plugin sources before enabling.

## Development

See `plugins/README.md` for manifest schema, capability flags, and test fixtures.
