# Integrations

## Outbound webhooks

Configure webhooks in **Luma Studio → Integrations → Webhooks**.

Events include:

- `page.published`
- `entry.published`
- `form.submission.created`

Each delivery is signed with HMAC (`X-Luma-Signature`). Failed deliveries retry via the queue with exponential backoff. View delivery history and manually retry from the admin UI.

## Integration API tokens

**Integrations → API Tokens** creates scoped bearer tokens for machine access:

- `content:read`
- `forms:read_submissions`
- `media:read`

Tokens are stored hashed; copy the plain token once at creation.

## Public integration routes

Scoped routes live under `/api/v1/integration/*` and require a valid integration token with the matching scope.

## Operations

Ensure a queue worker processes `DeliverWebhookJob`. On shared hosting without a daemon, use cron-driven `queue:work --stop-when-empty` or your host's queue panel.

See [security.md](./security.md) for webhook signing and failure isolation.
