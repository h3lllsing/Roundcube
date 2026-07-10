# 05 — WHAT WE DO NOT KNOW

> Honest inventory of unknowns.
> Not "we need to research" — specific questions we cannot answer today.

---

## 1. We Do Not Know If Users Want Fewer Items

**The entire recommendation rests on "less = better."**

But we have never asked a user: "Would you prefer a shorter sidebar?"

We have never measured:
- Whether users currently feel overwhelmed
- Whether they notice the 14-item Administration group
- Whether they wish items were hidden
- Whether they would trade discovery for simplicity

**What if users LIKE seeing everything?** What if the sidebar serves as a system map, and removing items creates anxiety about missing something?

**Unknown. Cannot answer without user research.**

---

## 2. We Do Not Know Actual Usage Frequency

The frequency table (Daily / Weekly / Monthly / Rarely) is PURE SPECULATION.

| Item | We Claim | We Actually Know |
|------|----------|-----------------|
| Tasks | Daily | Nothing. No data. |
| Vault | Daily | Nothing. No data. |
| Calendar | Weekly | Nothing. No data. |
| Reports | Weekly | Nothing. No data. |
| Domain Emails | Daily | Nothing. No data. |
| Audit Trail | Daily | Nothing. No data. |

**Every frequency claim in the recommendation is an untested hypothesis.**

**If actual frequencies differ by even 20%, the prioritization changes significantly.**

---

## 3. We Do Not Know How Users Navigate

| Navigation Theory | Our Confidence | Why We Don't Know |
|------------------|---------------|-------------------|
| Users scan categories | 0% | No eye-tracking data |
| Users click known positions | 0% | No click position heatmap |
| Users use search | 0% | No search event data |
| Users use bookmarks | 0% | No referrer data |
| Users memorize keyboard shortcuts | 0% | No command palette usage data |

**We cannot recommend a navigation architecture without understanding how users currently navigate.**

---

## 4. We Do Not Know the Support Cost of Change

The regression analysis (03_NAVIGATION_REGRESSION_ANALYSIS.md) estimates 400% more support calls. **This is also a guess.**

- We don't know how many current calls are navigation-related
- We don't know how IT Ops learn new systems (self-discovery vs training vs support)
- We don't know the cost of a support call (minutes, dollars, user frustration)

**The support cost could be 50% or 5000% of current.** Both are plausible.

---

## 5. We Do Not Know If Personas Are Real

The 8 personas are hypothesized. **They may not exist in this organization.**

| Persona | Exists? | Evidence |
|---------|---------|----------|
| IT Operator | Probably | Standard IT role |
| IT Manager | Probably | Standard management role |
| Service Desk | Probably | Common support role |
| Security Officer | MAYBE | 1-3 in 500 users. May not exist. |
| Procurement | MAYBE | IT procurement may be handled by IT Ops |
| IT Director | Probably | 1 person in org |
| Super Admin | Definitely | We created this role |
| End User | Definitely | All non-admin users |

**40-60% true.** Security Officer and Procurement are speculative. If they don't exist, the Oversight tier and persona profiles need adjustment.

---

## 6. We Do Not Know the Learning Curve Cost

**We know changing navigation creates a learning curve.**

**We do not know:**
- How long it takes to rebuild spatial memory
- Whether 34→20 items is a net productivity gain or loss in the first month
- Whether users will resist the change
- Whether some users will never adapt

**The transition cost may outweigh the steady-state benefit.**

---

## 7. We Do Not Know the Cultural Fit of "Workspace"

The workspace model uses "My Workspace," "Team Workspace," "Oversight," "System."

**We do not know:**
- Whether "Workspace" means anything to IT Ops (it's a tech startup term)
- Whether "Oversight" sounds positive or negative (can imply micromanagement)
- Whether "System" is too vague (System of WHAT?)
- Whether any users prefer the current group names ("Infrastructure," "Administration")

**The labels could resonate or confuse. We cannot predict without testing.**

---

## 8. We Do Not Know If the Current Nav Is Actually a Problem

**Critical unknown: Is the current 34-item sidebar causing measurable problems?**

| Potential Problem | Evidence It Exists | Evidence It Matters |
|------------------|-------------------|---------------------|
| Users can't find things | Support tickets? Unknown. | Unknown |
| Users feel overwhelmed | User feedback? Unknown. | Unknown |
| Task completion time is slow | Baseline measurement? None. | Unknown |
| Feature discovery is low | Feature adoption metrics? None. | Unknown |
| Training is hard | Training feedback? Unknown. | Unknown |

**Without evidence that the CURRENT nav is a problem, we cannot justify the cost of changing it.**

**The current nav may be perfectly adequate.** The IA violations we identified may be invisible to users. The 14-item Administration group may never cause a support call.

**We optimized a system we haven't proven is broken.**

---

## 9. We Do Not Know the Cost of NOT Changing

Counterpoint to #8: If the current nav IS causing problems, what's the cost of doing nothing?

| Cost | Magnitude | Unknown Factor |
|------|-----------|---------------|
| Lost productivity from slow navigation | Unknown | Current navigation speed |
| Lost feature adoption | Unknown | Feature usage gap |
| Higher training costs | Unknown | Training time |
| User frustration | Unknown | Not measured |

**We know the cost of change (estimated). We do NOT know the cost of inaction.** Without both, we cannot make a rational decision.

---

## 10. We Do Not Know the Future

The recommendation assumes:

- The system will grow to 250 modules → *Unknown*
- The organization will have 40 personas → *Unknown*
- Usage patterns will remain stable → *Unknown*
- The command palette will scale → *Unknown*
- Merge consolidations will not need to be undone → *Unknown*

**The 5-year future analysis (in 07_LONG_TERM_FAILURE_ANALYSIS.md) is speculation.**

---

## Summary: What We Truly Know vs What We Assume

```
┌────────────────────────────────────────────────────────────┐
│  WE KNOW (Definitive)                                       │
│  ├── Current nav has 34 items                              │
│  ├── Administration has 14 items (exceeds Hick's Law)      │
│  ├── "Other Services" is an IA violation                   │
│  ├── 53% of labels are technical jargon                    │
│  ├── My/Tasks, My/Shared Credentials are filters, not      │
│  │   separate entities                                     │
│  ├── Calendar queries Tasks and ExpiryTrackers             │
│  ├── Domain Emails FK to Domains                           │
│  └── Command palette exists                                │
│                                                            │
│  WE DO NOT KNOW (No evidence)                              │
│  ├── Whether users find the current nav difficult          │
│  ├── How users currently navigate                          │
│  ├── Actual usage frequency of any item                    │
│  ├── Whether persona profiles match real users             │
│  ├── Whether "less items = better UX"                     │
│  ├── The support cost of change                            │
│  ├── The learning curve cost                               │
│  ├── Whether workspace labels resonate                     │
│  ├── The cost of inaction                                  │
│  └── Long-term stability of any model                      │
│                                                            │
│  UNAMBIGUOUS CONCLUSION: We know enough to HYPOTHESIZE.    │
│  We do NOT know enough to RECOMMEND implementation.        │
│  The recommendation is a VALIDATED HYPOTHESIS at best.     │
└────────────────────────────────────────────────────────────┘
```
