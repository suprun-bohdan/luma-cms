# Luma Plugin SDK

Development kit for building Luma CMS plugins.

> **Status:** Not implemented yet. Directory reserved for Phase 3 (Extension Foundation).

## Purpose

- Helpers for plugin manifest validation
- TypeScript types for admin extension points
- Documentation and examples for `luma.plugin.json`

## Rules

- Plugins use `PluginContext`, not the full Laravel container
- Capabilities declared in manifest; default deny

See [../../docs/extensions.md](../../docs/extensions.md).
