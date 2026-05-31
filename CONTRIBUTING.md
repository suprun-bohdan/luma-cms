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
   - [apps/api/README.md](apps/api/README.md) — API, auth, local setup, tests
   - [apps/studio/README.md](apps/studio/README.md) — frontend scaffold

3. Stay in scope. Phase 1 (Content Core API) backend is done; next focus is **Studio Core** (login, collections, fields, entries UI). Do not build marketplace, full visual builder, or e-commerce engine before core workflows are stable.

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

Pull requests and pushes to `main` run **PHP build & test** (Composer + `php artisan test`) via:

- GitHub Actions: [`.github/workflows/php.yml`](.github/workflows/php.yml)
- GitLab CI: [`.gitlab-ci.yml`](.gitlab-ci.yml)

Ensure tests pass locally before pushing:

```bash
cd apps/api
composer install
cp .env.example .env && php artisan key:generate
php artisan test
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
