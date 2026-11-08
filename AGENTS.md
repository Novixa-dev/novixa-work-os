# AGENTS.md — Specialized Engineering Roles for Novixa WorkOS

This document specifies the professional disciplines, operating protocols, and quality standards for all engineering work on Novixa WorkOS.

---

## 1. Operating Roles & Responsibilities

### 1.1 Product Manager & Business Analyst
- Ensures every implemented feature serves genuine Novixa internal operations.
- Validates user stories, edge cases, status transitions, and data integrity constraints.
- Prevents scope creep: keeps accounting, payroll, and public portals strictly out of MVP.
- Ensures bilingual requirements (Arabic default, English secondary) are treated as first-class.

### 1.2 Software Architect
- Maintains the **Modular Monolith** boundary definitions:
  - `Identity`: Users, roles, permissions, personal preferences, audit logging.
  - `Work`: Tasks, subtasks, dependencies, labels, activity, comments, attachments.
  - `Projects`: Project lifecycle, ownership, milestones, team members, updates.
  - `Products`: Product catalog (Novixa Aqar, Muhajim, etc.), links, roadmaps, linked work.
  - `CRM`: Clients, contacts, opportunities, interactions, follow-ups, linked delivery projects.
  - `Knowledge`: SOPs, tech decisions, project notes, documentation.
  - `Notifications`: In-app notifications, preferences, reminders.
  - `Shared`: Common DTOs, API wrappers, filtering/sorting traits, auditing.
- Avoids premature microservices, Redis/GraphQL over-engineering, or distributed complexity.

### 1.3 Senior Backend Engineer (Laravel / PHP 8.4)
- Implements secure, typed, resource-efficient API endpoints.
- Uses Form Requests for validation, Eloquent API Resources for serializing responses.
- Enforces authorization via Laravel Policies and Gates on *every* protected query and mutation.
- Uses database transactions for all multi-table mutations.
- Eliminates N+1 queries using eager loading (`with()`).
- Keeps business logic in dedicated Domain Actions / Services rather than bloated controllers.

### 1.4 Senior Frontend Engineer (Nuxt 4 / Vue 3 / TypeScript)
- Implements strict TypeScript mode without `any`.
- Builds accessible, responsive components using Tailwind CSS and headless primitives.
- Ensures genuine bilingual RTL/LTR ergonomics (logical CSS properties, correct icon direction, localized formatting).
- Implements comprehensive UI states: loading skeletons, empty states, error boundaries, optimistic feedback.
- Uses cookie-based Sanctum SPA authentication; stores zero tokens in localStorage.

### 1.5 UI/UX Designer
- Designs a calm, focused, high-density B2B productivity workspace inspired by Linear, Plane, and Notion.
- Uses Novixa's official brand palette:
  - Primary Blue: `#2563EB` (Tailwind `blue-600`)
  - Deep Navy: `#0F172A` (Tailwind `slate-900`) / `#020617` (Tailwind `slate-950`)
  - Accent Teal: `#14B8A6` (Tailwind `teal-500`)
  - High-contrast neutral surfaces for dark and light modes.
- Evaluates and implements professional Arabic typography (Readex Pro / Cairo / IBM Plex Sans Arabic) paired with Inter.
- Never uses emoji for navigation icons; maintains a cohesive Lucide icon system.

### 1.6 Application Security Engineer
- Audits all endpoints against OWASP Top 10 and ASVS 5.0.
- Guarantees strict IDOR prevention, CSRF validation, session invalidation, and rate limiting.
- Ensures private file attachments are served through authenticated streaming routes, never public URLs.
- Eliminates credential leakage in Git, logs, error responses, and frontend bundles.

### 1.7 QA & Automation Engineer
- Author of backend Pest/PHPUnit tests, frontend unit tests, and Playwright end-to-end user journeys.
- Enforces the rule: *No feature is marked complete without automated tests proving functionality, authorization, and error handling.*
- Seeds deterministic, synthetic Arabic and English test fixtures.

### 1.8 DevOps & Operations Engineer
- Manages reproducible Docker Compose environments for local development and VPS production.
- Prepares Nginx reverse proxy configuration with TLS, gzip/brotli, security headers, and rate limiting.
- Formulates and tests database and attachment backup and restore automation.
- Authors health checks, migration runners, and production deployment scripts.

---

## 2. Review Protocols & Definition of Done

Every task must pass the 8-point verification gate before being committed:
1. **Domain Logic:** Business rules and transitions are strictly validated.
2. **Security & Authorization:** Policy checks prevent unauthorized or IDOR access.
3. **Data Integrity:** Database migrations, foreign keys, cascade rules, and transactions are in place.
4. **Bilingual RTL/LTR:** Verified in both Arabic (`dir="rtl"`) and English (`dir="ltr"`).
5. **Responsive UX:** Verified at 360px (mobile), 768px (tablet), and 1280px+ (desktop).
6. **State Completeness:** Loading, empty, error, and validation states are rendered.
7. **Automated Tests:** Unit/Feature tests pass.
8. **Documentation:** Progress, decisions, and traceability logs are updated.
