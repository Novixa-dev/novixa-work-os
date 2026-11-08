# Architecture Decision Records (ADRs) — Novixa WorkOS

## ADR Index
- [ADR-001: Modular Monolith Domain Architecture](#adr-001-modular-monolith-domain-architecture)
- [ADR-002: Nuxt 4 (Vue 3) Frontend with First-Party Sanctum Cookie Auth](#adr-002-nuxt-4-vue-3-frontend-with-first-party-sanctum-cookie-auth)
- [ADR-003: Dual Database Strategy (PostgreSQL in Production, SQLite in Dev/Test)](#adr-003-dual-database-strategy-postgresql-in-production-sqlite-in-devtest)
- [ADR-004: Native Bidirectional (RTL/LTR) Localization Engine](#adr-004-native-bidirectional-rtlltr-localization-engine)
- [ADR-005: Task Dependency Cycle Prevention via Graph Traversal](#adr-005-task-dependency-cycle-prevention-via-graph-traversal)
- [ADR-006: Private Disk Attachment Streaming with Server Authorization](#adr-006-private-disk-attachment-streaming-with-server-authorization)

---

### ADR-001: Modular Monolith Domain Architecture
* **Status:** Accepted
* **Context:** Novixa requires high cohesion across tasks, projects, products, and clients without the network latency, operational overhead, and distributed transaction complexity of microservices.
* **Decision:** Implement Laravel as a modular monolith organized into explicit domain modules:
  - `Identity`: Authentication, roles, permissions, user preferences, audit trails.
  - `Work`: Tasks, subtasks, dependencies, labels, activity, comments, attachments.
  - `Projects`: Project lifecycle, milestones, updates, members.
  - `Products`: Novixa software catalog, roadmaps, linked work.
  - `CRM`: Clients, contacts, opportunities pipeline, interaction history.
  - `Knowledge`: SOPs, tech decisions, documentation.
  - `Notify`: In-app notification center, user preferences.
  - `Shared`: Base classes, API responses, query builders.
* **Consequences:** Provides clean code boundaries, simplifies refactoring, and ensures atomic database transactions across interconnected entities.

---

### ADR-002: Nuxt 4 (Vue 3) Frontend with First-Party Sanctum Cookie Auth
* **Status:** Accepted
* **Context:** Storing JWT or bearer tokens in `localStorage` exposes sessions to Cross-Site Scripting (XSS) attacks.
* **Decision:** Use Laravel Sanctum SPA stateful authentication. The frontend initiates `GET /sanctum/csrf-cookie` before login. Sessions are maintained via `HttpOnly`, `SameSite=Lax`, and `Secure` cookies.
* **Consequences:** Maximizes security posture; tokens cannot be exfiltrated via JavaScript. Nuxt development server proxies `/api` and `/sanctum` to Laravel to maintain same-origin cookie transport.

---

### ADR-003: Dual Database Strategy (PostgreSQL in Production, SQLite in Dev/Test)
* **Status:** Accepted
* **Context:** Need zero-friction, ultra-fast developer onboarding on local machines without requiring heavy background database services, while maintaining robust PostgreSQL in production.
* **Decision:** Utilize Eloquent ORM with strictly portable migrations. Local development and automated tests run against file/in-memory SQLite, while Docker Compose and VPS deployments run PostgreSQL 16+.
* **Consequences:** Tests execute in seconds; production maintains enterprise-grade concurrency, indexing, and backup capabilities.

---

### ADR-004: Native Bidirectional (RTL/LTR) Localization Engine
* **Status:** Accepted
* **Context:** Arabic is the native language for Novixa's primary market in Yemen and the GCC, while English is used for technical and international operations.
* **Decision:** Treat Arabic as the default initial experience. Employ dictionary-based i18n (`locales/ar.json`, `locales/en.json`), dynamically toggle HTML `dir` and `lang` attributes, and use Tailwind logical properties (`ms-*`, `me-*`, `ps-*`, `pe-*`) to ensure layout symmetry.
* **Consequences:** Clean, authentic reading experience in both languages without visual mirroring bugs.

---

### ADR-005: Task Dependency Cycle Prevention via Graph Traversal
* **Status:** Accepted
* **Context:** Complex task graphs (blocking/blocked-by relationships and subtasks) can accidentally form infinite loops (Task A blocks B, B blocks A).
* **Decision:** Implement a Depth-First Search (DFS) cycle detection check in the `TaskDependencyAction` domain service before saving any dependency. Reject cyclic additions with HTTP 422 Unprocessable Entity.
* **Consequences:** Guarantees DAG (Directed Acyclic Graph) integrity for project schedules and automated blocker detection.

---

### ADR-006: Private Disk Attachment Streaming with Server Authorization
* **Status:** Accepted
* **Context:** File uploads (contracts, specs, client quotes) must never be accessible via guessing public URLs.
* **Decision:** Store attachments in `storage/app/private/` with UUID names. Serve file downloads strictly through an authorized endpoint `GET /api/v1/attachments/{id}/download` that validates the user's project/task permissions before streaming.
* **Consequences:** Complete protection against unauthorized file downloads and IDOR data leaks.
