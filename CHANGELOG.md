# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html) once releases begin.

## [Unreleased]

## [0.0.25-rc.2] - 2026-05-31

### Added

- Shared-hosting root entry package: `deploy/shared-hosting-root/` (`index.php`, `.htaccess`, `luma-requirements.php`) copied into release zip root
- `deploy/nginx/luma-root.conf.example` for pure-nginx panels without Apache `.htaccess`

### Changed

- Browser-first install polish: expanded `/admin/setup` wizard, login/setup redirects, install marker for requirements page
- `INSTALL.txt` and installation docs: document root = **extract folder** by default; `apps/api/public` is advanced/optional
- `release-shared.sh` bundles root entry files into shared zip

## [0.0.25-rc.1] - 2026-05-31

### Added

- Phase 8.2 clean-room validation: expanded `docs/installation-validation.md` (shared zip + Docker prod, Setup vs Onboarding)
- Automated release artifact content checks in `DistributionArtifactsTest` and `DistributionStudioCopyTest`
- PHPUnit coverage for setup token edge cases, owner/update RBAC, plugin path containment, webhook dispatch isolation, update failure audit
- Phase 8.4 real-user install rehearsal log (shared zip SQLite path)

### Changed

- Phase 8.3 Studio UX cleanup: grouped sidebar navigation, dashboard welcome and action cards, setup/onboarding copy separation, owner-only update confirm and permission messaging, plugin trusted-PHP and failed-state display, forms/media/pages empty states and helper text, settings documentation links
- `UpdateService` is no longer `final` to allow failure-path testing via container mock
- `apps/api/.env.production.example` admin password placeholder avoids production guard blocked value
- Runtime version synchronized to `0.0.25-rc.1` in `bootstrap/luma-requirements.php`

### Fixed

- Manual clean-room validation (2026-05-31): documented `make prod-setup` fixes for root-owned `.env` and production install password (outer `Makefile`)
- Phase 8.4 rehearsal: `.env.shared.example` uses file cache/session until Setup migrations complete; `INSTALL.txt` note added
- Phase 8.5: `.env.shared.example` seed password placeholder no longer uses a production-guard blocked value
- Plugin runtime path containment: normalize plugin root with trailing directory separator to block sibling-prefix escapes (`/plugins/foo` vs `/plugins/foo-evil`)

## [0.0.24] - 2026-05-30

### Added

- `owner` role; first install user receives owner (admin no longer has `system.update.run` or `setup.view_logs`)
- `ProductionPasswordGuard` blocks weak and default passwords in production during setup
- Setup API hardening: `EnsureSetupToken` (`LUMA_SETUP_TOKEN` / `X-Luma-Setup-Token`), rate limiters `setup` and `setup-write`
- `SystemUpdatePolicy` with dedicated permissions `system.update.check` and `system.update.run`; audit logs on update run
- `EnvFileWriter` allowlist, file lock, and atomic rename for `.env` writes during install
- Plugin runtime path containment (`realpath`), boot failure isolation (`PluginStatus::Failed`, `last_error`)
- Queue-native webhook delivery retry in `DeliverWebhookJob`; publish path isolates webhook dispatch failures
- Studio setup wizard sends `X-Luma-Setup-Token` when `VITE_LUMA_SETUP_TOKEN` is set
- Public documentation set under `docs/` (installation, production, security, plugins, integrations, studio, roadmap)
- `docs/installation-validation.md` clean-room install and update smoke checklist

### Changed

- Canonical admin UI URL is `/admin/` (Luma Studio); legacy `/studio/` redirects in nginx/apache samples
- Makefile and `release-shared.sh` build Studio with `VITE_BASE_PATH=/admin/`
- `StarterSiteService` CTA links to `/admin/`
- Studio Updates page shows owner-specific message on HTTP 403
- Settings PATCH validated via `UpdateSettingsRequest` FormRequest whitelist
- `AppServiceProvider::shouldBootPlugins()` tolerates missing DB during `composer install` / CI

### Fixed

- Integration webhook retry test stability; emitter no longer fails publish on dispatch errors

## [0.0.23] - 2026-05-31

### Added

