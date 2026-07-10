# RELEASE DECISION MATRIX

Evidence-based go/no-go criteria for v1.0 production release.

---

## Current State After Phases 1-3

| Area | Status | Verification |
|------|--------|-------------|
| Phase 1: RbacScope implementation | COMPLETE | Tested, working |
| Phase 2A-D: module_id auto-set + protect | COMPLETE | 18 permission tests pass |
| Phase 3A-C: user_id cleanup (code) | COMPLETE | 18 permission tests pass |
| user_id column in DB | NOT CHANGED | Needs migration |
| API/Web alignment | NOT STARTED | Phase 4 pending |
| Enterprise Architecture Reports | COMPLETE | 5 reports + self-review |
| Self-Cross-Examination | COMPLETE | 4 review files produced |

---

## Remaining Risks After Cross-Examination

### Risk 1: API/Web Data Discrepancy (R1)
**Verified as real.** 11 API controllers use `user_id` scoping. Web uses `module_id` scoping via RbacScope.

**Severity after challenge:** HIGH (not CRITICAL)

**Mitigation if released without fix:**
- Internal API consumers only → low probability of discovery
- External API consumers → guaranteed data discrepancy reports
- Workaround: consumer must implement their own module-aware querying

**Fix scope:** 11 API controllers + service resolver pattern alignment
**Fix effort:** 1-2 days
**Release gate:** CONDITIONAL — only blocks if API has external consumers

### Risk 2: user_id FK CASCADE (R2)
**Dormant. Never fires under normal operation.** Requires forceDelete or raw SQL.

**Severity after challenge:** HIGH (not P0)

**Mitigation if released without fix:**
- No production impact under normal operation
- No admin UI path to trigger it
- Data at risk only if: super-admin gains access to DB console AND runs manual hard delete

**Fix scope:** Migration (nullable column + nullOnDelete or remove FK)
**Fix effort:** 1 day (requires careful migration planning)
**Release gate:** NOT blocking

### Risk 3: Phase 4 incomplete (R3)
Dashboard, Export, and API controllers still use `user_id` for scoping in some paths.

**Severity after challenge:** HIGH for API, LOW for Dashboard/Export

**Release gate:** NOT blocking — Phase 4 is an alignment task, not a bug fix

---

## Release Decision Table

| Question | Answer | Evidence |
|----------|--------|----------|
| Is there an active data loss bug? | NO | All deletions are soft. FK CASCADE is dormant. Module deletion is safe. |
| Is there an active data discrepancy bug? | YES — API vs Web | 11 API controllers use different scoping than Web. Verified. |
| Can we ship with this bug? | depends | If API has external consumers: NO. If API is internal only: YES. |
| Are all tests passing? | YES | 18 permission tests + all feature tests pass. |
| Is RBAC working correctly? | YES | Module+role permission system functions. Phases 1-3 confirmed. |
| Is there any known regression? | NO | No test failures from any phase. |
| Is the user_id DB column blocking? | NO | Column works. Needs migration for removal but doesn't block. |
| Are architecture reports useful? | PARTIAL | 4/6 findings were wrong/overstated. Only API/Web discrepancy is validated. |

---

## Go/No-Go Recommendation

### If releasing TODAY:
**GO — with conditions:**
1. Document known limitation: "API currently scopes by record ownership. Web scopes by module access. These may return different results for cross-module users."
2. Plan Phase 4 for v1.1 (align API controllers to use RbacScope or module-aware services)
3. Schedule user_id migration for v1.1 (make nullable, change FK to nullOnDelete)

### If releasing in 1 week:
**GO — include Phase 4:**
1. Implement `RbacScope::apply()` pattern in all 11 API controllers
2. Update API Dashboard to use module_id scoping for services
3. Update API Export to use module_id scoping for admins
4. Run all tests
5. Document remaining user_id FK migration as v1.1 task

### If releasing in 1 month:
**GO — comprehensive:**
1. Phase 4 (API alignment) — 2 days
2. user_id migration (nullable + nullOnDelete) — 1 day
3. Remove `user_id` from all API $filters (now using module_id via RbacScope) — 0.5 day
4. Comprehensive integration test covering API/Web parity — 1 day
5. Remove all `user_id` route/model references from global records — 1 day

---

## Verdict

**No blocking issue prevents v1.0 release.**

The only validated bug (API/Web data discrepancy) is a CONDITIONAL blocker — it matters only if API has external consumers. If the API is strictly internal, ship now and fix in v1.1.

**The original P0/Critical/HIGH ratings were inflated in 4 of 6 cases.** The self-review corrected this. The system is more production-ready than the original reports suggested.

**Final recommendation:** Ship v1.0 with documented API scoping limitation. Plan Phase 4 for v1.1.
