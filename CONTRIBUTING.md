# Contributing to Luma CMS

Thank you for your interest in Luma CMS. This project is in **pre-alpha** — APIs and structure may change until the first stable release.

## Getting started

1. Clone the repository:

   ```bash
   git clone https://github.com/your-org/luma-cms.git
   cd luma-cms
   ```

2. Read the documentation before writing code:

   - [docs/vision.md](docs/vision.md)
   - [docs/architecture.md](docs/architecture.md)
   - [docs/roadmap.md](docs/roadmap.md)
   - [docs/development.md](docs/development.md)

3. Check [docs/roadmap.md](docs/roadmap.md) for current phase and scope. Do not implement features marked as "Not Now".

## How to contribute

### Issues

- Search existing issues before opening a new one.
- Use clear titles and describe expected vs actual behavior.
- For security vulnerabilities, see [SECURITY.md](SECURITY.md) — do not report zero-days in public issues.

### Pull requests

- Keep PRs small and focused on one concern.
- Reference related issues when applicable.
- Update documentation when behavior or architecture changes.
- Add tests when application code exists and your change affects behavior.

### Code style (when code exists)

**Backend (Laravel / PHP 8.3+):**

- Typed properties and return types
- Thin controllers; business logic in Actions, Services, or Policies
- Form Requests for validation; API Resources for responses
- Feature tests for API behavior; policy tests for permissions

**Frontend (React / TypeScript):**

- Strict TypeScript; avoid `any`
- TanStack Query for server state; Zod for form validation
- Small components; separate API clients, hooks, and UI

**General:**

- No jQuery
- No procedural architecture
- No raw HTML as content source of truth
- No new dependencies without clear justification

## Development environment

A runnable development setup is not available yet. Instructions will be added in [docs/development.md](docs/development.md) once the monorepo skeleton and first API exist.

Some contributors may use an optional outer local workspace with Docker for convenience. That workspace is **not** part of this repository and is not required to contribute to Luma CMS.

## Code of conduct

This project follows the [Contributor Covenant](CODE_OF_CONDUCT.md). By participating, you agree to uphold it.

## Questions

Open a GitHub Discussion or issue for questions about direction, scope, or architecture.
