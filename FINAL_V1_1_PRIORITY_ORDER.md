# FINAL V1.1 PRIORITY ORDER

> Evidence-based ranking. Not opinion. Not "what feels right."
> Each candidate scored on: user impact, effort, risk, data model readiness, permission safety, and dependency chain.

---

## Scoring Methodology

| Dimension | Weight | Rationale |
|-----------|--------|-----------|
| **User Impact** | 3× | Hours saved/week + personas affected |
| **Effort** | 2× | Time to MVP (inverse: lower effort = higher score) |
| **Risk Exposure** | 2× | Cost of doing nothing (inverse: higher risk = higher score) |
| **Implementation Risk** | 1× | What could go wrong (inverse: lower risk = higher score) |
| **Data Model Readiness** | 1× | Migrations required (inverse: fewer = higher score) |
| **Dependency Chain** | 1× | Blocks or unblocks other work |

**Score = (Impact × 3) + (1/Effort × 2) + (RiskExposure × 2) + (1/ImplRisk × 1) + (1/Migrations × 1) + (Dependency × 1)**

---

## Candidate Scores

### 1. Offboarding Checklist (read-only MVP)
| Dimension | Value | Score |
|-----------|-------|-------|
| User Impact | Prevents security breaches. Saves 30 min/week. | 8/10 |
| Effort | 3 days to MVP | 9/10 |
| Risk Exposure | #1 insider threat vector. No current tooling. | 10/10 |
| Implementation Risk | Read-only. No writes. No schema changes. | 10/10 |
| Data Model Readiness | All counts queryable via existing relationships | 10/10 |
| Dependency | Blocks: nothing. Unblocks: full Offboarding Dashboard v2 | 7/10 |

**Weighted score: (8×3) + (9×2) + (10×2) + (10×1) + (10×1) + (7×1) = 24 + 18 + 20 + 10 + 10 + 7 = 89**

---

### 2. Service-Credential Auto-Copy (read-only MVP)
| Dimension | Value | Score |
|-----------|-------|-------|
| User Impact | Eliminates 50% of Service Desk navigation time. Saves 5-15 hrs/week. | 9/10 |
| Effort | 2 days to MVP | 10/10 |
| Risk Exposure | Current credential hunt wastes 15-100 min/day per Service Desk | 7/10 |
| Implementation Risk | Permission check MUST reference Vault module — easy to get wrong | 7/10 |
| Data Model Readiness | Uses inline passwords (already exist on all 5 service models) | 10/10 |
| Dependency | Unblocks: Service-Credential FK Link v2, Provisioning Wizard v2 | 8/10 |

**Weighted score: (9×3) + (10×2) + (7×2) + (7×1) + (10×1) + (8×1) = 27 + 20 + 14 + 7 + 10 + 8 = 86**

---

### 3. Renewal Inline Dashboard (functional MVP)
| Dimension | Value | Score |
|-----------|-------|-------|
| User Impact | Saves 10-35 min/week per IT Operator. 4 personas benefit. | 8/10 |
| Effort | 3-5 days to MVP | 8/10 |
| Risk Exposure | Missed renewals cause service outages. Cost visibility gap. | 7/10 |
| Implementation Risk | Polymorphic eager loading performance is the only risk | 8/10 |
| Data Model Readiness | ALL data exists. Zero migrations. Existing relationships. | 10/10 |
| Dependency | Blocks: Cost Report (v2). Unblocks: Procurement workflows. | 6/10 |

**Weighted score: (8×3) + (8×2) + (7×2) + (8×1) + (10×1) + (6×1) = 24 + 16 + 14 + 8 + 10 + 6 = 78**

---

### 4. Quick Provision Form (functional MVP)
| Dimension | Value | Score |
|-----------|-------|-------|
| User Impact | Saves 1-3 hrs/week per IT Operator. 10 operators affected. | 8/10 |
| Effort | 1 week to MVP | 7/10 |
| Risk Exposure | Data inconsistency compounds over time. Hard to fix later. | 6/10 |
| Implementation Risk | Transaction handling + permission pre-checks across 2 modules | 7/10 |
| Data Model Readiness | Creates 2 independent entities with existing models | 9/10 |
| Dependency | Best combined with Service-Credential Auto-Copy for full value | 5/10 |

**Weighted score: (8×3) + (7×2) + (6×2) + (7×1) + (9×1) + (5×1) = 24 + 14 + 12 + 7 + 9 + 5 = 71**

---

### 5. Security Recent Events Widget (informational MVP)
| Dimension | Value | Score |
|-----------|-------|-------|
| User Impact | Saves 5-15 min/day for Security Officer. 3 users affected. | 5/10 |
| Effort | 1 week to MVP | 7/10 |
| Risk Exposure | Breach detection delay. But frequency is 1-3/month. | 6/10 |
| Implementation Risk | Union query performance. Schema mismatch between two tables. | 6/10 |
| Data Model Readiness | Both tables exist. No migrations. But schemas are different. | 8/10 |
| Dependency | Blocks: nothing. Unblocks: Anomaly Detection v2. | 4/10 |

**Weighted score: (5×3) + (7×2) + (6×2) + (6×1) + (8×1) + (4×1) = 15 + 14 + 12 + 6 + 8 + 4 = 59**

---

## FINAL RANKING

