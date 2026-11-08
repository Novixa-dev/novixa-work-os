# Product Requirements Document (PRD)

## 1. Product scope

A private internal web application for Novixa to manage its tasks, projects, products, clients, team, and knowledge. The initial release prioritizes daily work execution and reliable ownership.

## 2. MVP user stories and requirements

### Identity and team
- As an invited team member, I can sign in, sign out, reset my password, and manage my basic profile.
- As an administrator, I can create/invite, activate, deactivate, and assign roles to team members.
- As a user, I can choose Arabic or English and set my timezone.
- The app must not expose open registration by default.

### Tasks
- Create, view, edit, archive, and restore tasks according to policy.
- Set title, description, status, priority, creator, assignee, due date, start date, labels, and optional project/product/client context.
- Break tasks into subtasks and record blocking/blocked-by relationships.
- Add comments, mentions, watchers, and private attachments.
- View task activity and status history.
- Filter, sort, search, and paginate task lists.
- Display tasks in list and Kanban views. Calendar view may be included in MVP only if it does not delay core reliability.
- Provide My Tasks, My Day, Overdue, Assigned by Me, and (permission-gated) Unassigned views.

### Projects
- Create, update, archive, and view projects.
- Set project type, outcome/description, lead, members, status, priority, start/due dates, client, product links, and key milestones.
- Link tasks to projects and show project progress based on defined work items, not a manually editable percentage alone.
- Record project updates, risks, blockers, and decisions.

### Products
- Maintain product name, summary, lifecycle status, owner, target users, repository URL, website URL, demo URL, documentation URL, and related projects/tasks.
- Show a product overview and product-specific work queue.
- Keep releases/version history for a later release unless needed for a first product handover.

### Clients and light CRM
- Maintain client organization/person, type, contact details, website, source, owner, status, and notes.
- Maintain one or more contacts linked to a client.
- Track opportunities with stage, estimated value/currency (optional), owner, expected close date, source, next follow-up, and linked client.
- Record interaction/activity history with date, channel, summary, and next action.
- Link delivery projects to clients.
- Do not build contracts, e-signatures, invoicing, or payments in MVP.

### Dashboard and search
- Provide a role-aware dashboard with overdue tasks, tasks due today, blocked work, project status, recent activity, upcoming milestones, and client follow-ups.
- Widgets must show real authorized data and link to filtered source records.
- Provide global search across tasks, projects, products, clients, and knowledge pages that the current user may access.

### Collaboration and knowledge
- Provide in-app notifications for assignment, mention, comment, status changes relevant to watchers, and due-date reminders.
- Provide an activity feed with actor, action, target, timestamp, and safe metadata.
- Provide a basic knowledge base for SOPs, project notes, product docs, and decisions. Start with markdown/rich-text pages only if access control and editor safety are well defined; otherwise defer the editor and support links/notes first.

### Settings
- Workspace name and logo/brand settings.
- User preferences: locale, timezone, density/theme if supported.
- Manage task statuses, priorities, labels, notification preferences, and role permissions within safe boundaries.

## 3. Core entities

User, Role, Permission, Team/Department (optional lightweight grouping), Task, TaskStatus, TaskLabel, TaskComment, TaskAttachment, TaskWatcher, TaskDependency, Project, ProjectMember, Milestone, ProjectUpdate, Product, Client, Contact, Opportunity, Interaction, Notification, ActivityLog, KnowledgePage, and settings.

Do not create a separate database table for every UI concept unless it has an independent lifecycle or query need. Confirm entity boundaries in the domain model.

## 4. Important business rules

- An authenticated user must still be authorized for every resource and action.
- A task may be standalone or linked to a project. Product/client links must be consistent with the project context; validate this on the server.
- A task has one primary assignee in MVP. Use watchers/collaborators for additional interested users rather than introducing multiple ownership semantics prematurely.
- A task cannot depend on itself. Prevent dependency cycles.
- Parent/subtask relationships must not form cycles.
- Closed/archived records should remain discoverable to permitted users and should not vanish from audit history.
- Deactivated users cannot sign in; preserve historical attribution.
- Notification generation should be event-based and idempotent where practical.
- Dates/times are stored in UTC and rendered in the user's timezone. Date-only deadlines must not shift across time zones.

## 5. Quality attributes

- Security: secure session handling, CSRF protection, authorization policies, rate limits, safe uploads, secure secrets.
- Reliability: migrations, transactions, backup/restore, error handling, health checks.
- Performance: pagination, indexes, eager loading, bounded dashboards, and measurable query performance.
- Accessibility: keyboard support, semantic controls, focus state, contrast, labels, reduced-motion respect.
- Internationalization: Arabic/English translation coverage, correct RTL/LTR, locale-aware dates/numbers.
- Maintainability: typed frontend, testable backend, modular domain boundaries, documented decisions.
- Portability: reproducible local development and containerized deployment.

## 6. MVP exclusions

Full financial accounting, payroll, attendance, public customer portal, invoicing/payment processing, chat/messenger, native mobile app, AI-autonomous actions, advanced custom workflow builder, complex resource planning, and multi-tenant subscriptions.

## 7. Success measurement

Use operational signals described in `01-PRODUCT-VISION.md`. Avoid surveillance-style individual scorecards. Define baseline usage after the team has used the MVP for a few weeks.
