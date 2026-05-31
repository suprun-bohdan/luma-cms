# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html) once releases begin.

## [Unreleased]

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
