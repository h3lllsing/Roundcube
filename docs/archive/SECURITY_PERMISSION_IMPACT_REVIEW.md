# SECURITY & PERMISSION IMPACT REVIEW

> For each candidate: what authorization boundaries exist, what new surfaces are exposed,
> and what must be protected. Based on the actual permission architecture.

---

## Current Permission Architecture

```
User
 ├── roles()  (BelongsToMany via Tyro: user_roles pivot)
 │    └── privileges() (BelongsToMany via Tyro: privilege_role pivot)
 │         └── hasPrivilege('slug')  ← Tyro's custom can() method
 │
 ├── moduleRolePermissions() (ModuleRolePermission)
 │    └── can_create | can_read | can_update | can_delete
 │         can_approve | can_export | can_reveal  ← per module
 │
 ├── userModulePermissions() (UserModulePermission)
 │    └── per-user overrides of module_role_permissions
 │
 └── canOnModule(Module $module, string $action): bool
      └── checks UserModulePermission first, then ModuleRolePermission fallback
```

### Key permission points:

1. **`can_reveal`** — a boolean on `module_role_permissions` and `user_module_permissions`. Controls who can reveal passwords. Separate from `can_read`. A user may be able to VIEW a hosting record but not REVEAL its password.

2. **Module-scoped access** — `canOnModule()` checks whether a user can perform an action on a specific module. Every service type (Hosting, Domain, VPS, VoIP, OtherService) and Vault are SEPARATE modules with independent permissions.

3. **No per-record permissions** — the system has NO per-record/row-level permissions. Access is granted at the module level. If you can `can_read` on Hosting module, you can see ALL hosting records (modulo `user_id` ownership which is soft-enforced through UI).

4. **No custom Gates or Policies** — authorization uses the Tyro-based module permission system exclusively. No `app/Policies` directory exists.

---

## CANDIDATE 1: Service-Credential Auto-Copy + Link

### New attack surface

The "Copy Password" / "Reveal" button on service detail pages creates a new access path to credential data. Currently, password revelation is gated by the Vault module's `can_reveal` permission (via the Vault detail page). Adding a reveal button to the service detail page moves this gate to a different module context.

### Risk scenario

A user with `can_read` on Hosting module but WITHOUT `can_reveal` on Vault module should NOT be able to reveal the hosting password via the service detail page. The reveal action on the service page must check the VAULT module's `can_reveal` permission, not the Hosting module's.

### Required permission guard

```php
// WRONG — checks hosting permission (VULNERABLE):
$user->canOnModule($hostingModule, 'reveal'); // Hosting module may have can_reveal=true

// RIGHT — checks vault permission:
$user->canOnModule($vaultModule, 'reveal'); // Vault module's can_reveal controls ALL password reveal
```

### Implementation rule

**The `can_reveal` permission on the service page MUST check the VAULT module, not the service module.** This requires accessing the Vault module's permission configuration from the service context. Implementation:

```php
// In the password reveal component/controller:
$vaultModule = Module::where('slug', 'vault')->first();
if (!auth()->user()->canOnModule($vaultModule, 'reveal')) {
    abort(403, 'You do not have permission to reveal passwords.');
}
```

### Secondary risk: Activity log exposure

Every reveal action should be logged in the activity log with:
- `causer_id` = the user who revealed
- `subject` = the service record
- `event` = `credential_revealed`
- `properties` = `['vault_entry_id' => $id, 'service_name' => $name]`

This creates an audit trail. Without it, unauthorized reveals cannot be detected.

### Risk rating: MODERATE

