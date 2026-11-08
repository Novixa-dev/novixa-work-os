# 21 — Requirements Traceability Matrix: Novixa WorkOS

This matrix maps every requirement from the PRD to its technical implementation files, API routes, and test verification cases.

| Req ID | Requirement Description | Domain Module | Backend / Route | Frontend Component / Page | Test Verification |
|--------|-------------------------|---------------|-----------------|----------------------------|-------------------|
| **REQ-AUTH-01** | User authentication with email/password | `Identity` | `POST /api/v1/auth/login` | `pages/auth/login.vue` | `AuthFeatureTest::test_user_can_login` |
| **REQ-AUTH-02** | Session termination (Logout) | `Identity` | `POST /api/v1/auth/logout` | `components/layout/UserMenu.vue` | `AuthFeatureTest::test_user_can_logout` |
| **REQ-AUTH-03** | User provisioning & invitation | `Identity` | `POST /api/v1/users` | `pages/settings/team.vue` | `UserPolicyTest::test_only_admin_can_invite` |
| **REQ-AUTH-04** | Role-based authorization policies | `Identity` | Policies (`UserPolicy`, etc.) | App-wide navigation guards | `PermissionMatrixTest::test_all_roles` |
| **REQ-I18N-01** | Bilingual AR/EN toggle | `Shared` | User preferences API | `components/layout/LanguageToggle.vue` | `LocalizationE2ETest::test_rtl_switch` |
| **REQ-WORK-01** | Project creation & lifecycle | `Projects` | `POST /api/v1/projects` | `pages/projects/index.vue` | `ProjectFeatureTest::test_create_project` |
| **REQ-WORK-02** | Project progress calculation | `Projects` | `Project::calculateProgress()` | `components/modules/ProjectCard.vue` | `ProjectUnitTest::test_progress_formula` |
| **REQ-WORK-03** | Task CRUD & filtering | `Work` | `GET/POST /api/v1/tasks` | `pages/tasks/index.vue` | `TaskFeatureTest::test_task_crud` |
| **REQ-WORK-04** | Task Kanban Board | `Work` | `PATCH /api/v1/tasks/{id}/status` | `pages/tasks/kanban.vue` | `KanbanBoardE2ETest::test_status_change` |
| **REQ-WORK-05** | Subtasks & cycle rejection | `Work` | `POST /api/v1/tasks/{id}/subtasks`| `components/modules/SubtaskList.vue` | `TaskGraphTest::test_cycle_prevention` |
| **REQ-WORK-06** | Task dependencies (blocking) | `Work` | `POST /api/v1/tasks/{id}/deps` | `components/modules/TaskDeps.vue` | `TaskGraphTest::test_dependency_cycle` |
| **REQ-WORK-07** | My Work (My Day, Overdue) | `Work` | `GET /api/v1/my-work` | `pages/my-work/index.vue` | `MyWorkTest::test_filtered_tasks` |
| **REQ-WORK-08** | Task comments & mentions | `Work` | `POST /api/v1/tasks/{id}/comments`| `components/modules/CommentSection.vue`| `CommentTest::test_post_comment` |
| **REQ-PROD-01** | Product catalog directory | `Products` | `GET/POST /api/v1/products` | `pages/products/index.vue` | `ProductTest::test_product_listing` |
| **REQ-PROD-02** | Product-Project linkage | `Products` | `GET /api/v1/products/{id}/work` | `pages/products/[id].vue` | `ProductTest::test_linked_projects` |
| **REQ-CRM-01** | Client & contact directory | `CRM` | `GET/POST /api/v1/clients` | `pages/crm/clients/index.vue` | `ClientTest::test_client_crud` |
| **REQ-CRM-02** | Opportunity pipeline stages | `CRM` | `PATCH /api/v1/opportunities/{id}`| `pages/crm/pipeline.vue` | `OpportunityTest::test_stage_transitions` |
| **REQ-CRM-03** | Won deal to Delivery Project | `CRM` | `POST /api/v1/opportunities/{id}/convert` | `components/modules/WinDealModal.vue` | `OpportunityTest::test_conversion_flow` |
| **REQ-DASH-01** | Role-aware dashboard cards | `Shared` | `GET /api/v1/dashboard` | `pages/dashboard/index.vue` | `DashboardTest::test_dashboard_metrics` |
| **REQ-SRCH-01** | Global authorized search | `Shared` | `GET /api/v1/search` | `components/layout/CommandPalette.vue` | `SearchTest::test_permission_filtering` |
| **REQ-KNOW-01** | Knowledge SOP documentation | `Knowledge` | `GET/POST /api/v1/knowledge` | `pages/knowledge/index.vue` | `KnowledgeTest::test_sop_creation` |
| **REQ-NOTIF-01**| In-app notification center | `Notify` | `GET /api/v1/notifications` | `components/layout/NotificationMenu.vue` | `NotificationTest::test_unread_count` |
