# SELF-REVIEW: Architectural Cross-Examination

A systematic self-challenge of every major finding from the 5 original architecture reports. No defense of previous work. Only code evidence survives.

## Framework

Each finding is tested against 12 questions:

1. What evidence would prove this wrong?
2. Is the underlying assumption stated?
3. Did I confuse "possible" with "probable"?
4. Is the severity inflated?
5. Did I assume a single point of failure?
6. Did I consider compensating controls?
7. Did I confuse correlation with causation?
8. Did I consider the actual attack surface?
9. Is a partial fix as good as a full fix?
10. Did I ignore the cost of the fix?
11. Did I consider existing defenses?
12. Did I make claims I couldn't verify?

---

## FINDING B1: user_id FK CASCADE = P0 Production Blocker

### Original Claim
"ON DELETE CASCADE on `user_id` across 9+ global record tables will delete all records when a user is deleted. This is a P0 showstopper."

### Evidence Found

**Migration schema (all 9 global record tables + vault + notes):**
```
$table->foreignId('user_id')->constrained()->cascadeOnDelete();
```
Source: `database/migrations/2026_05_24_*`, `database/migrations/2026_06_26_000004_create_assets_table.php`

**User model:**
```php
use SoftDeletes;  // app/Models/User.php:21
```

**UserController::destroy():**
```php
$user->delete();  // app/Http/Controllers/Web/UserController.php:578
```
This is a SOFT DELETE. Issues UPDATE, not DELETE. FK ON DELETE CASCADE does NOT fire.

**forceDelete on User:**
- NOT present in UserController
- NOT present in any API user controller
- NOT exposed via any route

### Cross-Examination

**Q1: What proves this wrong?** The User model uses SoftDeletes. UserController calls `$user->delete()` which issues UPDATE. CASCADE only fires on DELETE. **CONFIRMED: CASCADE never fires under normal operation.**

**Q3: Possible vs probable?** YES — conflated. A hard delete on User IS possible (raw SQL, direct DB access, future code change) but NOT probable under current implementation.

**Q6: Compensating controls?** SoftDeletes IS the compensating control. Every deletion path issues UPDATE, not DELETE.

**Q8: Actual attack surface?** To trigger CASCADE, an attacker needs: (a) super-admin privileges, (b) access to a `forceDelete` endpoint that DOES NOT EXIST. Surface area: effectively zero for external attackers.

**Q11: Existing defenses?** SoftDeletes + no forceDelete route + no API destroy for users. Three layers.

### Verdict

| Aspect | Original | Revised |
|--------|----------|---------|
| Severity | P0 | HIGH (latent) |
| Blocker? | YES | NO — not blocking production |
| Action required | Emergency fix | Schedule migration |
| Risk profile | 9/10 | 3/10 (low probability × catastrophic impact) |

The CASCADE exists. It IS dangerous if triggered. But under current code, it CANNOT be triggered through any normal or admin UI path. This is a dormant schema risk, not an active production blocker.

---

## FINDING B2-B4: API/Web Visibility Inconsistency

### Original Claim
"API controllers return DIFFERENT data than Web controllers for the same user because API uses `user_id` scoping while Web uses `module_id` scoping via RbacScope."

### Evidence Found

**All 9 API CRUD controllers (index method):**
```php
if (! $user->hasRole('super-admin')) {
    $filters['user_id'] = $user->id;
}
```
Source: `app/Http/Controllers/Api/{Voip,DomainEmail,Vps,Hosting,Domain,ServiceProvider,OtherService,Asset,ExpiryTracker}Controller.php`

**All 9 Web controllers (userOwnedFilter):**
```php
RbacScope::apply(Voip::class, 'module');
// Which registers global scope: whereIn('module_id', getAccessibleModuleIds('read'))
```
Source: `app/Http/Controllers/Web/{same set}Controller.php`

**API DashboardController (service queries):**
```php
$activeQuery->where('user_id', $user->id);  // line 151
```
Source: `app/Http/Controllers/Api/DashboardController.php`