- Settings module: `settings` table, `GET/PATCH /api/v1/settings`, Studio `/settings` page
- Onboarding wizard: `GET/POST /api/v1/onboarding/*`, Studio `/onboarding` with progress bar and setup journal
- `OnboardingService` with welcome, site type, starter content, integrations skip, and finish steps
- Starter presets: `blog` and `portfolio` in `StarterSiteService` (in addition to `business`)
- Dashboard setup history panel (last 10 onboarding/setup log entries)

### Fixed

- Settings PATCH with dotted keys (`site.title`) now persists correctly when sent as flat JSON keys

### Added

- System update API: `GET /api/v1/system/version`, `GET /api/v1/system/update/check`, `POST /api/v1/system/update/run`
- Shared `UpdateService` for CLI `luma:update` and web update flow
- Studio `/settings/updates` page for post-FTP migration step on shared hosting
- `make update` Makefile target
- `LUMA_SKIP_ONBOARDING` config flag for local development
- Distribution artifact smoke test and system/update PHPUnit coverage

### Changed

- `GET /api/v1/health` version now reads `config('luma.version')`

## [0.0.22] - 2026-05-31

### Added

- PHP requirement system: `bootstrap/luma-requirements.php`, `EnvironmentRequirementChecker`, `GET /api/v1/system/requirements`, `public/luma-requirements.php`
- Web installer: `GET/POST /api/v1/setup/*`, Studio wizard at `/studio/setup`, `setup_logs` + `luma_installation` tables
- `InstallService` shared by CLI and web setup; `StarterSiteService` with business preset
- `php artisan luma:update` for post-release migrate/cache clear
- Release packaging: `make release-shared`, `make release-docker`, `luma-manifest.json`, `INSTALL.txt`
- Deploy samples: `deploy/nginx/luma.conf`, `deploy/apache/*`, `.env.shared.example`

### Changed

- `luma:install` uses `InstallService`; optional `--with-starter-site`
- `make restore` runs `luma:update --force` instead of raw migrate

## [0.0.21] - 2026-05-31

### Added

- Production Docker profile: PostgreSQL, queue worker, scheduler (`docker compose --profile prod`)
- CLI installer: `php artisan luma:install` with `--force`, `--admin-email`, `--admin-password`
- Studio production build served at `/studio/` via nginx (same-origin API)
- Outer Makefile targets: `prod-setup`, `build-studio`, `backup`, `restore`, `logs-queue`
- Production docs: `docs/production/storage.md`, `backup-restore.md`, `hardening-checklist.md`
- `.env.production.example` for API and outer compose

### Changed

- Dev compose includes dedicated `queue` service for webhooks and media jobs
- Studio router uses Vite `BASE_URL` basename for `/studio/` deployments
- Sidebar link to Integration API tokens
- Rate limit on public form submit (`10/min` per IP)

## [0.0.20] - 2026-05-31

### Added

- Integrations module: outbound webhooks with HMAC signatures (`X-Luma-Signature`), delivery log, and manual retry
- Integration API tokens with scoped machine access (`content:read`, `forms:read_submissions`, `media:read`)
- Events: `page.published`, `entry.published`, `form.submission.created`
- Admin API: `/api/v1/integrations/webhooks`, `/integrations/tokens`, scoped `/api/v1/integration/*` routes
- Studio: `/integrations/webhooks`, `/integrations/tokens`
- RBAC: `integrations.manage`, `integrations.tokens.manage`, `integrations.deliveries.read`
- Tests: `WebhookSignatureServiceTest`, `IntegrationWebhookTest`, `IntegrationTokenTest`

## [0.0.19] - 2026-05-30

### Added

- Plugin block type registration via `PluginContext::registerBlockType()` and `render.block` extension point
- `BlockTypeRegistry`, `BlockTypeService`, and `GET /api/v1/editor/block-types` for Studio palette merge
- Dynamic plugin blocks in `PageContentValidator` and `BlockRendererRegistry`
- Plugin HTTP routes via `PluginContext::registerRoute()` guarded by `routes.register` capability
- `PluginRouteRegistry`, `EnsurePluginRouteCapability` middleware, and catch-all plugin route dispatcher
- Demo plugin v0.3.0 — `quote` block type
- Tests: `BlockTypeRegistryTest`, `PluginBlockTypeTest`, `PluginRouteTest`

## [0.0.18] - 2026-05-30

### Added

