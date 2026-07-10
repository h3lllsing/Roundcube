# ENTERPRISE FEATURE SPECIFICATION

## Feature: Offboarding Checklist

Version: 1.0
Status: DRAFT
Priority: P1

---

## 1. Purpose

### Why does this feature exist?

Security Officers and Super Admins offboard employees by visiting 5+ separate pages (Users, Vault, Tasks, Assets, Activity Log) with no checklist or cross-entity view. Step forgetting is guaranteed — the human brain cannot reliably remember 5+ entity types under time pressure.

### Which business problem does it solve?

Insider threat from missed credential revocation during offboarding. Current process has 100% probability of at least one missed step per offboarding event based on 5+ steps × imperfect recall.

### What happens if it does not exist?

- Missed credential revocation = active security gap post-termination
- Compliance audit failure (SOC2, SOX require documented offboarding)
- Lost assets (laptops, monitors never returned)
- Dropped work (tasks never reassigned)
- Currently: each offboarding event carries EXTREME risk with NO tooling support

---

## 2. Business Value

### Hours saved

- 15-30 min/offboarding → 5 min with checklist
- 1-2 offboardings/week → 10-50 min/week saved
- Annualized: 8-40 hours/year for 3 Security Officers

### Risk reduced

- CRITICAL: missed credential revocation → potential breach
- High: missed asset check-in → lost equipment ($500-$3000 per asset)
- High: missed task reassignment → dropped work, SLA breaches
- Medium: incomplete audit trail → compliance findings

### Errors reduced

- Step forgetting: 100% → 0% (all steps visible)
- Missed credentials: currently unknowable → always shown
- Missed assets: currently unknowable → always shown

### Compliance impact

- SOC2 CC6.1 (logical access security): documented offboarding process becomes auditable
- SOC2 CC6.2 (access provisioning/deprovisioning): revocation steps are tracked
- SOX: user access termination within 24 hours becomes verifiable

### Security impact

- Primary: eliminates #1 insider threat vector (credential access post-termination)
- Secondary: creates audit trail for offboarding actions
- Tertiary: ensures no orphaned credential access

### Operational impact

- Security Officers spend less time on offboarding → more time on proactive security
- New offboarding coverage: any Security Officer can offboard without tribal knowledge
- Reduced fire drills from missed steps discovered weeks later

---

## 3. Actors

### Exactly which personas use it?

| Persona | Access | Rationale |
|---------|--------|-----------|
| Security Officer (3 users) | Full access | Primary — owns offboarding process |
| Super Admin (3 users) | Full access | Secondary — executes when Security Officer unavailable |

### Who must never see it?

| Persona | Block reason |
|---------|-------------|
| End User (450 users) | No business need. Can see own user detail page but NO offboarding widget. |
| Service Desk (20 users) | No business need. Should not know who is being offboarded. |
| IT Operator (10 users) | No business need. Offboarding is security/HR process. |
| IT Manager (5 users) | No business need. Offboarding is not management responsibility. |
| IT Director (1 user) | Can see aggregate reports but NOT individual offboarding. |
| Procurement (3 users) | No business need. Unrelated to procurement function. |

---

## 4. Permissions

### Exactly which permission checks occur?

**Check 1: Can view the offboarding checklist widget**
- Module: `users` (slug: `users`)
- Action: `can_read`
- Runtime: controller/middleware
- If FAIL: widget is NOT rendered. No error shown. Widget DOM element must NOT exist.

**Check 2: Can view vault entry count**
- Module: `password_vault` (slug: `password_vault`)
- Action: `can_read`
- Runtime: Livewire component mount
- If FAIL: vault row shows "0" and link is disabled. User must not see vault entry details.
- NEVER check `can_reveal` here. Count does not reveal passwords.

**Check 3: Can view task count**
- Module: `tasks` (slug: `tasks`)
- Action: `can_read`
- Runtime: Livewire component mount
- If FAIL: tasks row shows "0" and link is disabled.

