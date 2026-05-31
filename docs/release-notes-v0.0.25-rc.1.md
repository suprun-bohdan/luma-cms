# Luma CMS v0.0.25-rc.1 — First installable pre-alpha

## Summary

First installable pre-alpha release candidate of Luma CMS with web setup, Luma Studio, structured content, pages, media, forms, SEO, plugins, integrations, and shared-hosting release packaging.

This is a pre-alpha release candidate for dogfooding and early testing. **Not recommended for client production websites yet.**

## What works

- Browser-first setup wizard at `/admin/setup` (no Composer/npm required on host)
- Luma Studio admin UI at `/admin/` with structured content, pages, media, forms
- Public pages at `/p/{slug}`, sitemap, robots.txt
- Plugin runtime (trusted PHP) with capability approval
- Webhooks and integration tokens
- Shared-hosting release zip with bundled `vendor/` and built Studio
- CLI install for developers and Docker automation (secondary path)
- System updates via Studio (owner role) or `php artisan luma:update`

## Installation path

1. Download `luma-cms-0.0.25-rc.1-shared.zip`
2. Extract on hosting; set document root to `apps/api/public`
3. Copy `apps/api/.env.shared.example` to `apps/api/.env`
4. Open `/luma-requirements.php` → continue to `/admin/setup`
5. Complete setup wizard → sign in at `/admin/login`
6. Onboarding → create and publish first page at `/p/{slug}`

See `INSTALL.txt` in the release archive.

## Known limitations

- Plugin PHP is trusted, not sandboxed
- No marketplace or plugin upload UI
- No full theme ecosystem
- No automatic remote updater
- MySQL shared-hosting E2E may need one more real hosting pass
- Setup token browser E2E may need one more pass
- Redis is optional; file cache/session used by default

## Security notes

- Document root must be `apps/api/public` only
- Keep `.env` private; optional `LUMA_SETUP_TOKEN` protects setup API
- Use strong owner passwords (12+ characters in production)
- Setup endpoints disabled after installation

## Not production-stable yet

Pre-alpha quality: expect breaking changes before 1.0. Use for evaluation, internal dogfooding, and install rehearsal — not mission-critical production sites.