- Content lifecycle hooks: `content.afterCreate`, `content.afterUpdate`, `content.afterPublish` via `PluginHookService`
- `ContentLifecycleEvent` payload DTO wired into Create/Update/Publish Entry and Publish Page actions
- `admin.navigation` extension point with `AdminNavigationRegistry` and `GET /api/v1/admin/navigation-items`
- Studio plugin capabilities panel with dangerous-capability **Approve** flow
- Studio audit log page at `/plugins/audit-logs`
- Plugin sidebar items merged from enabled plugins (`admin.extend`)
- Demo plugin v0.2.0: `content.afterPublish` logging + «Demo insights» nav item
- Tests: `PluginHookServiceTest`, `ContentHookTest`, `AdminNavigationTest`, dangerous-capability lifecycle test

## [0.0.17] - 2026-05-31

### Added

- Ephemeral page preview API (`POST /api/v1/pages/preview-html`) for unsaved pages on `/pages/new`
- Section templates in Studio (Blank, Landing, Contact page, Full landing)
- New block types: `feature_grid`, `faq` (renderer, validator, inspector with item lists)
- Grouped block palette (Content / Actions / Forms)

## [0.0.16] - 2026-05-31

### Added

- Phase 4.2: drag-and-drop block reorder in visual page editor
- Phase 5 plugin foundation: manifest validation, lifecycle (discover/install/enable/disable/uninstall)
- Plugin tables (`plugins`, `plugin_capabilities`, `audit_logs`) and `PluginContext` API
- Plugin admin API and Studio page at `/plugins`
- Internal demo plugin at `plugins/luma.demo/`

### Fixed

- Advanced JSON editor keeps local draft while JSON is invalid (no more reset on typo)

## [0.0.15] - 2026-05-31

### Added

- Visual page editor (Phase 4 MVP): block palette, reorder list, typed block inspector
- Live server-side page preview for draft pages (`GET/POST /api/v1/pages/{slug}/preview-html`)
- `PageRenderService` shared between public renderer and preview API
- Studio 3-column page edit layout: blocks / preview / inspector

## [0.0.14] - 2026-05-31

### Added

- Forms module (Phase 3.4): `forms`, `form_fields`, `form_submissions` tables
- Forms CRUD API with `forms.manage` and `forms.read_submissions` permissions
- Public form submit at `POST /public/forms/{slug}/submit` with honeypot anti-spam
- `contact_form` page block type linked to form slug
- Studio forms admin at `/forms` and read-only submissions inbox
- `ContactFormSeeder` and demo contact block on `/p/home`

## [0.0.13] - 2026-05-31

### Added

- SEO module (Phase 3.3): `GET /sitemap.xml`, `GET /robots.txt` for published pages
- URL redirects table, global redirect middleware, CRUD API with `seo.manage` permission
- Canonical URL and `og:url` on public page renderer
- Studio redirects UI at `/seo/redirects`
- Reserved page slugs (`admin`, `api`, `p`, `sitemap.xml`, `robots.txt`)
- Vite dev proxy for `/sitemap.xml` and `/robots.txt`

### Fixed

- Studio Tailwind utilities not applied when `@import 'tailwindcss'` lived in SCSS — split to `tailwind.css`

## [0.0.12] - 2026-05-31

### Added

- Page block templates in Studio (hero, rich text, CTA, business landing preset)
- SEO metadata preview pane on page create/edit (search + Open Graph warnings)
- Navigation hub at `/menus` with header and footer menu editors
- Menu item reorder (Up/Down) in Studio
- Public footer menu on rendered pages
- `DemoSiteSeeder` for local onboarding (`home` page + header/footer menus)
- Product smoke test plan and `make setup` / `make test-api` Makefile targets

### Fixed

- API unauthenticated requests return 401 instead of 500 when no `login` web route exists
- Docker storage directory permissions via PHP entrypoint

## [0.0.11] - 2026-05-31

### Added

- Pages module (Phase 3.1): CRUD, publish/unpublish, public API, block content validation
- Public HTML page renderer at `GET /p/{slug}` with hero, rich_text, cta blocks
- Page SEO fields (title, description, og_image) with meta tags in public renderer
- Navigation module: menus + menu items API, public menu read, Studio header editor
- Studio pages list/create/edit with JSON block editor and SEO form
- Studio Design System Foundation (Phase 2X): expanded SCSS tokens, CSS theme vars
- Layout primitives: AdminShell, AppSidebar, ListPage, FormPage, SplitPane, PreviewPane, SettingsPanel
- UI primitives: Table, Checkbox, Fieldset; shared form field styles
- Design system documentation: `apps/studio/docs/studio-design-system.md`