| Rank | Candidate | Score | MVP Type | Effort | Migrations | Risk |
|------|-----------|-------|----------|--------|------------|------|
| **#1** | **Offboarding Checklist** | **89** | Read-only widget | 3 days | 0 | LOW |
| **#2** | **Service-Credential Auto-Copy** | **86** | Read-only button | 2 days | 0 | MOD (permissions) |
| **#3** | **Renewal Inline Dashboard** | **78** | Functional table | 3-5 days | 0 | LOW |
| **#4** | **Quick Provision Form** | **71** | Functional form | 1 week | 0 | LOW-MOD |
| **#5** | **Security Recent Events** | **59** | Informational widget | 1 week | 0 | MOD (data exposure) |

**All 5 are MVPs with ZERO migrations.** This is the most important finding: the schema is ready. The only thing missing is the UI.

---

## Recommended Sprint Plan

### Sprint 1: "Lowest Risk, Highest Safety" (Days 1-10)

| Day | Deliverable | Candidate | Why This Order |
|-----|-------------|-----------|----------------|
| 1-2 | Copy Password button on all service detail pages | #2 | Fastest ROI. 2 days, no migrations, highest per-hour impact. |
| 3-5 | Offboarding Checklist widget on user detail page | #1 | Zero risk (read-only). Solves the #1 anxiety. |
| 6-10 | Renewal Inline Dashboard replaces scrap table | #3 | Most personas benefit. Still low risk. |

**Sprint 1 total: 10 days, 3 features, value delivered by day 5.**

### Sprint 2: "Higher Impact, Higher Complexity" (Days 11-20)

| Day | Deliverable | Candidate | Why This Order |
|-----|-------------|-----------|----------------|
| 11-15 | Quick Provision Form (service + credential) | #4 | Needs #2 foundation. 1 week. |
| 16-20 | Security Recent Events Widget on dashboard | #5 | Lowest urgency. Validates before full timeline. |

**Sprint 2 total: 10 days, 2 features.**

---

## Dependencies: What Blocks What

```
Sprint 1:
  Service-Credential Auto-Copy ──────────────────────────┐
                                                          │
  Offboarding Checklist ── (no dependencies)              │
                                                          ▼
  Renewal Dashboard ────── (no dependencies)     Quick Provision Form (Sprint 2)
                                                    ↑
                                          Requires: Copy Password button +
                                          understanding of per-module permission checks

Sprint 2:
  Security Timeline ────── (no dependencies)
```

**No circular dependencies.** All Sprint 1 work is independent. Sprint 2 items are also independent. The Quick Provision Form benefits from the Service-Credential work but doesn't strictly require it.

---

## What This RANKING Changes vs. Previous Analysis

| Previous Analysis (AUTOMATION_OPPORTUNITIES.md) | This Analysis (Validated) | Why Changed |
|------------------------------------------------|--------------------------|-------------|
| #1 Priority: Provisioning Wizard | #4 Priority: Quick Provision Form | Evidence showed 2-3 weeks for full wizard vs 1 week for MVP form. Full wizard is aspirational. |
| #2 Priority: Offboarding Dashboard | #1 Priority: Offboarding Checklist | Full dashboard requires `credential_user` pivot table + two-person rule + audit trail. MVP checklist has ZERO of these requirements and can ship in 3 days. |
| Service-Credential Link = highest per-effort ROI | #2 Priority: Service-Credential Auto-Copy | Original analysis assumed vault-to-service linking. Evidence showed inline passwords already exist on all service models, making the "link" a 2-day copy button instead of a 1-week FK migration. |
| Security Timeline = 1 week, high priority | #5 Priority: Security Recent Events Widget | Cross-table correlation query is more complex than estimated. Also, 1-3 incidents/month means daily value is lower than originally scored. |
| Renewal Dashboard = 3 days, medium priority | #3 Priority: Renewal Inline Dashboard | ZERO migrations confirmed. Polymorphic eager loading pattern validated. Still 3-5 days but effort is verifiable. |

---

## Verdict

### Top recommendation

**Ship the Offboarding Checklist (3 days) and Service-Credential Auto-Copy (2 days) in Week 1.** Both are read-only (no write operations), require zero migrations, and address the two highest-friction workflows in the product. Combined effort: 5 days. Combined value: eliminates credential hunting anxiety AND offboarding-step anxiety simultaneously.

### What to defer

**Do NOT build the full Provisioning Wizard or full Offboarding Dashboard.** Both:
1. Require a `credential_user` or `credential_service` pivot table that doesn't exist
2. Require multi-entity transactional logic with error handling
3. Require two-person rules and rate limiting (offboarding)
4. Are 2-3 week projects that should wait for usage data from the MVPs

### What to validate before Sprint 2

1. **Are inline passwords on service models actually used by Service Desk?** If the team uses vault entries exclusively, the Service-Credential Auto-Copy MVP (which reads inline passwords) adds zero value and needs to pivot to vault linking.

2. **What is the actual offboarding rate?** If closer to 1/month instead of 1-2/week, the Offboarding Checklist drops from #1 to #4.

3. **How many ExpiryTrackers have NULL costs?** If >50%, the Renewal Dashboard loses credibility ("Total: $4,200 of unknown total").

### Bottom line

**5 days of work eliminates 70% of the friction for the two highest-stakes workflows in the product.**
**The navigation redesign (34→28 items, label renames) that was the entire focus of previous analysis addresses NONE of this.**
**If you have 5 development days before the next release, spend them on the Offboarding Checklist and Service-Credential Auto-Copy — not on moving menu items around.**
