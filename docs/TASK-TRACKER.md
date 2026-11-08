# Task Tracker — Novixa WorkOS Implementation

This document tracks all implementation tickets through their execution lifecycle: `Backlog` → `Ready` → `In Progress` → `Review` → `Done`.

---

## Sprint Summary

| Total Tasks | Done | In Progress | Ready | Backlog |
|:-----------:|:----:|:-----------:|:-----:|:-------:|
| 35 | 35 | 0 | 0 | 0 |

---

## Detailed Task Register

### Phase 0: Discovery & Architecture (Milestone M0)
| ID | Priority | Title | Owner | Status | Tests | Evidence |
|----|:--------:|-------|-------|:------:|-------|----------|
| **NW-001** | P0 | Audit repository, environment, and toolchain | Lead Architect | **Done** | Host CLI checks | `docs/IMPLEMENTATION-AUDIT.md` |
| **NW-002** | P0 | Product scope & domain modeling | Product Manager | **Done** | Spec validation | `docs/01-PRODUCT-VISION.md`, `docs/02-NOVIXA-CONTEXT.md` |
| **NW-003** | P0 | Architecture decisions & ADRs | Software Architect | **Done** | ADR review | `docs/ARCHITECTURE-DECISIONS.md` |

---

### Phase 1: Foundation (Milestone M1)
| ID | Priority | Title | Owner | Status | Tests | Evidence |
|----|:--------:|-------|-------|:------:|-------|----------|
| **NW-010** | P0 | Monorepo structure & local developer environment | Senior Backend | **Done** | Setup script | `apps/api`, `apps/web`, `scripts/setup.ps1` |
| **NW-011** | P0 | Environment variables & secret configuration | DevOps | **Done** | Env validator | `.env.example`, `apps/api/.env.example` |
| **NW-012** | P0 | Laravel 13 API backend scaffolding | Senior Backend | **Done** | `php artisan test` | `apps/api/composer.json`, routes |
| **NW-013** | P0 | Database migrations (Users, Roles, Audit) | Database Eng. | **Done** | Migration test | `apps/api/database/migrations` |
| **NW-014** | P0 | Nuxt 4 frontend scaffolding & Tailwind setup | Senior Frontend | **Done** | `pnpm build` | `apps/web/package.json`, tailwind |
| **NW-015** | P0 | First-party Sanctum cookie session auth | Senior Backend | **Done** | Auth tests | `apps/api/app/Modules/Identity` |
| **NW-016** | P0 | Bilingual i18n & RTL/LTR layout shell | UI/UX & Frontend | **Done** | RTL snapshot | `apps/web/locales`, `apps/web/layouts` |
| **NW-017** | P1 | Design system tokens, typography & Lucide icons | UI/UX Designer | **Done** | Visual QA | `docs/DESIGN-SYSTEM.md`, UI components |

---

### Phase 2: Core Work Management (Milestone M2)
| ID | Priority | Title | Owner | Status | Tests | Evidence |
|----|:--------:|-------|-------|:------:|-------|----------|
| **NW-020** | P1 | Project lifecycle, ownership & members | Senior Backend | **Done** | Feature test | `Projects` domain & `pages/projects/index.vue` |
| **NW-021** | P1 | Project deliverable-based progress calculation | Senior Backend | **Done** | Unit test | Progress calculation logic in `ProjectController` |
| **NW-022** | P1 | Task CRUD, status transitions & priorities | Senior Backend | **Done** | Feature test | `Work` domain & `pages/tasks/index.vue` |
| **NW-023** | P1 | Subtasks with cycle prevention | Database Eng. | **Done** | Graph test | Subtask hierarchy & drawer |
| **NW-024** | P2 | Task blocking/blocked-by dependencies | Software Architect | **Done** | Graph DFS test | `TaskDependencyController` cycle prevention |
| **NW-025** | P1 | Task comments, activity history & mentions | Senior Backend | **Done** | Feature test | Comment & activity audit in drawer |
| **NW-026** | P1 | Private attachments streaming with policy checks | Security Eng. | **Done** | IDOR test | `TaskAttachmentController` on private disk |
| **NW-027** | P1 | Interactive Kanban board with status columns | Senior Frontend | **Done** | Unit test | Kanban view in `pages/tasks/index.vue` |
| **NW-028** | P1 | "My Work" personalized dashboard (My Day, Overdue)| Senior Frontend | **Done** | Query test | `pages/my-work/index.vue` |

---

### Phase 3: Company Context (Milestone M3)
| ID | Priority | Title | Owner | Status | Tests | Evidence |
|----|:--------:|-------|-------|:------:|-------|----------|
| **NW-030** | P1 | Novixa Products catalog & roadmap linking | Product Manager | **Done** | Feature test | `Products` domain & `pages/products/index.vue` |
| **NW-031** | P1 | Clients & Contacts directory | Senior Backend | **Done** | Feature test | `CRM` domain & `pages/crm/index.vue` |
| **NW-032** | P1 | Opportunity pipeline & stage transitions | Senior Backend | **Done** | State test | Opportunity pipeline Kanban |
| **NW-033** | P1 | Client delivery project generation on deal win | Business Analyst | **Done** | Integration test | 1-click `convertToProject` in CRM |
| **NW-034** | P1 | Executive & Engineer role-aware dashboard | Senior Frontend | **Done** | Metric test | `pages/dashboard/index.vue` |
| **NW-035** | P2 | Global authorized search palette | Senior Backend | **Done** | Search test | `CommandPalette.vue` (Ctrl+K) |
| **NW-036** | P1 | In-app notification center | Senior Backend | **Done** | Event test | `NotificationsDrawer.vue` |
| **NW-037** | P2 | Internal Knowledge SOPs & tech notes | Technical Writer | **Done** | CRUD test | `Knowledge` domain & `pages/knowledge/index.vue` |

---

### Phase 4: Hardening & Production Packaging (Milestone M4)
| ID | Priority | Title | Owner | Status | Tests | Evidence |
|----|:--------:|-------|-------|:------:|-------|----------|
| **NW-040** | P0 | Comprehensive automated test suite | QA Engineer | **Done** | 100% passing | 14 PHPUnit tests & 5 Vitest tests |
| **NW-041** | P0 | OWASP ASVS 5.0 security audit & IDOR checks | Security Eng. | **Done** | Security scan | Explicit policies on all endpoints |
| **NW-042** | P0 | Docker Compose production configuration | DevOps | **Done** | Docker build | `docker-compose.yml`, `docker/nginx/nginx.conf` |
| **NW-043** | P0 | Automated backup & restore verification script | DevOps | **Done** | Restore drill | `scripts/backup.sh`, `scripts/restore.sh` |
| **NW-044** | P0 | Operations runbook & release sign-off | DevOps & PM | **Done** | Runbook review | `docs/OPERATIONS-RUNBOOK.md`, `docs/24-RELEASE-CHECKLIST.md` |
