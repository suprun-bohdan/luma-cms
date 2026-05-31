# Luma API

Laravel backend for Luma CMS.

> **Status:** Pre-alpha. Content Core + Media Core API with auth/RBAC.

## Stack

- PHP 8.3+
- Laravel 13
- Laravel Sanctum (API tokens)
- SQLite (local dev default); PostgreSQL planned for production

## Structure

```text
apps/api/
  app/
    Core/           # Kernel, contracts, bootstrapping
    Modules/
      Auth/         # Login, logout, me
      Users/        # Roles, permissions, RBAC
      Content/      # Collections, fields, entries
      Media/        # Upload, storage, variants
      Forms/        # Forms, fields, submissions
      Seo/
      Plugins/
      Settings/
    Support/
    Http/Controllers/
  routes/
    api.php         # Versioned API routes
  database/
  tests/
```

## API

Public web routes (no API prefix):

```
GET /p/{slug}              Public HTML page renderer
GET /sitemap.xml           Published pages sitemap
GET /robots.txt            Crawler rules + sitemap reference
POST /public/forms/{slug}/submit   Public HTML form submit (CSRF + honeypot)
```

Public API routes:

Authenticated routes (`Authorization: Bearer {token}`):

```
POST   /api/v1/auth/logout
GET    /api/v1/auth/me
GET    /api/v1/collections
POST   /api/v1/collections
GET    /api/v1/collections/{slug}
PUT    /api/v1/collections/{slug}
DELETE /api/v1/collections/{slug}
GET    /api/v1/collections/{slug}/fields
POST   /api/v1/collections/{slug}/fields
GET    /api/v1/collections/{slug}/fields/{field}
PUT    /api/v1/collections/{slug}/fields/{field}
DELETE /api/v1/collections/{slug}/fields/{field}
GET    /api/v1/collections/{slug}/entries          ?status=draft|published|archived
POST   /api/v1/collections/{slug}/entries
GET    /api/v1/entries/{id}
PUT    /api/v1/entries/{id}
DELETE /api/v1/entries/{id}
POST   /api/v1/entries/{id}/publish
POST   /api/v1/entries/{id}/unpublish
GET    /api/v1/media
POST   /api/v1/media                         multipart: file, optional alt_text
GET    /api/v1/media/{uuid}
PUT    /api/v1/media/{uuid}                  { "alt_text": "..." }
DELETE /api/v1/media/{uuid}
GET    /api/v1/redirects
POST   /api/v1/redirects
GET    /api/v1/redirects/{id}
PUT    /api/v1/redirects/{id}
DELETE /api/v1/redirects/{id}              requires seo.manage
GET    /api/v1/forms
POST   /api/v1/forms
GET    /api/v1/forms/{slug}
PUT    /api/v1/forms/{slug}
DELETE /api/v1/forms/{slug}              requires forms.manage
GET    /api/v1/forms/{slug}/submissions  requires forms.read_submissions
GET    /api/v1/plugins/discover               requires plugins.manage
POST   /api/v1/plugins/install                requires plugins.manage
GET    /api/v1/plugins                        requires plugins.manage
POST   /api/v1/plugins/{plugin_id}/enable     requires plugins.manage
POST   /api/v1/plugins/{plugin_id}/disable    requires plugins.manage
POST   /api/v1/plugins/{plugin_id}/capabilities/approve  requires plugins.manage
DELETE /api/v1/plugins/{plugin_id}            requires plugins.manage
GET    /api/v1/audit-logs                     requires plugins.audit
POST   /api/v1/pages/preview-html               requires pages.view (unsaved page preview)
GET    /api/v1/pages/{slug}/preview-html       requires pages.view
POST   /api/v1/pages/{slug}/preview-html       requires pages.view (live preview body)
```

Laravel health check:

```
GET /up
```

### Auth (local dev)

After migrate + seed:

```bash
docker compose exec php bash -c "cd apps/api && php artisan migrate && php artisan db:seed"
```

Default admin (override via `.env`):

| Variable | Default |
|----------|---------|
| `LUMA_SEED_ADMIN_EMAIL` | `admin@luma.test` |
| `LUMA_SEED_ADMIN_PASSWORD` | `password` |

Login:

```bash
curl -s -X POST http://localhost:8080/api/v1/auth/login \
  -H 'Content-Type: application/json' \
  -d '{"email":"admin@luma.test","password":"password"}'
```

### RBAC

| Role | Permissions |
|------|-------------|
| `admin` | all content.*, media.*, pages.*, menus.*, seo.manage, forms.*, plugins.manage, plugins.audit |
| `editor` | content.view/create/update; media.read/upload/update; pages.view/create/update/publish; menus.view/update (no delete) |

### Plugins

Plugins live under `plugins/` at the repo root (override with `LUMA_PLUGINS_PATH`). Lifecycle:

1. **Discover** — scan manifests on disk
2. **Install** — persist manifest snapshot in SQLite
3. **Enable** — load backend entrypoint (`PluginContract::register` + `boot`)
4. **Disable** / **Uninstall** — unregister and remove

Each plugin ships `luma.plugin.json` and a backend class implementing `App\Modules\Plugins\Contracts\PluginContract`. Plugins receive `PluginContext` only (not the Laravel container).

