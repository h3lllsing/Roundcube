# CROSS-MODULE CONSISTENCY RULES

> For every cross-module data relationship: what must remain consistent, how to enforce it,
> and what to do when consistency breaks.

---

## RULE 1: Service Name → Vault Entry Service Name

### Current state
- D1 in DUPLICATE_DATA_ANALYSIS.md
- Two storage locations, NO FK, NO sync, NO detection
- Drift: guaranteed

### Rule
```
SR1: vault_entry.service_name MUST match the service model name
     for all vault entries that represent a service credential.
```

### Enforcement

| Phase | Mechanism | Implementation |
|-------|-----------|----------------|
| Creation (F4) | Auto-fill from service name | Quick Provision form sets `vault_entry.service_name = $service->name`. Non-editable. |
| Creation (vault UI) | Auto-suggest from service list | Vault creation form includes "Link to existing service" dropdown. Selecting auto-fills service_name. |
| Update (service rename) | Observer | When service.name changes, update ALL vault entries linked via credential_service pivot. |
| Detection | Scheduled drift report | Weekly Artisan command: `vault_entry:check-service-names` — compares vault_entry.service_name against linked service.name. Reports mismatches. |
| Correction | One-click sync | Drift report includes "Sync now" button that updates vault_entry.service_name from service.name. |

### What to do when broken

1. User discovers mismatched name: shows inline warning on vault entry detail: "Linked service renamed. Update name?"
2. Report shows 30+ mismatches: run bulk sync command
3. Investigation needed: `SELECT ve.id, ve.service_name, s.name FROM password_vault ve LEFT JOIN hostings s ON ... WHERE ve.service_name != s.name`

---

## RULE 2: Service Name → Expiry Tracker Name

### Current state
- D2 in DUPLICATE_DATA_ANALYSIS.md
- Two storage locations, polymorphic link EXISTS but name is NOT derived from it

### Rule
```
SR2: expiry_tracker.name MUST match the trackable service name
     for all active expiry trackers.
```

### Enforcement

| Phase | Mechanism | Implementation |
|-------|-----------|----------------|
| Creation (F4) | Auto-fill from service name | Quick Provision form sets `expiry_tracker.name = $service->name`. Include in F4 scope. |
| Update (service rename) | Observer | When service.name changes, update linked expiry_tracker.name. |
| Renewal Dashboard (F3) | Inline display | Dashboard shows `trackable.name` directly, ignoring `expiry_tracker.name`. Prevents stale data display. |
| Detection | Dashboard orphan check | Dashboard highlights rows where `expiry_tracker.name != trackable.name` with amber warning. |

### What to do when broken

1. Dashboard shows amber warning next to mismatched name
2. Click warning → "Sync name from service" button
3. If user intentionally set a different name (e.g., "Acme Web (Production)"), dismiss warning with "Keep custom name"

---

## RULE 3: Service Password → Vault Entry Password

### Current state
- D3 in DUPLICATE_DATA_ANALYSIS.md — THE CRITICAL DUPLICATION
- 7 storage locations, NO FK, NO sync, NO detection
- This is the most consequential drift in the system

### Rule
```
SR3a (near-term): Service inline password and vault entry password
                  MUST be linked via a credential_service pivot table.
                  One credential, one source of truth.

SR3b (long-term): All service passwords MUST be stored in password_vault.
                  Service model password columns MUST be REMOVED.
                  password_vault becomes the SINGLE credential store.
```

### Enforcement (short-term)

| Phase | Mechanism | Implementation |
|-------|-----------|----------------|
| Creation (Quick Provision) | Store in BOTH locations | F4 creates both service.password AND vault_entry.encrypted_password in a single transaction. They are initially identical. |
| Update (service password change) | Warning | When user changes service.password, show: "You also have a vault entry for this service. Update vault entry too?" |
| Update (vault entry password change) | Warning | When user changes vault entry password, show: "This vault entry is linked to a service. Update service password too?" |
| Detection | Weekly drift report | Artisan command comparing service password hash vs vault entry for linked records. |

