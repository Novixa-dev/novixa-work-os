# 12 — Roles and Permissions: Novixa WorkOS

## 1. Role Definitions

Novixa WorkOS enforces a role-based access control (RBAC) model tailored to Novixa's team structure:

| Role | Arabic Title | Description & Scope |
|------|--------------|---------------------|
| `owner` | المالك | Company founder / executive. Complete access to workspace settings, financial and commercial opportunities, user provisioning, role assignments, audit logs, and irreversible administrative actions. |
| `admin` | مدير النظام | System administrator. Manages user provisioning, workspace settings, tags, workflow stages, audit reviews, and all projects/tasks. Cannot modify owner account. |
| `manager` | مدير مشاريع / قائد فريق | Project lead or commercial manager. Can create/manage projects, assign tasks, manage client records and pipeline opportunities, and invite team members. |
| `member` | عضو فريق | Core team member (engineer, designer, specialist). Can view workspace projects, create and update tasks, log comments, participate in discussions, and view company products and knowledge SOPs. |
| `guest` | ضيف / مراقب | External client or observer with strictly restricted read-only access to specifically assigned projects and tasks. Cannot view CRM, financial values, or internal audit logs. |

---

## 2. Granular Permissions Matrix

| Capability / Resource | Owner | Admin | Manager | Member | Guest |
|-----------------------|:-----:|:-----:|:-------:|:------:|:-----:|
| **Workspace Settings & Brand** | Full | Full | View | View | None |
| **User Provisioning & Roles** | Full | Full (non-owner) | Invite Only | None | None |
| **Audit Log Inspection** | Full | Full | None | None | None |
| **Projects (Create / Edit / Archive)** | Full | Full | Full | Assigned/Self | None |
| **Tasks (Create / Edit / Assign)** | Full | Full | Full | Full | Assigned Only |
| **Tasks (Delete / Permanent Archive)** | Full | Full | Assigned Projects | Creator Only | None |
| **Products Catalog & Roadmap (Edit)** | Full | Full | Full | View | None |
| **CRM Clients & Contacts (Create / Edit)** | Full | Full | Full | Assigned Only | None |
| **CRM Opportunity Pipeline (Edit Values)** | Full | Full | Full | Assigned Only | None |
| **Knowledge Base SOPs (Create / Edit)** | Full | Full | Full | Create / Edit | Read Only |
| **Private File Attachments (Download)** | Full | Full | Permitted Projects | Permitted Tasks | Assigned Only |

---

## 3. Server-Side Enforcement Rules

1. **Default-Deny:** In every Laravel Policy, methods return `false` unless explicitly authorized.
2. **Owner Immutability:** No admin or manager can demote, deactivate, or delete an `owner` user.
3. **IDOR Defense:** Direct Object References (`/api/v1/projects/{id}`, `/api/v1/tasks/{id}`) always query through Eloquent scopes or policy checks verifying the user has rights to that specific entity.
4. **Audit Trailing:** Any promotion, demotion, user deactivation, or permission alteration writes an immutable record to the `audit_logs` table with actor ID, IP address, timestamp, and changes diff.
