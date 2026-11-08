# Task Tracker — Novixa WorkOS Implementation

This document tracks all implementation tickets through their execution lifecycle: `Backlog` → `Ready` → `In Progress` → `Review` → `Done`.

---

## Sprint Summary

| Total Tasks | Done | In Progress | Ready | Backlog |
|:-----------:|:----:|:-----------:|:-----:|:-------:|
| 35 | 3 | 2 | 10 | 20 |

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
| **NW-010** | P0 | Monorepo structure & local developer environment | Senior Backend | **In Progress** | Setup script | `apps/api`, `apps/web`, `scripts/dev.ps1` |
| **NW-011** | P0 | Environment variables & secret configuration | DevOps | **In Progress** | Env validator | `.env.example`, `apps/api/.env.example` |
| **NW-012** | P0 | Laravel 11/12 API backend scaffolding | Senior Backend | **Ready** | `php artisan test` | `apps/api/composer.json`, routes |
| **NW-013** | P0 | Database migrations (Users, Roles, Audit) | Database Eng. | **Ready** | Migration test | `apps/api/database/migrations` |
| **NW-014** | P0 | Nuxt 4 frontend scaffolding & Tailwind setup | Senior Frontend | **Ready** | `pnpm build` | `apps/web/package.json`, tailwind |
| **NW-015** | P0 | First-party Sanctum cookie session auth | Senior Backend | **Ready** | Auth tests | `apps/api/app/Modules/Identity` |
| **NW-016** | P0 | Bilingual i18n & RTL/LTR layout shell | UI/UX & Frontend | **Ready** | RTL snapshot | `apps/web/locales`, `apps/web/layouts` |
| **NW-017** | P1 | Design system tokens, typography & Lucide icons | UI/UX Designer | **Ready** | Visual QA | `docs/DESIGN-SYSTEM.md`, UI components |

---

### Phase 2: Core Work Management (Milestone M2)
| ID | Priority | Title | Owner | Status | Tests | Evidence |
|----|:--------:|-------|-------|:------:|-------|----------|
| **NW-020** | P1 | Project lifecycle, ownership & members | Senior Backend | **Backlog** | Feature test | `Projects` domain |
| **NW-021** | P1 | Project deliverable-based progress calculation | Senior Backend | **Backlog** | Unit test | Progress calculation logic |
| **NW-022** | P1 | Task CRUD, status transitions & priorities | Senior Backend | **Backlog** | Feature test | `Work` domain |
| **NW-023** | P1 | Subtasks with cycle prevention | Database Eng. | **Backlog** | Graph test | Subtask hierarchy |
| **NW-024** | P2 | Task blocking/blocked-by dependencies | Software Architect | **Backlog** | Graph DFS test | Dependency engine |
| **NW-025** | P1 | Task comments, activity history & mentions | Senior Backend | **Backlog** | Feature test | Comment & activity audit |
| **NW-026** | P1 | Private attachments streaming with policy checks | Security Eng. | **Backlog** | IDOR test | Attachment controller |
| **NW-027** | P1 | Interactive Kanban board with drag-and-drop | Senior Frontend | **Backlog** | E2E test | Kanban view component |
| **NW-028** | P1 | "My Work" personalized dashboard (My Day, Overdue)| Senior Frontend | **Backlog** | Query test | My Work views |

---

### Phase 3: Company Context (Milestone M3)
| ID | Priority | Title | Owner | Status | Tests | Evidence |
|----|:--------:|-------|-------|:------:|-------|----------|
| **NW-030** | P1 | Novixa Products catalog & roadmap linking | Product Manager | **Backlog** | Feature test | `Products` domain |
| **NW-031** | P1 | Clients & Contacts directory | Senior Backend | **Backlog** | Feature test | `CRM` domain |
| **NW-032** | P1 | Opportunity pipeline & stage transitions | Senior Backend | **Backlog** | State test | Opportunity pipeline |
| **NW-033** | P1 | Client delivery project generation on deal win | Business Analyst | **Backlog** | Integration test | Deal conversion service |
| **NW-034** | P1 | Executive & Engineer role-aware dashboard | Senior Frontend | **Backlog** | Metric test | Dashboard widgets |
| **NW-035** | P2 | Global authorized search palette | Senior Backend | **Backlog** | Search test | Global search endpoint |
| **NW-036** | P1 | In-app notification center | Senior Backend | **Backlog** | Event test | Notification polling/events |
| **NW-037** | P2 | Internal Knowledge SOPs & tech notes | Technical Writer | **Backlog** | CRUD test | Knowledge base module |

---

### Phase 4: Hardening & Production Packaging (Milestone M4)
| ID | Priority | Title | Owner | Status | Tests | Evidence |
|----|:--------:|-------|-------|:------:|-------|----------|
| **NW-040** | P0 | Comprehensive automated test suite | QA Engineer | **Backlog** | 100% passing | Pest + Vitest + Playwright |
| **NW-041** | P0 | OWASP ASVS 5.0 security audit & IDOR checks | Security Eng. | **Backlog** | Security scan | Security audit report |
| **NW-042** | P0 | Docker Compose production configuration | DevOps | **Backlog** | Docker build | `docker-compose.yml`, Nginx |
| **NW-043** | P0 | Automated backup & restore verification script | DevOps | **Backlog** | Restore drill | `scripts/backup.sh`, `scripts/restore.sh` |
| **NW-044** | P0 | Operations runbook & release sign-off | DevOps & PM | **Backlog** | Runbook review | `docs/OPERATIONS-RUNBOOK.md` |