The permission check is straightforward but easy to get wrong (checking the wrong module's `can_reveal`). The existing `canOnModule()` method in `HasModulePermissions` trait makes this simple to implement correctly.

---

## CANDIDATE 2: Renewal Dashboard

### New attack surface

The dashboard inlines cost data from service models + provider data from ServiceProvider. Currently, cost data is visible only on individual service detail pages (each gated by its module's `can_read`). The dashboard aggregates costs across multiple modules in a single view.

### Risk scenario

A user with `can_read` on Hosting module but NOT on VPS module would see VPS cost data in aggregate if the dashboard query doesn't filter by module permissions. The "Total: $12,450" aggregate could leak cost information the user shouldn't have.

### Required permission guard

```php
// Get only the expiry trackers for modules the user can read:
$userModuleIds = auth()->user()->getAccessibleModuleIds('read');
$trackers = ExpiryTracker::whereIn('module_id', $userModuleIds)
    ->with('trackable')
    ->where('status', 'active')
    ->orderBy('expiry_date')
    ->get();

// Also filter by user_id if the module enforces ownership:
$trackers = $trackers->filter(fn($t) => $t->user_id === auth()->id() || auth()->user()->hasRole('admin'));
```

### Implementation rule

**The dashboard must scope to the user's accessible modules**, not return all records. Use `getAccessibleModuleIds('read')` to get the list of modules the current user can read. Only show ExpiryTrackers whose `module_id` is in that list.

### Aggregate data concern

Showing "Total: $12,450" requires summing costs across ALL visible trackers. If a user can see 8 of 10 trackers, the total is misleading (understated). Options:
1. Show total only for visible records: "Total (visible): $8,450" 
2. Show total with note: "Total tracked: $12,450 (based on your accessible modules)"
3. Hide total if the user doesn't have access to all modules

Option 1 is safest. Option 2 is most transparent.

### Secondary risk: The "Renew" action

One-click renewal updates `expiry_date` on the ExpiryTracker (and potentially the service model). This must be gated by `can_update` on the relevant module. The permission check must happen on the specific model being updated.

### Risk rating: LOW

The module-scoped access pattern is already implemented via `getAccessibleModuleIds()`. The dashboard simply applies the same pattern. The aggregate cost display is the only new consideration.

---

## CANDIDATE 3: Security Timeline

### New attack surface

The timeline merges LoginAudit data and ActivityLog data into a single view. Currently, these are on separate pages with separate permission checks. The timeline by design shows MORE than either page alone — it shows the RELATIONSHIP between events.

### Risk scenario

A compromised Security Officer account (or a user with access to both modules) can see the complete timeline. The timeline itself doesn't introduce new data, but it makes correlation dramatically easier. An attacker with read-only access to both modules can now:
1. See which users had failed logins AND subsequent permission changes
2. Identify high-value targets (users who recently got admin access AND accessed credentials)
3. Pattern the security review cycle (know when the Security Officer checks the timeline)

### Required permission guard

**Strict AND gate:** User must have `can_read` on BOTH modules:
```php
$canSeeLogins = auth()->user()->canOnModule($loginModule, 'read');
$canSeeActivity = auth()->user()->canOnModule($activityModule, 'read');

if (!$canSeeLogins && !$canSeeActivity) {
    abort(403);
}

if ($canSeeLogins && $canSeeActivity) {
    // Show full merged timeline
} elseif ($canSeeLogins) {
    // Show only login events
} elseif ($canSeeActivity) {
    // Show only activity events
}
```

### Activity log sensitivity

The Spatie activity log captures ALL model changes, including:
- Who created/updated/deleted which records
- Previous values vs new values (in properties JSON)
- Timestamps for every action

This is highly sensitive. The timeline must NOT expose the `properties` column directly. Only show: `description`, `event`, `created_at`, and a summary. Full details require navigating to the source page (where permission checks are already in place).

### Login audit sensitivity

Login audits reveal:
- Who logged in successfully/failed
- IP addresses
- User agent strings
- Timestamps

IP addresses and user agents should NOT be displayed in the timeline widget. They should be visible only on the dedicated LoginAudit detail page (existing behavior).

### Implementation rule

**Two-tier data exposure:**
- Timeline widget: show event type, user name, time, brief description. NO IPs. NO properties JSON.
- Detail navigation: clicking an event navigates to the source page (existing permission checks apply).

### Risk rating: MODERATE

The timeline doesn't expose new raw data, but it makes correlation trivially easy. The risk is acceptable if:
1. Access is restricted to Security Officer + Super Admin roles
2. The timeline widget shows only summary data
3. Full detail requires navigating to source pages

---

## CANDIDATE 4: Quick Provision Form / Provisioning Wizard

### New attack surface

A form that creates TWO entities (service + credential) in one submission creates a new authority boundary: the user needs `can_create` on BOTH modules. If the permission check only validates one module, the user could skip the other.

### Risk scenario

A user with `can_create` on Hosting but NOT on Vault could use the Quick Provision form to create a vault entry. The form pre-checks both permissions, but the server-side submission must also validate both. If the permission check is client-side only, it's bypassable.

### Required permission guard

```php
// Server-side double-check in the action class:
public function create(array $data): ProvisionResult
{
    $user = auth()->user();
    
    $canCreateService = $user->canOnModule($data['service_module'], 'create');
    $canCreateCredential = $user->canOnModule($vaultModule, 'create');
    
    if (!$canCreateService) {
        throw new AuthorizationException('You cannot create this service type.');
    }
    if (!$canCreateCredential) {
        throw new AuthorizationException('You cannot create credentials.');
    }
    
    // Proceed with creation...
}
```

### Implementation rule

**Pre-check all permissions in TWO places:**
1. Frontend: disable the form if user lacks any required permission (UX)
2. Backend: throw AuthorizationException if any permission is missing (security)

### Transaction rollback concern

If the service is created but the credential creation fails (permission revoked mid-session, database error), the service should roll back:
```php
DB::transaction(function () use ($data) {
    $service = $data['service_model']::create($data['service_data']);
    VaultEntry::create($data['credential_data']);
    // If this fails, $service is rolled back
});
```

### Secondary risk: Data consistency

The form auto-fills `vault_entry.service_name` from `service.name`. If the user then edits the service name after creation, the vault entry's `service_name` becomes stale. This is a data integrity concern, not a security concern, but it's worth noting.

### Risk rating: LOW-MODERATE

The dual-permission check is straightforward. The main risk is implementing the check on only one module instead of both. The transaction rollback handles the failure case cleanly.

---

## CANDIDATE 5: Offboarding Dashboard

### New attack surface

**EXTREME.** This is the highest-risk feature by far. The dashboard:
1. Lists ALL user associations across 5+ entity types
2. Performs destructive actions (suspend account, revoke credentials, reassign tasks)
3. Can affect 500+ users from a single interface
4. Has irreversible consequences (revoked credentials can't be un-revoked)

### Risk scenario: Single-actor offboarding

A compromised Security Officer account could offboard ALL 500 users in under 10 minutes:
1. Search for each user
2. Click "Suspend Account" 
3. Click "Revoke All Credentials"
4. Leave tasks unassigned (dropped work)
5. Leave assets unchecked (lost equipment)

This is a denial-of-service attack on the entire organization.

### Required permission guards

**Minimum: Confirmation step**
```php
public function offboard(User $user, array $steps): void
{
    // Require explicit confirmation for each destructive action:
    if (!request()->has('confirmed_steps')) {
        throw new ConfirmationRequiredException('You must confirm each offboarding step.');
    }
    
    // Log every action:
    activity()
        ->causedBy(auth()->user())
        ->performedOn($user)
        ->withProperties(['steps' => $steps])
        ->event('user_offboarded')
        ->log("User {$user->name} offboarded by " . auth()->user()->name);
}
```

**Recommended: Two-person rule**
```php
// Step 1: First user initiates offboarding → creates PENDING record
$pending = PendingOffboarding::create([
    'user_id' => $user->id,
    'initiated_by' => auth()->id(),
    'steps' => $steps,
    'status' => 'pending_approval',
]);

// Step 2: Notify second approver (Security Officer or Super Admin)
// Step 3: Second user approves → executes offboarding
// Step 4: Log with both actor IDs
```

**Strongly recommended: Rate limiting**
```php
// Max 3 offboardings per hour per user
$recentCount = PendingOffboarding::where('initiated_by', auth()->id())
    ->where('created_at', '>=', now()->subHour())
    ->count();

if ($recentCount >= 3) {
    throw new RateLimitException('Maximum 3 offboardings per hour.');
}
```

### Read-only MVP is safe

The MVP (read-only checklist) has NO write operations. It shows counts + links. The user must navigate to each section to perform actions manually. This preserves existing permission boundaries. The checklist is purely an information aid.

Risk of the MVP: the checklist might CREATE a false sense of completeness (user thinks "I did all 5 steps" but actually missed one because the checklist doesn't track completion state). Mitigation: add a client-side checkbox that RESETS on page reload (no persistence).

### Full dashboard risk escalation

| Feature | Risk | Mitigation |
|---------|------|------------|
| Suspend account | DoS — mass suspension | Confirmation + rate limit |
| Revoke credentials | **CRITICAL**— irreversible data loss | **TWO-PERSON RULE REQUIRED** |
| Reassign tasks | Data loss — dropped work | Confirmation + preview of reassignment |
| Check in assets | Equipment loss | Confirmation + physical verification step |
| Generate report | Information disclosure | Restricted to Security Officer role only |

### Permission boundary for full dashboard

The offboarding dashboard must check the **STRICTEST** permission of any action available:

```php
// User must have BOTH to access the dashboard at all:
$canManageUsers = $user->canOnModule($userModule, 'update');  // can suspend
$canManageVault = $user->canOnModule($vaultModule, 'delete'); // can revoke
$canManageTasks = $user->canOnModule($taskModule, 'update');  // can reassign

if (!($canManageUsers && $canManageVault && $canManageTasks)) {
    abort(403, 'You do not have permission to offboard users.');
}
```

### Audit trail requirements

Every offboarding action must be logged with:
- `causer_id` = the person performing the action
- `subject` = the user being offboarded
- `event` = `user_offboarded`
- `properties` = full step list with `['step' => 'revoke_credentials', 'vault_entry_id' => 123, 'status' => 'revoked']`
- IP address
- Timestamp

Without this audit trail, offboarding actions cannot be reviewed for compliance (SOC2, SOX).

### Risk rating: EXTREME (full dashboard) / LOW (read-only MVP)

The read-only checklist MVP has minimal risk (no write operations). The full dashboard with revoke actions has the highest risk of any feature in this analysis. Must not be implemented without:
1. Two-person rule
2. Rate limiting
3. Full audit trail
4. Confirmation dialogs
5. 24-hour delay on irreversible actions

---

## PERMISSION CHECK SUMMARY

| Candidate | Action | Permission to Check | Module to Check Against | Risk |
|-----------|--------|-------------------|----------------------|------|
| Service-Credential | Reveal password | `can_reveal` | **Vault** module (NOT service module) | HIGH if wrong module checked |
| Service-Credential | Copy password | `can_reveal` | **Vault** module | MODERATE |
| Renewal Dashboard | View dashboard | `can_read` | Each service module (scoped) | LOW — existing pattern |
| Renewal Dashboard | Click Renew | `can_update` | Service module of the specific record | LOW |
| Security Timeline | View timeline | `can_read` | BOTH LoginAudit AND ActivityLog | MODERATE |
| Quick Provision | Create service | `can_create` | Target service module | LOW |
| Quick Provision | Create credential | `can_create` | Vault module | LOW |
| Offboarding Checklist | View checklist | `can_read` | ALL modules (no write) | LOW |
| Offboarding Dashboard | Suspend user | `can_update` | Users module | HIGH |
| Offboarding Dashboard | Revoke credential | `can_delete` | Vault module | **CRITICAL** |
| Offboarding Dashboard | Reassign task | `can_update` | Tasks module | MODERATE |

## CRITICAL RULE

**The `can_reveal` permission must ALWAYS be checked against the Vault module (slug: 'vault'), never against the Hosting/Domain/VPS/VoIP/OtherService module.** This is the single most important permission rule in this entire review. Getting this wrong in the Service-Credential Link creates a credential exposure vulnerability. The module role permission system supports this (each module has independent `can_reveal`), but the implementation must explicitly reference the Vault module.
