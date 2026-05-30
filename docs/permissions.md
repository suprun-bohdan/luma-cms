# Permissions

Luma CMS uses a layered permission model with **default deny**.

> **Status:** Planned model. RBAC in Phase 1; ABAC and field-level permissions in later phases.

## Effective permission formula

```
effective_permission = user_permission ∩ plugin_capability ∩ system_policy
```

If **any** layer denies an action, the action is denied.

This applies when a plugin acts on behalf of a user or extends system behavior.

## Separate concepts

Do not mix these layers:

| Layer | Description |
|-------|-------------|
| **User permissions** | Direct grants to a user |
| **Role permissions** | Grants inherited through a role (RBAC) |
| **Plugin capabilities** | What a plugin is allowed to do when enabled |
| **API token scopes** | Restrictions on programmatic API access |
| **System policies** | Global rules enforced by the core (e.g. maintenance mode, rate limits) |

## Default deny

- Users have no permissions unless explicitly granted through roles or direct assignment.
- Plugins have no capabilities unless declared in their manifest and granted by an administrator.
- API tokens are scoped to specific operations.

## Role-based access control (Phase 1)

MVP 0.1 introduces basic RBAC:

- Users belong to roles
- Roles carry permissions (e.g. `content.create`, `content.publish`)
- Policies enforce permissions on API endpoints

## Planned extensions

- **Collection-level permissions** — restrict access per content type
- **Field-level permissions** — restrict read/write on specific fields
- **Attribute-based access control (ABAC)** — context-aware rules (future)

## Plugin capabilities

Plugins declare required capabilities in `luma.plugin.json`. The system grants capabilities at install/enable time. Dangerous capabilities require explicit admin approval and audit logging.

Examples:

- `content.read`, `content.create`, `content.update`, `content.delete`, `content.publish`
- `media.upload`, `media.delete`
- `ai.generate`, `ai.read_context` (dangerous)
- `system.settings.update`, `database.migrate` (dangerous)

See [extensions.md](extensions.md) for the full capability model.

## Dangerous capabilities

These require explicit approval and audit logging:

- `system.settings.update`
- `database.migrate`
- `routes.register`
- `ai.read_context`
- `content.bulk_update`
- `users.read`, `users.update`
- `secrets.read`

## Related documents

- [extensions.md](extensions.md) — plugin capabilities and lifecycle
- [security.md](security.md) — audit logging and security principles
