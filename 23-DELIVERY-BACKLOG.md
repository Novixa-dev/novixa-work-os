# Delivery Backlog — Initial Epics and Tickets

This is a starting backlog, not a promise that every item belongs in the first release. Claude must validate dependencies against the inspected repository and split tickets when they are too large. Keep each issue independently testable.

Priority: **P0** release blocker, **P1** MVP, **P2** V1/V2, **P3** later. No time estimates are assigned before repository and team capacity are known.

## EPIC 00 — Discovery and architecture

### NW-001 — Audit repository and runtime (P0)
- Inspect existing files, dependencies, Git state, runtime versions, deployment, and tests.
- Deliver `docs/IMPLEMENTATION_AUDIT.md` with verified findings and risks.
- Acceptance: no secrets printed; existing user changes preserved; each finding has evidence.

### NW-002 — Confirm product scope and domain model (P0)
- Validate roles, main journeys, entity relationships, and MVP exclusions.
- Acceptance: domain model and requirements traceability updated; assumptions are labeled.

### NW-003 — Record architecture decisions (P0)
- Compare proposed stack/deployment/auth options against actual constraints.
- Acceptance: accepted ADRs include options, rationale, consequences, and revisit triggers.

## EPIC 01 — Project foundation

### NW-010 — Establish reproducible local environment (P0)
- Configure local API, web client, database, and required services.
- Acceptance: new developer can follow README and reach a healthy local app.

### NW-011 — Configure environment and secret templates (P0)
- Add safe `.env.example` files and startup validation.
- Acceptance: no secret values committed; missing required configuration fails clearly.

### NW-012 — Configure CI quality gates (P0)
- Add formatting, lint, type checking/static analysis, backend tests, frontend tests, and production build.
- Acceptance: pipeline runs on pull requests and fails on a deliberately introduced test failure.

### NW-013 — Set up database migrations and synthetic factories (P0)
- Add schema baseline and isolated test database setup.
- Acceptance: migrations apply from empty database; factories produce synthetic records only.

## EPIC 02 — Identity and access

### NW-020 — Implement secure authentication (P0)
- Controlled account provisioning; sign-in/out; password recovery.
- Acceptance: sessions are secure, rate limits work, CSRF is enforced, and invalid credentials do not reveal account existence.

### NW-021 — Implement user profile and preferences (P1)
- Locale, timezone, display name, avatar or initials.
- Acceptance: preferences persist and all timestamps render in the selected timezone.

### NW-022 — Implement roles and permission policies (P0)
- Owner, Admin, Manager/Project Lead, Member; optional read-only later.
- Acceptance: endpoint-level tests cover allowed and denied operations for each role.

### NW-023 — Add audit events for sensitive actions (P0)
- Record role changes, user deactivation, access-sensitive changes, and administrative operations.
- Acceptance: regular users cannot tamper with audit records; secrets are not logged.

## EPIC 03 — App shell and localization

### NW-030 — Build authenticated app shell (P1)
- Navigation, profile menu, quick create, global search entry, responsive sidebar.
- Acceptance: no dead links; keyboard navigation and mobile navigation work.

### NW-031 — Add Arabic/English localization (P0)
- Translation resources, language preference, document direction, date/number formatting.
- Acceptance: zero missing visible strings in core journeys; RTL/LTR E2E smoke tests pass.

### NW-032 — Establish design tokens and accessible components (P1)
- Colors, typography, spacing, buttons, fields, dialogs, tables, badges, menus.
- Acceptance: contrast and focus states reviewed; one consistent icon system used.

## EPIC 04 — Projects

### NW-040 — Project CRUD and archive lifecycle (P1)
- Name, description/outcome, type, status, lead, dates, priority, client/product relationship.
- Acceptance: authorized users can create/update/archive; other users receive correct access denial.

### NW-041 — Project membership (P1)
- Add/remove members with role-appropriate controls.
- Acceptance: membership changes are authorized and recorded; removing a member updates access as defined.

### NW-042 — Project overview and progress (P1)
- Summary, linked tasks, milestones, blockers, progress counts.
- Acceptance: progress rule is documented and derived from eligible work items.

### NW-043 — Milestones and project updates (P2)
- Add milestones and periodic project updates.
- Acceptance: dates, ownership, completion, and history are visible and testable.

## EPIC 05 — Tasks and collaboration

### NW-050 — Task CRUD and validation (P1)
- Title, description, status, priority, dates, reporter, primary assignee, optional project.
- Acceptance: validation, pagination, sorting, authorization, and status transitions work.

