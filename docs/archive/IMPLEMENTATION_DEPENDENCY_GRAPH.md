# IMPLEMENTATION DEPENDENCY GRAPH

> Dependency map, risk register, effort estimates, and implementation sequencing for all 5 approved v1.1 features.
> This is the engineering contract. Deviations from this graph must be reviewed by Architecture Board.

---

## 1. Feature Dependency Map

```
                    ┌──────────────────────────┐
                    │  Prerequisite Work        │
                    │  (No user-facing feature) │
                    │                           │
                    │  P1: activity_log index   │
                    │      on created_at        │
                    │  P2: User model           │
                    │      assignedAssets()     │
                    │      and activities()     │
                    │      relationships        │
                    └────────────┬─────────────┘
                                 │
          ┌──────────────────────┼──────────────────────┐
          │                      │                      │
          ▼                      ▼                      ▼
┌─────────────────────┐  ┌─────────────────────┐  ┌─────────────────────┐
│ F1: Offboarding     │  │ F2: Service-Cred    │  │ F3: Renewal         │
│ Checklist           │  │ Auto-Copy           │  │ Dashboard           │
│                     │  │                     │  │                     │
│ Depends on: P2      │  │ Depends on: none    │  │ Depends on: none    │
│ Blocks: nothing     │  │ Blocks: F4 (opt)    │  │ Blocks: nothing     │
│ LIVE: Day 5         │  │ LIVE: Day 2         │  │ LIVE: Day 10        │
└─────────────────────┘  └──────────┬──────────┘  └─────────────────────┘
                                     │
                                     ▼
                          ┌─────────────────────┐
                          │ F4: Quick Provision  │
                          │                      │
                          │ Depends on:          │
                          │   F2 credential UX   │
                          │   (optional, reuse)  │
                          │ Blocks: nothing      │
                          │ LIVE: Day 15         │
                          └─────────────────────┘

┌─────────────────────────────────────────────────────────────┐
│ F5: Security Recent Events Widget                            │
│                                                              │
│ Depends on: P1 (activity_log index)                          │
│ Blocks: nothing                                              │
│ LIVE: Day 20 (must wait for P1 migration + deploy)           │
└─────────────────────────────────────────────────────────────┘
```

### Legend

```
F1 = 01_OFFBOARDING_CHECKLIST_SPEC.md
F2 = 02_SERVICE_CREDENTIAL_AUTOCOPY_SPEC.md
F3 = 03_RENEWAL_DASHBOARD_SPEC.md
F4 = 04_QUICK_PROVISION_SPEC.md
F5 = 05_SECURITY_WIDGET_SPEC.md
P1 = Prerequisite: activity_log created_at index migration
P2 = Prerequisite: User model relationship additions
```

---

## 2. Feature Details

### F1: Offboarding Checklist

| Property | Value |
|----------|-------|
| **Spec** | `01_OFFBOARDING_CHECKLIST_SPEC.md` |
| **MVP Effort** | 3 days |
| **Production Effort** | 5 days (with suspending action, rate limiting, audit log) |
| **Risk** | LOW (read-only MVP). MEDIUM (with suspend action). |
| **Migrations** | 0 |
| **New Models** | 0 (Livewire component only) |
| **New Controllers** | 0 (Livewire component handles logic) |
| **New Views** | 1 (Livewire Blade template) |
| **Modified Views** | 1 (users.show — add @livewire) |
| **Prerequisites** | P2: User model relationships for `assignedAssets()` and `activities()` |

### F2: Service-Credential Auto-Copy

| Property | Value |
|----------|-------|
| **Spec** | `02_SERVICE_CREDENTIAL_AUTOCOPY_SPEC.md` |
| **MVP Effort** | 2 days |
| **Production Effort** | 3 days (with rate limiting, full activity logging) |
| **Risk** | MODERATE (permission check must target vault module, not service module) |
| **Migrations** | 0 |
| **New Models** | 0 |
| **New Controllers** | 0 (Livewire component or AlpineJS) |
| **New Views** | 4 (per service type: hosting, vps, voip, other_services detail pages add password buttons) |
| **Prerequisites** | None. Inline passwords already exist on all 4 applicable service models. |

