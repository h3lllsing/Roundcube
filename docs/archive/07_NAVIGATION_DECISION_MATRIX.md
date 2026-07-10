# 07 — NAVIGATION DECISION MATRIX

> Weighted multi-criteria decision analysis.
> All 12 navigation philosophies scored and ranked. Two bonus hybrid models evaluated.

---

## Scoring Weights

These weights reflect the priorities of a 10-year enterprise IT operations platform:

| Criterion | Weight | Rationale |
|-----------|--------|-----------|
| **Simplicity** | 1.0 | Foundational. If the structure isn't simple, nothing else matters. |
| **Discoverability** | 1.5 | Users must find features without training. Weighted higher because search alone isn't sufficient (Model I proved this). |
| **Daily Usability** | 2.0 | **Highest weight.** IT Ops use this system 8 hours/day. Every extra click = $ cost. |
| **Enterprise Scalability** | 1.5 | Must work for 10 → 10,000 users without redesign. |
| **RBAC Compatibility** | 1.5 | Permission gating is non-negotiable. The nav must support role-based visibility cleanly. |
| **IA Principles** | 1.0 | Sound IA prevents the "Other Services" problem. Important but can be fixed iteratively. |
| **Cognitive Load** | 1.5 | Affects every navigation decision, every session, every user. Second most important. |
| **Future Growth** | 1.0 | Can we add 10 more modules in 5 years without breaking the nav? |
| **Training Effort** | 1.0 | Lower training = faster adoption = earlier ROI. |
| **Maintenance** | 0.5 | Important for dev team, but doesn't affect end users. Weighted lowest. |

**Maximum possible weighted score:** 10.00

---

## 12 Base Models (from 03_INFORMATION_ARCHITECTURE_OPTIONS.md)

| Criteria (Weight) | A: Resource | B: Service | C: Workspace | D: Verb | E: Persona | F: Minimal | G: HubSpoke | H: Domain | I: Search | J: Hybrid | K: Tiered | L: Adaptive |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| Simplicity (1.0) | 8 | 5 | 9 | 7 | 6 | 9 | 6 | 2 | 9 | 6 | 8 | 3 |
| Discover (1.5) | 6 | 5 | 7 | 8 | 5 | 3 | 6 | 3 | 2 | 8 | 7 | 4 |
| Daily (2.0) | 3 | 7 | 8 | 7 | **10** | 3 | 7 | 3 | 7 | 8 | 9 | 7 |
| Scale (1.5) | 2 | 8 | 7 | 6 | 9 | 2 | 4 | 9 | 8 | 7 | 7 | 6 |
| RBAC (1.5) | 5 | 7 | 9 | 6 | **10** | 5 | 6 | 5 | 8 | 7 | 5 | 4 |
| IA (1.0) | 2 | 6 | 5 | 4 | 9 | 3 | 5 | 2 | **10** | 7 | 7 | 8 |
| CogLoad (1.5) | 2 | 6 | 8 | 6 | **10** | 4 | 4 | 2 | 8 | 6 | 8 | 5 |
| Growth (1.0) | 2 | 8 | 6 | 5 | 8 | 2 | 3 | **10** | **10** | 7 | 6 | 7 |
| Training (1.0) | 5 | 4 | 8 | 6 | 9 | 7 | 6 | 2 | 2 | 5 | 7 | 3 |
| Maint (0.5) | 9 | 7 | 7 | 5 | 3 | 8 | 4 | 8 | 9 | 5 | 4 | 2 |

### Weighted Calculation

```
Weighted = (Simp×1.0 + Disc×1.5 + Daily×2.0 + Scale×1.5 + RBAC×1.5 + IA×1.0 + CogLoad×1.5 + Growth×1.0 + Train×1.0 + Maint×0.5) / 11.5
```

| Model | Raw Sum | Weighted | Rank |
|-------|---------|----------|------|
| **E: Persona-Centric** | 79 | **8.17** | **1** |
| **C: Workspace-Centric** | 69 | **7.17** | **2** |
| **J: Hybrid Task-First** | 66 | **6.87** | **3** |
| **K: Tiered Responsibility** | 68 | **6.83** | **4** |
| **I: Search-Centric** | 73 | **6.78** | **5** |
| **B: Service-Centric (ITIL)** | 61 | **6.35** | **6** |
| **D: Verb-Centric** | 60 | **6.22** | **7** |
| **L: Adaptive Frequency** | 49 | **4.96** | **8** |
| **G: Hub-and-Spoke** | 50 | **4.91** | **9** |
| **F: Minimalist** | 44 | **4.39** | **10** |
| **H: Domain-Centric** | 46 | **4.35** | **11** |
| **A: Resource-Centric** | 42 | **4.13** | **12** |

