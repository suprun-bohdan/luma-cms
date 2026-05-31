# Product roadmap

Public summary of Luma CMS direction. Detailed agent planning lives in the outer workspace (`.cursor/plans/`).

## Shipped (pre-alpha)

- Content core: collections, entries, auth, RBAC
- Luma Studio: pages, media, navigation, SEO, forms, visual editing
- Plugins: manifest, capabilities, blocks, boot containment
- Integrations: webhooks, API tokens
- Installer: web setup, CLI `luma:install`, shared release zip
- Onboarding wizard and site settings
- System updates: CLI and Studio (owner)

**Current release track:** `0.0.25-rc.4` — root `index.php` / `install.php` in project; auto-remove install after setup.

## Near term

- Tag `v0.0.25-rc.4` on GitHub; attach shared zip asset
- Plugin upgrade path and registry hardening (symlink/depth limits)
- Optional Redis queue / S3 media for larger deployments

## Explicit non-goals (for now)

- Plugin or theme marketplace
- Multi-tenant SaaS billing
- Full e-commerce engine
- Zip upload updater UI in Studio
- PHP plugin sandbox / marketplace signing
- Visual builder v2, AI agents

## Versioning

See `CHANGELOG.md` for release notes. Semantic versioning applies once `1.0.0` is declared.
