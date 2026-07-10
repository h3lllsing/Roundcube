# 02 — ASSUMPTION REGISTER

> Every assumption made in the recommendation, catalogued and challenged.
> Marked: VALIDATED, UNTESTED, LIKELY FALSE, or NEEDS DATA.

---

## Category A: Frequency Assumptions

### A1: "Tasks are daily for everyone"

| Property | Value |
|----------|-------|
| **Assumption** | Every persona uses Tasks daily |
| **Evidence** | None. No usage data exists. |
| **Source** | Persona model inference |
| **Status** | **UNTESTED** |
| **What if wrong?** | If Tasks are weekly for most users, it doesn't deserve top-3 positioning. Vault may be the only truly daily item. |
| **What data would validate?** | Session logs showing task page loads per user per day |

### A2: "Providers are weekly for IT Ops and Procurement"

| Property | Value |
|----------|-------|
| **Assumption** | Vendor management is a weekly activity |
| **Evidence** | None. Inferred from persona model. |
| **Source** | Persona model inference |
| **Status** | **LIKELY FALSE** |
| **What if wrong?** | Providers are typically onboarded once quarterly. Most weeks, no one looks at the Providers page. It doesn't deserve top-level visibility. |
| **What data would validate?** | Page views for service-providers index over 90 days |

### A3: "Reports are weekly for managers"

| Property | Value |
|----------|-------|
| **Assumption** | IT Manager, IT Director, Procurement run reports weekly |
| **Evidence** | None. |
| **Source** | Persona model inference |
| **Status** | **UNTESTED** |
| **What if wrong?** | If reports are monthly, they could be 2+ clicks away (under a "More" menu). Weekly justifies top-level. Monthly does not. |

### A4: "Calendar is viewed weekly"

| Property | Value |
|----------|-------|
| **Assumption** | Calendar has enough usage to justify standalone nav |
| **Evidence** | None. |
| **Source** | Persona model inference |
| **Status** | **UNTESTED — CRITICAL** |
| **What if wrong?** | If Calendar is viewed once per month, eliminating it is correct. If daily, keeping it is essential. This single data point flips the Calendar recommendation. |

### A5: "Domain Emails is accessed multiple times daily by Service Desk"

| Property | Value |
|----------|-------|
| **Assumption** | Mailbox password reset is a high-frequency Service Desk task |
| **Evidence** | Persona model: "Daily — multiple times" |
| **Source** | Persona model |
| **Status** | **UNTESTED — CRITICAL** |
| **What if wrong?** | If mailbox access is weekly, Domain Emails → Domains child is acceptable. If truly daily, the extra click is a real cost. |

---

## Category B: User Behavior Assumptions

### B1: "Users navigate by category, not position"

| Property | Value |
|----------|-------|
| **Assumption** | Workspace tiers are more intuitive than a flat list |
| **Evidence** | None. No eye-tracking, no clickstream analysis. |
| **Source** | IA theory (Hick's Law, Miller's Law) |
| **Status** | **LIKELY FALSE FOR TRAINED USERS** |
| **What if wrong?** | If users navigate by SPATIAL MEMORY ("fourth item in the second group"), reorganizing the sidebar BREAKS their navigation speed for 2-4 weeks while they rebuild muscle memory. The 34→20 reduction may not compensate for this breakage. |
| **What data would validate?** | Clickstream analysis: do users look at sidebar labels or click positions? A/B test: time-to-click for organized vs flat sidebar. |

### B2: "Users will use the command palette"

| Property | Value |
|----------|-------|
| **Assumption** | Ctrl+K / Cmd+K adoption will be high enough to justify search-primary design |
| **Evidence** | Command palette exists. Usage unknown. |
| **Source** | Assumption that IT operators use keyboard shortcuts |
| **Status** | **UNTESTED — CRITICAL** |
| **What if wrong?** | If <10% of users use the command palette, search-primary navigation fails. All navigation must work through sidebar clicks. |
| **What data would validate?** | Percentage of sessions that use Ctrl+K. Percentage of nav actions via command palette vs sidebar. |
| **Industry benchmark** | In most enterprise software, keyboard shortcut adoption is 5-15% of users. Power users (IT Ops) may be higher (20-30%). Still a minority. |

### B3: "Users discover features by browsing sidebar"

| Property | Value |
|----------|-------|
| **Assumption** | A visible sidebar item teaches users the feature exists |
| **Evidence** | Plausible but untested |
| **Source** | Cognitive science (visibility = knowledge) |
| **Status** | **NEEDS DATA** |
| **What if wrong?** | If users navigate ONLY to items they already know (training, word-of-mouth, documentation), the sidebar serves no discovery function. Hiding items doesn't hurt because users wouldn't discover them anyway. |

### B4: "64% of items are irrelevant to the average user"

| Property | Value |
|----------|-------|
| **Assumption** | Items user CAN access but DON'T use are "irrelevant" |
| **Evidence** | Persona model analysis: which items each persona WOULD use |
| **Source** | Persona model |
| **Status** | **MAY BE FALSE** |
| **What if wrong?** | The 64% figure counts items as "irrelevant" if the persona model says they don't need them. But:
1. Users may use items we didn't predict
2. Users may NEED items they use rarely (monthly audit log check — still necessary)
3. "Irrelevant" ≠ "should be hidden." Occasional use justifies visibility.
4. The persona model may be wrong about which items each persona uses.