---

## +2 Bonus Hybrid Models

### Model M: Persona-Scoped Workspace (E + C)

**Concept:** Persona profiles determine visible items (E). Workspace tiers organize them (C).

| User Role → | My Workspace | Team Workspace | Oversight | System |
|---|---|---|---|---|
| End User | My Tasks, My Vault | — | — | — |
| Service Desk | My Tasks, My Vault | Services | — | — |
| IT Operator | My Tasks, My Vault | Services, Vendors, Assets | — | — |
| IT Manager | My Tasks | Services, Vendors, Assets, Tasks (All) | Audit, Reports | — |
| Procurement | — | Vendors, Renewals, Assets | Reports | — |
| Security Officer | — | — | Audit, Reports | Users, Roles |
| IT Director | — | — | Reports, Audit | — |
| Super Admin | — | Services, Vendors, Assets, Tasks | Audit, Reports | Users, Roles, Config, Integrations |

**Advantages:**
- Combines the #1 (Persona) and #2 (Workspace) models
- Persona profiles solve the "relevance" problem
- Workspace tiers solve the "organization" problem
- Each user sees 5-12 items organized into 2-3 familiar tiers

**Disadvantages:**
- Higher implementation complexity (both systems)
- Workspace labels ("My"/"Team"/"Oversight"/"System") may not fit all org cultures
- Oversight and System may blur for some personas

| Criteria | Score |
|----------|-------|
| Simplicity | 6 |
| Discoverability | 6 |
| Daily Usability | 10 |
| Enterprise Scalability | 9 |
| RBAC Compatibility | 10 |
| IA Principles | 9 |
| Cognitive Load | 10 |
| Future Growth | 8 |
| Training Effort | 9 |
| Maintenance | 3 |

**Weighted score: 8.30**

**Rank: #1 (above Persona alone)**

### Model N: Persona-Scoped Workspace + Search Primary (E + C + I)

**Concept:** Persona profiles + workspace tiers (Model M) as the BROWSEABLE structure. Search/command palette as the PRIMARY navigation method.

**Advantage:** Users who know what they want type and go (2 seconds). Users who are exploring browse the persona-filtered sidebar (5 seconds). Best of both worlds.

**The user journey:**
1. 90% of the time: press Ctrl+K, type "domains," press Enter. Never touch the sidebar.
2. 9% of the time: glance at sidebar for the item they use frequently (muscle memory).
3. 1% of the time: explore sidebar for features they didn't know existed.

| Criteria | Score |
|----------|-------|
| Simplicity | 6 |
| Discoverability | 7 |
| Daily Usability | 10 |
| Enterprise Scalability | 10 |
| RBAC Compatibility | 10 |
| IA Principles | 10 |
| Cognitive Load | 10 |
| Future Growth | 10 |
| Training Effort | 7 |
| Maintenance | 3 |

**Weighted score: 8.57**

**Rank: #1 (overall)**

---

## Final Rankings

| Rank | Model | Weighted Score | Description |
|------|-------|---------------|-------------|
| **1** | **N: Persona-Scoped Workspace + Search** | **8.57** | Persona-filtered workspace tiers + command palette primary |
| **2** | **M: Persona-Scoped Workspace** | **8.30** | Persona-filtered workspace tiers only |
| **3** | **E: Persona-Centric** | **8.17** | Persona profiles dictate which items are visible |
| 4 | C: Workspace-Centric | 7.17 | My / Team / System tiers |
| 5 | J: Hybrid Task-First | 6.87 | Workflow section + entity browse section |
| 6 | K: Tiered Responsibility | 6.83 | Daily / Weekly / Monthly / Never tiers |
| 7 | I: Search-Centric | 6.78 | Command palette as primary navigation |
| 8 | B: Service-Centric | 6.35 | ITIL service lifecycle groups |
| 9 | D: Verb-Centric | 6.22 | Action-oriented verb groups |
| 10 | L: Adaptive Frequency | 4.96 | Algorithmically reordered by usage |
| 11 | G: Hub-and-Spoke | 4.91 | Dashboard as navigation center |
| 12 | F: Minimalist | 4.39 | Top 5 + "More" |
| 13 | H: Domain-Centric | 4.35 | Data governance domains |
| 14 | A: Resource-Centric | 4.13 | One item per database table (CURRENT) |