### Enforcement (long-term — v2)

1. Add `password_vault.service_id` polymorphic FK (or `credential_service` pivot)
2. Mark service model `password` column as DEPRECATED
3. Migrate all existing inline passwords to vault entries
4. Remove `password` columns from all service models
5. All password CRUD goes through vault module

### What to do when broken

Short-term: Show "Passwords differ from vault entry" warning on service detail page.
Long-term: Impossible — single credential store eliminates drift.

---

## RULE 4: Service Cost → Expiry Tracker Cost

### Current state
- D4 in DUPLICATE_DATA_ANALYSIS.md
- Expiry_tracker.cost is a copy of service cost with NO sync

### Rule
```
SR4a: expiry_tracker.cost SHOULD match trackable service cost.
      When service cost changes, expiry_tracker should be updated.

SR4b: If user intentionally sets a DIFFERENT cost on expiry tracker
      (e.g., renewal cost differs from subscription cost),
      the difference MUST be clearly labeled.
```

### Enforcement

| Phase | Mechanism | Implementation |
|-------|-----------|----------------|
| Creation | Auto-fill from service | Quick Provision (F4) copies service cost to expiry_tracker. |
| Detection | Dashboard warning | Renewal Dashboard (F3) highlights rows where cost differs from trackable: "Cost differs from service record." |
| Correction | "Sync from service" button | One-click update to match service cost. |
| Update (service cost change) | Observer | When service.cost changes, check linked expiry_tracker. If expiry_tracker.cost matches OLD service cost, auto-update to NEW service cost. If user manually edited expiry_tracker.cost, show notification instead. |

### What to do when broken

1. Dashboard shows cost with amber icon if mismatch detected
2. User can choose to sync or keep custom cost
3. If custom cost kept, ExpiryTracker records `cost_overridden_at` timestamp + `cost_overridden_by` user ID

---

## RULE 5: Service Expiry Date → Expiry Tracker Expiry Date

### Current state
- D5 in DUPLICATE_DATA_ANALYSIS.md
- Expiry_tracker.expiry_date has fallback accessor — hidden drift

### Rule
```
SR5: expiry_tracker.expiry_date MUST NOT be independently settable.
     The true source of expiry date is the SERVICE MODEL.
     expiry_tracker MUST display the service expiry date as READ-ONLY.
```

### Enforcement

| Phase | Mechanism | Implementation |
|-------|-----------|----------------|
| Model | Remove independent expiry_date | Drop `expiry_trackers.expiry_date` column. The accessor that reads from `trackable.expiry_date` becomes the ONLY source. |
| Model | Keep `renewal_date` | `expiry_trackers.renewal_date` remains independently settable (stores when the renewal was last processed). |
| Renewal Dashboard | Display `trackable.expiry_date` | Dashboard reads expiry date directly from the service model through the morph. |
| Renew action (F3) | Update service expiry_date | The "Renew" button extends the SERVICE MODEL's expiry_date, not the expiry_tracker's. Expiry_tracker just stores the renewal_date. |

### What to do when broken (pre-migration)

If expiry_tracker.expiry_date column cannot be dropped immediately:
1. Query: `SELECT * FROM expiry_trackers WHERE expiry_date != trackable.expiry_date`
2. Display warning on tracker detail: "Expiry date differs from linked service"
3. Offer "Sync from service" button

---

## RULE 6: Module Permissions (Designed Override)

### Current state
- D8 in DUPLICATE_DATA_ANALYSIS.md
- ModuleRolePermission + UserModulePermission is a DESIGNED override pattern

### Rule
```
SR6: UserModulePermission ALWAYS takes priority over ModuleRolePermission.
     The resolution order is:
     1. Check UserModulePermission for (user_id, module_id, action)
     2. If set (not null), return value
     3. Fall back to ModuleRolePermission for (user_role_id, module_id, action)
     4. If set, return value
     5. Return false (default deny)
```

