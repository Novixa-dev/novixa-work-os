# Novixa WorkOS (منظومة إدارة أعمال نوڤيكسا)

> **Engineered for Growth • نبني التقنية التي تجعل أعمالك أقوى**  
> A private, bilingual, high-density internal operations workspace built for **Novixa** (نوڤيكسا للبرمجيات والحلول الرقمية).

---

## 1. Overview

**Novixa WorkOS** connects daily work execution, project delivery, proprietary software roadmaps, and client relationships into a unified, secure operating system.

Built as an authenticated, single-workspace modular monolith, it eliminates disconnected spreadsheets and bloated SaaS subscriptions in favor of speed, privacy, and authentic Arabic-first usability.

### Core Modules
* **Dashboard:** Real-time priorities, tasks due today, overdue alerts, active project health, and client follow-ups.
* **My Work:** Focused personal workspace with My Day, My Tasks, Assigned by Me, and Overdue views.
* **Tasks & Execution:** Fast List and Kanban boards, subtasks with cycle prevention, blocking dependencies, comments, mentions, watchers, and private file attachments.
* **Projects & Milestones:** Project lifecycle, lead ownership, deliverable-calculated progress, milestone tracking, and linked clients/products.
* **Products Catalog:** Portfolio management for Novixa's proprietary platforms (*Novixa Aqar*, *Muhajim*, *Novixa Serve*, *Kitchen-POS*), linking repositories, demos, and active roadmaps.
* **Clients & CRM:** Lightweight B2B pipeline, contact records, interaction logging (Meetings, Calls, WhatsApp), scheduled follow-ups, and seamless deal-to-delivery handoffs.
* **Team & Permissions:** Strict role-based access (`Owner`, `Admin`, `Manager`, `Member`, `Guest`) with immutable audit trails.
* **Knowledge Base:** Internal SOPs, architecture records, and operational playbooks.

---

## 2. Technical Stack

* **Frontend:** [Nuxt 4](https://nuxt.com/) (Vue 3, TypeScript strict mode, Vite, Pinia, Tailwind CSS).
* **Backend:** [Laravel](https://laravel.com/) (PHP 8.4) modular monolith REST API.
* **Authentication:** First-party cookie session authentication via [Laravel Sanctum](https://laravel.com/docs/sanctum) SPA flow.
* **Database:** Dual-engine architecture:
  - **Local Development & Tests:** SQLite (zero background daemon friction, instant testing).
  - **Docker & Production:** PostgreSQL 16+ (full transactional concurrency and backup tooling).
* **Localization:** Native bidirectional engine (Arabic default RTL + English LTR).
* **DevOps:** Docker Compose, Nginx reverse proxy with TLS and security headers.

---

## 3. Quick Start (Local Development)

### Prerequisites
- Node.js `v20+` or `v24+` (pnpm or npm)
- PHP `8.3+` or `8.4+` with extensions: `pdo_sqlite`, `pdo_pgsql`, `intl`, `mbstring`, `fileinfo`
- Composer `2.8+`

### One-Command Setup
Clone the repository and run the setup script:

```bash
# On Windows PowerShell:
.\scripts\setup.ps1

# Or run backend and frontend manually:
# 1. Backend API:
cd apps/api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve --port=8000

# 2. Frontend Web App:
cd ../web
pnpm install
pnpm dev
```

Visit `http://localhost:3000` to access the application.

---

## 4. Documentation Index

Complete engineering and architecture documentation is maintained in [`docs/`](docs/):
- [`docs/01-PRODUCT-VISION.md`](docs/01-PRODUCT-VISION.md) — Strategic vision & operational pillars.
- [`docs/02-NOVIXA-CONTEXT.md`](docs/02-NOVIXA-CONTEXT.md) — Authentic corporate context & product catalog.
- [`docs/03-PRD.md`](docs/03-PRD.md) — Product Requirements Document.
- [`docs/05-SCOPE-AND-ROADMAP.md`](docs/05-SCOPE-AND-ROADMAP.md) — In-scope MVP vs post-MVP roadmap.
- [`docs/10-TECHNICAL-ARCHITECTURE.md`](docs/10-TECHNICAL-ARCHITECTURE.md) — System architecture & domain boundaries.
- [`docs/12-ROLES-AND-PERMISSIONS.md`](docs/12-ROLES-AND-PERMISSIONS.md) — RBAC matrix and access control.
- [`docs/13-SECURITY.md`](docs/13-SECURITY.md) — Security policies and OWASP ASVS 5.0 alignment.
- [`docs/15-ACCEPTANCE-CRITERIA.md`](docs/15-ACCEPTANCE-CRITERIA.md) — Testable acceptance criteria per epic.
- [`docs/16-IMPLEMENTATION-PLAN.md`](docs/16-IMPLEMENTATION-PLAN.md) — Phased delivery plan.
- [`docs/17-DEPLOYMENT-AND-DEVOPS.md`](docs/17-DEPLOYMENT-AND-DEVOPS.md) — Docker, CI/CD, backup & disaster recovery.
- [`docs/19-DECISIONS-AND-OPEN-QUESTIONS.md`](docs/19-DECISIONS-AND-OPEN-QUESTIONS.md) — Architectural decisions log.
- [`docs/20-PROGRESS.md`](docs/20-PROGRESS.md) — Timestamped milestone progress log.
- [`docs/21-TRACEABILITY.md`](docs/21-TRACEABILITY.md) — Requirements traceability matrix.
- [`docs/23-DELIVERY-BACKLOG.md`](docs/23-DELIVERY-BACKLOG.md) — Initial epics and ticket breakdown.
- [`docs/24-RELEASE-CHECKLIST.md`](docs/24-RELEASE-CHECKLIST.md) — Go/No-Go release readiness checklist.
- [`docs/IMPLEMENTATION-AUDIT.md`](docs/IMPLEMENTATION-AUDIT.md) — Phase 0 environment audit and findings.
- [`docs/ARCHITECTURE-DECISIONS.md`](docs/ARCHITECTURE-DECISIONS.md) — Detailed ADRs.
- [`docs/TASK-TRACKER.md`](docs/TASK-TRACKER.md) — Granular task status tracker.
- [`docs/DESIGN-SYSTEM.md`](docs/DESIGN-SYSTEM.md) — Design tokens, colors, typography, and RTL rules.
- [`docs/UX-RESEARCH.md`](docs/UX-RESEARCH.md) — UX patterns and interaction flows.

---

## 5. Security & License

This is proprietary software owned by **Novixa** (نوڤيكسا للبرمجيات والحلول الرقمية). Unauthorized copying or distribution is strictly prohibited.
