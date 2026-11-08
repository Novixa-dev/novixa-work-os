# 19 — Decisions and Open Questions: Novixa WorkOS

## 1. Architectural Decision Records (ADRs) Summary

### ADR-001: Modular Monolith vs Microservices
* **Status:** Accepted
* **Context:** Novixa needs rapid execution, atomic transactions between tasks and projects, and minimal operational overhead.
* **Decision:** Build Novixa WorkOS as a Modular Monolith in Laravel, with decoupled domain modules (`Identity`, `Work`, `Projects`, `Products`, `CRM`, `Knowledge`, `Notify`).
* **Consequences:** Eliminates distributed transaction failures and network latency; allows unified migrations while maintaining clear code boundaries.

### ADR-002: Nuxt 4 + Vue 3 SPA with First-Party Sanctum Cookies
* **Status:** Accepted
* **Context:** Session security and avoidance of token leakage in browser storage.
* **Decision:** Use Laravel Sanctum first-party cookie authentication with `HttpOnly`, `SameSite=Lax`, and CSRF protection. Avoid storing JWT or bearer tokens in `localStorage`.
* **Consequences:** Highest resistance to XSS session theft; clean seamless authentication flow.

### ADR-003: Dual Database Strategy (PostgreSQL in Docker/Production, SQLite in Local/Test)
* **Status:** Accepted
* **Context:** Developers on Windows / Linux need zero-friction instant local setup and fast CI test runs without demanding heavy database servers.
* **Decision:** Design Eloquent schema using standard database-agnostic types compatible with both PostgreSQL 16+ and SQLite 3. Provide automated configuration for both.
* **Consequences:** Extremely fast local testing; production-grade concurrency and performance on VPS.

### ADR-004: Dual-Engine Localization (Arabic-First + English LTR)
* **Status:** Accepted
* **Context:** Novixa is rooted in Hadramout and the GCC, where Arabic is the native business language, while engineering documentation frequently references English terminology.
* **Decision:** Build complete dictionary-driven i18n (`ar.json` and `en.json`) with Arabic as the default landing locale. Pair it with dynamic CSS `dir="rtl"` / `dir="ltr"` and logical Tailwind classes.
* **Consequences:** Zero hardcoded strings; native bilingual experience without UI distortion.

### ADR-005: Deferral of Financial Ledger and Public Client Portal
* **Status:** Accepted
* **Context:** Pressure to build invoicing, online payments, and client self-service logins in MVP.
* **Decision:** Exclude accounting, invoicing, payroll, and public portals from MVP. WorkOS focuses purely on internal work, delivery, and relationship tracking.
* **Consequences:** Preserves focus and ensures high reliability for core operational workflows.

---

## 2. Open Questions & Assumptions Log

| # | Question / Assumption | Assumption Made | Revisit Trigger |
|---|-----------------------|-----------------|-----------------|
| OQ-1 | Should clients have direct portal login to see their delivery progress? | Defer client portal to V2. WorkOS is an internal platform. Delivery updates can be exported or communicated via account managers. | Client demand exceeds capacity of manual progress sharing. |
| OQ-2 | Real-time chat vs task comments? | Use structured task comments and notifications; avoid maintaining WebSockets/chat infrastructure in MVP. | Team specifically requests replacing Slack/WhatsApp with internal chat. |
| OQ-3 | Subtask nesting depth limit? | Limit subtask nesting depth to 3 levels to prevent deeply recursive tree traversal and UI complexity. | Complex project breakdown requires deeper hierarchies. |
| OQ-4 | Primary assignee vs multi-assignees? | Enforce a single Primary Assignee per task for unambiguous accountability. Use "Watchers" for secondary collaborators. | Team requests shared co-ownership of indivisible tasks. |
