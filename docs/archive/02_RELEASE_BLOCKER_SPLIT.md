# RELEASE BLOCKER SPLIT — Architecture vs Production

## Strict Classification

### A. Must Fix Before Deployment

| Item | Why | Effort |
|------|-----|--------|
| **DemoDataSeeder production guard** | Code does not protect itself. Contradictory deployment docs tell operators to `--seed`. Defense-in-depth requires code fix. | 1 line |
| **Update deployment documentation** | Remove all `--seed` references from production docs. | 5 files, 15 minutes |

**Total: 2 items, ~30 minutes**

---

### B. Must Document Before Deployment

| Item | What to Document |
|------|------------------|
| API show/update/destroy uses user_id, not module_id | Known limitation. No frontend impact. V1.1 fix. |
| Module slugs must not be changed or deleted | Code-level coupling. 18+ locations depend on hardcoded slugs. |
| user_id is creator metadata, not ownership | Do not accept from input. Do not use for permission decisions. |
| Null module_id creates invisible records | Do not delete modules with active records. firstOrFail in v1.1. |
| 15 hidden business rules | Create BUSINESS_RULES.md. |
| Seeder production restriction | Document that `--seed` is for local development only. |

**Effort: ~2 hours for documentation**

---

### C. Can Ship As Known Limitation

| Item | Rationale |
|------|-----------|
| API show/update/destroy user_id inconsistency | Zero frontend consumption. Only affects future API consumers. |
| Web show/update/destroy userOwnedFilter | Same pattern as API — but Web is the only UI. Users see consistent behavior. |
| Module CRUD allows slug changes/deletion | Super-admin only. Documented warning suffices for v1.0. |
| Silent null module_id for super-admin | Super-admin only. Slug resolution is stable. firstOrFail is v1.1 improvement. |
| user_id in fillable | Known regression risk. Documented in BUSINESS_RULES.md. |

---

### D. v1.1 Technical Debt

| Item | Priority | Effort |
|------|----------|--------|
| firstOrFail in all 10 Web controllers' store() | High | 1 hour |
| firstOrFail in all 10 Web controllers' index()/create() | High | 1 hour |
| API show/update/destroy align to module-scoped | High | 1 day |
| ModuleSlug backed enum/singleton to eliminate hardcoded string arrays | Medium | 3 days |
| Duplicate hardcoded slug arrays consolidated into single registry | Medium | 2 days |
| ModulePolicy preventing slug changes and deletion | Medium | 4 hours |
| created_by migration (separate from user_id) | Low | 2 days (schema change) |
| user_id removed from $fillable (after created_by migration) | Low | 1 day |
| module_id NOT NULL constraint on business tables | Medium | 1 day (needs data cleanup) |
| All 13 models declare foreign key references to modules table | Medium | 1 day |

---

### E. Wontfix

| Item | Rationale |
|------|-----------|
| ServiceProvider polymorphic normalization | Too invasive for current architecture. No production impact. |
| Module → Feature hierarchy refactor (feature_id nullable) | Works as-is. Feature-module decoupling is not urgent. |
| Naming conventions rewrite (camelCase vs snake_case) | Cosmetic. No functional impact. |

---

## Correction from Prior Reports

| Prior Classification | Corrected Classification | Reason for Change |
|---------------------|--------------------------|-------------------|
| BLOCK RELEASE: API show/update/destroy inconsistency | **C — Known Limitation** | API has zero frontend consumers. See F2 validation. |
| BLOCK RELEASE: Null module_id ghost record | **C — Known Limitation** | Slug resolution is stable. Super-admin only. FirstOrFail is improvement, not blocker. |
| BLOCK RELEASE: Seeder production guard | **A — Must Fix** | Confirmed. Code + docs + defense-in-depth all require this. |
| BLOCK RELEASE (FINAL_GO_NO_GO): Multiple items | Corrected to: **A=2 items, B=5 items, C=3 items** | Proper separation applied. |

---

## Net Release Decision Criteria

**Release is blocked ONLY if:**
1. DemoDataSeeder guard is not fixed (A1)
2. Deployment docs are not updated (A2)

**Release can proceed with:** B (documentation) done before deployment, C (limitations), D (debt), E (wontfix) as-is.

**Revised recommendation:** CONDITIONAL GO — fix A1+A2, write B, ship C+D+E as v1.1 roadmap.
