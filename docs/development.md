# Development

Guide for contributors working on Luma CMS.

> **Status:** Pre-alpha. No runnable development environment yet.

## Repository layout

This repository is the clean open-source Luma CMS product:

```text
luma-cms/
  apps/
    api/          # Laravel backend (planned)
    studio/       # React TypeScript admin (planned)
  packages/       # SDK, plugin SDK, shared UI (planned)
  docs/           # Public documentation (this directory)
  README.md
  LICENSE
```

The monorepo skeleton (`apps/`, `packages/`) will be created in Phase 1A, after this documentation foundation.

## Current phase

**Phase 0: Foundation** — product documentation and repository structure.

Next: **Phase 1A** — monorepo skeleton, then Laravel API and React Studio scaffolds.

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
