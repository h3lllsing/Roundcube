# Project Log — Tyro RBAC Enterprise

**Started:** 2026-05-23
**Last Updated:** 2026-05-23 13:31 UTC

---

## Phase 1 — Environment Setup
| # | Task | Timestamp | Status |
|---|------|-----------|--------|
| 1.1 | MySQL started, DB `tyro_project` created (utf8mb4, XAMPP) | 12:20 | ✅ |
| 1.2 | Laravel 12.12.2 installed at `D:\xampp\htdocs\unknow` | 12:20 | ✅ |
| 1.3 | Composer dependencies — Tyro v1.6.0 + Sanctum v4.3.2 | 12:21 | ✅ |
| 1.4 | `.env` configured for MySQL, SQLite removed | 12:21 | ✅ |
| 1.5 | `tyro:sys-install` — migrations, 6 roles, admin user seeded | 12:22 | ✅ |
| 1.6 | Admin assigned super-admin + admin roles | 12:23 | ✅ |

## Phase 2 — Architecture Setup
| # | Task | Timestamp | Status |
|---|------|-----------|--------|
| 2.1 | `app/Services/` layer created | 12:23 | ✅ |
| 2.2 | `app/Traits/Blameable.php` — auto created_by/updated_by | 12:23 | ✅ |
| 2.3 | `app/Traits/HasModulePermissions.php` — multi-role permission merge | 12:23 | ✅ |
| 2.4 | `app/Http/Requests/` + `app/Http/Resources/` pattern set | 12:24 | ✅ |

## Phase 3 — Feature/Module System (RBAC Core)
| # | Task | Timestamp | Status |
|---|------|-----------|--------|
| 3.1 | Features migration + Feature model (hasMany modules, morphMany notes) | 12:23 | ✅ |
| 3.2 | Modules migration + Module model (belongsTo feature, hasMany permissions/tasks/notes) | 12:23 | ✅ |
| 3.3 | ModuleRolePermission pivot — 6 boolean actions (create/read/update/delete/approve/export) | 12:23 | ✅ |
| 3.4 | `FeatureModuleSeeder` — 4 features, 12 modules, super-admin permissions | 12:24 | ✅ |
| 3.5 | FeatureService + ModuleService + ModulePermissionService | 12:24 | ✅ |
| 3.6 | FeatureController + ModuleController + ModulePermissionController | 12:24 | ✅ |
| 3.7 | API routes + tested (features, modules, permissions) | 12:24 | ✅ |

## Phase 4 — Tasks System
| # | Task | Timestamp | Status |
|---|------|-----------|--------|
| 4.1 | Tasks migration + Task model (status/priority/due_date) | 12:24 | ✅ |
| 4.2 | `task_user` pivot migration (multi-assignee with assigned_at) | 12:24 | ✅ |
| 4.3 | TaskService (create/update with assignee sync, list/delete) | 12:24 | ✅ |
| 4.4 | TaskController + API routes | 12:24 | ✅ |
| 4.5 | API tested (CRUD, multi-assignee, filters) | 12:25 | ✅ |

## Phase 5 — Notes System (Polymorphic)
| # | Task | Timestamp | Status |
|---|------|-----------|--------|
| 5.1 | Notes migration — polymorphic `notable` (nullableMorphs) | 12:25 | ✅ |
| 5.2 | Note model (morphTo notable, belongsTo user) | 12:25 | ✅ |
| 5.3 | NoteService (create for feature/module/global, list, delete) | 12:25 | ✅ |
| 5.4 | NoteController + API routes (5 endpoints) | 12:25 | ✅ |
| 5.5 | Routes rewrite (fix missing notes routes) | 12:26 | ✅ |
| 5.6 | API tested (feature notes, module notes, global notes) | 12:26 | ✅ |

## Phase 6 — Activity Log (Spatie/laravel-activitylog)
| # | Task | Timestamp | Status |
|---|------|-----------|--------|
| 6.1 | `composer require spatie/laravel-activitylog` v4.12.3 | 12:37 | ✅ |
| 6.2 | Config published + 3 migrations run (`activity_log` table) | 12:37 | ✅ |
| 6.3 | LogsActivity trait applied — Feature model | 12:38 | ✅ |
| 6.4 | LogsActivity trait applied — Module model | 12:38 | ✅ |
| 6.5 | LogsActivity trait applied — ModuleRolePermission model | 12:38 | ✅ |
| 6.6 | LogsActivity trait applied — Task model | 12:38 | ✅ |
| 6.7 | LogsActivity trait applied — Note model | 12:38 | ✅ |
| 6.8 | ActivityLogController + ActivityLogResource created | 12:38 | ✅ |
| 6.9 | Routes added — `GET /api/activity-logs` (paginated, filterable) | 12:38 | ✅ |
| 6.10 | API tested — auto-logging on create, event/subject filters | 12:41 | ✅ |

