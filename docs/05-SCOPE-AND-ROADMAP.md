# 05 — Scope & Roadmap: Novixa WorkOS

## 1. Scope Governance

To ensure rapid delivery of a robust, high-performance internal management platform, Novixa WorkOS enforces a disciplined boundary between MVP requirements and deferred post-MVP capabilities.

```
                    ┌────────────────────────────────────────┐
                    │          Novixa WorkOS MVP             │
                    │  (P0 Release Blockers & P1 Essentials) │
                    └───────────────────┬────────────────────┘
                                        │
         ┌──────────────────────────────┼──────────────────────────────┐
         ▼                              ▼                              ▼
  Work & Execution              Company Entities              Security & Core
  - Tasks (List, Kanban)        - Products Directory          - Sanctum SPA Auth
  - Subtasks & Dependencies     - Clients & Contacts          - RBAC & Policies
  - Projects & Milestones       - Opportunity Pipeline        - Audit Logging
  - My Work (My Day, Overdue)   - Client Follow-ups           - Bilingual (AR/EN RTL)
  - Activity & Comments         - Executive Dashboard         - Private Attachments
                                - Knowledge Base SOPs         - Responsive (360px+)
```

---

## 2. In-Scope MVP Modules

### Module 1: Identity, Auth & Team Management
- Secure cookie-based SPA session authentication (Laravel Sanctum).
- Controlled account provisioning (admin invites/creation; no open registration).
- Password recovery and profile customization (display name, avatar initials, timezone, locale).
- Role-based access control (`Owner`, `Admin`, `Manager`, `Member`, `Guest`).
- Immutable audit log for security-sensitive operations.

### Module 2: Projects & Work Execution
- Comprehensive Project lifecycle (Draft, Active, On Hold, Completed, Archived).
- Deliverable-based progress tracking (derived from task completion, not manual percentages).
- Milestones and status update logs.
- Full Task management:
  - Title, rich description, status, priority, start/due dates.
  - Primary assignee, reporter, watchers.
  - Subtasks with parent-child integrity and cycle prevention.
  - Task dependencies (Blocking / Blocked By) with cycle validation.
  - Labels, tags, and searchable metadata.
  - Comments with user mentions.
  - Secure private file attachments.
  - Task views: List view with sorting/filtering, responsive Kanban board, and "My Work" personalized cockpit (My Day, My Tasks, Assigned by Me, Overdue).

### Module 3: Products Directory
- Catalog of Novixa's proprietary software (Novixa Aqar, Muhajim, Novixa Serve, etc.).
- Product metadata: summary, owner, lifecycle stage, official URLs (website, live demo, git repo, docs).
- Direct linking between products, ongoing projects, and active tasks.

### Module 4: Lightweight B2B CRM
- Client records (organizations and individuals) with status, contact channels, and account manager.
- Multiple contacts per client organization.
- Opportunity tracking through defined stages: `Lead`, `Qualified`, `Proposal`, `Negotiation`, `Won`, `Lost`.
- Interaction history logging (Meetings, Calls, Emails, WhatsApp) with next scheduled follow-up dates.
- Conversion of won opportunities into active client delivery projects.

### Module 5: Dashboard, Search & Collaboration
- Role-aware Executive & Engineer Dashboard:
  - Real-time priority widgets (Overdue tasks, tasks due today, blocked items).
  - Active project health status.
  - Upcoming milestones and scheduled client follow-ups.
  - Recent team activity stream.
- Global instant search across tasks, projects, products, and clients with strict permission boundaries.
- In-app notification center for assignments, mentions, and due dates.
- Knowledge base for internal SOPs, technical decisions, and operational guidelines.

---

## 3. Strict MVP Exclusions (Do Not Build in V1)
- **Financial Accounting & General Ledger:** No double-entry bookkeeping or tax filing.
- **Payroll & HR Attendance Clocking:** No biometric or timesheet payroll calculation.
- **Billing, Invoicing & Payment Gateways:** No automated Stripe/PayPal checkout or credit card vaults.
- **Public Client Portal:** No external unauthorized client self-service logins in MVP.
- **Realtime Chat / Instant Messenger:** Novixa utilizes Slack / WhatsApp; WorkOS provides structured task/project comments, not a full chat engine.
- **Native Mobile Apps (iOS / Android):** Full responsive PWA/web experience is delivered instead.
- **Autonomous AI Agents Executing Destructive Changes:** AI assistance is advisory only.

---

## 4. Release Milestones & Phase Gates

| Phase | Milestone | Deliverables | Exit Gate Criteria |
|-------|-----------|--------------|-------------------|
| **Phase 0** | Discovery & Foundations | Complete audit, architecture decisions, domain model, starter structure | All documents verified; stack confirmed; no blockers |
| **Phase 1** | Platform Foundation | Laravel 13/PHP 8.4 API, Nuxt 4 SPA, Sanctum auth, RBAC, i18n, design tokens | Authenticated user can log in, switch AR/EN, see authorized shell |
| **Phase 2** | Work Management | Projects, Tasks, Kanban, Subtasks, Dependencies, Comments, My Work | Full project sprint created and completed with passing tests |
| **Phase 3** | Company Context | Products, CRM pipeline, Client interactions, Dashboard, Search, Knowledge | Won opportunity creates client delivery project; search returns permitted records |
| **Phase 4** | Hardening & Release | Full test suite (Unit, Feature, Playwright E2E), ASVS security audit, Docker deploy | 100% test pass rate; zero critical security defects; backup drill verified |