**64% is a MODEL OUTPUT, not a MEASURED FACT.** True irrelevance can only be determined by usage data.

### B5: "Users are single-role"

| Property | Value |
|----------|-------|
| **Assumption** | Each user maps to ONE persona |
| **Evidence** | None. Real-world users often wear multiple hats. |
| **Source** | Convenience for IA design |
| **Status** | **LIKELY FALSE** |
| **What if wrong?** | If 30% of users have multi-role responsibilities:
- Persona-filtered nav shows only one persona's items
- User needs items from both personas but can only see one
- Role switching is confusing and adds complexity
- The "best" persona assignment creates frustration for multi-role users

---

## Category C: Technical Assumptions

### C1: "Blade template changes only — no backend changes needed"

| Property | Value |
|----------|-------|
| **Assumption** | The sidebar is entirely Blade-rendered and can be reorganized without backend changes |
| **Evidence** | SidebarComposer passes flags to the template |
| **Source** | Code review |
| **Status** | **LIKELY TRUE BUT INCOMPLETE** |
| **What if wrong?** | If persona-based filtering requires new role definitions (which it does — we don't have a "procurement" or "security" or "director" role), BACKEND CHANGES ARE REQUIRED. The recommendation claimed "template changes only" but persona profiles require role infrastructure that doesn't exist. |

### C2: "Merges can be done with URL redirects"

| Property | Value |
|----------|-------|
| **Assumption** | Old URLs can redirect to new merged URLs |
| **Evidence** | Standard Laravel route redirect |
| **Source** | Engineering assumption |
| **Status** | **TRUE BUT HAS COST** |
| **What if wrong?** | Every redirect:
- Adds HTTP round-trip (301 → 200)
- Breaks POST routes (form submissions to old URLs fail)
- Breaks API clients using old paths
- Requires documentation updates across all help content
- Requires communication to users
- May break browser bookmarks permanently if redirects are ever removed

### C3: "Command palette can index all entity types"

| Property | Value |
|----------|-------|
| **Assumption** | The existing search infrastructure supports adding all entity types |
| **Evidence** | Current search covers ~8 types |
| **Source** | Code inspection |
| **Status** | **UNTESTED** |
| **What if wrong?** | The command palette may have hardcoded type limits. Adding 15+ entity types may require query refactoring, performance optimization, or UI redesign of search results. |

---

## Category D: Business Assumptions

### D1: "The organization has 8 distinct personas"

| Property | Value |
|----------|-------|
| **Assumption** | There are exactly 8 persona types, and every user fits one |
| **Evidence** | Persona model document |
| **Source** | Expert analysis |
| **Status** | **LIKELY FALSE IN ITS EXACT FORM** |
| **What if wrong?** | Real organizations may have:
- More than 8 types (regional IT leads, MSP technicians, contractors)
- Hybrid roles that don't fit any persona
- No Procurement department (IT Ops handles procurement too)
- Department-specific variations

**Personas are always WRONG in their specifics.** They're useful approximations, not ground truth.

### D2: "The current role system can be extended"

| Property | Value |
|----------|-------|
| **Assumption** | We can create new roles (security, procurement, director) for persona mapping |
| **Evidence** | The system supports role CRUD (it's a menu item) |
| **Source** | Code capability |
| **Status** | **TECHNICALLY TRUE, POLITICALLY UNTESTED** |
| **What if wrong?** | Creating new roles may:
- Require approval from leadership
- Conflict with existing RBAC policy
- Require permission re-certification
- Break existing user assignments
- Require migration of existing users to new roles

**Technical feasibility ≠ organizational feasibility.**

### D3: "Reducing menu items improves productivity"

| Property | Value |
|----------|-------|
| **Assumption** | Time saved scanning sidebar > time lost discovering features |
| **Evidence** | Hick's Law (lab conditions) |
| **Source** | Cognitive psychology |
| **Status** | **UNTESTED IN THIS CONTEXT** |
| **What if wrong?** | If the productivity gained from fewer items (2 seconds saved per navigation) is less than the productivity lost from:
- Relearning positions (4 weeks × 2 minutes/day = 40 minutes total)
- Support calls for hidden features (30 minutes/day for help desk)
- Missed features due to non-discovery (infrequent but high value)

...then the change is a NET NEGATIVE.

---

## Summary: Assumption Health

| Category | Count | Validated | Untested | Likely False | Needs Data |
|----------|-------|-----------|----------|-------------|------------|
| A: Frequency | 5 | 0 | 4 | 1 | 1 |
| B: User Behavior | 5 | 0 | 3 | 1 | 1 |
| C: Technical | 3 | 1 | 1 | 0 | 1 |
| D: Business | 3 | 0 | 1 | 1 | 1 |
| **Total** | **16** | **1 (6%)** | **9 (56%)** | **3 (19%)** | **3 (19%)** |

**94% of assumptions are unvalidated.**

**The recommendation has a 6% evidence-based foundation.**

**This does not mean the recommendation is wrong.** It means the recommendation is a HYPOTHESIS, not a conclusion. It must be tested before implementation.
