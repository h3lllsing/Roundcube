# 04 — EVIDENCE GAP REPORT

> What evidence exists vs what evidence is needed.
> Honest assessment of what we know vs what we're guessing.

---

## Evidence That Exists

| Claim | Evidence | Strength | Source |
|-------|----------|----------|--------|
| Current nav has 34 items | Direct count | DEFINITIVE | Code inspection |
| Administration has 14 items | Direct count | DEFINITIVE | Code inspection |
| 6 IA violations exist | Analysis | STRONG | IA audit (02_INFORMATION_ARCHITECTURE_AUDIT.md) |
| 18+ labels are technical jargon | Analysis | STRONG | Menu semantic analysis |
| "Other Services" is an IA failure | Consensus | STRONG | Every IA principle |
| My Tasks + Task Management share same table | Code inspection | DEFINITIVE | Database schema |
| My Credentials + Shared Credentials share same table | Code inspection | DEFINITIVE | Database schema |
| Domain Emails is a child entity of Domains (FK) | Code inspection | DEFINITIVE | Database schema |
| Features is a child entity of Modules | Code inspection | DEFINITIVE | Database schema |
| Privileges is a child concept of Permissions | Code inspection | DEFINITIVE | Database schema |
| Calendar reads from tasks + expiry_trackers | Code inspection | DEFINITIVE | Controller logic |
| Command palette exists | Code inspection | DEFINITIVE | Admin layout JS |
| 8 personas hypothesized | Expert analysis | MEDIUM | Persona model document |
| Persona → required items mapped | Expert analysis | MEDIUM | Persona model document |

---

## Evidence Gaps (Missing)

### Gap 1: Actual Usage Data (CRITICAL)

| Question | Current State | What We Need |
|----------|--------------|-------------|
| How often is each nav item clicked? | No analytics. Zero data. | 90-day clickstream of page views per route |
| What is the first action users take after login? | No data. | Session start event log |
| How many navigations per session? | No data. | Event sequences |
| Do users use command palette? | Exists. Unknown usage. | Command palette invocation count |
| What search terms do users type? | Not logged. | Search query logs |
| Do users use Calendar? How? | No data. | Calendar view event counts. Calendar event creation. |
| Which items are NEVER clicked? | No data. | Zero-usage item list over 90 days |
| Average time-to-click? | No data. | Click timing instrumentation |

**Impact: Without this data, the entire recommendation is based on UNTESTED HYPOTHESES about user behavior.**

### Gap 2: User Mental Model (CRITICAL)

| Question | Current State | What We Need |
|----------|--------------|-------------|
| How do IT Ops ORGANIZE services mentally? | Guessing | Card sorting study: "Group these items into categories" |
| What LABELS do users expect? | Guessing | Label preference test |
| Do users think in personas? | Guessing | Diary study: "What role did you play today?" |
| What workflows do users perform? | Guessing | Task analysis: observe 5 IT Ops for 4 hours each |
| What is the FIRST thing users do? | Guessing | Session recording analysis |

**Impact: Without user mental model data, the grouping is architect's preference, not user need.**

### Gap 3: Feature Discovery (MEDIUM)

| Question | Current State | What We Need |
|----------|--------------|-------------|
| Do users discover features through sidebar browsing? | Assumed | User interview: "How did you first learn about [feature X]?" |
| What discovery path do NEW users follow? | Assumed | First-session recording for 10 new users |
| Do users prefer sidebar OR search for known items? | Assumed | Preference test: time to find known item via sidebar vs search |
| What happens when users can't find something? | Assumed | Support ticket analysis: "I can't find X" tickets by frequency |

**Impact: The "search-first" recommendation assumes users know what they want. If users discover through browsing, search-first fails.**

### Gap 4: Business Context (MEDIUM)

| Question | Current State | What We Need |
|----------|--------------|-------------|
| How many users per persona? | Guessing | Directory analysis: role distribution |
| What is the organization structure? | Not assessed | Interview with IT Director |
| What are the top 5 support ticket categories? | Not assessed | Support system analysis |
| What is the first-week user dropout rate? | Not assessed | User onboarding funnel |
| What do users complain about most? | Not assessed | Feedback log analysis |

**Impact: Without business context, we may optimize for the wrong personas.**

### Gap 5: Merge Impact (MEDIUM)

| Question | Current State | What We Need |
|----------|--------------|-------------|
| How many bookmarks point to old URLs? | Unknown | Web server log analysis of direct URL access |
| How many help pages reference old nav paths? | Unknown | Text search across all documentation |
| How many users have custom dashboards linking to old routes? | Unknown | Custom UI inventory |
| What is the true cost of redirect maintenance? | Estimated | Redirect traffic monitoring (post-change) |

**Impact: Merge cost may be higher than estimated.**

---

## Evidence Confidence Map

```
                            EVIDENCE EXISTS          NO EVIDENCE
                            ───────────────         ───────────
HIGH CONFIDENCE             Item count (34)          Actual usage frequency
(>80% certain)              IA violations            User mental model
                            DB schema analysis       Feature discovery paths
                            Technical feasibility    Workflow frequency
                            
MEDIUM CONFIDENCE           Persona model            Multi-role user prevalence
(50-80% certain)            Label analysis           Command palette adoption
                            Persona-item mapping     Support call patterns
                            Merge candidates         Documentation references
                            
LOW CONFIDENCE              Frequency claims         First-week user dropout
(<50% certain)              "64% irrelevant"         Cross-role collaboration needs
                            Training cost estimate   Internationalization impact
                            Productivity gain est.   Long-term nav stability
```

---

## Conclusion on Evidence

**Of the claims in the recommendation:**

| Evidence Level | % of Claims |
|---------------|-------------|
| Definitive (code inspection) | ~10% |
| Strong (expert analysis) | ~20% |
| Medium (informed hypothesis) | ~30% |
| Low (guess) | ~30% |
| None (assumption) | ~10% |

**Approximately 70% of the recommendation's claims are unsupported by direct evidence.**

**This does NOT mean the recommendation is wrong. It means the recommendation is a HYPOTHESIS, not a validated conclusion. It requires testing before implementation.**

### What Must Be Validated Before Implementation

| # | Claim | Validation Method | Cost | Time |
|---|-------|-------------------|------|------|
| 1 | Users find items faster with fewer nav choices | A/B test: time-to-click 34-item vs 20-item sidebar | Low (template only) | 2 weeks |
| 2 | Persona profiles match real user needs | Card sorting study with 10 users per persona | Medium (user recruitment) | 3 weeks |
| 3 | Multi-role users are uncommon | Role audit across user directory | Low (DB query) | 1 day |
| 4 | Command palette is used | Instrument Ctrl+K usage events | Low | 2 weeks data |
| 5 | Calendar is rarely used | Page view event for /calendar | Low | 2 weeks data |
| 6 | Domain Emails is accessed daily | Page view event for /domain-emails | Low | 2 weeks data |
| 7 | Users navigate by category not position | Clickstream analysis: what do users LOOK at? | High (eye tracking) | 4 weeks |
| 8 | Workspace labels are intuitive | Label preference test (5 users, 3 label sets) | Low | 1 week |
| 9 | Merging items doesn't increase support calls | Support ticket analysis pre/post change | Low | 4 weeks data |
| 10 | "Browse all" mitigates persona nav support issues | Usability test: "Find X" tasks with persona nav | Medium | 2 weeks |

**Total validation cost:** ~3-8 weeks depending on methods.

**If the recommendation is implemented without validation, we risk deploying a solution to a problem we haven't proven exists, with side effects we haven't measured.**
