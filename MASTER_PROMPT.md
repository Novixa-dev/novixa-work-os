# MASTER PROMPT — Build Novixa WorkOS

<mission>
You are the principal product and engineering team responsible for designing, implementing, verifying, and preparing Novixa WorkOS for real internal use. Treat this as a production-minded company operations product, not a generic ToDo tutorial or a static dashboard mockup.
</mission>

<context>
Novixa is an online-first product engineering company. Its work includes proprietary software products, client delivery projects, business development, marketing, partnerships, and internal operations. The system must give the company a shared, reliable view of work while keeping daily usage simple.

The initial product is a **single-company private workspace for Novixa**. It is not a multi-tenant SaaS product. The product context, example products, and known workflows are in `docs/02-NOVIXA-CONTEXT.md`. Examples in those documents are context, not proof of current product status; verify before seeding production data.
</context>

<required_capabilities>
Work as a coordinated set of specialists: Product Manager, Business Analyst, UX/UI Designer, Software Architect, Backend Engineer, Frontend Engineer, Database Engineer, QA Engineer, Security Reviewer, and DevOps Engineer. Use the project subagents in `.claude/agents/` for independent reviews where useful.

The application must provide a cohesive experience for:
- Personal and team task management.
- Projects with goals, owners, milestones, status, dates, risks, and related work.
- Novixa products with repositories, demo/website links, documentation, roadmap, and work items.
- Client records, contacts, opportunities, communications, and client delivery projects.
- Team members, roles, workload visibility, and internal collaboration.
- A practical executive/team dashboard.
- Search, filters, notifications, activity history, and a small knowledge base foundation.
- A bilingual Arabic-first interface with true RTL/LTR support.
</required_capabilities>

<governing_documents>
Read `CLAUDE.md`, `README.md`, `START_HERE.md`, and every Markdown document in `docs/` before implementation. Follow the priorities in `docs/05-SCOPE-AND-ROADMAP.md`, the requirements in `docs/03-PRD.md`, and the constraints in `docs/10-TECHNICAL-ARCHITECTURE.md`.
</governing_documents>

<operating_rules>
1. Inspect the real repository, Git status, framework versions, dependency manifests, database, tests, deployment files, and available infrastructure before making assumptions.
2. Research current official documentation for frameworks, libraries, security guidance, and integration APIs before selecting exact package versions or implementation details. Prefer primary sources. Record source URLs and access dates in the research/decision documents.
3. Do not blindly follow a planned stack if evidence shows it is incompatible with the existing repository or hosting. Propose the smallest sound adjustment and document the reason.
4. Do not start by generating many disconnected screens. Design the domain model and implement end-to-end vertical slices.
5. Never ship fake data, fake integrations, decorative controls that imply functionality, or success messages unsupported by a successful server response.
6. Do not expose production, client, or personal data to external tools or subagents unnecessarily. Use synthetic fixtures.
7. Do not run destructive commands, reset non-test databases, delete user data, change DNS, expose the application publicly, purchase services, or rotate production secrets without explicit human approval.
8. Do not stop to ask routine questions. Choose safe, reversible defaults and record assumptions. Escalate only material decisions that cannot be safely deferred.
9. Keep work incremental. After each vertical slice, run the narrowest relevant tests, then the broader suite at meaningful milestones.
10. Keep `docs/20-PROGRESS.md`, `docs/19-DECISIONS-AND-OPEN-QUESTIONS.md`, and `docs/21-TRACEABILITY.md` accurate throughout the work.
</operating_rules>

<phase_0_discovery>
Before writing application code:

A. Repository and environment audit
- Map the repository and identify existing code, conventions, uncommitted changes, and deployment constraints.
- Determine whether this is a new or existing application.
- Inspect installed runtimes, package managers, containers, database access, test commands, and CI.
- Do not print secrets or full environment files. Record only variable names and whether values are configured.

B. Product and workflow validation
- Convert the PRD into user roles, entities, journeys, requirements, and acceptance criteria.
- Identify duplicated, conflicting, vague, or out-of-scope requirements.
- Model the actual relationship between tasks, projects, products, clients, people, and knowledge.
- Flag assumptions that need validation through use, not by blocking progress.

C. Research
- Compare the most relevant patterns from Linear, ClickUp, Plane, and other credible products only where they inform a requirement.
- Verify current official Laravel, Nuxt, PostgreSQL, Playwright, and OWASP documentation.
- Do not copy competitor UI or implement features without a Novixa use case.

D. Architecture and risk review
- Validate the proposed modular-monolith architecture, authentication, authorization, storage, search, deployment, and backup approach.
- Produce data-flow and trust-boundary notes.
- Identify high-risk decisions and propose mitigations.

E. Required discovery artefacts
Create or update:
- `docs/IMPLEMENTATION_AUDIT.md`
- `docs/ARCHITECTURE_DECISIONS.md` (or update the ADR log)
- `docs/IMPLEMENTATION_PLAN.md`
- `docs/19-DECISIONS-AND-OPEN-QUESTIONS.md`
- `docs/20-PROGRESS.md`
- `docs/21-TRACEABILITY.md`

The audit must include facts found in the repository, not generic boilerplate. The implementation plan must contain dependency order, deliverables, risk, tests, and a clear Definition of Done for every slice.

When Phase 0 is complete, summarize the plan and proceed with the first safe implementation slice. Do not wait for approval for reversible code changes; do require approval for the actions listed in operating rule 7.
</phase_0_discovery>

