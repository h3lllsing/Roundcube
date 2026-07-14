# OpsPilot Final Portal-Wide Permission Model Audit (Corrected)

---

## A. Current Permission Architecture

### Permission Data Model

The system uses a **Feature → Module → Permission** hierarchy with **dual assignment** (role-level and user-level).

**8 boolean permission columns** shared across two tables:

| Column | Description |
|--------|-------------|
| `can_read` | View / Read access |
| `can_create` | Create new records |
| `can_update` | Edit existing records |
| `can_delete` | Delete (soft-delete) records |
| `can_approve` | Reserved — not currently enforced by any controller |
| `can_export` | Export data |
| `can_reveal` | Reveal/copy passwords |
| `can_import` | Import data |

### Tables

- **`module_role_permissions`** — maps a role to a module with 8 boolean flags (default false). Each row is unique `(module_id, role_id)`.
- **`user_module_permissions`** — user-level overrides per module. Same 8 columns, but **nullable** (null = no override, inherits from role).

### Permission Resolution (in `HasModulePermissions::canOnModule()`)

1. If a `UserModulePermission` exists AND its column is non-null → use that value
2. Else if ANY of the user's roles has that permission true via `ModuleRolePermission` → true
3. Else → false

**Multiple roles merge via OR** — if any role grants a permission, the user gets it.

### Super-Admin Bypass

Every controller checks `$user->hasRole('super-admin')` first:
```php
abort_unless($user->hasRole('super-admin') || ($module && $user->canOnModule($module, 'read')), 403);
```
Super-admins bypass all permission checks and see all data. There is NO centralized gate — 56+ controller files independently implement this pattern.

### Current Presets in UI (Simple Mode)

| Preset | Label | Permissions |
|--------|-------|-------------|
| 0 | No Access | All false |
| 1 | View Only | `can_read` |
| 2 | Manage | `can_read` + `can_create` + `can_update` |
| 3 | Custom | Any combination |

### Key Flaw: Delete Permission

`can_delete` is exposed as a checkbox alongside `can_read`, `can_create`, `can_update` in both Role Permission and User Permission UIs. Currently:
- Normal roles (customer, editor, user) in `RolePermissionSeeder` have `can_delete = false`
- `admin` role has `can_delete = true`
- The UI presents delete as a selectable option for normal roles/users

### Key Flaw: Reveal Permission

`can_reveal` is a separate checkbox in the "Sensitive Permissions" section. Normal roles need both `can_read` AND `can_reveal` to see passwords. This requires a separate explicit grant beyond Access.

### Key Architectural: Password Reveal Uses Vault Module

All resource password reveals (Hosting, VPS, VoIP, etc.) check `can_reveal` on the **vault module**, not on the resource's own module. The permission check flow is:
1. `can_read` on resource's own module (e.g., hostings) — to view the record
2. `can_reveal` on vault module — to reveal the password

This means that in the current architecture, granting a user "read" on hostings does NOT give them the password — they also need separate "reveal" on vault.

### Key Architectural: Import/Export Are Currently Super-Admin Only

Export (`ExportService::export()`) returns "Forbidden" for non-super-admins despite having RBAC-scoped code. Import routes are in `role:super-admin` middleware group. Both are de facto super-admin only today.

### Existing Exception Patterns

The following actions are intentionally NOT business-record deletions:

| Action | Type | Current Permission |
|--------|------|-------------------|
| Notification dismiss | Self-service (hard delete) | Owner only |
| API token revoke | Self-service (hard delete) | Owner only |
| Personal Note delete | Self-service personal content | Owner only |
| Personal Note edit | Self-service personal content | Owner only |
| Vault entry owner access | Owner bypass | Owner has read without module permission |

These exceptions are preserved in the final model.

---

## B. Final Target Permission Model

### Six-Level Control Per Module

| Level | Label | Permissions Granted |
|-------|-------|---------------------|
| 0 | No Access | None |
| 1 | **Access** | `can_read` + `can_reveal` (view record, copy login/password) |
| 2 | **Manage** | `can_read` + `can_create` + `can_update` + `can_reveal` |
| 3 | **Import** | `can_read` + `can_reveal` + `can_import` (includes Access base) |
| 4 | **Export** | `can_read` + `can_reveal` + `can_export` (includes Access base) |
| 5 | **Full Access** | `can_read` + `can_create` + `can_update` + `can_reveal` + `can_import` + `can_export` |

**Level 3 (Import) and Level 4 (Export) are standalone levels** — they provide their named capability plus the Access baseline (read + reveal). A user with Import on hostings can view hosting records and import CSV data but cannot create/edit/update.

**Delete is NEVER assignable to any level.** Only super-admin bypass enables delete.

**`can_approve` is reserved/unused** — not included in any level. Not currently enforced by any controller action.

### Role Model

Roles define **default permission packages** per module using one of 6 levels (No Access, Access, Manage, Import, Export, Full Access).

### User Model

- Users inherit all assigned role permissions (OR-merge across multiple roles)
- User-specific overrides may override inherited role levels
- User with no role can receive direct permissions via `UserModulePermission`
- **No Delete / Restore / Force Delete for any normal user or role**

### Super Admin Model

- Full unrestricted access
- Delete, Restore, Force Delete where supported
- No `ModuleRolePermission` or `UserModulePermission` rows needed

### Self-Service Exceptions

The following are preserved as user-owned actions (NOT super-admin-only):
- Notification dismissal (self-service, hard delete)
- API token revocation (self-service, hard delete)
- Personal note management (create, edit, delete own non-module notes)
- Vault entry owner access (owner can read/reveal without module permission)

---

## C. Exact Mapping of Old Fields to New Controls

### Internal Boolean Mapping

Old `can_` columns are preserved in the database but mapped to the new level-based model:

| New Level | can_read | can_create | can_update | can_import | can_export | can_reveal | can_approve | can_delete |
|-----------|----------|------------|------------|------------|------------|------------|-------------|------------|
| No Access | false | false | false | false | false | false | false | false |
| **Access** | **true** | false | false | false | false | **true** | false | false |
| **Manage** | **true** | **true** | **true** | false | false | **true** | false | false |
| **Import** | **true** | false | false | **true** | false | **true** | false | false |
| **Export** | **true** | false | false | false | **true** | **true** | false | false |
| **Full Access** | **true** | **true** | **true** | **true** | **true** | **true** | false | **false** |
| Super Admin | true | true | true | true | true | true | true | true |

**Key decisions:**
- **Access includes `can_reveal = true`** — Credential reveal is part of Access, not a separate Sensitive permission. Users with Access can view and copy passwords.
- **Import and Export each include the Access baseline** (`can_read` + `can_reveal`) plus their respective capability. They do NOT include `can_create` or `can_update`.
- **`can_delete` is NEVER set to true** for any normal role or user. Only super-admin bypass enables delete.
- **`can_approve` is NOT included in any level.** It is reserved/not currently enforced by any controller.

### Reveal Override Rule

