# POST-RELEASE FEEDBACK PLAN

> What feedback to gather from real users, how to gather it, and how to triage into v1.1.
>
> **Constraint:** Do not add analytics, telemetry, or tracking to the application. Feedback must be gathered through manual channels.

---

## Sources of Feedback

### Source 1: Shadow IT Operators (Days 1-3)

Have 1-2 IT Operators use OpsPilot alongside their existing tools. Observe, don't instruct.

| What to Watch | How to Capture |
|---------------|----------------|
| Which menu items do they click first? | Screen share + note first 3 clicks |
| Do they use Calendar? What for? | Ask after session: "What did you expect Calendar to show?" |
| Do they notice "Other Services"? What would they rename it? | Direct question |
| Do they use My Tasks vs Task Management? Which one? | Count clicks, ask: "Did you know there are two task lists?" |
| Do they find the command palette? | Observe if they use Ctrl+K or navigate via sidebar |
| How many clicks to "provision a new service"? | Time the "new hosting" workflow end-to-end |
| Do they ever click an admin-only item and get 403? | Watch for error pages |

### Source 2: Service Desk Technicians (Days 3-5)

| What to Watch | How to Capture |
|---------------|----------------|
| How do they find credentials for a user? | Time the credential retrieval workflow |
| Do they see irrelevant items? Ask them to ignore what they don't need | "Which items here would you remove from your view?" |
| How do they log a task after resolving an issue? | Observe task creation flow |
| Do they understand "Domain Emails"? | "What would you call this?" |

### Source 3: IT Manager / Team Lead (Day 5-7)

| What to Watch | How to Capture |
|---------------|----------------|
| Do they use Reports? | Check if they navigate to /reports |
| What report are they missing? | "What's the first report you'd want to see?" |
| Do they use the Dashboard for team oversight? | "Does the dashboard show what you need to know about your team?" |
| How do they check team workload? | Observe workflow |

### Source 4: End Users (Day 7-14)

| What to Watch | How to Capture |
|---------------|----------------|
| Can they find "My Credentials" without help? | Walk-up test: "Find your VPN password" |
| Do they understand "Vault"? | "What does Vault mean to you?" |
| Can they find Help Center when stuck? | "Where would you look for help?" |
| Do they notice irrelevant items? | "Is there anything on this page that doesn't apply to you?" |

### Source 5: Super Admin (Continuous)

| What to Watch | How to Capture |
|---------------|----------------|
| How long to onboard a new user? | Time the onboarding workflow |
| Do they use Import? | Ask during weekly check-in |
| Are they overwhelmed by 14 admin items? | "If you could hide 5 items, which would they be?" |
| Do they ever use API Access / Webhooks? | Ask: "Do you need these in the sidebar daily?" |

---

## Feedback Collection Methods

### A. Post-Session Interview (15 min)

After each shadowing session, ask these 5 questions:

1. **"What was the hardest thing to find?"** — identifies navigation failures
2. **"What did you expect to see but didn't?"** — identifies missing workflows
3. **"What did you see that confused you?"** — identifies label problems
4. **"If you could change one thing about the sidebar, what would it be?"** — identifies top pain point
5. **"Would you keep using this or go back to spreadsheets?"** — identifies adoption risk

### B. Single-Question Survey (embedded in footer, no backend)

Add a simple feedback link in the app footer (Blade-only, no DB):
```
Having trouble? Tell us what's confusing: [feedback@example.com]
```
Collect emails only from users who opt in. No anonymous submission.

### C. Weekly "Pain Point" Log (manual, team process)

IT Manager keeps a running doc of user complaints for the first 4 weeks:
```
Week 1:
- User A: "Couldn't find where to change my password" → Profile label unclear
- User B: "Clicked 'Other Services' and didn't know what went there"
```

---

## Feedback Triage Process

```
User reports issue
        │
        ▼
   Is this a BUG?           ───→ Yes → Fix immediately (Tier 0)
   (wrong data, 403, 500)
        │
        ▼ No
   Is this a MISSING         ───→ Yes → Is it needed for initial onboarding?
   FEATURE?                        │
   (new workflow, new report)      ├── Yes → Tier 1: Must do before onboard
                                   └── No  → v1.1 backlog (Tier 3/4)
        │
        ▼ No
   Is this a CONFUSING        ───→ Yes → Does 2+ users report the same issue?
   LABEL/GROUPING?                   │
   ("where is X?", "what is Y?")     ├── Yes → Tier 2: Feedback-driven fix
                                     └── No  → Monitor, revisit if reported again
        │
        ▼ No
   Is this a PERFORMANCE      ───→ Yes → Log priority; fix if impacting adoption
   / SPEED ISSUE?                    │
   ("dashboard is slow")             └── If 3+ users complain → Tier 1 escalation
        │
        ▼ No
   Defer to v1.1 backlog
```

---

## Decision Gates

| Gate | When | Who | Decision |
|------|------|-----|----------|
| **Gate 1** | Day 3 (after IT Ops shadow) | Lead dev + IT Manager | Which Tier 2 items to start? Which label renames to prioritize? |
| **Gate 2** | Day 7 (after all personas) | Product owner + IT Director | Go/no-go on navigation restructure (Phase B-C). Validate with data. |
| **Gate 3** | Day 14 (after end user onboarding) | Full team | Publish v1.1 scope. Start Sprint 1. |
| **Gate 4** | Day 30 | Stakeholders | Re-prioritize v1.1 backlog. Plan v2.0. |

---

## What NOT to Ask (Pre-Bias)

Avoid these leading questions:

| ❌ Don't Ask | ✅ Ask Instead |
|-------------|---------------|
| "Do you think the sidebar should be organized by role?" | "How do you decide where to click in the sidebar?" |
| "Don't you think 'Other Services' is a bad name?" | "What does 'Other Services' mean to you?" |
| "Would you prefer a Quick Actions menu?" | "What's the first thing you do when you log in?" |
| "Are there too many items in Administration?" | "Which items in the sidebar do you use daily? Weekly?" |

---

## Feedback-to-Action Log Template

```
Date       | User/Role       | Feedback                          | Category   | Action Item          | Priority
───────────|─────────────────|───────────────────────────────────|────────────|──────────────────────|─────────
2026-07-08 | Jordan (IT Ops) | "I clicked Task Management but   | Confusing  | Merge My Tasks +     | Tier 2
           |                 |  it showed everyone's tasks"     | label      | Task Management      | 
```
