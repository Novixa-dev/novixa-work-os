# Release Readiness Checklist

Complete this checklist before opening Novixa WorkOS to the real team.

## Product and UX
- [ ] MVP journeys are implemented end-to-end.
- [ ] No dead buttons, fake integrations, or placeholder routes are exposed.
- [ ] All critical flows work in Arabic RTL and English LTR.
- [ ] Desktop, tablet, and mobile layouts are reviewed.
- [ ] Keyboard navigation, focus, labels, dialogs, and contrast are checked.
- [ ] User onboarding guide and task/project conventions are available.

## Security
- [ ] Authentication and password recovery are verified.
- [ ] CSRF, session cookies, CORS, and TLS are verified in staging.
- [ ] Permission matrix and IDOR tests pass.
- [ ] Private file access and download authorization are tested.
- [ ] Rate limits and safe error messages are verified.
- [ ] Secrets are not in Git, image layers, browser bundles, or logs.
- [ ] Production debug mode is off; security headers are configured.
- [ ] Dependencies and container images are reviewed for known vulnerabilities.

## Data and reliability
- [ ] Migrations apply to a fresh database.
- [ ] Upgrade path is tested against representative staging data.
- [ ] Foreign keys, constraints, indexes, and archive behavior are reviewed.
- [ ] Database and uploaded-file backups are enabled and encrypted.
- [ ] Restore drill has succeeded and evidence is recorded.
- [ ] Queue failures, scheduler, health checks, and alerts are verified.

## Quality
- [ ] Formatting, lint, type checking, static analysis, and build pass.
- [ ] Backend unit and feature tests pass.
- [ ] Frontend tests pass.
- [ ] Critical Playwright E2E journeys pass.
- [ ] No unresolved P0/P1 defect remains.
- [ ] Known limitations are documented and accepted.

## Deployment
- [ ] Production environment variables are configured securely.
- [ ] Domain, DNS, TLS, reverse proxy, and firewall are verified.
- [ ] Database and Redis (if used) are not publicly exposed.
- [ ] Queue worker and scheduler are running.
- [ ] Persistent storage/volumes are verified.
- [ ] Rollback and incident contacts/runbook are available.
- [ ] Initial accounts are provisioned securely; no default admin password remains.

## Go / No-Go

Release is **Go** only when all P0 items are checked, no critical/high security finding is unresolved, backup restoration is proven, and an authorized Novixa owner accepts any remaining known limitations.