`can_reveal` is automatically treated as `true` when `can_read` is `true` **UNLESS** the user has an explicit `can_reveal = false` in their `UserModulePermission` row. This preserves:
- The default business rule: Access = read + reveal
- The ability to explicitly deny reveal to specific users via override
- Backward compatibility with existing explicit `can_reveal = false` rows

### UI Representation

The User and Role Permission UIs should show per module:

- **No Access**
- **Access** (Read + Reveal)
- **Manage** (Access + Create + Update)
- **Import** (Access + Import)
- **Export** (Access + Export)
- **Full Access** (Manage + Import + Export)

No Delete checkbox. No Reveal checkbox (reveal is included in Access by default, can be denied via explicit override).

---

## D. Module-by-Module Action Matrix

### Infrastructure Feature

| Module | Slug | Index | Show | Create | Edit | Delete | Restore | ForceDelete | Import | Export | Reveal | Notes |
|--------|------|-------|------|--------|------|--------|---------|-------------|--------|--------|--------|-------|
| Vendors / Service Providers | `service-providers` | Access | Access | Manage | Manage | SA only | SA only | SA only | Import | Export | Access | has passwords |
| Hosting | `hostings` | Access | Access | Manage | Manage | SA only | SA only | SA only | Import | Export | Access | has passwords |
| Domains | `domains` | Access | Access | Manage | Manage | SA only | SA only | SA only | Import | Export | Access | has login |
| Domain Emails | `domain-emails` | Access | Access | Manage | Manage | SA only | SA only | SA only | Import | Export | Access | has passwords |
| VPS | `vps` | Access | Access | Manage | Manage | SA only | SA only | SA only | Import | Export | Access | has passwords |
| VoIP | `voip` | Access | Access | Manage | Manage | SA only | SA only | SA only | Import | Export | Access | has passwords |
| SaaS / Other Services | `other-services` | Access | Access | Manage | Manage | SA only | SA only | SA only | Import | Export | Access | has passwords |
| Renewals / Expiry Trackers | `expiry-trackers` | Access | Access | Manage | Manage | SA only | SA only | SA only | Import | Export | Access | has passwords |
| Hardware Assets | `assets` | Access | Access | Manage | Manage | SA only | SA only | SA only | Import | Export | Access | has passwords (AnyDesk) |
| G-Mails | `g-mails` | Access | Access | Manage | Manage | SA only | SA only | SA only | Import | Export | Access | has passwords |

### Productivity Feature

| Module | Slug | Index | Show | Create | Edit | Delete | Restore | ForceDelete | Import | Export | Reveal | Notes |
|--------|------|-------|------|--------|------|--------|---------|-------------|--------|--------|--------|-------|
| Tasks | `tasks` | Access | Access | Manage | Manage | SA only | SA only | SA only | Import | Export | — | |
| Notes (module-attached) | `notes` | Access | Access | Manage | Manage | SA only | SA only | SA only | Import | Export | — | Owner can manage personal notes |
| Notes (personal) | `notes` | — | — | Self | Self | Self | SA only | SA only | — | — | — | Owner-manageable self-service |
| Vault / Credentials | `vault` | Access | Access | Manage | Manage | SA only | SA only | SA only | Import | Export | Access | Owner bypass for own entries |
| Calendar | `calendar` | Access | Access | Manage | Manage | SA only | SA only | SA only | Import | Export | — | |
| Monitoring | `monitoring` | Access | Access | Manage | Manage | SA only | SA only | SA only | Import | Export | — | |

### Administration Feature (Route-Grouped Super-Admin Only)

| Module | Slug | Index | Show | Create | Edit | Delete | Restore | ForceDelete | Notes |
|--------|------|-------|------|--------|------|--------|---------|-------------|-------|
| Users | `users` | SA | SA | SA | SA | SA | SA | SA | Roles, override perms |
| Roles | `roles` | SA | SA | SA | SA | SA | — | — | Route-grouped SA |
| Privileges | `privileges` | SA | SA | SA | SA | SA | — | — | Route-grouped SA |
| Modules | `modules` | SA | SA | SA | SA | SA | — | — | Route-grouped SA |
| Features | `features` | SA | SA | SA | SA | SA | — | — | Route-grouped SA |
| Permissions | `module-permissions` | SA | — | — | — | — | — | — | Route-grouped SA |
| Activity Logs | `activity-logs` | SA | SA | — | — | — | — | — | Route-grouped SA |
| Login History | `login-audits` | SA | SA | — | — | SA | — | — | Route-grouped SA |
| Mail Settings | `smtp-profiles` | SA | SA | SA | SA | SA | — | — | Route-grouped SA |
| Notifications | `notifications` | Self | Self | — | — | Self | — | — | Owner self-service |
| Attachments | `attachments` | SA/module | SA/module | SA | SA | SA | SA | SA | Module-scoped |
| Webhooks | `webhooks` | SA | SA | SA | SA | SA | — | — | Route-grouped SA |
| API Tokens | `tokens` | Self | Self | Self | — | Self | — | — | Owner self-service |
| Import | `import` | SA | — | SA | — | — | — | — | Route-grouped SA |
| Export | `export` | SA | — | — | — | — | — | — | Route-grouped SA |
| Reports | `reports` | SA | SA | — | — | — | — | — | Route-grouped SA |

### Profile / Self-Service (No Module)

| Page | Access | Notes |
|------|--------|-------|
| My Profile | All authenticated users | |
| My Permissions | All authenticated users | Read-only view of own permissions |
| Dashboard | All authenticated users | |
| Help Center | All authenticated users | |

### Route-Level Access in Current Code

**Administration modules** (Users, Roles, Privileges, Features, Modules, Permissions, Activity Logs, Login History, Webhooks, SMTP Profiles, Reports, Import, Export) are route-grouped with `role:super-admin` middleware. They remain super-admin-only in this implementation phase.

**Self-service exceptions** (Notifications, API Tokens, personal Notes) use owner-level checks and remain outside the super-admin-only grouping.

---

## E. Credential Reveal Mapping

### Current State