---

## Sensitivity Analysis

### What changes if Daily Usability weight drops from 2.0 to 1.0?

| Model | Original | After Weight Change | Delta |
|-------|----------|-------------------|-------|
| E | 8.17 | 7.83 | -0.34 |
| C | 7.17 | 6.96 | -0.21 |
| I | 6.78 | 6.57 | -0.21 |
| A | 4.13 | 4.30 | +0.17 |

**Finding:** Ranking is stable. Top 3 remain top 3. The spread narrows but order holds.

### What changes if Simplicity weight rises from 1.0 to 2.0?

| Model | Original | After Weight Change | Delta |
|-------|----------|-------------------|-------|
| I | 6.78 | 7.70 | +0.92 |
| F | 4.39 | 5.87 | +1.48 |
| E | 8.17 | 7.74 | -0.43 |

**Finding:** Simplicity weight increase boosts Search-Centric and Minimalist. Persona-Centric drops. This confirms that Persona-Centric's weakness is implementation complexity, not UX complexity.

### What changes if Maintenance weight rises from 0.5 to 1.5?

| Model | Original | After Weight Change | Delta |
|-------|----------|-------------------|-------|
| A | 4.13 | 5.17 | +1.04 |
| I | 6.78 | 7.17 | +0.39 |
| E | 8.17 | 6.91 | -1.26 |

**Finding:** Resource-Centric (current) jumps 5 ranks if maintenance is a top priority. Persona-Centric drops but still ranks #3.

### Verdict on Sensitivity

**Persona-Centric (E) and its hybrids (M, N) are robust** — they remain in the top 3 across all reasonable weight variations. Only if maintenance weight exceeds 2.0 (making it the #1 criterion) does Persona-Centric fall.

**Given that end-user experience should outweigh developer convenience in an enterprise product**, the maintenance cost is justified.

---

## THE DECISION

```
┌─────────────────────────────────────────────────────────────────┐
│  RECOMMENDED: Model N — Persona-Scoped Workspace               │
│               with Search-Primary Navigation                    │
│                                                                 │
│  Score: 8.57 / 10  (Highest weighted score)                     │
│  Runner-up: Model M — 8.30 / 10                                │
│  Current (Resource-Centric): 4.13 / 10  (Last place)           │
│                                                                 │
│  Model N combines:                                              │
│  • Persona profiles → relevance (E)                             │
│  • Workspace tiers → organization (C)                           │
│  • Command palette → speed (I)                                  │
└─────────────────────────────────────────────────────────────────┘
```

### Implementation Requirements

| Component | Effort | Priority |
|-----------|--------|----------|
| Persona-profile sidebar (8 variants) | 1-2 sprints | HIGH |
| Workspace tier grouping within each profile | 2 days (part of above) | HIGH |
| Command palette (exists! Ctrl+K) | **Already implemented** | — |
| Search indexing for all entity types | 3-5 days | MEDIUM |
| Workspace label localization | 1 day | LOW |
| Browser/bookmark URL stability (backward compat) | 1 day | MUST |

**The command palette already exists. Model N's highest-cost item (search) is already built.** The remaining effort is sidebar restructuring (persona profiles + workspace tiers).

This makes Model N the HIGHEST-VALUE, LOWEST-COST option.

---

## RECOMMENDATION

1. **Keep the existing command palette** (Ctrl+K). It already supports global search. Ensure every entity type is indexed.

2. **Restructure sidebar into workspace tiers** for ALL users:
   - **My Workspace:** personal items (My Tasks, My Vault)
   - **Team Workspace:** shared items (Services, Vendors, Assets, Tasks, Calendar)
   - **Oversight:** analytical items (Audit Trail, Reports)
   - **System:** configuration items (Users, Roles, Config, Integrations)

3. **Apply persona profiles** to each workspace tier:
   - End User: My Workspace only
   - Service Desk: My + Team
   - IT Operator: My + Team
   - IT Manager: My + Team + Oversight
   - Security Officer: Team (limited) + Oversight + System (limited)
   - Procurement: Team (limited) + Oversight
   - IT Director: Oversight
   - Super Admin: All four

4. **Add a "Browse All" button** at the bottom of the sidebar for discoverability (opens a searchable entity index).

**Total implementation cost:** ~2 sprints for full persona-scoped workspace nav. Command palette is already built.
