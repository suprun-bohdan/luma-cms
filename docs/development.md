# Development

Guide for contributors working on Luma CMS.

> **Status:** Pre-alpha. No runnable development environment yet.

## Repository layout

This repository is the clean open-source Luma CMS product:

The monorepo skeleton is in place. Application scaffolds (Laravel, React) are not yet created.

```text
luma-cms/
  apps/
    api/          # Laravel backend (Phase 1B)
    studio/       # React TypeScript admin (Phase 1C)
  packages/
    sdk/          # Public TypeScript SDK
    plugin-sdk/   # Plugin development kit
    ui/           # Shared UI components
  docs/
  README.md
  LICENSE
```

## Current phase

**Phase 1: Content Core (MVP 0.1)** — next up.

1. **Phase 1B** — Laravel API scaffold (`apps/api/`)
2. **Phase 1C** — React Studio scaffold (`apps/studio/`)
3. **Phase 1D** — Content Core (collections, fields, entries, API v1)

See [roadmap.md](roadmap.md) for the full plan.

## Getting started (when code exists)

Installation and development setup instructions will be added here once the first runnable version is available.

Expected workflow (planned):

```bash
git clone https://github.com/your-org/luma-cms.git
cd luma-cms
# setup commands TBD
```

Do not add fake install commands before the application exists.

## How to contribute

1. Read [CONTRIBUTING.md](../CONTRIBUTING.md)
2. Read relevant docs in this directory before coding
3. Check [roadmap.md](roadmap.md) — work on the current phase only
4. Keep PRs small and focused
5. Add tests when changing application behavior

## Code organization (planned)

**Backend:** `apps/api/app/`

- `Core/` — kernel, contracts
- `Modules/` — Auth, Content, Media, SEO, Plugins, Settings
- `Support/` — shared helpers

**Frontend:** `apps/studio/`

- API clients, query hooks, UI components, pages — kept separate

**Packages:** `packages/`

- `sdk/` — public TypeScript SDK
- `plugin-sdk/` — plugin development kit
- `ui/` — shared UI components

## Local development workspace

Some contributors may use an optional outer local workspace with Docker, Makefile, and environment files for convenience. That workspace is **not** part of this repository and is **not** required to develop or use Luma CMS.

Luma CMS must be usable without any outer development shell.

## Documentation

When you change architecture or behavior:

- Update the relevant file in `docs/`
- Update [CHANGELOG.md](../CHANGELOG.md)
- For significant decisions, document rationale in commit messages or future ADR files

## Related documents

- [CONTRIBUTING.md](../CONTRIBUTING.md)
- [architecture.md](architecture.md)
- [roadmap.md](roadmap.md)
