# Implementation Plan

This is the planned sequence. The discovery phase must adapt it to the real repository and constraints.

## Phase 0 — discovery and decisions

Deliverables:
- Repository/environment audit.
- Workflow map and refined requirements.
- Domain model and risk register.
- Architecture decisions and alternatives.
- Confirmed stack, repository shape, local setup, and test commands.
- Implementation issues grouped into vertical slices.

Exit: no critical unknown about whether the selected stack can run in the target environment; risky assumptions are documented.

## Phase 1 — foundation

1. Create/confirm repository structure and local developer setup.
2. Configure API and web app foundations.
3. Configure environment variables and secret templates.
4. Configure database and migrations.
5. Implement auth/session, password reset, invitation/provisioning.
6. Implement users, profile preferences, roles, policies.
7. Add CI, formatting, static analysis, unit/feature tests.
8. Build app shell, navigation, locale, direction, theme, responsive baseline.

Exit: a real provisioned user can sign in, switch locale, and access only authorized shell pages.

## Phase 2 — work management

1. Project CRUD, project ownership, members, archive.
2. Task CRUD, primary assignee, status, priority, dates, project link.
3. Task list and details.
4. Labels and filtering.
5. Subtasks and dependency graph validation.
6. Comments, mentions, watchers, activity feed.
7. Private attachments.
8. Kanban board and My Work views.
9. Dashboard cards based on actual authorized data.

Exit: team can manage one project end-to-end.

## Phase 3 — company context

1. Product catalogue and detail pages.
2. Product-project-task relationships.
3. Client and contact records.
4. Opportunity pipeline and interaction history.
5. Client delivery projects and follow-ups.
6. Global search and in-app notifications.
7. Basic knowledge notes/pages if secure and useful.

Exit: one product workflow and one client workflow can be managed without duplicating source records.

## Phase 4 — hardening and release readiness

1. Full permission matrix regression tests.
2. Arabic/English and RTL/LTR QA.
3. Responsive/accessibility review.
4. Performance review and indexes.
5. Backup/restore drill.
6. Production Docker build, TLS/reverse proxy, worker/scheduler, health checks.
7. Staging acceptance and user feedback.
8. Production runbook and handover.

Exit: release gates in `docs/15-ACCEPTANCE-CRITERIA.md` and `docs/17-DEPLOYMENT-AND-DEVOPS.md` pass.

## Phase 5 — expansion

Only after real usage demonstrates need: knowledge base depth, marketing calendar, GitHub integration, email/calendar integration, advanced reports, AI-assisted summaries, automation.

## Task breakdown format

Each implementation ticket should include:
- ID and title.
- User/problem statement.
- Scope and non-scope.
- Dependencies.
- API/data changes.
- Permission implications.
- UI states and RTL impact.
- Tests.
- Acceptance criteria.
- Rollback/migration considerations.
