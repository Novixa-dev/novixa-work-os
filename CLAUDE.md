# CLAUDE.md — Novixa WorkOS

This file contains persistent project instructions for Claude Code. Read it before making changes.

## Mission

Build and maintain **Novixa WorkOS**, a private internal operating workspace for Novixa. It connects tasks, projects, products, clients, team coordination, and company knowledge. The first release serves one company/workspace only.

## Read before acting

For significant work, consult the relevant documents under `docs/`. The product brief and technical constraints are defined in:

- `docs/01-PRODUCT-VISION.md`
- `docs/02-NOVIXA-CONTEXT.md`
- `docs/03-PRD.md`
- `docs/05-SCOPE-AND-ROADMAP.md`
- `docs/10-TECHNICAL-ARCHITECTURE.md`
- `docs/12-ROLES-AND-PERMISSIONS.md`
- `docs/13-SECURITY.md`
- `docs/15-ACCEPTANCE-CRITERIA.md`
- `docs/16-IMPLEMENTATION-PLAN.md`

Use `MASTER_PROMPT.md` as the main execution brief. Do not treat assumptions as confirmed facts. Inspect the actual repository before relying on any planned folder or dependency.

## Non-negotiable principles

1. **Working software over mockups.** Every visible control must work, be intentionally disabled with an explanation, or be clearly marked as not yet implemented. Never fake success.
2. **Server-enforced authorization.** Hiding a button is not access control. Every protected API action and record lookup must authorize the current user.
3. **Arabic-first, bilingual.** Support Arabic and English throughout, including correct RTL/LTR layout, direction-aware icons/spacing, validation messages, dates, and accessible labels.
4. **One workspace first.** Do not add multi-tenancy, billing, a public client portal, or microservices without an evidenced requirement and an ADR.
5. **Maintainable architecture.** Use a modular monolith, explicit domain boundaries, small components, typed contracts, and boring proven technology.
6. **No secret leakage.** Never commit credentials, tokens, `.env` files, private client information, or production database dumps.
7. **Test behavior.** Add automated tests for business rules, permissions, API behavior, and critical user journeys.
8. **Data integrity.** Use database constraints, transactions, foreign keys, explicit deletion/archival rules, and safe migrations.
9. **Accessible and responsive.** Target WCAG 2.2 AA practices where applicable. Keyboard operation and visible focus are required.
10. **Document decisions and progress.** Update `docs/19-DECISIONS-AND-OPEN-QUESTIONS.md`, `docs/20-PROGRESS.md`, and `docs/21-TRACEABILITY.md` as applicable.

## Suggested technology direction (validate before pinning)

- Frontend: Nuxt 4 + Vue 3 + TypeScript.
- Backend: Laravel 13 + PHP 8.3 or later supported version.
- Database: a currently supported PostgreSQL release.
- Authentication: first-party browser session authentication using Laravel Sanctum's SPA flow, with Fortify or equivalent headless auth routes if required. Prefer secure HttpOnly cookies over browser localStorage tokens.
- UI: Tailwind CSS and a carefully selected accessible component foundation. Do not mix multiple component systems without a reason.
- Testing: Pest/PHPUnit for backend, Vitest for frontend units, and Playwright for critical browser journeys.
- Delivery: Docker Compose for reproducible local and VPS environments; CI on every pull request.

These are starting recommendations, not permission to ignore compatibility or hosting constraints. Verify current official docs and package compatibility before choosing exact versions.

## Engineering workflow

For each feature:

1. Read the requirement and identify the relevant user journey.
2. Inspect the existing implementation and patterns.
3. Write or update the acceptance criteria and traceability entry.
4. Implement the smallest complete vertical slice.
5. Add migrations, authorization, validation, API contract, UI states, and tests as needed.
6. Run formatting, lint, static analysis, relevant tests, and build checks.
7. Review accessibility, RTL/LTR, empty/loading/error states, and responsive behavior.
8. Update documentation and progress.

Do not make broad refactors while implementing an unrelated feature. Separate refactors into justified, testable changes.

## Code quality rules

- TypeScript strict mode; avoid `any` unless a narrow, documented boundary requires it.
- Validate all untrusted input on the server.
- Use request/response DTOs or Laravel API Resources rather than exposing models directly.
- Use Policies/Gates and domain services/actions for non-trivial business logic.
- Avoid N+1 queries; paginate large collections; add indexes based on query patterns.
- Use explicit enums or constrained values for statuses and priorities.
- Use soft deletion only where restoration is a real business need. Prefer archival for completed historical entities.
- Do not create generic abstractions before two or more real use cases require them.
- Do not store derived progress as an editable truth if it can be calculated reliably.
- Keep dates in UTC in storage and render in the user's configured timezone.
- Use stable IDs; never rely on display names as identifiers.

## Product scope guardrails

MVP priorities: authentication, team and permissions, tasks, projects, products, clients/contacts and light pipeline, dashboard, search, notifications, activity history, basic attachments, and admin settings.

Do not implement full accounting, payroll, HR attendance, invoicing/payments, public client portal, chat replacement, or AI autonomy in MVP.

## Security and operations

- Follow `docs/13-SECURITY.md` and use OWASP ASVS 5.0 as a verification reference.
- Never run destructive database commands against non-test environments without explicit approval and a verified backup.
- Never disable TLS, CSRF, rate limiting, authorization, or audit logging to make a test pass.
- Do not expose private uploaded files through public URLs.
- Log meaningful operational events without secrets, tokens, or unnecessary personal data.
- Keep production debug mode off and secrets outside Git.

## Claude Code delegation

Use the project subagents under `.claude/agents/` when an independent specialist review is useful. Do not spawn agents merely to appear thorough. Parallelize independent read-only reviews; keep dependent code changes under one coordinating agent to avoid conflicting edits. Review every subagent result before applying it.

## Completion report

When finishing a task, report: what changed, the files/modules affected, commands and tests run with their actual results, known limitations, migration/deployment notes, and the next recommended action. Never claim tests passed if they were not run.
