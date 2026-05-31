# Luma CMS v0.0.25-rc.10 — Database setup fix

Fixes HTTP 500 on **Test connection & continue** (SQLite/MySQL/PostgreSQL/MariaDB).

## Fixes

- Save database settings returns 422 with a clear message instead of 500
- SQLite: creates and tests `apps/api/database/database.sqlite` by default
- Release zip: writable `database/`, pre-created `.env`, chmod for setup
- Wizard restores your last step after browser refresh (localStorage)

Download: `luma-cms-0.0.25-rc.10-shared.zip`

Tag: `v0.0.25-rc.10`
