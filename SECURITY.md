# Security Policy

## Supported versions

Luma CMS is in **pre-alpha**. There is no production-ready release.

| Version | Supported |
|---------|-----------|
| Pre-alpha (current) | Best-effort security fixes for reported issues |

Once stable releases exist, this table will list supported versions.

## Reporting a vulnerability

**Do not** report security vulnerabilities in public GitHub issues.

Please report vulnerabilities privately:

1. Email the maintainers (contact to be published when the project opens for wider contribution), or
2. Use GitHub Security Advisories if enabled for this repository.

Include:

- Description of the vulnerability
- Steps to reproduce
- Potential impact
- Suggested fix (if any)

We will acknowledge receipt within a reasonable timeframe and work on a fix before public disclosure when appropriate.

## Security principles

Luma CMS is designed with security in mind from the start. See [docs/security.md](docs/security.md) for the full security model, including:

- Default deny and least privilege
- Role-based access control (RBAC)
- Plugin capability model
- Audit logging for dangerous actions
- Safe handling of AI-assisted features

## Scope

Reports related to the Luma CMS product code in this repository are in scope.

Reports about third-party dependencies, hosting configuration, or optional local development tooling outside this repository should be directed to the appropriate vendor or maintainer.