## Phase 7 — Notifications
| # | Task | Timestamp | Status |
|---|------|-----------|--------|
| 7.1 | `notifications` table migration created + run | 12:41 | ✅ |
| 7.2 | TaskAssigned notification class | 12:41 | ✅ |
| 7.3 | NoteAdded notification class | 12:41 | ✅ |
| 7.4 | TaskService — notifyAssignees() wired (create + update) | 12:41 | ✅ |
| 7.5 | NoteService — notifyNoteAdded() wired (to super-admins) | 12:44 | ✅ |
| 7.6 | NotificationController (index, unread, markAsRead, markAllAsRead, destroy) | 12:41 | ✅ |
| 7.7 | Routes added — `/api/notifications*` under auth:sanctum | 12:41 | ✅ |
| 7.8 | API tested — TaskAssigned notification received | 12:42 | ✅ |
| 7.9 | Bugfix — ambiguous `id` column in whereHas query | 12:44 | ✅ |

## Phase 8 — End-to-End Playbook
| # | Task | Timestamp | Status |
|---|------|-----------|--------|
| 8.1 | E2E test script: feature → module → perms → task → note → activity → notification | 13:00 | ✅ |
| 8.1a | scripts/e2e-test.php written (26 assertions) | 13:00 | ✅ |
| 8.1b | Run: 26/26 passed, 0 failed | 13:00 | ✅ |
| 8.2 | Deployment checklist (FTP + phpMyAdmin) | 13:01 | ✅ |
| 8.2a | DEPLOY.md — 8-step guide with FTP structure, .env config, DB import | 13:01 | ✅ |
| 8.2b | .env.example — production-ready with placeholders | 13:01 | ✅ |

## Phase 9 — Auth + Permission Routing
| # | Task | Timestamp | Status |
|---|------|-----------|--------|
| 9.1 | AuthController — login (POST, returns Sanctum token), logout (POST, revoke), me (GET, user+roles+permissions) | 13:05 | ✅ |
| 9.2 | Routes restructured — tasks/notes/notifications under `auth:sanctum`, features/modules/permissions list/show under `auth:sanctum`, mutation endpoints under `super-admin` | 13:05 | ✅ |
| 9.3 | TaskController permission checks — can_read/can_create/can_update/can_delete per module | 13:05 | ✅ |
| 9.4 | TaskService — module_ids array filter + my_assignee_id orWhere (for non-super-admin assignment filtering) | 13:05 | ✅ |
| 9.5 | GET /my/tasks — tasks assigned to current user | 13:05 | ✅ |
| 9.6 | FeatureController + ModuleController — list/show filtered by user's module permissions | 13:05 | ✅ |
| 9.7 | DashboardController — centralized stats (counts, tasks by status, my tasks, notifications, recent activity) | 13:05 | ✅ |
| 9.8 | User::getAllModulePermissions() returns all module:can_* flags for current user's roles | 13:05 | ✅ |

## Phase 10 — Testing & Verification
| # | Task | Timestamp | Status |
|---|------|-----------|--------|
| 10.1 | E2E test: 26/26 passed (includes note delete route fix) | 13:31 | ✅ |
| 10.2 | test-api.php: 28/28 passed (includes dashboard + me + login tests) | 13:31 | ✅ |
| 10.3 | DatabaseSeeder updated — admin user gets super-admin role, test user uses updateOrCreate | 13:31 | ✅ |

---

## Files Created/Modified