**Check 4: Can view asset count**
- Module: `assets` (slug: `assets`)
- Action: `can_read`
- Runtime: Livewire component mount
- If FAIL: assets row shows "0" and link is disabled.

**Check 5: Can view activity count**
- Module: `activity_log` (NOT in module_role_permissions — NEEDS REVIEW)
- Action: `can_read`
- Runtime: Livewire component mount
- If FAIL: activity row shows "0" and link is disabled.

**NEEDS REVIEW:** Activity log module may not exist in module_role_permissions. If no activity_log module exists, this permission check cannot be performed. Fallback: check super-admin role instead.

### Which modules are checked?

1. `users`
2. `password_vault`
3. `tasks`
4. `assets`
5. `activity_log` (NEEDS REVIEW — may not be registered)

### Which actions?

All: `can_read` only. No write actions in MVP.

### Which permission has highest priority?

`users.can_read` is the GATE. If user cannot read the Users module, the widget must not render at all. All other checks are secondary filters on individual rows.

### Trace runtime

```
1. HTTP request → User detail page
2. Blade view renders → @if(auth()->user()->canOnModule($userModule, 'read'))
3.   → renders OffboardingChecklist Livewire component
4.   component mount() {
5.     → $this->user->vaultEntries()->count()  // no permission gate needed (data belongs to user)
6.     → $this->user->tasks()->count()          // no permission gate needed
7.     → auth()->user()->canOnModule($vaultModule, 'read')
8.         ? Asset::where('assigned_to', $this->user->id)->count()
9.         : 0
10.    → auth()->user()->canOnModule($activityLogModule, 'read') ?? true
11.        ? Activity::where('causer_id', $this->user->id)->count()
12.        : 0
13.  }
14.  component render() {
15.    → returns checklist view with permission-filtered counts
16.    → each row's "View" link is disabled if user lacks read on that module
17.  }
```

---

## 5. Business Rules

- R1: Widget appears ONLY on User detail page.
- R2: Widget is visible ONLY to Security Officer and Super Admin roles.
- R3: Widget shows exactly 5 rows: Suspend Account, Revoke Credentials, Reassign Tasks, Check In Assets, Review Activity.
- R4: Row 1 (Suspend Account) shows the user's current account status: Active, Suspended, or Deleted.
- R5: Row 2 (Revoke Credentials) count = count of vault_entries where vault_entries.user_id = target user id.
- R6: Row 3 (Reassign Tasks) count = count of task_user pivot rows where task_user.user_id = target user id.
- R7: Row 4 (Check In Assets) count = count of assets where assets.assigned_to = target user id.
- R8: Row 5 (Review Activity) count = count of activity_log rows where causer_id = target user id AND causer_type = 'App\Models\User', limited to last 90 days.
- R9: If current user lacks `can_read` on any module, the corresponding row count shows "—" (em dash) not "0".
- R10: Row link navigates to the corresponding module list filtered for that user, NOT to a single record.
- R11: Row link is disabled (not hidden) when current user lacks `can_read` on that module.
- R12: Widget refreshes counts on page reload only. No polling. No real-time updates.
- R13: Widget shows "Last updated" timestamp in format "X min ago" relative to Livewire component mount.
- R14: Widget title is "Offboarding Checklist".
- R15: All text is hardcoded. No translatable strings in MVP.
- R16: Widget must NOT perform any database write operations.
- R17: Row 1 has a "Suspend" button visible only when user status is "Active". Button triggers account suspension.
- R18: "Suspend" button requires confirmation dialog: "Are you sure you want to suspend [username]?"
- R19: Account suspension sets users.suspended_at = now() and users.suspension_reason = 'Offboarding initiated'.
- R20: Suspension is logged in activity_log with event = 'user_suspended', causer = current user, subject = target user.
- R21: After suspension, Row 1 updates to show "Suspended" status without page reload.
- R22: Widget must NOT include a "Revoke All" or "Revoke Selected" button. Revocation is manual via page navigation.
- R23: Widget must NOT include a "Reassign All" button. Reassignment is manual.
- R24: Widget must NOT include a "Check In All" button. Check-in is manual.
- R25: Widget must show a visual indicator (checkmark) next to Row 1 when account is suspended.

