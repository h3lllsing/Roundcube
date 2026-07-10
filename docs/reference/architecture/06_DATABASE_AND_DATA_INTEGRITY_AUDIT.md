# DATABASE & DATA INTEGRITY AUDIT

---

## 6.1 MIGRATION HEALTH

| Metric | Value |
|--------|-------|
| Total migrations | 68 |
| Batched migrations | YES |
| Down methods | ✅ ALL PRESENT |
| Pending migrations | 0 (all run) |

### Issues

**M-08: Deferred FK Constraint**

`expiry_tracker_notifications.smtp_profile_id` has a deferred foreign key (`NOT VALID` or similar). This means the constraint is not enforced on insert. If an `smtp_profile_id` references a deleted profile, the notification becomes a dangling reference.

**Impact:** Orphaned notification records. SoftDelete or cascade behavior not enforced.

---

**M-09: Retroactive SoftDeletes**

SoftDeletes trait was added to models after migrations created the tables. The `deleted_at` column was retroactively added:

| Table | SoftDeletes Added? |
|-------|-------------------|
| `users` | Yes (retroactive migration) |
| `assets` | Yes |
| `monitoring` | Yes |
| `help_center_articles` | Yes |
| `help_center_categories` | Yes |
| `expiry_tracker` | Yes |
| `expiry_tracker_notifications` | Yes |

**Impact:** Model/migration temporal coupling. Cannot roll back to pre-SoftDeletes state without data loss. All delete queries now do soft deletes; hard deletes require `forceDelete()`.

---

## 6.2 INDEX ANALYSIS

**Missing indexes on FK columns (suspected):**

| Parent Table | FK Column | Has Index? |
|-------------|-----------|------------|
| `users` | `department_id` | ⚠️ CHECK |
| `assets` | `category_id` | ⚠️ CHECK |
| `assets` | `type_id` | ⚠️ CHECK |
| `assets` | `location_id` | ⚠️ CHECK |
| `assets` | `assigned_to` | ⚠️ CHECK |
| `monitoring` | `assigned_to` | ⚠️ CHECK |
| `monitoring` | `department_id` | ⚠️ CHECK |
| `expiry_tracker` | `assigned_to` | ⚠️ CHECK |
| `help_center_articles` | `category_id` | ⚠️ CHECK |

**Fix:** Run `SHOW INDEX FROM <table>` for each and add indexes where missing.

---

## 6.3 COLUMN TYPE AUDIT

| Column | Type | Suggestion |
|--------|------|------------|
| All IDs | BIGINT UNSIGNED | ✅ Correct for Laravel |
| All timestamps | TIMESTAMP/DATETIME | ✅ Correct |
| `users.email` | VARCHAR(255) | ✅ Correct |
| `users.password` | VARCHAR(255) | ✅ Correct |
| Boolean flags | TINYINT(1) | ✅ Correct |
| Price/amount | DECIMAL(?, ?) | ✅ Correct |

---

## 6.4 SEEDING SAFETY

| Seeder | Safety Concern | Severity |
|--------|---------------|----------|
| `DatabaseSeeder` | Creates test@example.com/password | 🔴 C-03 |
| `DemoDataSeeder` | Hardcoded plaintext passwords in source | 🔴 C-02 |
| `RoleAndPermissionSeeder` | ✅ Clean — uses config/permissions.php | ✅ |
| Other seeders | ✅ Not sensitive | ✅ |

---

## 6.5 DATA INTEGRITY RISKS

| Risk | Severity | Details |
|------|----------|---------|
| Permission race condition | MEDIUM | Concurrent save → last writer wins. Transient denial of privilege. |
| Orphaned notification records | MEDIUM | Deferred FK on smtp_profile_id. |
| Permission cache stale | MEDIUM | 1hr TTL not bumped on override save. |
| Missing module_id validation | MEDIUM | Permission save accepts any module_id without FK check. |

---

## 6.6 FOREIGN KEY CASCADE BEHAVIOR

| FK | On Delete | Status |
|----|-----------|--------|
| User → Department | SET NULL | ✅ |
| Asset → Category/Type/Location | SET NULL | ✅ |
| Asset → User (assigned_to) | SET NULL | ✅ |
| Monitoring → User/Dept | CASCADE/SET NULL | ⚠️ Check per FK |
| Notifications → smtp_profile | NO ACTION | ❌ M-08 |

---

## SUMMARY

| Area | Verdict |
|------|---------|
| Migration completeness | ✅ GOOD |
| Column types | ✅ GOOD |
| Index coverage | ⚠️ NEEDS VERIFICATION |
| FK cascade behavior | ✅ MOSTLY GOOD |
| Seeders | 🔴 2 CRITICAL issues |
| Data integrity | ⚠️ 4 MEDIUM risks |
