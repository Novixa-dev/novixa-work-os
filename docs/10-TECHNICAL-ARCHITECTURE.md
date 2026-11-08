# 10 — Technical Architecture: Novixa WorkOS

## 1. Architectural Philosophy

Novixa WorkOS is architected as a **Modular Monolith** with a decoupled modern frontend client:
- **Backend:** Laravel 11/12 (PHP 8.4) serving a RESTful JSON API.
- **Frontend:** Nuxt 4 (Vue 3, TypeScript strict mode, Vite) client application.
- **Data Layer:** PostgreSQL (production / Docker) and SQLite (fast local development & automated testing).
- **Authentication:** First-party session cookie authentication powered by Laravel Sanctum SPA flow. Zero access tokens stored in browser `localStorage`.
- **Styling & Design System:** Tailwind CSS with custom CSS variables supporting dark/light mode and true bidirectional (RTL/LTR) layouts.

```
                    ┌────────────────────────────────────────┐
                    │          Nuxt 4 Frontend SPA           │
                    │      (Vue 3, Pinia, Tailwind CSS)      │
                    │   RTL / LTR  •  Arabic / English i18n  │
                    └───────────────────┬────────────────────┘
                                        │
                         HTTPS / Secure Cookie (SameSite)
                                        │
                    ┌───────────────────▼────────────────────┐
                    │           Nginx Reverse Proxy          │
                    │  TLS, Security Headers, Rate Limiting  │
                    └───────────────────┬────────────────────┘
                                        │
                    ┌───────────────────▼────────────────────┐
                    │          Laravel Modular Monolith      │
                    │               (PHP 8.4)                │
                    │                                        │
                    │  ┌───────────┐ ┌───────────┐ ┌──────┐  │
                    │  │ Identity  │ │ Work Mgmt │ │ CRM  │  │
                    │  └───────────┘ └───────────┘ └──────┘  │
                    │  ┌───────────┐ ┌───────────┐ ┌──────┐  │
                    │  │ Products  │ │ Projects  │ │ Docs │  │
                    │  └───────────┘ └───────────┘ └──────┘  │
                    │  ┌───────────────────────────────────┐  │
                    │  │   Shared Core: Auth, Audit, Policy │  │
                    │  └───────────────────────────────────┘  │
                    └───────────────────┬────────────────────┘
                                        │
               ┌────────────────────────┴────────────────────────┐
               ▼                                                 ▼
      PostgreSQL / SQLite Database                     Private Disk Storage
  (Foreign Keys, Indexes, Trans.)                   (Non-public Attachments)
```

---

## 2. Monorepo Repository Structure

The workspace is organized cleanly as a structured monorepo:

```
novixa-work-os/
├── apps/
│   ├── api/                    # Laravel RESTful API (Backend)
│   │   ├── app/
│   │   │   ├── Modules/        # Modular Monolith Boundaries
│   │   │   │   ├── Identity/   # Auth, Users, Roles, Preferences, Audit
│   │   │   │   ├── Work/       # Tasks, Subtasks, Dependencies, Labels, Comments
│   │   │   │   ├── Projects/   # Projects, Members, Milestones, Updates
│   │   │   │   ├── Products/   # Product catalog, Roadmaps, Links
│   │   │   │   ├── CRM/        # Clients, Contacts, Opportunities, Interactions
│   │   │   │   ├── Knowledge/  # SOPs, Tech Notes, Documentation
│   │   │   │   ├── Notify/     # Notifications & Preferences
│   │   │   │   └── Shared/     # Base controllers, DTOs, Policies, Helpers
│   │   │   └── Models/         # Eloquent entity models
│   │   ├── config/             # Laravel configuration files
│   │   ├── database/
│   │   │   ├── migrations/     # Versioned database migrations
│   │   │   ├── seeders/        # Deterministic synthetic test seeders
│   │   │   └── factories/      # Eloquent model factories
│   │   ├── routes/
│   │   │   └── api.php         # Versioned `/api/v1` routes
│   │   ├── storage/            # Private file storage & logs
│   │   └── tests/              # Pest / PHPUnit test suites
│   │
│   └── web/                    # Nuxt 4 Frontend Application
│       ├── app.vue             # Root application component
│       ├── assets/             # Global CSS, Tailwind base & fonts
│       ├── components/         # Reusable UI component library
│       │   ├── ui/             # Buttons, inputs, modals, cards, badges
│       │   ├── layout/         # Header, sidebar, navigation, command palette
│       │   └── modules/        # Module-specific views (tasks, projects, etc.)
│       ├── composables/        # Vue composables (useAuth, useApi, useI18n, etc.)
│       ├── locales/            # Translation dictionary (ar.json, en.json)
│       ├── pages/              # File-based Nuxt routing
│       │   ├── auth/           # Login, reset password, accept invite
│       │   ├── dashboard/      # Executive & operational dashboard
│       │   ├── my-work/        # My Day, My Tasks, Overdue, Assigned by me
│       │   ├── tasks/          # Task list, kanban board, detail drawer
│       │   ├── projects/       # Projects directory & detail view
│       │   ├── products/       # Products catalog & roadmap
│       │   ├── crm/            # Clients, contacts, opportunities pipeline
│       │   ├── knowledge/      # Internal knowledge base & SOPs
│       │   └── settings/       # Workspace, profile & team settings
│       └── stores/             # Pinia state stores
│
├── docker/                     # Dockerfiles & Nginx configs
├── docs/                       # Complete engineering & product documentation
├── scripts/                    # Development, migration, backup, & test scripts
├── docker-compose.yml          # Local and staging container orchestration
└── package.json                # Root package workspace definition
```

---

## 3. Data Integrity & Domain Modeling

1. **Foreign Key Integrity:** All relationships (e.g. `task -> project`, `task -> assignee`, `opportunity -> client`) enforce strict foreign key constraints with explicit `ON DELETE CASCADE` or `RESTRICT`.
2. **Cycle Prevention:**
   - Tasks cannot be subtasks of themselves or create cyclic ancestry chains. Enforced in validation and transaction logic.
   - Task dependencies cannot form cycles (Task A blocks B, B blocks A). Enforced using graph cycle detection before persisting.
3. **Optimistic Locking & Transactions:** Multi-step mutations (e.g., converting an opportunity to a project, reordering Kanban columns, archiving parent tasks with subtasks) execute within atomic database transactions.
4. **Time & Locale Standardization:** All dates and timestamps are stored in UTC (`timestamp with time zone` / ISO 8601) and transformed to the user's localized timezone (`Asia/Riyadh` or `Asia/Aden` by default) in the UI layer.

---

## 4. First-Party Cookie Authentication & Security Architecture

1. **Sanctum SPA Flow:**
   - Frontend requests `GET /sanctum/csrf-cookie` to establish CSRF token.
   - Login POSTs credentials; session cookie is issued with `HttpOnly`, `Secure`, and `SameSite=Lax`.
   - Subsequent API requests automatically send the session cookie.
   - If session expires, API returns `401 Unauthorized`, prompting seamless re-authentication without data loss.
2. **Access Control Policies:**
   - Every API controller method delegates to a dedicated Laravel Policy (`TaskPolicy`, `ProjectPolicy`, `ClientPolicy`, etc.).
   - Default-deny: If no explicit permission allows the action, access is rejected with `403 Forbidden`.
3. **Attachment Streaming:**
   - Attachments are stored on private storage disks with randomly generated UUID filenames.
   - File downloads are accessed only via `GET /api/v1/attachments/{id}/download`, which verifies policy access before streaming the binary data with security headers (`X-Content-Type-Options: nosniff`).
