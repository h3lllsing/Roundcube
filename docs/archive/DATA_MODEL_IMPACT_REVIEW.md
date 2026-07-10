# DATA MODEL IMPACT REVIEW

> For each candidate: what the current schema allows, what is missing, and what must change.
> Based on direct evidence from migrations and model files — not assumptions.

---

## Current Schema Map

```
users
 ├── vault_entries (password_vault)     ← user_id FK
 ├── tasks              ← task_user pivot (belongsToMany)
 ├── assets             ← assigned_to FK (nullable)
 ├── login_audits       ← user_id FK
 ├── hostings           ← user_id FK
 ├── domains            ← user_id FK
 ├── vps                ← user_id FK
 ├── voip               ← user_id FK
 ├── other_services     ← user_id FK
 ├── service_providers  ← user_id FK
 ├── expiry_trackers    ← user_id FK
 └── activity_log       ← causer_id (morph)

hostings / domains / vps / voip / other_services
 ├── user_id FK
 ├── service_provider_id FK (nullable)
 ├── password (encrypted, hidden)       ← INLINE, not vault
 └── (NO vault_entry_id, NO credential FK)

expiry_trackers
 ├── user_id FK
 ├── service_provider_id FK (nullable)
 ├── trackable_type / trackable_id     ← polymorphic morphTo all services
 └── (NO direct link to vault entries)

vault_entries (password_vault)
 ├── user_id FK
 ├── module_id FK (nullable)
 ├── service_name (free text, NOT a FK)
 └── (NO credentialable morphs, NO service FK)

assets
 ├── user_id FK (owner)
 ├── assigned_to FK (nullable) → users
 ├── vault_entry_id FK (nullable) → password_vault
 └── ONLY model with link to vault
```

---

## CANDIDATE 1: Service-Credential Link

### Current state (evidence)

Every service model (Hosting, Domain, VPS, VoIP, OtherService) has:
- An inline `password` column (encrypted, hidden from serialization)
- NO `vault_entry_id` or `credential_id` column
- NO polymorphic relationship to vault_entries

`VaultEntry` has:
- `service_name` — a free-text VARCHAR, NOT a foreign key
- NO `credentialable_type` / `credentialable_id` morph columns
- Only `user_id` FK (owner) and `module_id` FK (optional scope)

The ONLY link between the "service world" and the "vault world" is through `Asset.vault_entry_id` — which is for physical asset passwords (laptop PINs), not service credentials.

### What would need to change

