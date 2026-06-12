# API Reference — Tyro RBAC

**Base URL:** `http://localhost:8000/api`  
**Auth:** Sanctum Token — `Authorization: Bearer {token}` (excl. login)

---

## Authentication

### Login (public)
```
POST /login
  Body: { email, password, device_name? }
  Returns: { token: "...", user: { id, name, email, roles } }
```

### Logout
```
POST /logout
  Auth: Bearer token
  Returns: { message: "Logged out successfully" }
```

### Me
```
GET /me
  Auth: Bearer token
  Returns: { data: { id, name, email, roles: [...], permissions: { module_id: { can_create, can_read, ... } } } }
```

---

## Dashboard (authenticated)
```
GET /dashboard
  Auth: Bearer token
  Returns: {
    total_features, total_modules,
    tasks_by_status: { pending: N, in_progress: N, completed: N },
    total_tasks, my_tasks_total, my_pending_tasks,
    total_notes, my_notes,
    unread_notifications, total_notifications,
    recent_activity: [...],
    total_users? (super-admin only)
  }
```

---

## Features (authenticated for list/show, super-admin for CRUD)
### List
```
GET /features
  Params: ?is_active=1&search=term&per_page=20
  Note: Non-super-admin users see only features with accessible modules
```
### Create
```
POST /features  (super-admin)
  Body: { name, slug, description?, icon?, is_active? }
```
### Show
```
GET /features/{id}
```
### Update
```
PUT|PATCH /features/{id}  (super-admin)
```
### Delete (soft)
```
DELETE /features/{id}  (super-admin)
```

---

## Modules (authenticated for list/show, super-admin for CRUD)
### List by Feature
```
GET /features/{feature}/modules
```
### Create (under Feature)
```
POST /features/{feature}/modules  (super-admin)
```
### Show
```
GET /modules/{id}
```
### Update
```
PUT|PATCH /modules/{id}  (super-admin)
```
### Delete (soft)
```
DELETE /modules/{id}  (super-admin)
```

---

## Module Permissions (super-admin)
### List for Module
```
GET /modules/{module}/permissions
```
### Create/Update (upsert)
```
POST /modules/{module}/permissions
  Body: { role_id, can_create?, can_read?, can_update?, can_delete?, can_approve?, can_export? }
```
### Delete
```
DELETE /modules/{module}/permissions/{roleId}
```
### User's All Permissions (by admin)
```
GET /users/{user}/module-permissions
```

---

## Self-Service Permissions (authenticated)
### My All Permissions
```
GET /my/module-permissions
```
### My Permissions for Module
```
GET /modules/{module}/my-permissions
```

---

## Tasks (authenticated, permission-checked)
### List
```
GET /tasks
  Params: ?status=pending&priority=high&module_id=1&module_ids[]=1&module_ids[]=2&assigned_to=1&my_assignee_id=1&per_page=20
  Note: Non-super-admin users see only tasks from accessible modules OR tasks they're assigned to
```
### My Tasks
```
GET /my/tasks
  Returns: tasks assigned to current user
```
### Create
```
POST /tasks
  Body: { title, description?, module_id?, status?, priority?, due_date?, assignee_ids?: [1,2,3] }
  Requires: can_create on the task's module
  Note: Triggers TaskAssigned notification to each assignee
```
### Show
```
GET /tasks/{id}
  Requires: can_read on module OR be an assignee
```
### Update
```
PUT|PATCH /tasks/{id}
  Body: { title?, description?, module_id?, status?, priority?, due_date?, assignee_ids?: [...] }
  Requires: can_update on module OR be an assignee
```
### Delete (soft)
```
DELETE /tasks/{id}
  Requires: can_delete on module
```

---

## Notes (authenticated)
### Global — Create
```
POST /notes
  Body: { content }
  Note: Triggers NoteAdded notification to other super-admins
```
### Global — List
```
GET /notes
  Params: ?per_page=50
```
### Feature — Create
```
POST /features/{feature}/notes
  Body: { content }
```
### Feature — List
```
GET /features/{feature}/notes
```
### Module — Create
```
POST /modules/{module}/notes
  Body: { content }
```
### Module — List
```
GET /modules/{module}/notes
```
### Delete
```
DELETE /notes/{id}
```

---

## Activity Logs (super-admin)
### List
```
GET /activity-logs
  Params: ?subject_type=App\Models\Feature&event=created&causer_id=1&date_from=2026-01-01&date_to=2026-12-31&per_page=50
```
### Show
```
GET /activity-logs/{id}
```

---

## Notifications (authenticated)
### List
```
GET /notifications
  Params: ?per_page=20
```
### Unread
```
GET /notifications/unread
```
### Mark Read
```
POST /notifications/{id}/read
```
### Mark All Read
```
POST /notifications/read-all
```
### Delete
```
DELETE /notifications/{id}
```

---

## Response Conventions
- **Success:** `{ data: { ... }, message?: "..." }`
- **Paginated:** `{ data: [...], links: { ... }, meta: { current_page, from, last_page, per_page, to, total } }`
- **Validation Error:** 422 `{ message: "...", errors: { field: ["..."] } }`
- **Auth Error:** 401 `{ message: "Unauthenticated." }`
- **Forbidden:** 403 `{ message: "Forbidden." }`
- **Not Found:** 404 `{ message: "Not Found." }`

## Status Codes
| Code | Usage |
|------|-------|
| 200 | GET, PUT, PATCH — success |
| 201 | POST — resource created |
| 422 | Validation failure |
| 401 | Unauthenticated |
| 403 | Forbidden (role middleware or permission check) |
| 404 | Resource not found |
| 500 | Server error |

## Route Tiers
| Tier | Middleware | Routes |
|------|-----------|--------|
| Public | — | `POST /login` |
| Authenticated | `auth:sanctum` | logout, me, dashboard, tasks, notes, notifications, features/modules list/show, self-service permissions |
| Super-admin | `auth:sanctum` + `role:super-admin` | feature/module CRUD, module permissions, user permissions, activity logs |