Dangerous capabilities require explicit admin approval before enable. Lifecycle events are written to `audit_logs`.

Demo plugin: `plugins/luma.demo/` (extension point `system.booted`).

### Media

Upload (multipart):

```bash
curl -s -X POST http://localhost:8080/api/v1/media \
  -H 'Authorization: Bearer {token}' \
  -F 'file=@/path/to/photo.jpg' \
  -F 'alt_text=Hero image'
```

Public read:

```bash
curl -s http://localhost:8080/api/v1/public/media/{uuid}
```

Storage:

- Files on the `public` disk under `storage/app/public/media/{uuid}/`
- Run once after migrate: `php artisan storage:link`
- Outer Docker workspace mounts `luma-api-storage` volume on `apps/api/storage` for persistent uploads

Config: `config/media.php` (max size, allowed mime types, thumbnail width).

### SEO (Phase 3.3)

Sitemap and robots (set `APP_URL=http://localhost:8080` in Docker dev):

```bash
curl -s http://localhost:8080/sitemap.xml
curl -s http://localhost:8080/robots.txt
```

Create redirect:

```bash
curl -s -X POST http://localhost:8080/api/v1/redirects \
  -H 'Authorization: Bearer {token}' \
  -H 'Content-Type: application/json' \
  -d '{"from_path":"/legacy","to_path":"/p/home","status_code":301}'
```

Reserved page slugs: `admin`, `api`, `p`, `sitemap.xml`, `robots.txt`.

### Forms (Phase 3.4)

Seed default contact form (included in `DemoSiteSeeder`):

```bash
docker compose exec php bash -c "cd apps/api && php artisan db:seed --class=App\\\\Modules\\\\Forms\\\\Database\\\\Seeders\\\\ContactFormSeeder"
```

List forms:

```bash
curl -s http://localhost:8080/api/v1/forms \
  -H 'Authorization: Bearer {token}'
```

Public submit (from HTML form on `/p/home`; honeypot field `_hp` must stay empty):

```bash
curl -s -X POST http://localhost:8080/public/forms/contact/submit \
  -H 'Cookie: ...' \
  -F '_token={csrf}' \
  -F '_hp=' \
  -F 'name=Ada' \
  -F 'email=ada@example.com' \
  -F 'message=Hello'
```

Page block type `contact_form` props: `form_slug`, `title`, `submit_label`.

### Visual editing (Phase 4)

Preview draft page HTML (admin, `pages.view`):

```bash
curl -s http://localhost:8080/api/v1/pages/home/preview-html \
  -H 'Authorization: Bearer {token}'
```

Preview unsaved content (POST body overrides title/content/seo):

```bash
curl -s -X POST http://localhost:8080/api/v1/pages/home/preview-html \
  -H 'Authorization: Bearer {token}' \
  -H 'Content-Type: application/json' \
  -d '{"title":"Draft","content":{"blocks":[{"id":"t1","type":"rich_text","props":{"body":"Hello"}}]}}'
```

Studio: edit page at `/pages/{slug}/edit` — visual block list, inspector, live preview pane.

### Fields

Supported field types: `text`, `textarea`, `number`, `boolean`, `datetime`, `json`.

Create field example:

```bash
curl -s -X POST http://localhost:8080/api/v1/collections/pages/fields \
  -H 'Authorization: Bearer {token}' \
  -H 'Content-Type: application/json' \
  -d '{"name":"Title","type":"text","required":true}'
```

Adding, updating (schema-affecting attrs), or deleting a field increments the parent collection `schema_version`.

### Entries

Entry `data` is validated against the collection field schema (strict keys, required fields, type checks).

Create draft entry:

```bash
curl -s -X POST http://localhost:8080/api/v1/collections/pages/entries \
  -H 'Authorization: Bearer {token}' \
  -H 'Content-Type: application/json' \
  -d '{"data":{"title":"Hello"}}'
```

Publish (creates `entry_versions` snapshot with current `schema_version`):

```bash
curl -s -X POST http://localhost:8080/api/v1/entries/1/publish \
  -H 'Authorization: Bearer {token}'
```

Public read (published entries only, no auth):

```bash
curl -s http://localhost:8080/api/v1/public/collections/pages/entries
```

Note: single-entry JSON responses return fields at the root (no outer `data` wrapper) because the payload attribute is also named `data`. List responses use a standard `data` array wrapper.

## CI

On push/PR to `main`, PHP 8.4 build and tests run automatically:

- **GitHub Actions:** `.github/workflows/php.yml`
- **GitLab CI:** `.gitlab-ci.yml`

Steps: `composer validate` → `composer install` → Laravel `.env` bootstrap → `php artisan test` (SQLite in-memory).

## Local development

From the outer dev workspace (Docker):

```bash
make up
# API: http://localhost:8080/api/v1/health
```

Run tests inside the PHP container:

```bash
docker compose exec php bash -c "cd apps/api && php artisan test"
```

## Rules

- Thin controllers; business logic in Actions, Services, Policies
- Public API uses `/api/v1/` prefix
- API Resources for all public responses
- SQLite is source of truth; policies enforce RBAC permissions