### F3: Renewal Dashboard

| Property | Value |
|----------|-------|
| **Spec** | `03_RENEWAL_DASHBOARD_SPEC.md` |
| **MVP Effort** | 3-5 days |
| **Production Effort** | 7 days (with filters, sort, CSV export, aggregate) |
| **Risk** | LOW (all data exists, zero migrations) |
| **Migrations** | 0 |
| **New Models** | 0 |
| **New Controllers** | 0 (replace existing ExpiryTrackerController@index) |
| **New Views** | 1 (dashboard blade replacing index) |
| **Modified Views** | 1 (expiry_trackers/index → dashboard) |
| **Prerequisites** | None. Polymorphic relationships exist. MorphMap is registered. |

### F4: Quick Provision

| Property | Value |
|----------|-------|
| **Spec** | `04_QUICK_PROVISION_SPEC.md` |
| **MVP Effort** | 5 days (1 week) |
| **Production Effort** | 7-10 days (with provider inline creation, full validation, transaction management) |
| **Risk** | MEDIUM (transactional integrity across 2 entities, permission pre-checks) |
| **Migrations** | 0 |
| **New Models** | 0 |
| **New Controllers** | 1 (ProvisionController) |
| **New Views** | 1 (provision/create.blade.php) |
| **New Routes** | 2 (GET + POST /provision) |
| **Prerequisites** | F2 recommended but not required. F2 provides the copy password UX which Quick Provision can reuse for the credential section. Without F2, Quick Provision must implement its own password field handling. |

### F5: Security Recent Events Widget

| Property | Value |
|----------|-------|
| **Spec** | `05_SECURITY_WIDGET_SPEC.md` |
| **MVP Effort** | 3-5 days |
| **Production Effort** | 7 days (with filters, polling, user search) |
| **Risk** | MODERATE (query performance on unindexed activity_log) |
| **Migrations** | 1 REQUIRED: `$table->index('created_at')` on `activity_log` |
| **New Models** | 0 |
| **New Controllers** | 0 (Livewire component) |
| **New Views** | 1 (Livewire Blade template) |
| **Modified Views** | 1 (dashboard or security page — add @livewire) |
| **Prerequisites** | P1: activity_log.created_at index migration. Without this index, the feature will NOT perform at scale (>10K activity rows). |

---

## 3. Prerequisite Work

### P1: activity_log.created_at Index

| Property | Value |
|----------|-------|
| **Type** | Database migration |
| **Effort** | 30 minutes |
| **Migration content** | `Schema::table('activity_log', fn(Blueprint $t) => $t->index('created_at'));` |
| **Risk** | LOW — adding index is non-destructive |
| **Blocking** | F5 Security Timeline (CRITICAL: without this, query performance degrades to table scan) |
| **Also benefits** | Any existing feature that queries activity_log by date range |
| **When to deploy** | Day 1, before any feature work. Zero downtime. |

### P2: User Model Relationships

| Property | Value |
|----------|-------|
| **Type** | Model change (no migration) |
| **Effort** | 15 minutes |
| **Changes** | Add to `app/Models/User.php`:
```php
public function assignedAssets(): HasMany
{
    return $this->hasMany(Asset::class, 'assigned_to');
}

public function activities(): HasMany
{
    return $this->hasMany(Activity::class, 'causer_id')
        ->where('causer_type', static::class);
}
``` |
| **Risk** | LOW — adding Eloquent relationships is read-only |
| **Blocking** | F1 Offboarding Checklist (needs elegant relationship access to assets and activities) |
| **Also benefits** | Any future feature needing user→asset or user→activity queries |
| **When to deploy** | Day 1, before F1 work |

---

## 4. Risk Register

