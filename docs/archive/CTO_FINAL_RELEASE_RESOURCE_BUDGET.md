# CTO FINAL RELEASE RESOURCE BUDGET

**Date:** 2026-07-09

---

## Estimated Hours by Category

| Category | P0 Hours | P1 Hours | P2 Hours | Total |
|----------|----------|----------|----------|-------|
| Security fixes | 2.5 | 4 | 3 | 9.5 |
| RBAC/Authorization | 3 | 16 | 8 | 27 |
| Data Integrity | 15 | 14 | 4 | 33 |
| Code Quality | 1 | 3 | 8 | 12 |
| Testing | 0 | 6 | 4 | 10 |
| Deployment | 0 | 10 | 2 | 12 |
| Module-specific labels/forms | 2 | 6 | 4 | 12 |
| **Total** | **23.5** | **59** | **33** | **115.5** |

---

## Recommended Staffing

| Role | Hours | Focus |
|------|-------|-------|
| Senior Backend (RBAC/API) | 40h | Phase 3 authorization, optimistic locking |
| Full Stack | 40h | Phase 1-2 P0 fixes, module forms, labels |
| DevOps | 10h | Phase 4 deployment, cPanel, cron |
| QA | 16h | Phase 5 testing, export tests, verification |
| **Total** | **~106h** | Over 2 sprints |

---

## Key Risk: Single-Developer Dependency

| Area | Risk | Mitigation |
|------|------|------------|
| RBAC internals (HasModulePermissions) | High | Document logic before changes |
| RbacScope + eager loading | Medium | Review with senior dev |
| Optimistic locking pattern | Low | Standard Laravel `updated_at` check |
| cPanel deployment | Medium | Test with staging first |

---

## Budget Verdict

| Sprint | Hours | Team Size | Duration |
|--------|-------|-----------|----------|
| Sprint 1: P0 + P1 blockers | 60h | 2 devs | ~4 days |
| Sprint 2: Remaining P1 + P2 | 55h | 2 devs | ~4 days |
| **Total** | **115h** | **2 developers** | **~10 working days** |

**Recommended:** 2 full-stack developers for 2 sprints (10 days).