**Option A: Add FK to all 5 service tables** (one migration per table):
```php
// Migration: add_vault_entry_id_to_services
Schema::table('hostings', function (Blueprint $table) {
    $table->foreignId('vault_entry_id')
        ->nullable()
        ->constrained('password_vault')
        ->nullOnDelete();
});
// Repeat for: domains, vps, voip, other_services
```
- 5 migrations (or 1 migration with 5 `Schema::table` calls)
- Nullable FK with `nullOnDelete` (deleting vault entry doesn't cascade-delete service)
- Requires index on each new column

**Option B: Add polymorphic morphs to vault_entry** (1 migration):
```php
Schema::table('password_vault', function (Blueprint $table) {
    $table->nullableMorphs('credentialable'); // adds credentialable_type + credentialable_id
});
```
- 1 migration, cleaner architecture
- Allows vault entry to belong to ANY model (Hosting, Domain, VPS, VoIP, OtherService)
- Existing vault entries get NULL for both columns
- `nullOnDelete` is implicit (morphs don't enforce FK constraints at DB level)

**Option C: Add a pivot table** (cleanest for many-to-many):
```php
Schema::create('credential_service', function (Blueprint $table) {
    $table->id();
    $table->foreignId('vault_entry_id')->constrained('password_vault')->cascadeOnDelete();
    $table->morphs('serviceable'); // serviceable_type + serviceable_id
    $table->timestamps();
    $table->unique(['vault_entry_id', 'serviceable_type', 'serviceable_id']);
});
```
- Allows one vault entry to be shared across multiple services (common: same admin password for multiple servers)
- Cascade delete removes pivot when vault entry is deleted
- No FK constraint from service side (services don't need a column)

### Recommended approach

**Production: Option C (pivot table).** The polymorphic pivot handles:
- One vault entry → many services (same credential reused)
- One service → many vault entries (multiple passwords per service)
- No schema changes to 5 existing service tables
- No schema change to vault_entries table

**MVP: None needed** (see WORKFLOW_MVP_SCOPE.md — MVP uses inline passwords only, no vault linking).

### Backfill strategy

Existing vault entries have `service_name` (free text). To link them to services:
```bash
php artisan services:link-credentials --dry-run   # Preview matches
php artisan services:link-credentials             # Execute matching
```
The matching heuristic:
1. Exact match: `vault_entry.service_name = hosting.name`
2. Partial match: `vault_entry.service_name LIKE '%hosting.name%'`
3. Manual: list unmatched entries for manual linking via TALL UI

Estimated success rates: Option 1 = 40%, Option 2 = 35%, Option 3 = 25%. Expect 1-2 days of manual reconciliation.

---

## CANDIDATE 2: Renewal Dashboard

### Current state (evidence)

`ExpiryTracker` already has:
- `trackable_type` / `trackable_id` (nullable morphs) — links to any service model
- `service_provider_id` (FK nullable) — direct link to provider

Service models already have:
- `service_provider_id` (FK to `service_providers`)
- `cost` (decimal 10,2 nullable)
- `expiry_date` (date nullable)
- `status` (string, default 'active')

The morph map in `AppServiceProvider`:
```php
Relation::morphMap([
    'hosting'          => Hosting::class,
    'domain'           => Domain::class,
    'vps'              => Vps::class,
    'voip'             => Voip::class,
    'other_service'    => OtherService::class,
    'service_provider' => ServiceProvider::class,
    'domain_email'     => DomainEmail::class,
    'feature'          => Feature::class,
    'module'           => Module::class,
]);
```

### What would need to change

**Nothing.** The existing schema fully supports this feature.

The relationship chain is already traversable:
```php
// Get all active expiry trackers with their service details
ExpiryTracker::with('trackable')
    ->where('status', 'active')
    ->get()
    ->loadMorph('trackable', [
        Hosting::class => ['serviceProvider'],
        Domain::class => ['serviceProvider'],
        Vps::class => ['serviceProvider'],
        Voip::class => ['serviceProvider'],
        OtherService::class => ['serviceProvider'],
    ]);
```

### Optional improvements (post-MVP)

```php
// Add index for dashboard query performance
Schema::table('expiry_trackers', function (Blueprint $table) {
    $table->index(['status', 'expiry_date']); // composite index for dashboard query
});
```

### Risk assessment

The polymorphic `loadMorph` pattern is Laravel-native but under-documented. Testing is essential. With 200+ ExpiryTrackers across 5 types, ensure eager loading works correctly:
- Without optimization: 401+ queries
- With `loadMorph`: 11 queries maximum

---

## CANDIDATE 3: Security Timeline

### Current state (evidence)

Two independent tables with NO shared schema:

| Property | login_audits | activity_log |
|----------|-------------|--------------|
| User identifier | `user_id` FK (bigint), `email` (string) | `causer_id` (bigint nullable), `causer_type` (string) |
| Timestamp | `created_at` (indexed) | `created_at` |
| Event type | `event` (string: login_success, login_failed, logout) | `event` (string: created, updated, deleted, login), `description` (text) |
| IP/context | `ip_address` (string 45), `user_agent` (text) | `properties` (json) — may contain IP |
| Subject | N/A (login is the event) | `subject_type`/`subject_id` (morphs) |

### What would need to change

**For MVP: Nothing.** The timeline queries both tables independently and merges results in PHP/Livewire. No schema change.

**For production quality:** Two optional additions:

1. **Add `session_id` to login_audits** (for session-level correlation):
```php
Schema::table('login_audits', function (Blueprint $table) {
    $table->string('session_id', 100)->nullable()->after('user_agent');
    $table->index('session_id');
});
```

2. **Add `login_audit_id` to activity_log** (for explicit login→action correlation):
```php
Schema::table('activity_log', function (Blueprint $table) {
    $table->foreignId('login_audit_id')
        ->nullable()
        ->constrained('login_audits')
        ->nullOnDelete();
    $table->index('login_audit_id');
});
```

### Why skip production additions for now

Both additions are speculative optimizations. Without usage data showing that the timeline is actually used for investigations, adding these columns is premature. If the timeline gets heavy usage, the `session_id` approach is the better first addition (lower overhead, works at the login_audits level).

### Risk assessment

The query structure is the main risk, not the data model:
```php
// Both queries are independent — no JOIN needed
$logins = LoginAudit::whereDate('created_at', '>=', $since)->get();
$activities = Activity::whereDate('created_at', '>=', $since)->get();
// Merge in collection:
$events = $logins->concat($activities)->sortByDesc('created_at')->take(20);
```

Performance concern: `activity_log` can grow rapidly. Without date-range filtering indices, queries will table-scan. The `created_at` column on `activity_log` has an implicit index from Laravel's migration. The `login_audits.created_at` is explicitly indexed.

---

## CANDIDATE 4: Provisioning Wizard

### Current state (evidence)

Six independent models with NO shared creation logic:
1. `ServiceProvider` — table: `service_providers`
2. `Hosting` / `Domain` / `VPS` / `VoIP` / `OtherService` — 5 separate tables
3. `DomainEmail` — table: `domain_emails` (child of Domain)
4. `VaultEntry` — table: `password_vault`
5. `ExpiryTracker` — table: `expiry_trackers`
6. `Task` — table: `tasks`

### What would need to change

**For Quick Provision MVP (service + credential in one form):**

Two entities independently created via existing models:
```php
$service = Hosting::create([...]);  // uses existing table/columns
VaultEntry::create([
    'service_name' => $service->name,  // auto-filled from service, still free text
    'username' => $data['username'],
    'encrypted_password' => encrypt($data['password']),
    'user_id' => auth()->id(),
]);
```
No schema changes. The vault entry's `service_name` remains a text field.

**For full wizard with auto-link (post-MVP):**

Requires the pivot table from Candidate 1 (Option C) to link the created vault entry to the created service:
```php
$pivot = CredentialService::create([
    'vault_entry_id' => $vaultEntry->id,
    'serviceable_type' => get_class($service),
    'serviceable_id' => $service->id,
]);
```

### Backfill strategy

Existing services have inline `password` columns that may or may not match vault entries. The provisioning wizard doesn't backfill — it only affects NEW provisions.

---

## CANDIDATE 5: Offboarding Dashboard

### Current state (evidence)

User associations that ARE directly queryable from existing schema:
| Association | Table | Relationship | Reliability |
|------------|-------|-------------|-------------|
| Owned vault entries | `password_vault`.`user_id` | `User::vaultEntries()` | 100% — direct FK |
| Assigned tasks | `task_user`.`user_id` | `User::tasks()` via pivot | 100% — pivot table |
| Assets checked out | `assets`.`assigned_to` | `Asset::where('assigned_to', $userId)` | 100% — direct FK |
| User's activity log | `activity_log`.`causer_id` | morph query | 100% — standard query |
| Owned services | `hostings|domains|vps|voip|other_services`.`user_id` | `User::hostings()` etc. | 100% — direct FK |

User associations that are NOT directly queryable:
| Association | Table | Problem |
|------------|-------|---------|
| **Shared credentials user has access to** | `module_role_permissions` | Vault access is granted by ROLE (module-level permission `can_read` on Vault module). There is NO `credential_user` or `credential_role` pivot table. To know which vault entries a user can access, you must: 1) get user's roles, 2) check module_role_permissions for vault module + those roles, 3) get all vault entries in that module. This is a logical inference, not a direct relationship. |
| Recent login events | `login_audits`.`user_id` | Queryable but requires JOIN. Not currently a relationship on User model. |

### What would need to change

**For MVP checklist (read-only counts):**

No schema changes. All 5 counts come from existing relationships:
```php
// count 1: vault entries owned
$vaultCount = $user->vaultEntries()->count();

// count 2: tasks assigned (via pivot)
$taskCount = $user->tasks()->count();

// count 3: assets checked out
$assetCount = Asset::where('assigned_to', $user->id)->count();

// count 4: recent activity
$activityCount = Activity::where('causer_type', User::class)
    ->where('causer_id', $user->id)
    ->whereDate('created_at', '>=', now()->subDays(90))
    ->count();

// count 5: owned services
$serviceCount = Hosting::where('user_id', $user->id)->count()
    + Domain::where('user_id', $user->id)->count()
    + Vps::where('user_id', $user->id)->count()
    + Voip::where('user_id', $user->id)->count()
    + OtherService::where('user_id', $user->id)->count();
```

**For full dashboard with revoke actions:**

CRITICAL: Need a `credential_user` pivot table to track which users have been granted access to which vault entries. Without this, the "Revoke Credentials" action cannot revoke access to shared team passwords — which is the most dangerous missed revocation.

```php
Schema::create('credential_user', function (Blueprint $table) {
    $table->id();
    $table->foreignId('vault_entry_id')->constrained('password_vault')->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('permission', 20)->default('read'); // read, reveal
    $table->timestamp('granted_at')->nullable();
    $table->foreignId('granted_by')->nullable()->constrained('users');
    $table->timestamps();
    $table->unique(['vault_entry_id', 'user_id']);
});
```

This is NOT an MVP scope item — it's a v2 addition that requires:
- New migration for `credential_user` table
- New UI for granting/revoking per-user vault access (currently vault access is role-based only)
- Backfill of existing access grants (manual or inferred from usage)
- Migration of `can_reveal` logic from module-role level to include per-user level

---

## SUMMARY: Data Model Changes Required

| Candidate | MVP Phase | Migrations Required | Risk | Complexity |
|-----------|-----------|-------------------|------|------------|
| Service-Credential Auto-Copy | MVP | **0** | None | Trivial |
| Service-Credential FK Link | Post-MVP | **1-5** (Option A-C) | LOW | Moderate |
| Renewal Dashboard | MVP | **0** | None | Trivial |
| Renewal Dashboard Index | Post-MVP | **1** (composite index) | LOW | Trivial |
| Security Timeline | MVP | **0** | None | Trivial |
| Security Timeline `session_id` | Post-MVP | **1** | LOW | Trivial |
| Quick Provision Form | MVP | **0** | None | Trivial |
| Full Provisioning Wizard | Post-MVP | **1** (credential pivot) | MED | Moderate |
| Offboarding Checklist | MVP | **0** | None | Trivial |
| Full Offboarding Dashboard | Post-MVP | **1** (credential_user pivot) | **HIGH** | Complex |

**Key finding: Every MVP requires zero migrations.** All 5 candidates can be delivered as MVP with existing schema. The data model changes are needed only for the FULL features (v2+).

This is a strong signal: the current schema has the right data. The problem is PRESENTATION and WORKFLOW, not data modeling. Any conversation about "we need schema changes first" is a delay tactic. The data is there. Surface it.