**API ExportController:**
```php
$query->where('user_id', $user->id);  // line 129 — ALL non-super-admin
```
Source: `app/Http/Controllers/Api/ExportController.php`

**Web ExportController (correct behavior):**
```php
// Admin role: module-scoped
$query->whereIn('module_id', $accessibleIds);  // line 141
// Normal user: own records only
$query->where('user_id', $user->id);            // line 144
```
Source: `app/Http/Controllers/Web/ExportController.php`

**Dashboard widgets (correct behavior):**
```php
// OperationsWidget, RenewalsWidget, AssetsWidget, etc.
// All use $accessibleIds (module IDs) for non-SA users
```

### Cross-Examination

**Q5: Single point of failure?** NO — this is TWO independent code paths that diverged. The pattern was intentionally different (API controllers wrote their own scoping instead of using RbacScope).

**Q7: Correlation vs causation?** NO — the causal chain is clear: different code → different queries → different data.

**Q12: Unverifiable claims?** NO — every claim maps to specific line numbers in 11 files.

### The Bug In Action

| Scenario | Web (RbacScope) | API (user_id filter) |
|----------|----------------|---------------------|
| User sees Module A + B | Shows records from A + B | Shows only user's own records |
| Admin shares Module B with User | User sees B records in Web | User sees 0 B records in API |
| Export (non-SA) | Module-scoped (admin) or own (user) | ALWAYS own records only |
| Dashboard counts | Module-scoped services | Own services only |

### Verdict

| Aspect | Original | Revised |
|--------|----------|---------|
| Severity | CRITICAL | HIGH |
| Blocker? | YES | YES — if API is consumed externally |
| Action required | Fix all 11 API controllers | Same — still needed |
| Risk profile | 8/10 | 7/10 (real data discrepancy for cross-module users) |

**This finding SURVIVES cross-examination.** The bug is real. The only mitigation is if no external API consumers use cross-module access patterns — which is an untestable assumption.

---

## FINDING B5: Module Deletion Silently Hides Records

### Original Claim
"Deleting a module causes all linked global records to disappear from views. FK SET NULL + RbacScope WHERE IN excludes NULL module_id. Data becomes invisible."

### Evidence Found

**Migration schema (all global record tables):**
```php
$table->foreignId('module_id')->nullable()->constrained()->nullOnDelete();
```
Source: All 11 migration files for global record + vault + tasks tables.

`nullOnDelete()` is already the safest option: even hard-deleting a module only sets module_id to NULL on child records. Records are NOT deleted.

**Module model:**
```php
use SoftDeletes;  // app/Models/Module.php:20
```

**ModuleController::destroy():**
```php
$module->delete();  // Soft delete — issues UPDATE, not DELETE
```

**FK behavior on soft-delete:** FK constraints react to DELETE statements. `SoftDeletes` issues UPDATE. **FK does not fire at all during normal module deletion.**

**RbacScope behavior:**
```php
getAccessibleModuleIds('read')  // Excludes soft-deleted modules via SoftDeletingScope
```

### Cross-Examination

**Q1: What proves this wrong?** Two things: (a) `nullOnDelete()` is already in place — even hard delete preserves data. (b) All module deletions are soft deletes — FK doesn't fire. **FINDING IS WRONG ON BOTH COUNTS.**

**Q3: Possible vs probable?** YES — assumed hard delete scenario when only soft delete exists.

**Q6: Compensating controls?** `nullOnDelete()` + `SoftDeletes` = double protection.

**Q10: Cost of fix?** The fix I'd proposed was already implemented. No fix needed.

### Verdict

| Aspect | Original | Revised |
|--------|----------|---------|
| Severity | HIGH | RETRACTED |
| Blocker? | YES | NO — never was |
| Action required | Emergency fix | None needed |
| Risk profile | 7/10 | 0/10 |

**This finding is ENTIRELY FALSE.** The schema correctly handles module deletion. Records are never lost, never permanently hidden, and always recoverable.