### Changed

- AppShell refactored to AdminShell layout primitives
- Collections, pages, navigation, and entry preview migrated to ListPage/Table/Input primitives

## [0.0.10] - 2026-05-30

### Added

- Media Core module: upload, list, show, update alt text, delete (soft delete)
- Media RBAC permissions: `media.read`, `media.upload`, `media.update`, `media.delete`
- Image thumbnail variants via `GenerateMediaVariantsJob`
- Public media read: `GET /api/v1/public/media/{uuid}`
- Studio media library UI (upload, grid, alt edit, delete, copy URL)
- Docker volume `luma-api-storage` for persistent API uploads (outer workspace)

### Changed

- Studio `/media` replaces placeholder stub
- Updated API and Studio READMEs for media workflows

## [0.0.9] - 2026-05-30

### Added

- Studio Phase 2 Core: Bearer token auth, protected routes, app shell
- Collections, fields, and entries management UI with RBAC-aware actions
- Entry publish/unpublish, admin + public preview page
- Media placeholder page (stub until Media Core backend)
- Dashboard with API health and collection count
- GitHub Actions workflow: Studio build and lint (`apps/studio`)

### Changed

- Refactored Studio to feature-based folder structure
- Updated Studio README with routes, auth flow, and smoke test checklist

## [0.0.8] - 2026-05-31

### Added

- GitHub Actions workflow: PHP build and test (`apps/api`, Composer + PHPUnit)
- GitLab CI pipeline: build and test stages for the Laravel API

### Changed

- Updated root README, CONTRIBUTING, and app READMEs for current Phase 1 status
- CONTRIBUTING: correct repo URL, CI instructions, removed stale “no dev setup” note

## [0.0.7] - 2026-05-31

### Added

- Entries CRUD with draft/publish workflow
- `EntryDataValidator` — strict schema validation against collection fields
- Publish/unpublish actions with `entry_versions` snapshot on publish
- Public read API for published entries (`/api/v1/public/...`)
- `EntryPolicy` with RBAC permission checks
- Feature tests for entries and public entry API

## [0.0.6] - 2026-05-31

### Added

- Nested Fields CRUD API under collections (`/api/v1/collections/{slug}/fields`)
- `FieldType` enum: text, textarea, number, boolean, datetime, json
- Field actions with automatic `schema_version` bump on create/update/delete
- `FieldPolicy` with RBAC permission checks
- Feature tests for fields API (401/403/404/422, schema versioning)

## [0.0.5] - 2026-05-31

### Added

- Laravel Sanctum API token authentication
- RBAC: roles, permissions, role/permission pivots
- Auth module: `POST /api/v1/auth/login`, `POST /api/v1/auth/logout`, `GET /api/v1/auth/me`
- Users module: Role/Permission models, PermissionEvaluator, RolesAndPermissionsSeeder
- Collections API protected with `auth:sanctum` and permission-based CollectionPolicy
- Feature tests for auth and collection authorization (401/403)

### Changed

- Collections API now requires Bearer token
- Removed temporary CollectionPolicy local/testing bypass

## [0.0.4] - 2026-05-30

### Added

- Content model migrations: collections, fields, entries, entry_versions
- Content module with Actions, Form Requests, API Resources, Policy
- Collections CRUD API: `/api/v1/collections`
- Feature tests for collections API

## [0.0.3] - 2026-05-30

### Added

- React Studio scaffold in `apps/studio/` (Vite, TypeScript, Tailwind, TanStack Query, Zod)
- Typed API client and health check page
- Vite dev proxy for `/api` → Laravel backend

## [0.0.2] - 2026-05-30

### Added

- Laravel 13 API scaffold in `apps/api/`
- Modular directories: `Core/`, `Modules/`, `Support/`
- Versioned API route: `GET /api/v1/health`
- Feature test for health endpoint

## [0.0.1] - 2026-05-30

### Added

- Monorepo skeleton: `apps/api/`, `apps/studio/`, `packages/{sdk,plugin-sdk,ui}/`
- Placeholder README files describing planned structure for each package

## [0.0.0] - 2026-05-30

### Added

- MIT License
- Product foundation documentation (README, CONTRIBUTING, docs/)

### Notes

Pre-alpha. No runnable application. Documentation-only foundation release.
