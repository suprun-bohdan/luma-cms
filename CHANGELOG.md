# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html) once releases begin.

## [Unreleased]

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
