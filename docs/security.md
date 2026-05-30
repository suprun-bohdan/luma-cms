# Security

Luma CMS is designed with security as a first-class concern from the start.

> **Status:** Principles defined. Implementation begins with Phase 1 (Content Core).

## Default security position

- **Default deny** — no access unless explicitly granted
- **Least privilege** — grant minimum required permissions
- **Explicit permissions** — no implicit or inherited access without definition
- **Audit dangerous actions** — log security-relevant events
- **Never trust** plugin code, request input, or AI output

## Required security concepts

The system is designed to support:

| Concept | Phase |
|---------|-------|
| RBAC (role-based access control) | Phase 1 |
| ABAC (attribute-based access control) | Future |
| Field-level permissions | Future |
| Collection-level permissions | Future |
| Plugin capabilities | Phase 3 |
| API token scopes | Phase 1+ |
| Audit logs | Phase 3 |
| CSRF protection | Phase 1 |
| XSS protection | Phase 1 |
| Rate limiting | Phase 1+ |
| Secure sessions | Phase 1 |
| Encrypted secrets | Phase 1+ |
| Safe migrations | Phase 1+ |

## Permission model

See [permissions.md](permissions.md) for the full model.

```
effective_permission = user_permission ∩ plugin_capability ∩ system_policy
```

## Secrets

- Do not store secrets in plain database settings
- Use environment variables or encrypted settings
- Never commit secrets to the repository
- See [SECURITY.md](../SECURITY.md) for vulnerability reporting

## Audit log events

The system will log at least:

**Plugin lifecycle:**

- `plugin.installed`, `plugin.enabled`, `plugin.disabled`, `plugin.uninstalled`
- `plugin.permission_granted`, `plugin.permission_revoked`
- `plugin.migration_ran`, `plugin.failed`

**Content:**

- `content.created`, `content.updated`, `content.published`, `content.deleted`

**Authentication:**

- `user.login`, `user.failed_login`

**Authorization:**

- `role.updated`, `permission.updated`

## Plugin security

- Plugins use `PluginContext`, not the full Laravel container
- Plugins cannot run raw SQL or migrations without explicit capability
- Dangerous capabilities require admin approval and audit logging
- See [extensions.md](extensions.md)

## AI security

AI features (planned, Phase 5) must be permission-controlled.

- The capability `ai.read_context` is **dangerous** — it allows reading content context for AI prompts
- AI output must not be blindly executed as code, SQL, migrations, policies, or configuration
- All AI operations are audit-logged

## Input validation

- All API input validated through Form Requests or typed Data objects
- No mass assignment of raw request data
- Output escaped appropriately to prevent XSS

## Related documents

- [permissions.md](permissions.md)
- [extensions.md](extensions.md)
- [SECURITY.md](../SECURITY.md)