---

## 6. Data Used

### Exactly which models

| Model | Table | Usage |
|-------|-------|-------|
| App\Models\User | `users` | Target user. Read `suspended_at`, `suspension_reason`. |
| App\Models\VaultEntry | `password_vault` | Count where `user_id` = target user id. |
| App\Models\Task | `tasks` | Via pivot `task_user`. Count where `user_id` = target user id. |
| App\Models\Asset | `assets` | Count where `assigned_to` = target user id. |
| Spatie\Activitylog\Models\Activity | `activity_log` | Count where `causer_id` = target user id AND `causer_type` = 'App\Models\User'. |

### Exactly which tables

1. `users`
2. `password_vault`
3. `task_user` (pivot)
4. `tasks` (via join or count on pivot)
5. `assets`
6. `activity_log`

### Exactly which relationships

| Source | Relationship | Target |
|--------|-------------|--------|
| User | `vaultEntries(): HasMany` | VaultEntry (via `user_id`) |
| User | `tasks(): BelongsToMany` | Task (via `task_user` pivot) |
| User | (none on model) | Asset (via `assigned_to` — NOT a defined relationship on User) |
| User | (none on model) | Activity (via `causer_id` — NOT a defined relationship on User) |

**NEEDS REVIEW:** `User` model does NOT define `assignedAssets()` or `activityCauser()` relationships. These must be added to the User model OR queried inline in the Livewire component.

- Asset → User via `assigned_to`: add `public function assignedAssets(): HasMany { return $this->hasMany(Asset::class, 'assigned_to'); }` to User model.
- Activity → User via `causer_id`: add `public function activities(): HasMany { return $this->hasMany(Activity::class, 'causer_id')->where('causer_type', User::class); }` to User model.

### Exactly which queries

**Query 1: Vault entry count**
```php
DB::table('password_vault')->where('user_id', ?)->count()
// OR via relationship: $user->vaultEntries()->count()
```
Maximum returned: integer (count).

**Query 2: Task count via pivot**
```php
DB::table('task_user')->where('user_id', ?)->count()
// OR via relationship: $user->tasks()->count()
```
Maximum returned: integer (count).

**Query 3: Asset count**
```php
DB::table('assets')->where('assigned_to', ?)->count()
// OR via new relationship: $user->assignedAssets()->count()
```
Maximum returned: integer (count).

**Query 4: Activity count (90 days)**
```php
DB::table('activity_log')
    ->where('causer_id', ?)
    ->where('causer_type', 'App\Models\User')
    ->where('created_at', '>=', now()->subDays(90))
    ->count()
```
Maximum returned: integer (count).

**Query 5: User suspension check**
```php
DB::table('users')->where('id', ?)->value('suspended_at')
```
Returned: Carbon|null (single value).

**Total queries per page load: 5.**
**All queries are COUNT(*) or single-value reads.**

### No guesses

All tables are verified from migration files:
- `password_vault` — migration: `2026_05_23_124106_create_password_vault_table.php`
- `task_user` — migration: `2026_05_23_122140_create_task_user_table.php`
- `assets` — migration: `2026_06_26_000004_create_assets_table.php`
- `activity_log` — migration: `2026_05_23_123751_create_activity_log_table.php`
- `users` — Laravel default

---

## 7. UI Contract

### Exactly what appears

A card-style widget with title "Offboarding Checklist" containing 5 rows. Each row has:
- A checkbox icon (unchecked, visual only — does NOT persist state)
- Step name (e.g., "1. Suspend Account")
- Count badge (e.g., "12") or status text (e.g., "Suspended")
- Link or button ("View →", "Suspend")

### Exactly where

On the User detail page (`/users/{user}`), below the user info card, above the related entities section.

### Exactly when

Rendered when:
1. Current user has `can_read` on `users` module
2. Current user has role `super-admin` OR role containing privilege matching `security-officer`