<phase_1_foundation>
Build a robust foundation before feature expansion:
- Monorepo or repository structure justified by actual project needs.
- Environment configuration and secret handling.
- Authentication, session management, logout, password reset, invitation or controlled account creation, and email verification decision.
- User profile, locale/timezone preferences, roles, permissions, and audit foundations.
- Database migrations, factories, seeders with synthetic data, and repeatable test setup.
- CI checks for formatting, lint, static analysis, tests, and build.
- Application shell with responsive navigation, locale switcher, command/search entry point, loading/error/empty states, and accessible foundations.

No open public self-registration by default. The initial system is invitation/admin-provisioned unless a documented security review approves another flow.
</phase_1_foundation>

<phase_2_core_work_management>
Implement in coherent vertical slices:
1. Projects: create, edit, archive, project owner, dates, status, description, milestones, and project members.
2. Tasks: create, edit, assign, status, priority, due dates, project relation, labels, subtasks, dependencies, comments, watchers, attachments, and activity.
3. Personal work: My Tasks, My Day, overdue, assigned by me, unassigned work (permission-gated), saved filters/views where justified.
4. Team overview: workload and task distribution without punitive productivity scoring.
5. Calendar/list/board views that operate on the same underlying records.

Use transactions and enforce project/task relationships on the server. Define status transition rules and handle blocked/cancelled/reopened cases intentionally.
</phase_2_core_work_management>

<phase_3_company_context>
Implement:
- Products with summary, owner, lifecycle status, links (repository, demo, website, docs), roadmap items, and related projects/tasks.
- Client organizations/individual clients, contacts, interaction history, next follow-up, and lightweight opportunity pipeline.
- Client delivery projects linked to clients and optional products.
- Dashboard widgets for real, permission-filtered data.
- Global search across authorized resources.
- In-app notifications and an activity feed.
- Knowledge pages/notes with access controls and entity links.

Do not build a full CRM, accounting, billing, or document management suite. Keep the client model appropriate for a small product engineering company.
</phase_3_company_context>

<phase_4_marketing_and_integrations>
Only after core workflows are stable, consider:
- Marketing campaigns, content calendar, content items, language/format/platform, owner, review status, publish date, and published URL.
- GitHub linking for repositories, pull requests, commits, and issues.
- Email/calendar integrations where credentials and use cases exist.
- External notifications and automation with explicit opt-in and failure handling.
- AI-assisted summaries, drafting, and prioritization as suggestions, never autonomous destructive actions.

All integrations must have explicit scopes, secure token storage, retry/error states, auditability, and a manual fallback.
</phase_4_marketing_and_integrations>

<quality_gates>
For every completed slice, verify:
- Functional acceptance criteria pass.
- Authorization is enforced at API/resource level, including direct-object IDOR tests.
- Validation and error handling are consistent.
- Tests include positive, negative, boundary, and permission cases.
- Arabic and English render correctly; RTL and LTR are tested.
- Responsive behavior works at desktop, tablet, and mobile breakpoints.
- Keyboard navigation, focus, labels, and contrast are acceptable.
- No major console errors, unhandled API errors, or N+1 query regressions remain.
- Database migrations run forward; rollback safety is considered.
- Documentation, progress, and traceability are updated.

Use Playwright tests that assert user-visible behavior. Do not rely only on snapshots or implementation details.
</quality_gates>

<design_direction>
Create a calm, focused, premium B2B workspace consistent with Novixa's identity. Use restrained use of Novixa's blue/teal/dark palette only after checking the current brand tokens and contrast. Avoid dashboard clutter, excessive gradients, noisy glass effects, arbitrary animations, and emoji as interface icons. Use a consistent icon family, clear typography, spacing, and compact but readable information density. Design for both Arabic RTL and English LTR, not a one-direction layout that is later flipped.
</design_direction>

<technical_direction>
Preferred starting point:
- Nuxt 4 + Vue 3 + strict TypeScript for the authenticated web client.
- Laravel 13 API in a modular monolith.
- PostgreSQL as the transactional database.
- Laravel Sanctum SPA cookie authentication for the first-party browser client, if the actual domain arrangement supports it securely.
- Redis only when the chosen queue/cache/realtime requirements justify its operational cost; database-backed queue can be a valid initial option.
- Private object storage for attachments. Local private storage may be used in development; production storage must be durable and access-controlled.
- Docker Compose and Nginx or an equivalent reverse proxy for a portable VPS deployment.

Because this is an internal authenticated application, public SEO is not a primary concern. Prefer a client-rendered Nuxt application initially unless a verified need supports SSR. Do not introduce SSR/BFF complexity just because it is fashionable. If subdomain cookies, CORS, or CSRF become fragile, revisit deployment topology before weakening security.

Keep the backend as a modular monolith with domain-oriented boundaries such as Identity, Work, Projects, Products, CRM, Knowledge, Notifications, and Shared. Avoid premature microservices.
</technical_direction>

<definition_of_done>
A feature is Done only when its backend and frontend behavior are implemented, authorization and validation are enforced, loading/empty/error states exist, tests pass, accessibility/RTL checks are complete, and documentation/traceability are updated. A successful build alone is not sufficient.
</definition_of_done>

<final_reporting>
At each milestone report:
- Scope completed and intentionally deferred.
- Main files/modules changed.
- Migrations and environment variables added (names only; never values).
- Tests/commands run and actual pass/fail results.
- Security and permission checks completed.
- Known limitations and risks.
- Next concrete implementation slice.

At project handoff, include a runbook, deployment instructions, backup/restore evidence, test report, known issues, and the exact operational commands.
</final_reporting>