`can_reveal` is an **explicit, separate permission** checked on the **vault module** (not on the resource's own module). It is classified as a "sensitive" permission in `config/permissions.php`.

Modules with passwords that use `can_reveal` checks:
- Hosting (`getPassword`)
- VPS (`getPassword`)
- VoIP (`getPassword`, `getExtensionPassword`)
- Domain Email (`getPassword`)
- Service Provider (`getPassword`)
- Other Service (`getPassword`)
- G-Mail (`getPassword`)
- Vault (`reveal`, `getPassword`)
- Asset (`getAnyDeskPassword`)

All password routes have `throttle:10,1` rate limiting.

**Current permission check pattern in ALL password controllers:**
```php
// Step 1: Check can_read on resource's module (to view record)
abort_unless($user->hasRole('super-admin') || ($module && $user->canOnModule($module, 'read')), 403);

// Step 2: Check can_reveal on vault module (to reveal password)
abort_unless($user->hasRole('super-admin') || ($vaultModule && $user->canOnModule($vaultModule, 'reveal')), 403);
```

**Current seeder values for `can_reveal`:**
- All 4 seeded roles (admin, customer, editor, user) have `can_reveal = false` in `RolePermissionSeeder`
- `RoleTemplateSeeder` has `can_reveal = true` for Admin template (infrastructure modules only) and IT Support template (6 modules)

**Risk of blanket migration:** ~112 role permission rows (4 roles × 28 modules) have `can_read = true, can_reveal = false`. Setting `can_reveal = 1` on all of them without review would silently grant password access to roles that currently cannot see passwords. **Do not use a blanket migration.**

### Target Model

**Access level (and above) automatically grants `can_reveal` at the CODE level:**

1. **Internal column `can_reveal` stays in the database** — no schema change needed
2. **Code-level auto-grant:** In `HasModulePermissions::canOnModule()`, when `$action === 'reveal'`, if the user has `can_read = true` on the vault module (via override or role), treat `can_reveal` as true automatically
3. **Explicit deny override:** If a `UserModulePermission` row exists with `can_reveal = false`, that explicit deny overrides the auto-grant
4. **`can_reveal` is removed from the user-facing UI** — no separate checkbox
5. **Existing `can_read = true, can_reveal = false` production rows require MANUAL REVIEW** — not blanket migration

**Implementation:**
```php
public function canOnModule(Module $module, string $action): bool
{
    if ($this->hasRole('super-admin')) return true;

    $column = 'can_'.$action;

    // Check user-level override first
    $userOverride = UserModulePermission::where('user_id', $this->id)
        ->where('module_id', $module->id)
        ->first();

    // Explicit deny always wins
    if ($userOverride && $userOverride->$column !== null) {
        // For 'reveal', explicit false beats everything
        if ($action === 'reveal' && $userOverride->$column === false) {
            return false;
        }
        if ($userOverride->$column) {
            return true;
        }
    }

    // For 'reveal', can_read auto-grants reveal (unless explicitly denied above)
    if ($action === 'reveal') {
        $readOverride = $userOverride && $userOverride->can_read !== null
            ? $userOverride->can_read
            : null;
        if ($readOverride === true) return true;
        // Check if any role grants can_read
        $hasRead = ModuleRolePermission::whereIn('role_id', $this->getRoleIds())
            ->where('module_id', $module->id)
            ->where('can_read', true)
            ->exists();
        if ($hasRead) return true;
    }

    // Standard permission check
    return ModuleRolePermission::whereIn('role_id', $this->getRoleIds())
        ->where('module_id', $module->id)
        ->where($column, true)
        ->exists();
}
```

**Security safeguards preserved:**
- Rate limiting: 10 password reveals per minute
- Activity logging: ALL password reveals are audited
- Vault module: reveal permission is still gated on the vault module specifically
- Explicit deny: `UserModulePermission` with `can_reveal = false` overrides auto-grant

**Production data review required (not automated migration):**
Before enabling this code change, run read-only queries to identify:
```sql
-- All role permission rows with can_read=true, can_reveal=false
SELECT r.name, m.slug FROM module_role_permissions mrp
JOIN roles r ON r.id = mrp.role_id
JOIN modules m ON m.id = mrp.module_id
WHERE mrp.can_read = 1 AND mrp.can_reveal = 0;

-- All user override rows with can_read=true, can_reveal=false
SELECT u.name, u.email, m.slug FROM user_module_permissions ump
JOIN users u ON u.id = ump.user_id
JOIN modules m ON m.id = ump.module_id
WHERE ump.can_read = 1 AND ump.can_reveal = 0;
```

Each row found must be evaluated: should this role/user have password access? If yes, set `can_reveal = 1`. If no, set `can_read = 0` (they should not have Access without reveal in the new model) OR document as an explicit-denial exception.

### Vault Special Case (Owner Bypass)

`VaultEntry` has owner-specific logic in `HasModulePermissions::canAccessVault()`:
```php
if ($vault->user_id === $this->id) return true;  // Owner bypass
```

However, the `reveal()` and `getPassword()` actions check `canOnModule($entry->module, 'reveal')` — they do NOT have an owner bypass. This means a vault entry owner without `can_reveal` on the entry's module cannot reveal their own password. **This should be preserved as-is** (the owner bypass is for read access, not reveal access).

The reveal auto-grant (Access = reveal) does not widen vault access because the reveal check is on the vault entry's own module, and the owner must also have `can_read` on that module. The owner bypass in `canAccessVault` is for viewing the entry list, not for revealing passwords.

---

## F. Delete / Restore / Force-Delete Violations

### Business Rules

- **Delete/Restore/Force Delete for shared business records** → Super Admin only
- **Notification dismissal** → Owner self-service (exception, hard delete)
- **API token revocation** → Owner self-service (exception, hard delete)
- **Personal note deletion** → Owner self-service (exception, soft delete)
- **Module-attached/shared business note deletion** → Super Admin only

### Controllers Where Destroy IS Gated by `can_delete` (VIOLATIONS)

These controllers currently allow `can_delete` to grant delete permission to non-super-admin roles/users for shared business records:

| Controller | File | Line | Permission Check | Risk |
|-----------|------|------|-----------------|------|
| `BaseResourceController::destroy()` | `Web/BaseResourceController.php` | 182 | `hasRole('super-admin')` OR `canOnModule($module, 'delete')` | **VIOLATION** — admin role has `can_delete=true` |
| `AssetController::destroy()` | `Web/AssetController.php` | 174 | same pattern | **VIOLATION** |
| `DomainEmailController::destroy()` | `Web/DomainEmailController.php` | 150 | same pattern | **VIOLATION** |
| `ExpiryTrackerController::destroy()` | `Web/ExpiryTrackerController.php` | 173 | same pattern | **VIOLATION** |
| `NoteController::destroy()` | `Web/NoteController.php` | 98 | `authorizeNoteAccess($note, 'delete')` — module-delete path | **VIOLATION** (module-attached note path) |
| `TaskController::destroy()` | `Web/TaskController.php` | 260 | same pattern | **VIOLATION** |
| `VaultController::destroy()` | `Web/VaultController.php` | 174 | same pattern | **VIOLATION** |
| `ServiceProviderController::destroy()` | `Web/ServiceProviderController.php` | 126 | overridden, same pattern + dependent check | **VIOLATION** |
| API: `AssetController::destroy()` | `Api/AssetController.php` | 71 | same pattern | **VIOLATION** |
| API: `DomainController::destroy()` | `Api/DomainController.php` | 174 | same pattern | **VIOLATION** |
| API: `DomainEmailController::destroy()` | `Api/DomainEmailController.php` | 181 | same pattern | **VIOLATION** |
| API: `ExpiryTrackerController::destroy()` | `Api/ExpiryTrackerController.php` | 191 | same pattern | **VIOLATION** |
| API: `GMailController::destroy()` | `Api/GMailController.php` | 109 | same pattern | **VIOLATION** |
| API: `HostingController::destroy()` | `Api/HostingController.php` | 188 | same pattern | **VIOLATION** |
| API: `OtherServiceController::destroy()` | `Api/OtherServiceController.php` | 188 | same pattern | **VIOLATION** |
| API: `ServiceProviderController::destroy()` | `Api/ServiceProviderController.php` | 182 | same pattern | **VIOLATION** |
| API: `NoteController::destroy()` | `Api/NoteController.php` | 287 | complex check — module-delete path | **VIOLATION** |
| API: `TaskController::destroy()` | `Api/TaskController.php` | 283 | same pattern | **VIOLATION** |
| API: `VaultController::destroy()` | `Api/VaultController.php` | 239 | `isVaultOwner()` AND `canOnModule($module, 'delete')` | **VIOLATION** |
| API: `VoipController::destroy()` | `Api/VoipController.php` | 211 | same pattern | **VIOLATION** |
| API: `VpsController::destroy()` | `Api/VpsController.php` | 199 | same pattern | **VIOLATION** |

### Controllers That Are NOT Violations (Already SA-Only or Exception)

These already require `hasRole('super-admin')` for `destroy()`:
- `FeatureController` (line 79) — SA only ✓
- `ModuleController` (line 96) — SA only ✓
- `RoleController` (line 81) — SA only ✓
- `PrivilegeController` (line 81) — SA only ✓
- `UserController` (line 529) — SA only ✓
- `LoginAuditController` (line 37) — SA only ✓
- `WebhookController` (line 134) — SA only ✓
- `SmtpProfileController` (line 115) — SA only ✓
- `AttachmentController` (line 104) — SA only ✓
- `ModulePermissionController` (line 87) — SA only ✓

**Exceptions (self-service, NOT violations):**
- `NotificationController` (line 49) — owner dismisses own notification, hard delete ✓
- `TokenController` (line 52) — owner revokes own API token, hard delete ✓

### NoteController: Must Distinguish Personal vs Module Notes

**Current `destroy()`:** `authorizeNoteAccess($note, 'delete')` allows:
- Module-attached note: requires `can_delete` on the module → **VIOLATION: change to SA-only**
- Personal note: owner-check → **NOT a violation; preserve as self-service**

**Current `restore()`:** `authorizeNoteAccess($note, 'delete')` — change to `hasRole('super-admin')`

**Current `forceDelete()`:** `authorizeNoteAccess($note, 'delete')` — change to `hasRole('super-admin')`

**Revised `authorizeNoteAccess()`:**
```php
private function authorizeNoteAccess(Note $note, string $action): void
{
    if (Auth::user()->hasRole('super-admin')) { return; }

    // Module-attached notes: require module permission (read/view/edit)
    // Delete is NOT module-permission-based — only SA for business records
    if ($note->notable instanceof Module) {
        abort_unless(in_array($action, ['read', 'update']) && Auth::user()->canOnModule($note->notable, $action), 403);
        abort(403);  // destroy/restore/forceDelete require SA
    }

    // Personal notes: owner only
    abort_unless($note->user_id === Auth::id(), 403);
}
```

### Bulk Delete Violations

`BaseResourceController::index()` line 119:
```php
$canBulkDelete = $isSuperAdmin || ($module && $user->canOnModule($module, 'delete'));
```
**VIOLATION** — change to `$canBulkDelete = $isSuperAdmin`.

`BulkActionService::execute()` — change bulk delete/restore/force-delete to require super-admin.
**Bulk `update-status` should REMAIN permission-based** (requires `can_update`).

### Restore / ForceDelete — Already Super-Admin Only

All `restore()` and `forceDelete()` methods already check `hasRole('super-admin')`:
- `BaseResourceController::restore()` — SA only ✓
- `BaseResourceController::forceDelete()` — SA only ✓
- All custom restore/forceDelete methods (User, Vault, etc.) — SA only ✓

**Exception:** `NoteController::forceDelete()` — change to SA only.

---

## G. Soft-Delete Coverage

### Models Using SoftDeletes

**All 30 models** use `Illuminate\Database\Eloquent\SoftDeletes`. Every table has a `deleted_at` column.

| # | Model | Table | SoftDeletes | Web delete route | restore | forceDelete |
|---|-------|-------|-------------|-------------------|---------|-------------|
| 1 | User | users | Yes | UserController | Yes (SA) | Yes (SA) |
| 2 | Role | roles | Yes | RoleController | No | No |
| 3 | Privilege | privileges | Yes | PrivilegeController | No | No |
| 4 | Note | notes | Yes | NoteController | Yes (SA) | Yes (SA) |
| 5 | Task | tasks | Yes | TaskController | Yes (SA) | No |
| 6 | Attachment | attachments | Yes | AttachmentController | No | Yes (SA) |
| 7 | Hosting | hostings | Yes | BaseResourceController | Yes (SA) | Yes (SA) |
| 8 | Domain | domains | Yes | BaseResourceController | Yes (SA) | Yes (SA) |
| 9 | Vps | vps | Yes | BaseResourceController | Yes (SA) | Yes (SA) |
| 10 | Voip | voip | Yes | BaseResourceController | Yes (SA) | Yes (SA) |
| 11 | OtherService | other_services | Yes | BaseResourceController | Yes (SA) | Yes (SA) |
| 12 | ServiceProvider | service_providers | Yes | SPController (override) | Yes (SA) | Yes (SA) |
| 13 | DomainEmail | domain_emails | Yes | DomainEmailController | Yes (SA) | Yes (SA) |
| 14 | ExpiryTracker | expiry_trackers | Yes | ExpiryTrackerController | Yes (SA) | Yes (SA) |
| 15 | ExpiryTrackerNotification | expiry_tracker_notifications | Yes | (no controller) | No | No |
| 16 | Asset | assets | Yes | AssetController | Yes (SA) | Yes (SA) |
| 17 | AssetType | asset_types | Yes | (no controller) | No | No |
| 18 | AssetLocation | asset_locations | Yes | (no controller) | No | No |
| 19 | AssetCategory | asset_categories | Yes | (no controller) | No | No |
| 20 | AssetAssignment | asset_assignments | Yes | (no controller) | No | No |
| 21 | Module | modules | Yes | ModuleController | No | No |
| 22 | ModuleRolePermission | module_role_permissions | Yes | ModulePermissionController | No | No |
| 23 | Feature | features | Yes | FeatureController | No | No |
| 24 | RoleTemplate | role_templates | Yes | (no controller) | No | No |
| 25 | SmtpProfile | smtp_profiles | Yes | SmtpProfileController | No | No |
| 26 | VaultEntry | password_vault | Yes | VaultController | Yes (SA) | Yes (SA) |
| 27 | Webhook | webhooks | Yes | WebhookController | No | No |
| 28 | GMail | g_mails | Yes | BaseResourceController | Yes (SA) | Yes (SA) |
| 29 | LoginAudit | login_audits | Yes | LoginAuditController | No | No |
| 30 | UserModulePermission | user_module_permissions | Yes | (via service) | No | No |

### Activity Log Preservation

All models using `LogsActivity` trait preserve history when soft-deleted. Activity logs are stored in the separate `activity_log` table (Spatie package) with polymorphic `subject_id`/`subject_type`. Force-deleting a record does NOT cascade to activity logs — the log entries remain with their original subject identifiers.

### Recommended Super-Admin-Only Restore Workflow

1. **All restore/forceDelete operations require super-admin** — already true except NoteController
2. **Personal notes exception:** Personal notes can be soft-deleted by owner (self-service), restore/forceDelete remain SA only
3. **Trash/deleted views** — optional enhancement, low priority
4. **API restore/forceDelete endpoints** — currently missing from API for all modules; add in a future phase

---

## H. Role Permission UI Design (Final)

### Current Role Permission Page (`module-permissions.index`)

- Two modes: simple (focused role, dropdown list) and advanced (matrix table)
- Each module has 8 checkboxes: Create, Read, Update, Delete, Approve, Export, Reveal, Import
- Delete is visible and selectable

### Final Role Permission Page Design

Per module, show a **Level selector** (radio or select) with these mutually exclusive options:

```
[ ] No Access          → all permissions false
[ ] Access             → Read + Reveal
[ ] Manage             → Read + Create + Update + Reveal
[ ] Import             → Read + Reveal + Import
[ ] Export             → Read + Reveal + Export
[ ] Full Access        → Read + Create + Update + Reveal + Import + Export
```

- **No Delete checkbox** anywhere
- **No Reveal checkbox** (included in Access by default, explicit deny via override only)
- **No Approve checkbox** (reserved, not currently used by any controller)
- **Matrix view** shows levels as labels (No Access, Access, Manage, Import, Export, Full Access) — not as CRUD letter codes
- Import and Export are NOT buried inside Full Access — they are standalone selectable levels

### Level-to-Columns Mapping

```php
public const LEVELS = [
    'no_access'   => [],
    'access'      => ['can_read' => true, 'can_reveal' => true],
    'manage'      => ['can_read' => true, 'can_create' => true, 'can_update' => true, 'can_reveal' => true],
    'import'      => ['can_read' => true, 'can_reveal' => true, 'can_import' => true],
    'export'      => ['can_read' => true, 'can_reveal' => true, 'can_export' => true],
    'full_access' => ['can_read' => true, 'can_create' => true, 'can_update' => true,
                       'can_reveal' => true, 'can_import' => true, 'can_export' => true],
];
```

`can_delete` and `can_approve` are NEVER set by any level. They remain `false` for normal roles/users.

---

## I. User Permission UI Design (Final)

### Key Constraint: Preserve Nullable Mixed Override Semantics

The existing `UserModulePermission` model supports mixed null/true/false values:
- null = inherit from role (no override for that specific capability)
- true = explicit allow (overrides role's false to true)
- false = explicit deny (overrides role's true to false)

**A single level selector CANNOT represent mixed overrides.** For example: a user who needs Access-level (read+reveal) on hostings but explicitly wants Export OFF cannot be represented by any single level. The UI must support the "Custom" path for mixed overrides while hiding Delete.

### Final User Permission Page Design

Per module, show:

```
Level selector (radio):
[ ] Inherit from Role   → no UserModulePermission row (default)
[ ] No Access           → all false
[ ] Access              → Read + Reveal
[ ] Manage              → Read + Create + Update + Reveal
[ ] Import              → Read + Reveal + Import
[ ] Export              → Read + Reveal + Export
[ ] Full Access         → Read + Create + Update + Reveal + Import + Export
[ ] Custom              → per-capability checkboxes (for mixed overrides)
```

**Custom mode** shows these per-capability toggles:
- [ ] Read (can_read) — **always enabled in Custom**
- [ ] Create (can_create)
- [ ] Update (can_update)
- [ ] Import (can_import)
- [ ] Export (can_export)
- Reveal is NOT a separate toggle — it's automatically true when Read is true (unless explicit deny)

**No Delete checkbox** in any mode.
**No Approve checkbox** in any mode.
**No Reveal checkbox** — it's part of Access by default; explicit deny via Custom mode is read-only data with `can_read=true, can_reveal=false`.

**Inherit behavior:**
- Default for all modules with no existing override
- Selecting Inherit when a `UserModulePermission` row exists → deletes the row (full inherit)
- The current per-capability save flow (sending only changed modules, deleting rows for omitted modules) is preserved

**Import and Export as standalone levels:** Yes, they include Access baseline plus their respective capability.

**User with no role:**
- All modules show "No Access" as baseline
- Override selection works identically
- User receives direct `UserModulePermission` rows

**Role-change warning preserved:** "Reset to new role defaults" vs "Keep existing overrides"

### Save Backend Logic

The existing `UserPermissionService::saveUserModulePermissions()` handles mixed semantics correctly:
- Upserts based on `[user_id, module_id]` composite key
- Stores `null` for columns that should inherit
- Deletes rows where all columns are null
- Deletes rows for modules not in incoming payload

**Changes needed:**
1. Add level-to-columns mapping for presets 0-5
2. For preset 6 (Custom), use per-capability checkboxes (not the old 8-flat-mode with delete/reveal/approve)
3. Force `can_delete = false` even if Custom mode tries to set it
4. Force `can_reveal` logic: if `can_read = true`, set `can_reveal = true` unless explicitly unchecked in Custom
5. Remove `can_approve` from the editable set

---

## J. Backend Enforcement Changes Required

### 1. Fix `destroy()` Permission Checks (21 controllers)

Change from:
```php
abort_unless($user->hasRole('super-admin') || ($module && $user->canOnModule($module, 'delete')), 403);
```
To:
```php
abort_unless($user->hasRole('super-admin'), 403);
```

**Affected files (shared business records):**
- `BaseResourceController::destroy()` — fixes 6 children: Domain, GMail, Hosting, Vps, Voip, OtherService
- `ServiceProviderController::destroy()` — **override, must fix separately**
- `AssetController::destroy()`
- `DomainEmailController::destroy()`
- `ExpiryTrackerController::destroy()`
- `TaskController::destroy()`
- `VaultController::destroy()`
- `NoteController::destroy()` — only the module-attached note path; personal note path remains owner-only
- API: `AssetController::destroy()`
- API: `DomainController::destroy()`
- API: `DomainEmailController::destroy()`
- API: `ExpiryTrackerController::destroy()`
- API: `GMailController::destroy()`
- API: `HostingController::destroy()`
- API: `OtherServiceController::destroy()`
- API: `ServiceProviderController::destroy()`
- API: `TaskController::destroy()`
- API: `VaultController::destroy()`
- API: `VoipController::destroy()`
- API: `VpsController::destroy()`
- API: `NoteController::destroy()` — module-attached path only

**NOT changed (exceptions):**
- `NotificationController::destroy()` — owner self-service, hard delete
- `TokenController::destroy()` — owner self-service, hard delete
- `NoteController::destroy()` — personal note path, owner self-service

### 2. Fix Bulk Actions

**`BaseResourceController::index()`** — change to:
```php
$canBulkDelete = $isSuperAdmin;
$canBulkRestore = $isSuperAdmin;
$canBulkForceDelete = $isSuperAdmin;
```

**`BulkActionService::execute()`** — change delete/restore/force-delete to require super-admin.
**`BulkActionService::execute()`** — keep `update-status` permission-based (requires `can_update`).
**`BulkActionController::action()`** — add super-admin check for `delete`, `restore`, `force-delete`.

### 3. Fix NoteController

**`NoteController::destroy()`:**
- Module-attached note: require super-admin only
- Personal note: preserve owner-only check

**`NoteController::restore()`:** Change to `hasRole('super-admin')` only.

**`NoteController::forceDelete()`:** Change to `hasRole('super-admin')` only.

**`authorizeNoteAccess()`:** See revised logic in Section F.

### 4. Backend Enforce Access Includes Reveal

In `HasModulePermissions::canOnModule()`, add reveal auto-grant logic:

```php
public function canOnModule(Module $module, string $action): bool
{
    if ($this->hasRole('super-admin')) return true;

    $column = 'can_'.$action;

    // Check user-level override
    $userOverride = UserModulePermission::where('user_id', $this->id)
        ->where('module_id', $module->id)
        ->first();

    if ($userOverride && $userOverride->$column !== null) {
        if ($action === 'reveal' && $userOverride->$column === false) {
            return false; // explicit deny beats everything
        }
        if ($userOverride->$column) {
            return true;
        }
    }

    // Auto-grant reveal if user has read (unless explicitly denied above)
    if ($action === 'reveal') {
        $readOverride = $userOverride && $userOverride->can_read !== null
            ? $userOverride->can_read : null;
        if ($readOverride === true) return true;
        $hasRoleRead = ModuleRolePermission::whereIn('role_id', $this->getRoleIds())
            ->where('module_id', $module->id)
            ->where('can_read', true)
            ->exists();
        if ($hasRoleRead) return true;
    }

    // Standard role-based check
    return ModuleRolePermission::whereIn('role_id', $this->getRoleIds())
        ->where('module_id', $module->id)
        ->where($column, true)
        ->exists();
}
```

### 5. Prevent `can_delete` Assignment for Normal Roles/Users

**In `ModulePermissionService::setForRole()`:** If the role is NOT super-admin, force `can_delete = false` regardless of input.

**In `UserPermissionService::saveUserModulePermissions()`:** If the user is NOT super-admin, force `can_delete = false` regardless of input.

### 6. Keep `can_delete` in Config for Now

Do NOT remove `can_delete` from `config('permissions.keys')` in this phase. The column must remain accessible for:
- Super-admin bypass (works through `hasRole()` check, not column check)
- Backward compatibility with existing data
- Potential API clients that reference the key

The column is functionally removed from the permission model by:
- Preventing assignment at the service layer (step 5 above)
- Removing it from the UI (Sections H, I)
- Changing `destroy()` checks to super-admin only (step 1 above)

### 7. UI Backend: Level-to-Columns Mapping

Add a shared helper (service or enum) for mapping levels to column arrays (see Section H for the mapping). Both `ModulePermissionService` and `UserPermissionService` use this helper when:
- Loading: convert DB columns back to level display
- Saving: convert selected level to column assignments
- Custom mode: save individual columns as-is (except forced `can_delete = false`)

---

## K. Database / Schema Impact

### No Schema Changes Required

The current 8-column schema in `module_role_permissions` and `user_module_permissions` is fully compatible with the new model:

| Old Column | Status After Change |
|------------|-------------------|
| `can_read` | Used for Access, Manage, Import, Export, Full Access |
| `can_create` | Used for Manage, Full Access |
| `can_update` | Used for Manage, Full Access |
| `can_delete` | Column preserved but NEVER set to true for normal roles/users. Only super-admin bypass uses it. Kept in `config('permissions.keys')` for compatibility. |
| `can_approve` | Column preserved — reserved/unused. Not part of any level. Not enforced by any controller. |
| `can_export` | Used for Export, Full Access |
| `can_reveal` | Column preserved — auto-granted at code level when `can_read` is true. Removed from UI. |
| `can_import` | Used for Import, Full Access |

### Data Cleanup Required

1. **Set `can_delete = 0` for all non-super-admin role permissions.** This is mandatory and safe: the admin role currently has `can_delete = true` in `RolePermissionSeeder`. This must be removed to prevent any non-super-admin user from having `can_delete`. Migration: `UPDATE module_role_permissions SET can_delete = 0 WHERE role_id NOT IN (SELECT id FROM roles WHERE slug = 'super-admin')`.

2. **Set `can_delete = 0` for all user override rows.** Migration: `UPDATE user_module_permissions SET can_delete = 0 WHERE can_delete = 1 AND user_id NOT IN (SELECT user_id FROM tyro_role_user JOIN roles ON roles.id = tyro_role_user.role_id WHERE roles.slug = 'super-admin')`.

3. **No blanket `can_reveal` migration.** Existing `can_read = true, can_reveal = false` rows must be reviewed manually. A production read-only query must enumerate these rows; each must be evaluated case-by-case. See Section E for required queries.

4. **Update `RolePermissionSeeder`** to reflect new defaults: remove `can_delete` from all non-super-admin roles, set `can_reveal = true` for all roles.

### Backward Compatibility

- `can_` columns remain in the database indefinitely
- Old code paths that check `canOnModule($module, 'delete')` will return `false` for all non-super-admins after data cleanup, even without code changes to those paths
- The `can_delete` config key stays for compatibility
- The `can_reveal` column continues to function; the code-level auto-grant ensures it's treated as true when `can_read` is true, while allowing explicit deny

---

## L. Migration / Backward-Compatibility Plan

### Phase 0: Production Data Audit (Before Any Changes)

1. Run read-only queries against production to identify:
   - All non-super-admin `module_role_permissions` rows with `can_delete = true`
   - All `user_module_permissions` rows with `can_delete = true`
   - All rows with `can_read = true, can_reveal = false` (both tables)
   - All rows with `can_approve = true` (documentation only, mark as unused)
2. Review each `can_read=true, can_reveal=false` row — decide: should this role/user have password access?
3. Document the review outcomes for the migration

### Phase 1: Data Cleanup (Safe DB Changes)

1. Migration: `SET can_delete = 0` for all non-super-admin `module_role_permissions` and `user_module_permissions`
2. Update `RolePermissionSeeder` with new defaults
3. Update `config/permissions.php`:
   - Remove `can_delete` and `can_reveal` from `sensitive_permissions`
   - Add comment that `can_delete` is preserved for backward compatibility only
   - Add comment that `can_reveal` is auto-granted at code level

### Phase 2: Backend Enforcement (Security-Critical)

1. Fix `BaseResourceController::destroy()` — super-admin only
2. Fix `ServiceProviderController::destroy()` — super-admin only
3. Fix all custom `destroy()` methods (Asset, DomainEmail, ExpiryTracker, Task, Vault) — super-admin only
4. Fix all API `destroy()` methods — super-admin only (11 controllers)
5. Fix bulk delete/restore/force-delete in `BulkActionService` and `BulkActionController` — SA only
6. Keep bulk `update-status` permission-based
7. Fix `NoteController::authorizeNoteAccess()` — personal vs module-attached distinction
8. Fix `NoteController::restore()` and `forceDelete()` — SA only
9. Add reveal auto-grant in `HasModulePermissions::canOnModule()`
10. Add `can_delete = false` enforcement in `ModulePermissionService::setForRole()`
11. Add `can_delete = false` enforcement in `UserPermissionService::saveUserModulePermissions()`
12. Run all tests

### Phase 3: Role Permission UI

1. Add level-to-columns mapping helper
2. Update `ModulePermissionService`: use level mapping for loading/saving
3. Update `ModulePermissionController`: pass level data to view
4. Update `module-permissions/index.blade.php`: replace 8 checkboxes with 6-level selector
5. Update matrix mode to display level labels
6. Remove Delete, Reveal, Approve from UI

### Phase 4: User Permission UI

1. Update `UserPermissionService`: add level mapping with Custom fallback
2. Update `UserController::editPermissions()`/`updatePermissions()`: use levels
3. Update `permissions.js` Alpine component: level-based model + Custom mode for mixed overrides
4. Update `users/permissions.blade.php`: replace inline editor with level selector + Custom checkboxes
5. Remove Delete, Reveal, Approve from all modes
6. Remove sensitive permission modal
7. In Custom mode, auto-set `can_reveal` when `can_read` is true (unless explicitly denied)
8. Simplify to single view (remove Simple/Advanced tab toggle)

### Phase 5: Read-Only Views

1. Update `auth/my-permissions.blade.php` — show levels instead of per-column checkmarks
2. Update `users/show.blade.php` permission matrix — show level with source
3. Update `roles/show.blade.php` — show level instead of CRUD strings

### Phase 6: Config Cleanup & Optional Enhancements

1. Add restore/forceDelete API endpoints for major modules
2. Add trash/deleted views for major resource modules
3. Add `deleted_at` filter to all resource index views
4. Clean up unused permission components and views
5. Document `can_approve` as reserved/unused

### Backward Compatibility During Migration

- All `can_` columns remain in the database
- Old code checking `canOnModule($module, 'delete')` returns false for all non-super-admins after Phase 1
- The reveal auto-grant in `canOnModule()` is a CODE change, not a data migration — existing DB values are untouched
- No API breaking changes: the column names in API responses remain the same
- Role templates with `permissions_json` are updated only when the template is reapplied

---

## M. Test Plan

### Phase 0 Tests (Production Data Audit)

| Test | Description |
|------|-------------|
| `identify_can_delete_violations` | Query confirms non-SA rows with can_delete=true |
| `identify_read_without_reveal` | Query confirms can_read=true, can_reveal=false rows |
| `identify_mixed_user_overrides` | Query confirms mixed null/true/false UserModulePermission rows |

### Phase 1 Tests (Data Cleanup)

| Test | Description |
|------|-------------|
| `delete_removed_from_admin_role` | Verify admin role's can_delete=0 for all modules |
| `delete_removed_from_all_non_super_admin` | Verify all roles except super-admin have can_delete=0 |
| `delete_preserved_for_super_admin` | Verify super-admin bypass still works |
| `reveal_not_blanket_migrated` | Verify can_read=true, can_reveal=false rows are unchanged |
| `seeder_reflects_new_defaults` | Verify RolePermissionSeeder has correct new values |

### Phase 2 Tests (Backend Enforcement)

| Test | Description |
|------|-------------|
| `normal_user_cannot_destroy_business_record` | Normal user gets 403 on destroy for Hosting, Domain, VPS, etc. |
| `admin_user_cannot_destroy` | Admin role user gets 403 on destroy |
| `super_admin_can_destroy` | Super-admin can destroy all module types |
| `normal_user_cannot_bulk_delete_or_restore` | Bulk delete/restore/force-delete returns 403 for non-SA |
| `normal_user_can_bulk_update_status` | Bulk update-status works with can_update permission |
| `notification_destroy_owner_only` | Owner can dismiss own notification |
| `token_destroy_owner_only` | Owner can revoke own API token |
| `personal_note_destroy_owner_only` | Owner can delete own personal note |
| `module_note_destroy_super_admin_only` | Module-attached note destroy requires SA |
| `note_restore_force_delete_super_admin_only` | Note restore/forceDelete require SA |
| `reveal_auto_granted_with_read` | User with can_read=true on vault gets can_reveal=true via code |
| `reveal_explicit_deny_overrides` | User with can_reveal=false in UserModulePermission keeps deny |
| `api_destroy_super_admin_only` | All API destroy routes return 403 for non-SA |
| `can_delete_force_false_in_service` | ModulePermissionService forces can_delete=false for non-SA |
| `bulk_update_status_still_permission_based` | update-status works with can_update permission |

### Phase 3-4 Tests (UI)

| Test | Description |
|------|-------------|
| `role_permission_ui_six_levels` | Role UI shows No Access, Access, Manage, Import, Export, Full Access |
| `role_permission_ui_no_delete` | Delete checkbox absent |
| `role_permission_ui_no_reveal` | Reveal checkbox absent |
| `user_permission_ui_six_levels_plus_inherit_custom` | User UI shows Inherit + 6 levels + Custom |
| `user_permission_ui_custom_preserves_mixed_overrides` | Custom mode saves mixed null/true/false correctly |
| `saving_access_grants_read_and_reveal` | Selecting Access saves can_read=1, can_reveal=1 |
| `saving_manage_grants_correct_columns` | Selecting Manage saves 4 columns |
| `saving_import_grants_read_reveal_import` | Selecting Import saves can_read=1, can_reveal=1, can_import=1 |
| `saving_export_grants_read_reveal_export` | Selecting Export saves can_read=1, can_reveal=1, can_export=1 |
| `saving_full_access_grants_all_six` | Selecting Full Access saves all 6 non-delete columns |
| `inheriting_deletes_user_override_row` | Selecting Inherit deletes UserModulePermission row |
| `custom_mode_auto_sets_reveal_with_read` | Custom mode sets can_reveal=true when can_read=true |
| `custom_mode_preserves_explicit_reveal_deny` | Custom mode preserves explicit can_reveal=false |

### Phase 5 Tests (Read-Only Views)

| Test | Description |
|------|-------------|
| `my_permissions_shows_level` | My Permissions page shows level instead of CRUD |
| `user_show_shows_level_source` | User show page shows level with source for each module |
| `role_show_shows_level` | Role show page shows level per module |

### Regression Tests

| Test | Description |
|------|-------------|
| `super_admin_bypass_unchanged` | All super-admin bypass checks still work |
| `sidebar_hides_without_read` | Sidebar correctly hides modules without read |
| `password_reveal_works_with_access` | Users with Access can reveal passwords |
| `password_reveal_fails_without_access` | Users without Access cannot reveal |
| `vault_owner_read_bypass_preserved` | Vault owner can read own entry without module permission |
| `vault_owner_reveal_requires_module_reveal` | Vault owner needs can_reveal to reveal own password |
| `notifications_still_owner_only` | Notification self-service still works |
| `tokens_still_owner_only` | Token self-service still works |
| `existing_data_not_corrupted` | Existing permission data remains valid |
| `multiple_role_or_merge_still_works` | OR-merge across multiple roles still functions |
| `user_module_permission_nullable_still_works` | Null columns still mean inherit |

---

## N. Recommended Implementation Batches in Safest Order

### Batch 0: Production Data Audit (Before Any Code Changes)
*Risk: None. Read-only queries only.*

1. Run read-only queries to enumerate:
   - Non-SA rows with `can_delete = true`
   - Rows with `can_read = true, can_reveal = false`
   - Mixed nullable UserModulePermission rows
2. Review each `can_read=true, can_reveal=false` row — determine which should get reveal
3. Document all findings for migration

### Batch 1: Data Cleanup & Seeders
*Risk: Low. DB changes only, no behavioral change until Batch 2.*

1. Migration: `SET can_delete = 0` for all non-SA `module_role_permissions`
2. Migration: `SET can_delete = 0` for all non-SA `user_module_permissions`
3. Update `RolePermissionSeeder`: remove `can_delete` from non-SA roles, set `can_reveal = true` for all roles
4. Update `config/permissions.php`: remove `can_delete` and `can_reveal` from `sensitive_permissions`, add compatibility comments

### Batch 2: Backend Enforcement (Security-Critical)
*Risk: Medium. Changes authorization logic. Test thoroughly.*

1. Fix `BaseResourceController::destroy()` — SA only (fixes 6 children)
2. Fix `ServiceProviderController::destroy()` — SA only (override)
3. Fix all custom `destroy()` methods — SA only (Asset, DomainEmail, ExpiryTracker, Task, Vault)
4. Fix all API `destroy()` methods — SA only (11 controllers)
5. Fix `NoteController::authorizeNoteAccess()`: personal vs module-attached distinction
6. Fix `NoteController::restore()`, `forceDelete()` — SA only
7. Fix bulk actions: `BaseResourceController::index()`, `BulkActionService`, `BulkActionController` — delete/restore/force-delete SA only; update-status remains permission-based
8. Add reveal auto-grant in `HasModulePermissions::canOnModule()`
9. Add `can_delete = false` enforcement in `ModulePermissionService::setForRole()`
10. Add `can_delete = false` enforcement in `UserPermissionService::saveUserModulePermissions()`
11. Run all tests

### Batch 3: Role Permission UI
*Risk: Medium. UI changes with backend level mapping.*

1. Add level-to-columns mapping helper (shared between ModulePermissionService and UserPermissionService)
2. Update `ModulePermissionService`: use level mapping
3. Update `module-permissions/index.blade.php`: replace 8 checkboxes with 6-level selector
4. Update matrix mode to display level labels
5. Remove Delete, Reveal, Approve from Role UI

### Batch 4: User Permission UI
*Risk: High. Complex JS changes + must preserve mixed override semantics.*

1. Update `UserPermissionService`: level mapping + Custom fallback
2. Update `UserController::editPermissions()` / `updatePermissions()`: use levels
3. Rewrite `permissions.js` Alpine component: level-based model + Custom mode
4. Update `users/permissions.blade.php`: level selector + Custom per-capability checkboxes
5. Remove Delete, Reveal, Approve from all modes
6. In Custom mode: auto-set `can_reveal` when `can_read` is true (unless explicitly denied)
7. Remove sensitive permission confirmation modal
8. Remove Simple/Advanced tab toggle — single unified view

### Batch 5: Read-Only Views
*Risk: Low. Display-only.*

1. Update `auth/my-permissions.blade.php` — levels instead of per-column ✓/✗
2. Update `users/show.blade.php` — level + source
3. Update `roles/show.blade.php` — level labels
4. Update any dashboard widgets that display permission info

### Batch 6: Config Cleanup & Post-Migration Tasks
*Risk: Low. Optional cleanup.*

1. Verify all `can_delete` references in non-SA controller paths are dead code
2. Remove unused permission view components
3. Add restore/forceDelete API endpoints for major modules (optional)
4. Add trash/deleted views for major resource modules (optional)
5. Document `can_approve` as reserved/unused in code comments

---

## Appendix: File Reference Index

| Area | Key File(s) |
|------|-----------|
| Permission config | `config/permissions.php` |
| Permission trait | `app/Traits/HasModulePermissions.php` |
| Module cache | `app/Helpers/ModuleCache.php` |
| RBAC scope | `app/Helpers/RbacScope.php` |
| Base resource controller | `app/Http/Controllers/Web/BaseResourceController.php` (L182 destroy, L199 restore, L215 forceDelete) |
| Module permission service | `app/Services/ModulePermissionService.php` |
| User permission service | `app/Services/UserPermissionService.php` |
| Bulk action service | `app/Services/BulkActionService.php` |
| Bulk action controller (web) | `app/Http/Controllers/Web/BulkActionController.php` |
| Bulk action controller (api) | `app/Http/Controllers/Api/BulkActionController.php` |
| Role permission UI | `resources/views/module-permissions/index.blade.php` |
| User permission UI | `resources/views/users/permissions.blade.php` + `resources/js/permissions.js` |
| My permissions | `resources/views/auth/my-permissions.blade.php` |
| User show permission matrix | `resources/views/users/show.blade.php` |
| Permission components | `resources/views/components/permissions/` (10 files) |
| Sidebar composer | `app/Http/View/Composers/SidebarComposer.php` |
| Role permission seeder | `database/seeders/RolePermissionSeeder.php` |
| Feature/module seeder | `database/seeders/FeatureModuleSeeder.php` |
| Role templates | `database/seeders/RoleTemplateSeeder.php` |
| Route definitions | `routes/web.php` (web), `routes/api.php` (api) |
| Middleware config | `bootstrap/app.php` |
| Controllers (all) | `app/Http/Controllers/Web/` (40 files), `app/Http/Controllers/Api/` (32 files) |
| Models (all 30) | `app/Models/` |
| Export service | `app/Services/ExportService.php` |
| Import service | `app/Services/ImportService.php` |
| Data type config | `app/Helpers/DataTypeConfig.php` |
| Import controller (web) | `app/Http/Controllers/Web/ImportController.php` |
| Export controller (web) | `app/Http/Controllers/Web/ExportController.php` |
| Import controller (api) | `app/Http/Controllers/Api/ImportController.php` |
| Export controller (api) | `app/Http/Controllers/Api/ExportController.php` |
| Note controller | `app/Http/Controllers/Web/NoteController.php` |
| Note service | `app/Services/NoteService.php` |
| Notification controller | `app/Http/Controllers/Web/NotificationController.php` |
| Token controller | `app/Http/Controllers/Web/TokenController.php` |
| Attachment controller | `app/Http/Controllers/Web/AttachmentController.php` |
| Attachment service | `app/Services/AttachmentService.php` |
| Vault controller (web) | `app/Http/Controllers/Web/VaultController.php` |
| Vault controller (api) | `app/Http/Controllers/Api/VaultController.php` |
| Vault entry model | `app/Models/VaultEntry.php` |

---

FINAL CORRECTED PORTAL-WIDE PERMISSION MODEL AUDIT COMPLETE — STOPPING BEFORE IMPLEMENTATION