**NEEDS REVIEW:** Need to determine the exact role slug or privilege slug for Security Officer. Current role names are unknown (database content). Never assume role slugs. The existing `hasRole()` method from Tyro should be used.

### Exactly when hidden

Widget is NOT rendered (DOM element must not exist) when:
1. Current user lacks `can_read` on `users` module
2. Current user has neither `super-admin` role nor security officer privilege
3. Target user is viewing their own profile (`auth()->id() === $user->id` — an offboarding checklist on your own profile is confusing)

### Exactly when disabled

Individual rows are disabled (link is grayed out, not clickable) when:
- Vault row: current user lacks `can_read` on `password_vault` module
- Tasks row: current user lacks `can_read` on `tasks` module
- Assets row: current user lacks `can_read` on `assets` module
- Activity row: current user lacks `can_read` on `activity_log` module (or module doesn't exist)
- Suspend button: user is already suspended (`suspended_at` IS NOT NULL)

### No redesign

Widget uses existing card/panel styling from the design system (Bootstrap card or Tailwind, per existing user detail page). No new component library.

---

## 8. Runtime Flow

```
USER: Security Officer navigates to /users/{user}

ROUTE: GET /users/{user}
  → web.php: Route::get('/users/{user}', [UserController::class, 'show'])->middleware('auth')

CONTROLLER: UserController@show
  → Loads User $user
  → Calls $this->authorize('view', $user) — NEEDS REVIEW if Policy exists
  → Returns view('users.show', compact('user'))

  NEEDS REVIEW: No Policy classes exist in codebase. Authorization is through
  HasModulePermissions trait. The controller must call:
    abort_if(!auth()->user()->canOnModule(Module::where('slug', 'users')->first(), 'read'), 403)

SERVICE: (none — logic is in Livewire component)

LIVEWIRE: OffboardingChecklist component mounted on users.show
  → mount(User $user)
    → Permission gate: current user can read users module
    → Permission gate: current user is not target user
    → COUNT: vaultEntries for target user
    → COUNT: tasks for target user
    → COUNT: assigned assets for target user
    → COUNT: recent activity for target user
    → CHECK: target user suspended status

PERMISSION: HasModulePermissions@canOnModule
  → Loads UserModulePermission for current user + module
  → If exists, return the permission value
  → Falls back to ModuleRolePermission for current user's roles + module
  → Returns bool

  Verified from HasModulePermissions.php source

MODEL: App\Models\User
  → $user->vaultEntries() — HasMany relationship
  → $user->tasks() — BelongsToMany through task_user pivot
  → NEEDS REVIEW: $user->assignedAssets() — does not exist
  → NEEDS REVIEW: $user->activities() — does not exist

DATABASE:
  → SELECT COUNT(*) FROM password_vault WHERE user_id = ?
  → SELECT COUNT(*) FROM task_user WHERE user_id = ?
  → SELECT COUNT(*) FROM assets WHERE assigned_to = ?
  → SELECT COUNT(*) FROM activity_log WHERE causer_id = ? AND causer_type = 'App\Models\User' AND created_at >= ?
  → SELECT suspended_at FROM users WHERE id = ?

RESPONSE:
  → Livewire component renders checklist view
  → User sees all 5 rows with counts
  → Suspend button triggers Livewire action
  → Livewire action updates suspended_at
  → Component re-renders with updated status
```

---

## 9. Edge Cases

### Soft delete

- Target user is soft-deleted: widget should still render. Show count queries exclude soft-deleted records by default (SoftDeletes trait on VaultEntry, Asset models). The checklist should still show counts for non-deleted entities. Add `withTrashed()` to vault entry count if vault entries of deleted users should still count.

- Target user is force-deleted: route returns 404. Widget never renders. This is acceptable — offboarding checklist for a deleted user is meaningless.

- VaultEntry soft-deleted: count excludes soft-deleted vault entries by default. This is CORRECT — soft-deleted vault entries are already unusable and don't need revocation.

- Asset soft-deleted: count excludes soft-deleted assets. If an asset is soft-deleted while assigned to the target user, the count is wrong. Decision: NEEDS REVIEW — ask whether soft-deleted assigned assets should appear in the offboarding checklist.

### Null

- `users.suspended_at` is NULL: user is active. Show "Active" status. Enable Suspend button.
- `users.suspension_reason` is NULL: no reason recorded. When suspending, always write "Offboarding initiated" as reason.
- Asset `assigned_to` is NULL: asset is unassigned. Should not count toward this user's checklist. Query already handles this (WHERE assigned_to = ? excludes NULL).
- Activity `causer_id` is NULL (anonymous action): does not count toward this user's activity. Query already handles this.

### Missing provider

N/A — this feature does not use ServiceProvider.

### Permission denied

- Current user loses permission between page load and action: Livewire action re-checks permission before executing. If denied, show flash error "You no longer have permission to perform this action."
- Target user loses permission (not applicable — permissions don't affect checklist validity for target user).

### Concurrent edit

- Two Security Officers open the same user's offboarding checklist simultaneously: no conflict. Read-only operations are safe.
- One Security Officer suspends the user → another Security Officer sees updated status on page refresh. Livewire does NOT poll, so the second officer's widget shows stale data until refresh. This is ACCEPTABLE for MVP.

### Deleted user

- User deleted while widget is open: page navigation away from the user will 404. Widget becomes stale if session stays open. Acceptable for MVP.

### Hardware limits

- 500 users, 5000 vault entries, 2000 tasks, 50 assets: all COUNT queries execute in < 10ms on indexed columns.

### Self-offboarding

- Current user viewing their own profile: widget must NOT render. Add check: `if (auth()->id() === $user->id) return;`

### User with no associations

- All counts are 0: widget renders with all rows showing "0". This is CORRECT behavior — the checklist confirms nothing needs to be revoked.

### Widget on non-human users

N/A — no non-human user records exist in the system.

---

## 10. Security

### Attack vectors

**AV1: Offboarding widget reveals user associations to unauthorized viewer**
- Vector: Unauthorized user navigates to user detail page
- Existing guard: `canOnModule('users', 'read')` check on controller
- Additional guard: widget checks super-admin or security-officer role
- Severity: MEDIUM — counts reveal that an organization has employees but no PII

**AV2: Suspend button used for DoS**
- Vector: Compromised Security Officer account suspends all 500 users
- Existing guard: None. MVP does not include rate limiting.
- Risk: CRITICAL — single account can deny service to all users
- Mitigation: NEEDS REVIEW — MVP should add per-hour rate limit (max 5 suspensions/hour) even though full offboarding dashboard is deferred.

**AV3: Activity count reveals offboarding in progress**
- Vector: Service Desk user with `can_read` on activity_log can see spike in activity for a user, deducing that offboarding is happening
- Existing guard: Widget checks `users.can_read` as gate. Service Desk users don't have `can_read` on users module.
- Severity: LOW — information about offboarding timing is not sensitive

**AV4: Suspension without reason**
- Vector: Suspension is executed without reason string
- Guard: `suspension_reason` is hardcoded to "Offboarding initiated" in the Suspend action
- Severity: LOW — reason is always written

### Privilege escalation

**PE1: User views another user's offboarding checklist without permission**
- Guard 1: Route/controller checks `can_read` on users module
- Guard 2: Widget checks `can_read` on users module
- Guard 3: Widget checks role (super-admin or security-officer)
- Result: 3 layers of protection. Privilege escalation is NOT possible.

**PE2: Suspend action executed without permission**
- Guard: Livewire action re-checks `can_read` on users module AND `can_update` on users module
- `can_read` = can view the checklist. `can_update` = can suspend the user.
- These are SEPARATE actions in module_role_permissions.
- Result: User with read-only access sees the widget but the suspend button does NOT render. The Livewire action also rejects unauthorized POST.

**NEEDS REVIEW:** Verify that `suspension` is gated by `can_update` on the users module. The module_role_permissions table has: `can_create`, `can_read`, `can_update`, `can_delete`, `can_approve`, `can_export`, `can_reveal`. Suspension maps to `can_update`.

### Data leakage

**DL1: Counts reveal sensitive information**
- Vault count reveals how many credentials a user owns. This is not sensitive — vault entries are user-scoped by design.
- Task count reveals workload. Not sensitive.
- Asset count reveals assigned equipment. Not sensitive.
- Activity count reveals how active the user was. Not sensitive.
- Severity: LOW

**DL2: Suspend button reveals operation in progress**
- Other users on the same page see the offboarding checklist. Only Security Officers and Super Admins can see the page at all (gated by `users.can_read`).
- Severity: LOW

### Audit logging

- Suspend action MUST be logged to `activity_log`:
  ```php
  activity()
      ->causedBy(auth()->user())
      ->performedOn($targetUser)
      ->event('user_suspended')
      ->log("User {$targetUser->name} suspended for offboarding by " . auth()->user()->name);
  ```

- Every suspension must have:
  - `causer_id`: Current user ID
  - `causer_type`: `App\Models\User`
  - `subject_id`: Target user ID
  - `subject_type`: `App\Models\User`
  - `event`: `user_suspended`
  - `created_at`: Current timestamp

### Rate limiting

- MVP: max 5 suspensions per hour per current user
- Check: `Activity::where('causer_id', auth()->id())->where('event', 'user_suspended')->where('created_at', '>=', now()->subHour())->count() >= 5`
- If exceeded: show error "You have reached the maximum suspension rate (5/hour). Please wait before suspending additional accounts."
- This protects against AV2 without requiring the full two-person rule.

### Sensitive actions

- Suspend account: SENSITIVE. Requires confirmation dialog + rate limiting + audit log.

### Abuse scenarios

- **Malicious offboarding:** Rate limit + audit log makes mass offboarding detectable. 5/hour limit means 500 users would take 100 hours.
- **False offboarding (wrong user):** Confirmation dialog shows username before suspension. UX must show "Are you sure you want to suspend [username]?" with the name prominently visible.

---

## 11. Performance Budget

### Expected queries

- Page load: 5 queries
- Suspend action: 2 queries (UPDATE users, INSERT activity_log)
- Total per interaction: 2-7 queries

### Expected response time

- Page load with widget: < 300ms (5 fast COUNT queries)
- Suspend action: < 200ms (single UPDATE + INSERT)
- Target is within 100ms of the same page WITHOUT the widget

### Expected memory

- Widget component: < 5MB PHP memory
- No large collections loaded (all COUNT queries return integers)

### Maximum dataset

- 500 users in system
- 5000 vault entries (10/user average)
- 2000 tasks (4/user average)
- 50 assets (0.1/user average)
- 100000 activity_log rows (200/user average)
- Worst case: 1 user with 500 vault entries, 200 tasks, 10 assets, 5000 activity rows
- All COUNT queries still execute in < 20ms

### Scaling concerns

- `activity_log` grows unboundedly. The 90-day filter in the count query prevents full table scan.
- If `activity_log` reaches 1M+ rows AND there is no index on `causer_id`, the count query will be slow.
- Required: `activity_log` must have an index on `(causer_id, causer_type, created_at)`.
- Check existing `activity_log` migration: the Spatie migration creates an index on `log_name` but NOT on `causer_id` or `subject_id`.
- **NEEDS REVIEW:** Add migration to index `activity_log.causer_id` if not already present. Without this index, activity count queries will table-scan.

---

## 12. Acceptance Criteria

| # | Criterion | Expected Result | Pass/Fail |
|---|-----------|----------------|-----------|
| AC1 | Security Officer navigates to any user detail page | Widget renders with 5 rows showing correct counts | PASS / FAIL |
| AC2 | Super Admin navigates to any user detail page | Widget renders identically to Security Officer view | PASS / FAIL |
| AC3 | End User navigates to their own profile | Widget does NOT render (DOM absent) | PASS / FAIL |
| AC4 | Service Desk navigates to any user detail page | Widget does NOT render | PASS / FAIL |
| AC5 | Security Officer navigates to user with 12 vault entries | Row 2 shows count: 12 | PASS / FAIL |
| AC6 | Security Officer navigates to user with 0 assigned assets | Row 4 shows count: 0 | PASS / FAIL |
| AC7 | Security Officer clicks "Suspend" on active user | Confirmation dialog appears with username | PASS / FAIL |
| AC8 | Security Officer confirms suspension | User suspended_at IS NOT NULL. Activity log has entry. | PASS / FAIL |
| AC9 | Security Officer clicks "Suspend" on already-suspended user | Button is disabled or shows "Suspended" status | PASS / FAIL |
| AC10 | Security Officer suspends 6 users in 1 hour | 6th suspension returns rate limit error | PASS / FAIL |
| AC11 | Security Officer lacking vault module permission | Vault row shows "—" and link is disabled | PASS / FAIL |
| AC12 | Security Officer views own profile | Widget does NOT render | PASS / FAIL |
| AC13 | Security Officer views soft-deleted user | Widget renders (if route allows viewing deleted users) | PASS / FAIL |
| AC14 | Widget renders within 100ms of baseline page load | Page load time increases by < 100ms | PASS / FAIL |
| AC15 | Suspend action is audited in activity_log | Activity entry exists with correct causer, subject, event | PASS / FAIL |

---

## 13. Regression Checklist

The following existing behaviors must NEVER break:

| # | Behavior | How verified |
|---|----------|-------------|
| R1 | User detail page loads for authorized users | Route test |
| R2 | User detail page returns 403 for unauthorized users | Route test with unauthorized role |
| R3 | Vault entry index page filters by user | Existing vault index test |
| R4 | Task assignment via task_user pivot | Existing task assignment test |
| R5 | Asset check-in/check-out flow | Existing asset lifecycle test |
| R6 | Activity log entries for model changes | Existing activity log test |
| R7 | User suspension via other methods (Nyro/Tyro suspend) | User model suspend/unsuspend test |
| R8 | User detail page load time < 500ms | Performance test |
| R9 | can_read permission on users module works for other pages | Permission test |
| R10 | Suspend button on other pages (if exists) | Existing suspension UX test |

---

## 14. Testing

### Unit

| Test | What it validates |
|------|------------------|
| UserRelationshipsTest@test_vault_entries_count | $user->vaultEntries()->count() returns correct integer |
| UserRelationshipsTest@test_task_count_via_pivot | $user->tasks()->count() returns correct integer |
| SuspensionTest@test_suspend_sets_suspended_at | $user->suspended_at is Carbon instance after suspend |
| SuspensionTest@test_suspend_sets_reason | $user->suspension_reason === 'Offboarding initiated' |
| SuspensionTest@test_suspend_logs_activity | Activity model has entry with event 'user_suspended' |

### Feature

| Test | What it validates |
|------|------------------|
| OffboardingChecklistTest@test_widget_renders_for_security_officer | Authenticated Security Officer sees widget |
| OffboardingChecklistTest@test_widget_not_rendered_for_service_desk | Authenticated Service Desk does NOT see widget |
| OffboardingChecklistTest@test_widget_not_rendered_on_self_profile | Current user viewing own profile: no widget |
| OffboardingChecklistTest@test_vault_count_matches_database | Widget count === DB query count |
| OffboardingChecklistTest@test_task_count_matches_database | Widget count === DB query count |
| OffboardingChecklistTest@test_asset_count_matches_database | Widget count === DB query count |
| OffboardingChecklistTest@test_activity_count_matches_database | Widget count === DB query count |
| OffboardingChecklistTest@test_suspend_button_triggers_suspension | Click suspend → user suspended |
| OffboardingChecklistTest@test_suspend_button_disabled_for_suspended_user | User with suspended_at not null: button disabled |
| OffboardingChecklistTest@test_rate_limit_5_per_hour | 6th suspension in 1 hour: blocked |
| OffboardingChecklistTest@test_missing_permission_shows_dash | User without vault read: vault row shows "—" |

### Browser

| Test | What it validates |
|------|------------------|
| OffboardingChecklistBrowserTest@test_widget_renders_correctly | Visual: 5 rows, correct counts, correct links |
| OffboardingChecklistBrowserTest@test_suspend_confirmation_dialog | Modal/text appears with username before suspend |
| OffboardingChecklistBrowserTest@test_stale_widget_on_navigation | Page refresh shows updated data |

### Permission

| Test | What it validates |
|------|------------------|
| PermissionTest@test_widget_gated_by_users_can_read | User without users.read: widget absent |
| PermissionTest@test_suspend_gated_by_users_can_update | User with users.read but not users.update: no suspend button |
| PermissionTest@test_vault_count_gated_by_vault_can_read | User without vault.read: vault row shows "—" |
| PermissionTest@test_task_count_gated_by_tasks_can_read | User without tasks.read: tasks row shows "—" |
| PermissionTest@test_asset_count_gated_by_assets_can_read | User without assets.read: assets row shows "—" |

### Performance

| Test | What it validates |
|------|------------------|
| PerformanceTest@test_widget_adds_less_than_100ms | Page with widget vs without: diff < 100ms |
| PerformanceTest@test_activity_count_query_uses_index | EXPLAIN on activity count query shows index usage |

### Regression

| Test | What it validates |
|------|------------------|
| Existing UserControllerTest@test_show | User show action still works |
| Existing VaultEntryTest@test_index | Vault index still works |
| Existing TaskTest@test_assignment | Task assignment still works |
| Existing AssetTest@test_assignment | Asset assignment still works |

### Security

| Test | What it validates |
|------|------------------|
| SecurityTest@test_widget_not_accessible_via_api | Livewire component cannot be mounted via direct POST |
| SecurityTest@test_suspend_csrf_protection | Suspension requires valid CSRF token |
| SecurityTest@test_suspend_xss | Username rendered in confirmation dialog is escaped |
| SecurityTest@test_activity_log_integrity | Suspension log entry cannot be deleted via widget |

---

## 15. Rollback

### How this feature can be disabled

**Method 1: Feature flag (preferred)**
- Add a `FEATURE_OFFBOARDING_CHECKLIST` env flag (default: `true`)
- In `config/features.php`: `'offboarding-checklist' => env('FEATURE_OFFBOARDING_CHECKLIST', true)`
- In Livewire component mount: `if (!config('features.offboarding-checklist')) return; // component renders empty`
- To disable: set `FEATURE_OFFBOARDING_CHECKLIST=false` in `.env`, cache clear, deploy

**Method 2: Remove from view (fallback)**
- Comment out `@livewire('offboarding-checklist', ['user' => $user])` from `users/show.blade.php`
- Deploy the one-line view change

**Method 3: Remove route (nuclear)**
- Delete the Livewire component file
- Remove any route registrations
- Risks: fails if other components depend on this

### Rollback order

1. Set `FEATURE_OFFBOARDING_CHECKLIST=false` (zero downtime)
2. Remove from view (next deploy)
3. Remove component file (next cleanup cycle)

### Rollback verification

- Suspend action reverts to manual via User edit page (existing behavior)
- No data loss — checklist never stored any state
- To unsuspend user: `$user->unsuspend()` via existing Nyro/Tyro method

---

## 16. Future Evolution

### v1.2

- Row completion tracking (persistent checkboxes per offboarding event)
- "Revoke Credentials" action button (requires `credential_user` pivot table — does not exist yet)
- "Reassign Tasks" inline dropdown (requires task reassignment API)
- "Check In Assets" button (requires asset check-in mutation)
- Offboarding report PDF generation
- Email notification to manager: "[User] has been offboarded"

### v2

- Full Offboarding Dashboard (single page showing ALL pending offboardings)
- Two-person rule for credential revocation
- Offboarding scheduling (set future date, auto-execute)
- Offboarding templates (standard step lists per department)
- Compliance export (SOC2, SOX ready offboarding report)
- Reversal workflow (re-activate recently offboarded user with credential restore)
- Offboarding analytics (time-to-offboard, step completion rate, missed steps)