### Phase 6
| File | Action |
|------|--------|
| `config/activitylog.php` | published |
| `database/migrations/2026_05_23_123751_create_activity_log_table.php` | published |
| `database/migrations/2026_05_23_123752_add_event_column_to_activity_log_table.php` | published |
| `database/migrations/2026_05_23_123753_add_batch_uuid_column_to_activity_log_table.php` | published |
| `app/Http/Controllers/Api/ActivityLogController.php` | **created** |
| `app/Http/Resources/ActivityLogResource.php` | **created** |
| `app/Models/Feature.php` | +LogsActivity |
| `app/Models/Module.php` | +LogsActivity |
| `app/Models/ModuleRolePermission.php` | +LogsActivity |
| `app/Models/Task.php` | +LogsActivity |
| `app/Models/Note.php` | +LogsActivity |

### Phase 7
| File | Action |
|------|--------|
| `database/migrations/2026_05_23_124105_create_notifications_table.php` | created |
| `app/Notifications/TaskAssigned.php` | **created** |
| `app/Notifications/NoteAdded.php` | **created** |
| `app/Http/Controllers/Api/NotificationController.php` | **created** |
| `app/Services/TaskService.php` | +notifyAssignees |
| `app/Services/NoteService.php` | +notifyNoteAdded |

### Phase 8
| File | Action |
|------|--------|
| `scripts/e2e-test.php` | **created** (26 assertions, full E2E flow) |
| `DEPLOY.md` | **created** (deployment guide for shared hosting) |
| `.env.example` | **added** (production config template) |

### Phase 9 — Auth + Permissions + Dashboard
| File | Action |
|------|--------|
| `app/Http/Controllers/Api/AuthController.php` | **created** (login/logout/me with Sanctum) |
| `app/Http/Controllers/Api/DashboardController.php` | **created** (centralized stats) |
| `app/Models/User.php` | **+getAllModulePermissions()** |
| `app/Services/TaskService.php` | **updated** (module_ids + my_assignee_id filters) |
| `app/Services/FeatureService.php` | **updated** (accessible_module_ids filter) |
| `app/Http/Controllers/Api/FeatureController.php` | **updated** (permission-aware index) |
| `routes/api.php` | **restructured** (3-tier: public/auth/super-admin) |

### Phase 10 — Tests & Seed Fixes
| File | Action |
|------|--------|
| `database/seeders/DatabaseSeeder.php` | **updated** (super-admin role assignment, updateOrCreate) |
| `scripts/test-api.php` | **updated** (me endpoint fix) |
| `routes/api.php` | **+DELETE /api/notes/{note}** |

### Swagger/OpenAPI
| File | Action |
|------|--------|
| `app/OpenApi.php` | **created** (OA\Info, OA\SecurityScheme) |
| `config/l5-swagger.php` | Sanctum auth configured |
| `app/Http/Controllers/Api/*Controller.php` (7 files) | **#[OA] attributes** added to all endpoints |
| `storage/api-docs/api-docs.json` | Generated (22 paths, 7 tags) |

**UI:** `GET /api/documentation` — interactive Swagger UI with "Try it out"
**Spec:** `GET /docs` — OpenAPI 3.0.0 JSON

---

## Key Decisions
- **Activity logs:** Spatie v4.12.3 (v5 requires PHP 8.4, downgraded to v4 for PHP 8.2 compat)
- **Log config:** `logFillable() + logOnlyDirty() + dontSubmitEmptyLogs()` — only dirty changes logged, no empty log entries
- **Causer detection:** Automatic via Auth — Spatie detects `request()->user()` as causer
- **Notifications:** Database channel only (no mail/queue for now — shared hosting constraints)
- **NoteAdded notification:** Skips the note creator, sends to all other super-admins
- **TaskAssigned notification:** Fired on create (new assignees) and update (sync-ed assignees)
- **Auth:** Dedicated AuthController instead of inline routes — login/logout/me endpoints return Sanctum token + user roles + module permissions
- **Task permissions:** Non-super-admin users limited to modules where they have `can_read`; assignees can read/update their tasks without module-level `can_read`
- **API docs:** l5-swagger v11 + swagger-php v6 with PHP 8 attributes (`#[OA\...]`); Sanctum bearer auth pre-configured
  - UI at `/api/documentation`, JSON spec at `/docs` (OpenAPI 3.0.0, 22 endpoints, 7 tags)
- **Dashboard:** Single `GET /api/dashboard` endpoint with all summary data for the authenticated user
- **Route tiers:** `public` (login), `auth:sanctum` (dashboard, tasks, notes, notifications, features/modules list/show, me, logout), `super-admin` (feature/module CRUD, permissions, activity logs)
