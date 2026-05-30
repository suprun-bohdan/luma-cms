# Versioning

Luma CMS maintains **separate version axes** to prevent coupling and breaking changes.

> **Status:** Versioning policy defined. No releases yet.

## Version axes

| Axis | Description | Example |
|------|-------------|---------|
| **Core version** | Overall Luma CMS release | `0.1.0` |
| **Public API version** | REST API route prefix | `/api/v1/` |
| **Plugin API version** | Plugin runtime contract | `1.0` |
| **Manifest schema version** | `luma.plugin.json` format | `1.0` |
| **Database schema version** | Laravel migrations | per migration |
| **Content schema version** | Collection field definitions | `collections.schema_version` |

Each axis evolves independently within its own rules.

## Core version

Follows [Semantic Versioning](https://semver.org/):

- **MAJOR** — breaking changes to core APIs or behavior
- **MINOR** — new features, backward compatible
- **PATCH** — bug fixes, backward compatible

Current status: **0.0.0 pre-alpha** — no stable release.

## Public API version

REST routes are prefixed with a version:

```
/api/v1/collections
/api/v1/entries/{entry}
```

Breaking API changes require a new version (`/api/v2/`). Old versions are supported for a deprecation period once stable releases exist.

Rules:

- Do not expose internal model structure in API responses
- Use API Resources as the public contract
- Document changes in CHANGELOG.md

## Plugin API version

The plugin runtime contract (PluginContext, extension points, lifecycle) has its own version, declared in plugin manifests:

```json
"compatibility": {
  "luma": "^0.1.0",
  "pluginApi": "^1.0"
}
```

Breaking changes to PluginContext or extension point signatures increment the plugin API version.

## Manifest schema version

The `luma.plugin.json` format is versioned via `schemaVersion`. Validators reject manifests with unsupported schema versions.

## Database schema version

Laravel migrations manage database schema. Migrations should be reversible where practical. Plugin migrations require the `database.migrate` capability and admin approval.

## Content schema version

Each collection has a `schema_version` field. When fields are added, removed, or changed:

1. Increment the collection's `schema_version`
2. Provide migration logic for existing entries
3. Entry versions preserve historical snapshots

This prevents silent data loss when content types evolve.

## Related documents

- [content-model.md](content-model.md) — entry versions and schema_version
- [extensions.md](extensions.md) — plugin manifest and compatibility
- [architecture.md](architecture.md) — API structure