### Verified from source

The `HasModulePermissions@canOnModule()` trait implements exactly this order. Source confirmed.

### Enforcement

No changes needed — the runtime resolution in the trait is correct. The potential issue is:
- If a user has `can_reveal = false` via UserModulePermission but `can_reveal = true` via ModuleRolePermission, the user gets `false`. CORRECT.
- If a user has `can_reveal = null` (not set) via UserModulePermission but `can_reveal = true` via ModuleRolePermission, the user gets `true`. CORRECT (falls back to role).

### Drift detection

Not needed — runtime resolution handles the two sources correctly.

---

## RULE 7: User Email → Login Audit Email (Designed Divergence)

### Current state
- D6 in DUPLICATE_DATA_ANALYSIS.md
- Login audit stores a snapshot of email at login time

### Rule
```
SR7: login_audits.email MUST preserve the email AS IT WAS at login time.
     User email changes MUST NOT retroactively update login audit records.
     Login audit records are immutable after creation.
```

### Enforcement

- LoginAudit is READ-ONLY via the `login-audits` module. No user can edit it.
- The email is written once by the authentication system at login.
- No mechanism exists to update historic login audit emails.

### Drift detection

Not needed — divergence is intentional. If email forensics is needed, the difference between `users.email` (current) and `login_audits.email` (at login time) provides valuable information about account changes.

---

## CONSISTENCY CHECK: MASTER TABLE

| Rule | Entities | Tables | Enforcement Type | Current Status | Priority |
|------|----------|--------|-----------------|---------------|----------|
| SR1 | Service name → vault service_name | 7+ tables | Observer + auto-fill + drift report | **UNENFORCED** | P1 |
| SR2 | Service name → expiry tracker name | 6+ tables | Observer + auto-fill + dashboard warning | **UNENFORCED** | P2 |
| SR3 | Service password → vault password | 7+ tables | Pivot table + migration to single store | **UNENFORCED** | P0 |
| SR4 | Service cost → expiry tracker cost | 6+ tables | Auto-fill + dashboard warning + notification | **UNENFORCED** | P2 |
| SR5 | Service expiry → expiry tracker expiry | 6+ tables | Drop expiry_tracker column + morph fallback | **UNENFORCED** | P2 |
| SR6 | ModuleRolePermission vs UserModulePermission | 2 tables | Runtime resolution (ALREADY CORRECT) | **ENFORCED** | N/A |
| SR7 | User email → login audit email | 2 tables | Immutable audit record (ALREADY CORRECT) | **ENFORCED** | N/A |

**4 of 7 consistency rules are completely unenforced. The system is accumulating data drift silently.**

---

## RECOMMENDED IMPLEMENTATION ORDER

| Sprint | Rules | Effort | Features Enabled |
|--------|-------|--------|-----------------|
| Current (F4 scope) | SR1 (creation), SR2 (creation), SR4 (creation) | Part of Quick Provision | Stops NEW drift |
| v1.2 | SR1 (observer + pivot) | 3 days | Stops drift from renames |
| v1.2 | SR3 (pivot table) | 2 days | Makes vault↔service relationship explicit |
| v1.2 | SR5 (drop expiry_tracker.expiry_date) | 1 day | Eliminates expiry date drift |
| v2 | SR3 (eliminate inline passwords) | 5-10 days | SINGLE CREDENTIAL STORE |
| v2 | SR4 (observer + drift warning) | 2 days | Monitors cost drift |

**The first 4 consistency rules (SR1-SR4) are directly related to the v1.1 features.** F2 (Auto-Copy) reads inline password — which may be STALE compared to vault entry. F3 (Renewal Dashboard) shows expiry_tracker data — which may be STALE compared to service data. F4 (Quick Provision) is the opportunity to STOP creating new drift.

**Without these consistency rules, every v1.1 feature operates on potentially stale data.**
