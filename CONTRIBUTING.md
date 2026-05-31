# Contributing to Luma CMS

Thank you for your interest in Luma CMS. This project is in **pre-alpha** — APIs and structure may change until the first stable release.

## Getting started

1. Clone the repository:

   ```bash
   git clone https://github.com/suprun-bohdan/luma-cms.git
   cd luma-cms
   ```

2. Read before coding:

   - [README.md](README.md) — project status and layout
   - [CHANGELOG.md](CHANGELOG.md) — what shipped recently
   - [apps/api/README.md](apps/api/README.md) — API, auth, install, tests
   - [apps/studio/README.md](apps/studio/README.md) — Studio routes, lint, build

3. Stay in scope. Core CMS, Studio, installer, and plugin foundation are in place. Prefer fixes and incremental features over marketplace, SaaS billing, or full e-commerce before the first stable release.

## How to contribute

### Issues

- Search existing issues before opening a new one.
- Use clear titles and describe expected vs actual behavior.
- For security vulnerabilities, see [SECURITY.md](SECURITY.md) — do not report zero-days in public issues.

### Pull requests

- Keep PRs small and focused on one concern.
- Reference related issues when applicable.
- Update README / CHANGELOG when behavior or public API changes.
- Add or update feature tests when API behavior changes.

### CI

Pull requests and pushes to `main` run **path-filtered** pipelines:

| Stack | GitHub Actions | GitLab CI | Local check |
|-------|----------------|-----------|-------------|
| Laravel API | [`.github/workflows/php.yml`](.github/workflows/php.yml) | `build` + `test` jobs | `cd apps/api && composer install && php artisan test` |
| Luma Studio | [`.github/workflows/studio.yml`](.github/workflows/studio.yml) | `studio:build` job | `cd apps/studio && npm ci && npm run lint && npm run build` |

Ensure the relevant jobs pass locally before pushing. API-only changes do not run Studio CI (and vice versa) until both areas are touched.

**API (PHP 8.4, SQLite in-memory tests):**

```bash
cd apps/api
composer install
cp .env.example .env && php artisan key:generate
php artisan test
```

**Studio (Node 22):**

```bash
cd apps/studio
npm ci
npm run lint
npm run build
```

### Code style

**Backend (Laravel / PHP 8.3+):**

- Typed properties and return types
- Thin controllers; business logic in Actions, Services, or Policies
- Form Requests for validation; API Resources for responses
- Feature tests for API behavior; policy tests for permissions

**Frontend (React / TypeScript):**

- Strict TypeScript; avoid `any`
- TanStack Query for server state; Zod for API validation
- Small components; separate API clients, hooks, and UI

**General:**

- No jQuery
- No procedural architecture
- No raw HTML as content source of truth
- No new dependencies without clear justification

## Development environment

The Laravel API lives in `apps/api/`. Use PHP 8.3+ with Composer, or an optional outer Docker stack if you have one. See [apps/api/README.md](apps/api/README.md).

Luma Studio (`apps/studio/`) expects the API reachable for `/api` calls during local dev.

## Code of conduct

This project follows the [Contributor Covenant](CODE_OF_CONDUCT.md). By participating, you agree to uphold it.

## Questions

Open a GitHub Discussion or issue for questions about direction, scope, or architecture.