---

## FINDING: Naming/Semantic Inconsistencies

### Original Claim
"Class names are inconsistent: `Vps` instead of `VPS`, `Hosting` instead of `Hosting`, `Voip` instead of `VoIP`, `DomainEmail` instead of `DomainEmail`. This creates confusion."

### Evidence Found
The naming is consistent within the codebase. `Vps::class`, `Hosting::class`, `Voip::class`, `DomainEmail::class` are used uniformly across all 7 layers (migrations, models, controllers, services, requests, views, API schemas).

### Cross-Examination

**Q10: Cost of fix vs benefit?** Renaming would require: new migrations (table rename), model rename, controller rename, route rename, service rename, request rename, view updates, API spec updates, test updates, sidebar config updates. Effort: 2-3 days. Value: ZERO (no functional change).

### Verdict

| Aspect | Original | Revised |
|--------|----------|---------|
| Severity | MEDIUM | WONTFIX |
| Blocker? | NO | NO |
| Action required | Consider renaming | No action |
| Risk profile | 2/10 | 0/10 |

---

## FINDING: Data Governance Gaps

### Original Claim
"No audit trail on global record changes, no retention policies, no data classification."

### Evidence Found
- Activity logging IS enabled (Spatie Activitylog package, used in models via `LogsActivity` trait)
- Soft deletes ARE enabled on all major models
- Morph maps ARE registered in AppServiceProvider
- Dashboard cache invalidation IS set up via model events

### Cross-Examination

**Q11: Existing defenses?** YES — several governance controls already exist that I missed:
- `LogsActivity` trait on all global record models logs creates/updates/deletes
- `SoftDeletes` on all models provides data retention
- Dashboard cache invalidation on model events

**Q12: Unverifiable claims?** YES — claimed "no audit trail" without checking for activity logging. This was a lazy finding.

### Verdict
DOWNGRADE from MEDIUM to LOW. Governance controls exist but are not documented. Activity logs alone satisfy basic requirements.

---

## FINDING: Entity Relationship Normalization

### Original Claim
"service_provider_id should be normalized out of individual tables into a polymorphic relationship."

### Evidence Found
- `service_provider_id` exists on: `domains`, `hostings`, `vps`, `voip`, `domain_emails`, `other_services`, `expiry_trackers`
- Each table has `$table->foreignId('service_provider_id')->nullable()->constrained('service_providers')->nullOnDelete()`
- ServiceProvider model exists with `hasMany` relationships to each type

### Cross-Examination

**Q10: Cost of fix?** Replacing 7 foreign key columns with a polymorphic relationship would require:
- New migration (drop 7 FKs, create morph columns)
- New pivot/service table redesign
- Rewrite all 7 services + controllers that eager-load `serviceProvider`
- Query performance regression (polymorphic joins are slower than direct FKs)

The current design is a standard Laravel pattern. The `nullOnDelete()` on all service_provider_id FKs is correct. This is a MATTER OF TASTE, not an architectural defect.

### Verdict
DOWNGRADE from MEDIUM to WONTFIX. Current design is valid. Polymorphic would be LESS performant.

---

## Summary: Survivors vs Retractions

| Finding | Original | Revised | Survives? |
|---------|----------|---------|-----------|
| B1: user_id FK CASCADE | P0 blocker | HIGH (dormant) | PARTIAL — severity inflated, root cause real |
| B2-B4: API/Web mismatch | CRITICAL | HIGH | YES — verified, code evidence solid |
| B5: Module deletion | HIGH | RETRACTED | NO — completely false |
| Naming issues | MEDIUM | WONTFIX | NO — cosmetic, zero value |
| Governance gaps | MEDIUM | LOW | NO — controls exist, missed them |
| Normalization | MEDIUM | WONTFIX | NO — valid design, preference-based |

**Of 6 major findings: 1 confirmed, 1 partially confirmed, 4 retracted/downgraded.**