| Risk | Feature | Probability | Impact | Mitigation |
|------|---------|-------------|--------|------------|
| R1: `can_reveal` checked against wrong module (service instead of vault) | F2 | MEDIUM | HIGH | Code review MUST verify vault module is referenced. Add integration test. |
| R2: Activity log query performance with no index | F5 | HIGH (if P1 not done) | HIGH | Block F5 on P1. P1 is a 30-minute migration. DO NOT deploy F5 without P1. |
| R3: Suspension without rate limit (DoS) | F1 | MEDIUM | HIGH | Rate limit of 5/hour is in spec. Implement in MVP, not deferred. |
| R4: Polymorphic N+1 query on renewal dashboard | F3 | MEDIUM | MEDIUM | Use `loadMorph()` pattern. Test with 200+ trackers. |
| R5: Quick Provision transaction rollback fails | F4 | LOW | MEDIUM | Wrap both creates in `DB::transaction`. Test rollback explicitly. |
| R6: VaultEntry.module_id not set during Quick Provision | F4 | MEDIUM | MEDIUM | NEEDS REVIEW — if vault entries require module_id for permission filtering, set it from the service module. |
| R7: Existing plaintext passwords in DB break encrypted cast | F2 | MEDIUM | HIGH | Audit existing service passwords. Migration to re-encrypt any plaintext values. |
| R8: Domain model has no password column (feature doesn't apply) | F2 | KNOWN | LOW | F2 explicitly excludes Domain from scope. Verified from migration files. No code change needed. |
| R9: `activity_log` module not registered in module_role_permissions | F5 | MEDIUM | LOW | If no `activity_log` module exists, default to always including activity data. |
| R10: Security Officer/Super Admin role slugs unknown | F1 | MEDIUM | LOW | NEEDS REVIEW — check database for role slugs. Fallback: check privilege matching 'security' in name. |

---

## 5. Effort Summary

| Feature | MVP | Production | Migrations | Risk Level | Blocked By |
|---------|-----|-----------|------------|------------|------------|
| P1: activity_log index | 30 min | 30 min | 1 | LOW | Nothing |
| P2: User relationships | 15 min | 15 min | 0 | LOW | Nothing |
| **F2: Auto-Copy** | **2 days** | **3 days** | **0** | MOD | Nothing |
| **F1: Offboarding** | **3 days** | **5 days** | **0** | MOD | P2 |
| **F3: Renewal Dashboard** | **5 days** | **7 days** | **0** | LOW | Nothing |
| **F4: Quick Provision** | **5 days** | **10 days** | **0** | MED | Nothing (F2 recommended) |
| **F5: Security Widget** | **5 days** | **7 days** | **1** | MOD | P1 |

**Total MVP effort: 4 weeks (20 working days) for ALL 5 features, 2 migrations/relationship changes.**
**Total production effort: 6 weeks (32 working days) for ALL 5 features with full hardening.**

---

## 6. Safe Implementation Order

### Phase 1: Foundation (Day 1)

```
Order: P1 → P2 (parallel, independent)
Effort: 45 minutes total

P1: activity_log.created_at index
  → php artisan make:migration add_created_at_index_to_activity_log
  → php artisan migrate
  → Verify: DB::table('activity_log')->whereDate('created_at', '>=', now())->explain();
  → Shows 'index' in possible_keys

P2: User model relationships
  → Add assignedAssets() and activities() to app/Models/User.php
  → Verify: $user->assignedAssets()->count() returns correct count
  → Verify: $user->activities()->count() returns correct count
```

### Phase 2: Quick Wins (Day 1-5)

```
Order: F2 → F1 (parallel, independent)
Effort: 5 days total combined
Parallelization: YES — different code owners can work simultaneously

F2: Service-Credential Auto-Copy (Days 1-2)
  → Add copy/reveal password component
  → Modify 4 service detail views (hosting, vps, voip, other_services)
  → Add activity logging for copy/reveal actions
  → Add rate limiting (30/min)
  → Deploy: Day 2

F1: Offboarding Checklist (Days 3-5)
  → Add OffboardingChecklist Livewire component
  → Add to User detail view
  → Implement 5 count queries
  → Implement Suspend action with rate limiting (5/hour)
  → Add activity logging for suspend
  → Deploy: Day 5
```

### Phase 3: Data Visibility (Day 6-10)

```
Order: F3 (independent, can run parallel with Phase 2)
Effort: 5 days
Parallelization: YES — if team has 2+ developers, F3 starts Day 1 alongside F1/F2

F3: Renewal Dashboard (Days 6-10, or Days 1-5 parallel)
  → Replace expiry tracker index view with inline dashboard
  → Implement loadMorph eager loading pattern
  → Implement filter/sort/aggregate
  → Implement Renew action with confirmation
  → Add CSV export
  → Deploy: Day 10
```

### Phase 4: Creation Workflow (Day 11-15)

```
Order: F4
Effort: 5 days
Parallelization: NO — F4 should follow F2 to reuse credential UX patterns

F4: Quick Provision (Days 11-15)
  → Create ProvisionController
  → Build dynamic form (service type selector + dynamic fields)
  → Implement credential section (reuse F2 password patterns)
  → Implement transaction management
  → Deploy: Day 15
```

### Phase 5: Security Visibility (Day 16-20)

```
Order: F5
Effort: 5 days
Parallelization: YES (runs alongside F4 if team has 2+ developers)
Note: MUST come AFTER P1 (the index migration)

F5: Security Recent Events Widget (Days 16-20)
  → Create SecurityTimeline Livewire component
  → Implement merge query (LoginAudit + ActivityLog)
  → Implement filters and time range
  → Add auto-polling (60s)
  → Deploy: Day 20
```

---

## 7. Parallelization Opportunities

| Track A (Frontend-heavy) | Track B (Backend-heavy) | Week |
|--------------------------|------------------------|------|
| F2: Auto-Copy (Days 1-2) | P1: Index migration (Day 1, 30min) | W1 |
| F1: Offboarding (Days 3-5) | P2: Model relationships (Day 1, 15min) | W1 |
| F3: Dashboard UI (Days 6-10) | F3: Controller/queries (Days 6-10) | W2 |
| F4: Form UI (Days 11-15) | F4: Transaction/validation (Days 11-15) | W3 |
| F5: Timeline UI (Days 16-20) | F5: Merge queries/polling (Days 16-20) | W4 |

**With 2 developers: ALL 5 features complete in 4 weeks.**
**With 3 developers: COMPRESS to 3 weeks (F2 + F3 in parallel during W1).**

---

## 8. Critical Path

```
P1 (30min) ─── F5 (5 days) ──── Day 20
                  │
P2 (15min) ─── F1 (3 days) ───── Day 5
                  │
F2 (2 days) ──────┐
                  ├── F4 (5 days) ── Day 15
                  │
F3 (5 days) ──────┘
```

**Critical path: P1 → F5 = 20 days** (longest chain).
BUT: F5 can be deferred to Week 4 without blocking any other feature. The ACTUAL critical path for the first 3 features (F1, F2, F3) is:

**P2 → F1 = 5 days OR F2 = 2 days OR F3 = 5 days** — all independent, all parallel.

**Result: The first 3 features ship in Week 1-2 regardless. The critical path only matters for F5.**

---

## 9. Deployment Plan

| Feature | Can Ship Independently? | Rollback Method | Deployment Window |
|---------|------------------------|-----------------|-------------------|
| P1: Index | YES | `php artisan migrate:rollback` | Any time |
| P2: Relationships | YES (model change only) | Revert commit | Any time |
| F2: Auto-Copy | YES | Feature flag | Day 2 |
| F1: Offboarding | YES | Feature flag | Day 5 |
| F3: Dashboard | YES | Route revert | Day 10 |
| F4: Quick Provision | YES | Route + nav link revert | Day 15 |
| F5: Security Widget | YES (requires P1) | Feature flag | Day 20 |

**Every feature can ship independently.** No feature blocks another from shipping. The dependency graph is about IMPLEMENTATION ORDER (for code reuse and testing efficiency), not about DEPLOYMENT ORDER.

---

## 10. NEESD REVIEW Items Register

The following items could not be verified from existing source code and require investigation by the implementation team before work begins:

| # | Item | Affected Feature | Question |
|---|------|-----------------|----------|
| NR1 | Activity Log module registration | F5 | Is there an `activity_log` module in the `modules` table and `module_role_permissions` table? If not, the permission check for activity_log cannot use `canOnModule()`. |
| NR2 | Security Officer role slug | F1 | What is the exact role slug or privilege slug for the Security Officer role? Must check database `roles` and `privileges` tables. |
| NR3 | Existing password data format | F2 | Are existing `password` values in `hostings`, `vps`, `voip`, `other_services` tables encrypted with Laravel's `encrypted` cast, or are they plaintext? A mismatch will cause decryption failures. |
| NR4 | VaultEntry.module_id requirement | F4 | Does `VaultEntry` require a `module_id` for permission filtering? If vault entries are scoped to modules (for `canOnModule` checks on vault module), then Quick Provision must set `module_id` from the service module. |
| NR5 | Policy class existence | F1, F2, F3, F4, F5 | Is there a `UserPolicy` or any other Policy class? The codebase has no `app/Policies` directory based on initial review, but custom package policies may exist. |
| NR6 | AuthServiceProvider gates | ALL | Are there any registered Gates in `AppServiceProvider` or `AuthServiceProvider`? The `@can` Blade directive will fail silently if no Gate is registered. |
| NR7 | Domain password column | F2 | Confirmed: Domain model has NO `password` column. F2 does NOT apply to Domain. No action needed — documented for awareness. |
| NR8 | activity_log causer_id index | F5 | Does `activity_log` have an index on `(causer_id, causer_type)`? The Spatie migration creates `log_name` index only. Need to verify and potentially add index. |
| NR9 | Login audit created_at index | F5 | Does `login_audits` have an index on `created_at`? The migration shows it's indexed (`$table->index('created_at')`). Confirmed. |
| NR10 | Scheduled tasks/queues for offboarding | F1 | Does the system use queues or scheduled tasks? The suspension action is synchronous (not queued). Confirmed from existing user suspension code. |

---

## 11. Summary: The Complete Engineering Contract

### What ships

| Day | Feature | Effort | Migrations | Code Change |
|-----|---------|--------|------------|-------------|
| 1 | P1 + P2 | 45 min | 1 index | 2 model relationships |
| 2 | F2: Auto-Copy | 2 days | 0 | 4 views modified, 1 component |
| 5 | F1: Offboarding | 3 days | 0 | 1 component, 1 view modified |
| 10 | F3: Dashboard | 5 days | 0 | 1 view replaced, 1 component |
| 15 | F4: Quick Provision | 5 days | 0 | 1 controller, 1 view, 2 routes |
| 20 | F5: Security Widget | 5 days | 0 (index done day 1) | 1 component, 1 view modified |

### What does NOT ship

The following are explicitly OUT OF SCOPE for this implementation wave:
- Full Offboarding Dashboard (with revoke/reassign/check-in actions) — requires `credential_user` pivot table
- Full Provisioning Wizard (6-step wizard with data flow) — requires `credential_service` pivot table
- Full Security Timeline (with anomalous event detection) — requires ML/correlation logic
- Full Renewal Automation (auto-renew, vendor comparison) — requires approval workflow
- Navigation redesign (label renames, group reorganization) — separate workstream
- Any data model changes beyond the `activity_log.created_at` index

### What must NOT break

Every existing test suite must PASS after all 5 features are deployed:
- LoginAudit CRUD
- ActivityLog CRUD
- ExpiryTracker CRUD
- Hosting/Domain/VPS/VoIP/OtherService CRUD
- VaultEntry CRUD
- Task CRUD
- Asset CRUD
- User CRUD (including suspension)
- Module permission checks for all existing features
- Existing role-based access control

### Acceptance gate

**Feature freeze is LIFTED on Day 21.** All 5 features must pass their respective acceptance criteria before the freeze lifts. Any feature that fails acceptance is deferred to v1.2 and the relevant feature flag remains `false` in production.