### NW-051 — Task list and filters (P1)
- Filter by status, priority, assignee, project, due date, labels.
- Acceptance: filters compose safely; query parameters are allow-listed.

### NW-052 — Task detail and activity history (P1)
- Task detail, editable fields, activity timeline.
- Acceptance: activity shows meaningful diffs and does not expose protected fields.

### NW-053 — Labels and task categorization (P1)
- Create/manage labels and apply them to tasks.
- Acceptance: permissions and filtering work; duplicates are prevented.

### NW-054 — Subtasks and parent-child integrity (P1)
- Create nested tasks with sensible depth limits.
- Acceptance: no self/descendant cycles; archive behavior is consistent.

### NW-055 — Task dependencies (P2)
- Blocking/blocked-by and related relationships.
- Acceptance: self-link and cycles are rejected transactionally.

### NW-056 — Comments, mentions, and watchers (P1)
- Add comments, notify mentioned users, watch/unwatch tasks.
- Acceptance: mention notifications are permission-safe and idempotent.

### NW-057 — Private attachments (P1)
- Upload/download files using private storage.
- Acceptance: file limits, MIME checks, random names, and unauthorized-download tests pass.

### NW-058 — Kanban board (P1)
- Display tasks by status and update status with keyboard/touch support.
- Acceptance: drag/drop or equivalent interaction persists server-side; errors roll back visibly.

### NW-059 — My Work views (P1)
- My Day, My Tasks, Overdue, Assigned by Me, and permission-gated Unassigned.
- Acceptance: counts and results match the underlying task query.

## EPIC 06 — Products

### NW-060 — Product directory and profile (P1)
- Create products with lifecycle, owner, summary, and external links.
- Acceptance: URLs validate; access is role controlled.

### NW-061 — Link products to projects and work (P1)
- Show product-related projects/tasks.
- Acceptance: no duplicate tasks; context consistency is validated.

### NW-062 — Product roadmap grouping (P2)
- Group work by feature, release, or initiative without full planning engine.
- Acceptance: groups are visible and do not duplicate underlying task records.

## EPIC 07 — Clients and lightweight CRM

### NW-070 — Client and contact records (P1)
- Company/person client, contacts, safe contact info, owner, source, status.
- Acceptance: CRM data is restricted by policy; changes are audited where needed.

### NW-071 — Opportunity pipeline (P1)
- Opportunity stage, owner, optional value/currency, expected close, next follow-up.
- Acceptance: stage transitions and permission checks work.

### NW-072 — Interaction history and follow-ups (P1)
- Log calls, meetings, emails, messages, and next actions.
- Acceptance: authorized users can see history; dashboard surfaces follow-ups due.

### NW-073 — Client delivery project flow (P1)
- Create project from a won opportunity or client record.
- Acceptance: client relationship and project membership are preserved and tested.

## EPIC 08 — Dashboard, search, and notifications

### NW-080 — Role-aware dashboard (P1)
- Tasks due, overdue, blocked, active projects, milestones, client follow-ups, recent activity.
- Acceptance: every widget uses authorized data and links to its underlying records.

### NW-081 — Global search (P2)
- Search tasks, projects, products, clients, and knowledge pages.
- Acceptance: hidden record names/snippets never leak through results or counts.

### NW-082 — In-app notifications (P1)
- Assignment, mention, comment, watcher updates, reminders.
- Acceptance: read/unread state, pagination, user preferences, and duplicate prevention work.

## EPIC 09 — Knowledge and operations

### NW-090 — Basic knowledge pages (P2)
- Store SOPs, decisions, project notes, and product docs with entity links.
- Acceptance: permissions, editing, version/audit behavior, and search are defined.

### NW-091 — Backup and restore automation (P0)
- Backup database and private attachments.
- Acceptance: encrypted backup exists off-server and a restore drill is documented with evidence.

### NW-092 — Production monitoring and runbook (P0)
- Health checks, logs, failed jobs, disk/DB alerts, certificate expiry.
- Acceptance: alerts are actionable and runbook contains recovery steps.

## EPIC 10 — Post-MVP

### NW-100 — Marketing calendar and campaigns (P2)
### NW-101 — GitHub repository/PR linking (P2)
### NW-102 — Email/calendar integration (P3)
### NW-103 — AI-assisted summaries and drafts (P3)
### NW-104 — Human-approved workflow automation (P3)
### NW-105 — Advanced portfolio reports (P3)

Do not start post-MVP tickets until the MVP exit gate in `05-SCOPE-AND-ROADMAP.md` is met or a deliberate reprioritization is documented.
