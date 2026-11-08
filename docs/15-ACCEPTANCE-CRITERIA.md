# 15 — Acceptance Criteria: Novixa WorkOS

This document specifies the objective, testable acceptance criteria for every MVP capability.

---

## AC-01: Authentication & User Provisioning
- [ ] User can authenticate via valid email and password.
- [ ] Session cookie is issued with `HttpOnly`, `Secure`, and `SameSite` flags.
- [ ] Invalid credentials return 422/401 with generic message, without revealing user existence.
- [ ] Failed login attempts exceed rate limit after 5 tries, returning 429 Too Many Requests.
- [ ] Admin can invite or provision new users with assigned role.
- [ ] User can log out; server session is revoked and cookie cleared.
- [ ] Deactivated user immediately loses ability to authenticate or query API.

## AC-02: Bilingual Localization & RTL Layout
- [ ] Language toggle instantly switches entire UI between Arabic and English.
- [ ] Setting Arabic sets `document.documentElement.dir = 'rtl'` and `lang = 'ar'`.
- [ ] Setting English sets `document.documentElement.dir = 'ltr'` and `lang = 'en'`.
- [ ] All dates render in localized Arabic or English format (e.g. Gregorian Arabic: "8 نوفمبر 2026").
- [ ] Number formatting and currency symbols render appropriately in both languages.
- [ ] User language preference is persisted to the database and rehydrated on subsequent sessions.

## AC-03: Responsive Application Shell
- [ ] At 360px (mobile): Sidebar collapses into a touch-accessible mobile drawer; tables scroll horizontally or collapse into cards.
- [ ] At 768px (tablet): Navigation is accessible via compact bar; multi-column forms adapt to 1 or 2 columns.
- [ ] At 1280px+ (desktop): Full sidebar with quick create, global search, and multi-pane views.
- [ ] Zero horizontal overflow or clipped text across breakpoints.

## AC-04: Projects Management
- [ ] Authorized user can create a project with Name, Code, Description, Owner, Type, Priority, and Dates.
- [ ] Project can be linked optionally to a Novixa Product or CRM Client.
- [ ] Project progress percentage is dynamically calculated from completed vs total tasks.
- [ ] Adding/removing project members updates permissions immediately.
- [ ] Project can be archived; archived projects are excluded from default views but remain auditable.

## AC-05: Task Management (List & Kanban)
- [ ] Tasks can be created with Title, Description, Status, Priority, Assignee, Reporter, Project, and Due Date.
- [ ] Tasks can be organized in List View with sorting by due date, priority, and status.
- [ ] Tasks can be filtered by Project, Assignee, Priority, Status, and Labels.
- [ ] Kanban board displays tasks grouped by status columns with drag-and-drop or status selector.
- [ ] Subtasks can be created under a parent task; recursive cycles are rejected.
- [ ] Task dependencies (blocking/blocked by) reject circular dependencies.
- [ ] Comments can be posted on tasks and rendered with timestamp and author initials.
- [ ] "My Work" screen displays personal tasks organized into:
  - My Day (scheduled for today)
  - My Tasks (all assigned tasks)
  - Overdue (tasks past due date)
  - Assigned by Me

## AC-06: Products Catalog & Roadmap
- [ ] Displays Novixa products: Novixa Aqar, Muhajim, Novixa Serve, Kitchen-POS.
- [ ] Each product displays lifecycle status, description, official URLs (Demo, Docs, Git).
- [ ] Product view lists all projects and tasks linked to that product.

## AC-07: Clients & CRM Pipeline
- [ ] Client records store organization/contact name, industry, website, phone, and account owner.
- [ ] Contacts can be added and linked to clients.
- [ ] Opportunities track stage (`Lead`, `Qualified`, `Proposal`, `Negotiation`, `Won`, `Lost`) and value.
- [ ] Moving an opportunity to `Won` prompts one-click creation of a linked Delivery Project.
- [ ] Interaction logs record calls, meetings, WhatsApp notes, and schedule the next follow-up date.

## AC-08: Executive & Team Dashboard
- [ ] Overdue tasks card highlights past-due items with direct links.
- [ ] Tasks Due Today displays actionable items for the current user.
- [ ] Active Projects card displays status indicators and calculated progress.
- [ ] Client Follow-ups widget displays upcoming touchpoints for the current week.
- [ ] Recent Activity feed renders chronological events with actor, action, and entity link.

## AC-09: Global Search & Notifications
- [ ] Global search input queries tasks, projects, products, and clients.
- [ ] Search results strictly respect authorization (unauthorized items never appear in results or counts).
- [ ] In-app notification bell shows unread count and list of assignment/mention events.
- [ ] Clicking notification marks it read and navigates to target entity.

## AC-10: Automated Testing & Code Quality
- [ ] Backend tests (Pest / PHPUnit) pass 100%.
- [ ] Frontend unit tests pass 100%.
- [ ] Type check (`vue-tsc --noEmit` or `tsc`) completes with 0 errors in strict mode.
- [ ] Zero hardcoded user strings in Vue templates.
- [ ] Zero unhandled API rejections or console errors.
