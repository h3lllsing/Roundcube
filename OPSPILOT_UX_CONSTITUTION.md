# OpsPilot UX Constitution — Revision 3

> Generated 2026-07-11  
> This document defines the user experience philosophy, job-based information architecture, and interaction standards for OpsPilot.  
> Every decision is measured by: less thinking, less searching, less scrolling, less clicking, less training, fewer mistakes.  
> Organizing principle: user jobs, not pages. A page is only successful if it reduces the effort to complete a job.

---

## Operating Philosophy

> **OpsPilot is not a database viewer. OpsPilot is an operational workspace.**

Users come here to complete work, not to inspect database records. Every screen must answer **"What does the user need to do next?"** before showing **"What information exists?"**

## 30 Second Rule

A brand-new employee who has never used OpsPilot before must understand:

- Where they are
- What this page is for
- What to do next

within 30 seconds without training.

If a page fails the 30 Second Rule, it must be simplified until it passes.

## UX Decision Gate

Before implementing any page change, answer every question below.

Questions 1–4 are mandatory qualitative answers. Questions 5–9 must be measured or estimated; a change is not required to improve every metric, but it **must not materially worsen any metric without explicit justification**, and it **must produce a meaningful net usability improvement**.

| # | Question | Required Answer |
|---|----------|----------------|
| 1 | What is the user's actual job on this page? | Clear, one-sentence statement of the job, not the feature. |
| 2 | Why does this page currently feel difficult? | Specific friction points, not general complaints. |
| 3 | What information is unnecessary for completing the job? | Every field on the page must be challenged. |
| 4 | What information is hidden but should be immediately visible? | Fields that save clicks or scrolling if brought to Level 1. |
| 5 | Does this change reduce clicks? | Measure before/after click count for the primary job. |
| 6 | Does this change reduce scrolling? | Measure before/after scroll distance (in pixels or viewports). |
| 7 | Does this change reduce thinking? | Measure before/after decision time or decoding effort. |
| 8 | Does this change reduce training? | Measure before/after time for a new employee to understand the page. |
| 9 | Does this change reduce mistakes? | Identify specific error types and estimate probability reduction. |

**Stop implementation if:**
- The user's job is unclear.
- The change has no measurable usability benefit.
- It creates an unjustified usability regression (any metric worsens without documented justification).
- It violates the 30 Second Rule.
- It conflicts with a protected DO NOT TOUCH area.

## How To Read This Document

This is not a list of pages to redesign. It is a map of user jobs. Each job flows through multiple screens. The audit traces the **complete job**, not one page in isolation. When we simplify a page, we measure the impact on the **entire job**, not just that page.

---

## Table of Contents

1. [User Jobs Map](#1-user-jobs-map)
2. [Job A: Find A Hosting Account And Access cPanel](#2-job-a-find-a-hosting-account-and-access-cpanel)
3. [Job B: Create And Onboard A New User](#3-job-b-create-and-onboard-a-new-user)
4. [Job C: Suspend A User](#4-job-c-suspend-a-user)
5. [Job D: Change Module Permissions For A Role](#5-job-d-change-module-permissions-for-a-role)
6. [Job E: Audit What A User Can Access](#6-job-e-audit-what-a-user-can-access)
7. [Pilot Recommendation](#7-pilot-recommendation)

---

## 1. User Jobs Map

Every role has a set of recurring jobs. Each job flows through multiple pages. This map shows the connections.

```
SUPER ADMIN JOBS
─────────────────────────────────────────────────────────────────
Job A: Find a hosting → access cPanel          │ Hosting Index → Hosting Show
Job B: Create and onboard a new user           │ Users Index → Users Create → Users Show
Job C: Suspend a user                          │ Users Index → Users Show (or bulk)
Job D: Change module permissions for a role    │ Module Permissions
Job E: Audit what a user can access            │ Users Show (Permissions Matrix)
Job F: View login history                      │ Users Show → or Login Audits
Job G: Export data                             │ Various Index pages
Job H: Configure SMTP                          │ SMTP Profiles

ADMIN JOBS
─────────────────────────────────────────────────────────────────
Job A: Find a hosting → access cPanel          │ Hosting Index → Hosting Show
Job I: Create a hosting record                 │ Hosting Create
Job J: Track renewals                          │ Expiry Trackers
Job K: Check service provider details          │ Service Providers → Show

IT SUPPORT JOBS
─────────────────────────────────────────────────────────────────
Job A: Find a hosting → access cPanel          │ Hosting Index → Hosting Show
Job L: Troubleshoot DNS (check IPs)            │ Hosting Show (Technical section)
Job M: Reset cPanel password                   │ Hosting Show (Access section)

OFFICE MANAGEMENT JOBS
─────────────────────────────────────────────────────────────────
Job N: View upcoming renewals and costs        │ Expiry Trackers
Job O: View reports                            │ Reports
```

**Key insight**: Job A (Find hosting → Access cPanel) appears in **3 roles**. It is the highest-frequency job in the system. Any improvement to Job A pays for itself 3× over.

---

## 2. Job A: Find A Hosting Account And Access cPanel

### User Job

> "I need to log into the cPanel of a specific hosting account to manage files, databases, or email."

This is the most common job in OpsPilot. Admin does it 10–20×/day. IT Support does it 5–10×/day. Super Admin does it 2–3×/day.

### Workflow Before

```
Step 1: Open Hosting Index
──────────────────────────
User sees 10 columns. Must visually skip:
  - Serial (meaningless number)
  - Domain IP (not needed yet)
  - Mail Domain IP (not needed yet)
  - cPanel URL (visible but not actionable from list)
  - cPanel ID (visible but not actionable from list)
  - cPanel PW (masked, not actionable from list)
  - cPanel IP (not needed yet)
  
User finds the domain name in the table.
Time so far: 3–5 seconds (scanning and filtering).

Step 2: Click View
──────────────────
User clicks the View icon.
Page loads Hosting Show.

Step 3: Scroll past Overview
─────────────────────────────
User sees: Name, Provider, Plan, Domain.
Useful context but not what they need.
Scroll down (~200px).

Step 4: Reach Access section
─────────────────────────────
User sees: cPanel URL, Username, Password.
Clicks the cPanel URL to open in new tab.
Or copies the username.
Total time: 7–12 seconds per hosting.
```

### Workflow After

```
Step 1: Open Hosting Index
──────────────────────────
User sees 5 columns: Domain, Provider, Status, Expiry, Actions.
No visual noise. Domain name is the first data column.
User finds the domain instantly.
Time: 1–2 seconds.

Step 2: Click View
──────────────────
User clicks the View icon or domain name (both link to show).
Page loads Hosting Show.

Step 3: See Access section immediately
──────────────────────────────────────
User sees: Status bar at top (confirms correct hosting).
Below it: cPanel URL, Username, Password.
No scrolling required.
Clicks cPanel URL. Copies username if needed.
Total time: 3–5 seconds per hosting.
```

### User Frustration Points

| # | Frustration | Severity |
|---|-------------|----------|
| 1 | Hosting index has too many columns — hard to find the domain | High |
| 2 | Hosting index requires horizontal scroll at 1280px to see Actions | High |
| 3 | Access section is below Overview on show page — must scroll | Medium |
| 4 | cPanel URL is visible in the index but can't open it from there (wasted glance) | Medium |
| 5 | Expiry date is not in the index — must open page to check | Medium |
| 6 | Serial/ID column is meaningless noise | Low |

### Estimated Frustration Reduction

| Frustration | After Fix | Reduction |
|-------------|-----------|-----------|
| 1 (too many columns) | 5 columns instead of 10 | −50% visual noise |
| 2 (horizontal scroll) | All columns fit at 1024px | Eliminated |
| 3 (Access buried) | Access is #1 section on show page | Eliminated |
| 4 (cPanel URL in index) | Removed from index — no wasted glance | Eliminated |
| 5 (expiry missing) | Added to index | Eliminated |
| 6 (Serial noise) | Removed | Eliminated |

### Decision Time

| Decision | Before | After | Improvement |
|----------|--------|-------|-------------|
| "Which hosting is this?" (index scan) | 3–5 seconds | 1–2 seconds | −60% |
| "Is this the right record?" (show page) | 2–3 seconds (scroll + confirm) | <1 second (status bar) | −75% |
| "Where is the cPanel URL?" | 2 seconds (scan sections) | 0 seconds (first thing visible) | −100% |

### Error Probability

| Error | Before | After | Why |
|-------|--------|-------|-----|
| Click wrong action button (dense action cluster) | Low | Lower | Fewer inline actions = fewer misclicks |
| Click wrong hosting (similar names) | Low | Same | Name is always the primary identifier |
| Copy wrong credential | Low | Same | Credentials only appear on show page — same as before |
| Horizontal-scroll misclick | Medium | Eliminated | No scroll = no scroll-related misclicks |
| **Overall error probability** | **Low** | **Very Low** | Fewer columns = fewer places to make a mistake |

### Training Time

| Task | Before | After | Improvement |
|------|--------|-------|-------------|
| "Show me how to find a hosting and open cPanel" | 2 minutes (explain 10 columns, which to ignore, where Access is) | 30 seconds (name → view → cPanel URL is right there) | −75% |
| "Where do I find the IP address?" | 1 minute (scroll down past Access, Financial, to Technical) | 10 seconds (click "Technical Details" accordion) | −83% |

### Information Hierarchy

#### Level 1 — Always Visible

These fields appear on the Hosting Index without any user action.

| Field | Why It Deserves Level 1 |
|-------|------------------------|
| **Domain name** | This is the search key. The user always starts with a domain. |
| **Provider** | Answers "who manages this?" without a click. Saves 1 click per record. |
| **Status** | Answers "is this hosting healthy?" without a click. Drives urgency. |
| **Expiry** | Answers "when does this need attention?" without a click. Drives action. |

#### Level 2 — Click Once

These fields appear on the Hosting Show page as soon as it loads (no scroll, no expand).

| Field | Why It Deserves Level 2 |
|-------|-------------------------|
| **Status badge** | Confirms the record is correct. |
| **Provider name** | Confirms provider context. |
| **cPanel URL** | This is the #1 destination. Every second of delay is friction. |
| **Username** | Needed alongside cPanel URL. Side-by-side access. |
| **Password (masked)** | Needed alongside cPanel URL. Reveal-gated but visible in the same section. |

#### Level 3 — Expand

These fields require one click to reveal (collapsible accordion or tab).

| Field | Section | Why Level 3 |
|-------|---------|-------------|
| Domain IP | Technical Details | Needed for DNS troubleshooting, not for daily access. |
| Mail Domain IP | Technical Details | Same — troubleshooting only. |
| cPanel IP | Technical Details | Same — infrastructure detail. |
| Plan | Plan & Billing | Needed during renewal review, not daily access. |
| Cost | Plan & Billing | Same — financial review. |
| Billing Period | Plan & Billing | Same — financial review. |
| Start Date | Plan & Billing | Rarely needed. |
| Notes | Notes | Occasionally needed. |
| Linked Domains | Tab | Related list — secondary to the main job. |
| Renewals | Tab | Related list — separate workflow. |
| Domain Emails | Tab (within Linked Domains) | Nested related list. |

#### Level 4 — Audit Only

These fields require navigation to a separate page or are only shown during formal review.

| Field | Location | Why Level 4 |
|-------|----------|-------------|
| **Last Login** (user) | Users Show → Security section | Audit signal, not daily need. |
| **Created date** (user) | Users Show → Profile section | Historical reference. |
| **Activity Timeline** (hosting) | Hosting Show → Activity tab | Audit trail — review only. |
| **Serial/ID** (hosting) | Never shown to user | Internal database artifact. Remove entirely. |

### Things That Should NEVER Be Visible On The Default Screen

| Thing | Current Location | Why It Should Never Be Default |
|-------|-----------------|-------------------------------|
| **Database Serial/ID** | Hosting Index column | Zero value for every role. It is a technical artifact. |
| **Domain IP, Mail IP, cPanel IP** | Hosting Index columns | These are troubleshooting details, not discovery criteria. They occupy 30% of table width for a need that arises <5% of the time. |
| **cPanel credentials** | Hosting Index columns | Security anti-pattern. Exposing access points in a bulk list violates least-privilege principles. Even masked, the copy button invites use. |
| **Created date** (users) | Users Index column | Does not help find a user. Does not drive any decision. |
| **Last Login** (users) | Users Index column | Audit information. Creates noise for daily scanning. |
| **Full permission letter codes** (CRUDAREI) | Module Permissions cells | Requires cognitive decoding every time. Should be labeled toggles or not visible at all. |

### Can A Brand-New Employee Understand This Page In Under 30 Seconds?

**Before**: No. A new employee sees 10 columns. They ask: "What is Serial? Do I need Domain IP? What is Mail Domain IP separate from Domain IP? Is cPanel PW actually the password? Why are there 3 IPs?" Explanation takes 2 minutes.

**After**: Yes. A new employee sees 5 columns: Domain, Provider, Status, Expiry, Actions. They think: "I find the domain name. I check if it's Active or Expired. I click View to see details." Explanation takes 15 seconds.

### Why?

Because the page now matches the user's mental model. The user thinks in domains ("I need example.com's cPanel"), not in database records ("I need hosting #42's domain_ip, mail_domain_ip, cpanel_ip, cpanel_url, username, password, and cpanel_ip").

### Workflow Connections

Job A does not exist in isolation. It connects to downstream jobs:

```
Hosting Index ──→ Hosting Show ──→ cPanel (opens in new tab)
                    │
                    ├──→ Technical Details (DNS troubleshooting)
                    ├──→ Linked Domains (view domains on this hosting)
                    ├──→ Renewals (view/update renewal tracker)
                    └──→ Notes (add operational note)
```

When we simplify the Hosting Index, we improve every downstream job that starts with "find a hosting." The ripple effect:

| Downstream Job | Improvement |
|----------------|-------------|
| DNS troubleshooting (IT Support) | Finds hosting 2–3 seconds faster |
| Renewal tracking (Admin) | Finds hosting 2–3 seconds faster |
| Password reset (IT Support) | Finds hosting 2–3 seconds faster |
| Record deletion (Super Admin) | Finds hosting 2–3 seconds faster |

At 20 hosting lookups per day, saving 3 seconds each = **1 minute saved per day per Admin**. Across 3 roles = **3 minutes saved per day = 15 hours saved per year**.

---

## 3. Job B: Create And Onboard A New User

### User Job

> "I need to create a new employee's account, assign them to the right role, and set their module permissions."

This is a Super Admin–only job. Frequency: weekly to monthly.

### Workflow Before

```
Step 1: Users Index → Create
─────────────────────────────
Open Users Index. Click Create.

Step 2: Fill user details
─────────────────────────
Name, Email, Password, Confirm Password, Status.

Step 3: Assign role
───────────────────
Select from role dropdown.

Step 4: Set permissions (optional)
───────────────────────────────────
The create form has a full permission matrix.
Super Admin must either:
  (a) Skip permissions → user gets role defaults → come back later to tweak
  (b) Set permissions now → scroll through 28 modules with toggles

Step 5: Submit → redirected to Users Show
───────────────────────────────────────────
Super Admin verifies the user.

Step 6: Optionally clone from existing user
────────────────────────────────────────────
If cloning, navigate to source user → click Clone → fill form → submit.
```

### Workflow After

No significant changes to the create flow itself — the form is already well-organized.

**One improvement**: The permission matrix on the create form (Step 4) should be collapsed by feature group (Infrastructure, Productivity, Administration, Integration) with only Infrastructure expanded by default. This matches the pattern from Module Permissions.

### User Frustration Points

| # | Frustration | Severity |
|---|-------------|----------|
| 1 | Create form shows all 28 modules expanded — overwhelming for onboarding a single user | Medium |
| 2 | After creating, the user needs to go to Users Show to verify — but the show page has a dense permission matrix that is hard to scan | Medium |
| 3 | No "quick role" presets — must manually select role and trust defaults | Low |

### Information Hierarchy — Users Create Form

#### Level 1 — Always Visible
- Name, Email, Password, Confirm Password
- Role selector
- Status toggle (Active/Suspended)
- Create button

#### Level 2 — Click Once
- Permission section heading "Module Permissions (optional)"
- Feature group accordions (Infrastructure expanded, others collapsed)

#### Level 3 — Expand
- Individual module permission toggles (within each feature accordion)
- "Clone from existing user" section

#### Level 4 — Audit Only
- N/A for create form

### Things That Should NEVER Be Visible On The Default Screen

| Thing | Why It Should Never Be Default |
|-------|-------------------------------|
| All 28 modules expanded in permission matrix | Overwhelming for a task where role defaults usually suffice. Collapse by feature group. |

### Can A Brand-New Employee Understand This Page In Under 30 Seconds?

**Before**: No — the expanded permission matrix looks like a configuration dashboard.
**After**: Yes — "Fill in name, email, pick a role, click Create. Permissions are optional."

### Workflow Connections

```
Users Index ──→ Users Create ──→ Users Show ──→ Users Permissions (optional tweaks)
                  │                              │
                  │                              └──→ Module Permissions (role-wide changes)
                  │
                  └──→ Users Clone (alternative: clone from existing)
```

---

## 4. Job C: Suspend A User

### User Job

> "This employee has left the company. I need to lock their account immediately."

Super Admin only. Frequency: monthly.

### Workflow Before

```
Step 1: Users Index
───────────────────
Find the user in an 8-column table with 6 action buttons per row.
The Suspend button is mixed among View, Permissions, Clone, Edit, Delete.
Risk: clicking Delete instead of Suspend (both are red-toned).
Time: 3–5 seconds to find user, 2 seconds to locate Suspend among 6 buttons.

Step 2: Confirm suspension
───────────────────────────
Click Suspend → confirmation dialog → confirm.
Time: 2 seconds.

Total time: 7–9 seconds.
```

### Workflow After

```
Step 1: Users Index
───────────────────
Find the user in a 5-column table with 2 inline action buttons (View, Edit).
Suspend is in the overflow menu (⋮).
Time: 1–2 seconds to find user, 1 second to click ⋮ → Suspend.

Step 2: Confirm suspension
───────────────────────────
Same confirmation dialog.
Time: 2 seconds.

Total time: 4–5 seconds.
```

### User Frustration Points

| # | Frustration | Severity |
|---|-------------|----------|
| 1 | 6 action buttons per row — Suspend is visually lost among them | High |
| 2 | Delete and Suspend both look destructive (red/orange) — easy to misclick | High |
| 3 | Created and Last Login columns add no value to this job | Medium |

### Estimated Frustration Reduction

- 6 action buttons → 2 (view, edit) + overflow: −67% visual noise
- Suspend separated from Delete by an overflow menu: misclick risk eliminated
- Time to find user: 3–5 seconds → 1–2 seconds

### Decision Time

| Decision | Before | After | Improvement |
|----------|--------|-------|-------------|
| "Is this the right user?" | 3–5 seconds (scan 8 columns) | 1–2 seconds (scan 5 columns) | −60% |
| "Which button suspends?" | 2 seconds (scan 6 buttons) | 1 second (click ⋮ → see "Suspend") | −50% |
| "Am I sure this is Suspend, not Delete?" | 1 second (check color) | 0 seconds (text label in overflow menu) | −100% |

### Error Probability

| Error | Before | After | Why |
|-------|--------|-------|-----|
| Click Delete instead of Suspend | Low-Medium | Very Low | Both are red-toned inline. Overflow menu uses text labels. |
| Click wrong user's Suspend | Low | Same | Risk unchanged — determined by row, not button density. |
| **Overall** | **Low-Medium** | **Very Low** | Lower button density + text labels = fewer misclicks. |

### Training Time

| Task | Before | After | Improvement |
|------|--------|-------|-------------|
| "Show me how to suspend a user" | 45 seconds (explain 6 buttons, which is which, warning about Delete) | 15 seconds (find user → click ⋮ → Suspend) | −67% |

### Information Hierarchy — Users Index

#### Level 1 — Always Visible
| Field | Why It Deserves Level 1 |
|-------|------------------------|
| **Name** | The search key. Always needed. |
| **Email** | Secondary identifier. Needed to confirm identity. |
| **Roles** | Needed to understand who you're suspending (suspending an admin vs a user matters). |
| **Status (Active/Suspended)** | The trigger signal. Suspended users have different available actions. |

#### Level 2 — Click Once
| Element | Why Level 2 |
|---------|-------------|
| **View action** | Most common action — opens details. |
| **Edit action** | Second most common — opens edit form. |

#### Level 3 — Expand
| Element | Why Level 3 |
|---------|-------------|
| **Suspend / Unsuspend** | Destructive — belongs behind overflow menu. |
| **Delete** | Destructive — belongs behind overflow menu. |
| **Permissions** | Infrequent — belongs behind overflow menu. |
| **Clone** | Rare — belongs behind overflow menu. |

#### Level 4 — Audit Only
| Field | Why Level 4 |
|-------|-------------|
| **Last Login** | Audit signal. Goes to Users Show → Security section. |
| **Created** | Historical reference. Goes to Users Show → Profile section. |

### Things That Should NEVER Be Visible On The Default Screen

| Thing | Why It Should Never Be Default |
|-------|-------------------------------|
| **Created date** | Does not help suspend a user. |
| **Last login** | Does not help suspend a user. |
| **Clone button** | Does not help suspend a user. Belongs in overflow. |
| **Permissions button** | Does not help suspend a user. Belongs in overflow. |

### Can A Brand-New Employee Understand This Page In Under 30 Seconds?

**Before**: No. "There are 8 columns and 6 buttons per row. What does Permissions do? What's Clone? Is Delete permanent?" Overwhelming.

**After**: Yes. "Find the person's name. Click the eye to see their details. Click the pencil to edit them. Click the three dots to suspend or delete."

### Workflow Connections

```
Users Index ──→ Suspend (inline confirm) ──→ User suspended (redirect to index)
                  │
                  └──→ Users Show (alternative: view first, then suspend from show page)
```

When we simplify the Users Index, suspension becomes faster and safer. The overflow menu acts as a safety buffer for destructive actions.

---

## 5. Job D: Change Module Permissions For A Role

### User Job

> "The Admin role needs access to the new 'Reports' module. I need to turn on 'Read' for them."

Super Admin only. Frequency: monthly.

### Workflow Before

```
Step 1: Open Module Permissions
────────────────────────────────
Page loads with 28 rows × N role columns.
Horizontal scroll almost always required (4+ roles).

Step 2: Find the module
───────────────────────
Scan 28 rows looking for "Reports."
Feature column repeats "Administration" for every admin module.
Time: 4–8 seconds.

Step 3: Find the Admin column
─────────────────────────────
If 4 roles exist, Admin is column 2 or 3.
Must read column headers to confirm.
Time: 2–3 seconds.

Step 4: Decode the permission cell
───────────────────────────────────
Cell shows: "CRUD E Rev I"
Must decode: C=create, R=read, U=update, D=delete, E=export, Rev=reveal, I=import.
"Read is 'R'. Is there an R? Yes, second letter. So Read is On."
Time: 2–3 seconds per cell.

Step 5: Click to edit
─────────────────────
Click the cell → modal opens.

Step 6: Toggle permission in modal
───────────────────────────────────
Find the "Read" checkbox in the modal.
Uncheck/check.
Time: 2 seconds.

Step 7: Save
────────────
Click Save → modal closes.
Time: 1 second.

Total time for one permission change: 12–18 seconds.
```

### Workflow After

```
Step 1: Open Module Permissions
────────────────────────────────
Role selector at top. Select "Admin" from dropdown.
No horizontal scroll. Only one role shown at a time.

Step 2: Find the module
───────────────────────
Feature accordions: Infrastructure, Productivity, Administration, Integration.
"Reports" is under Administration (collapsed by default).
Click "Administration" to expand.
Scroll to Reports.
Time: 3–5 seconds.

Step 3: Toggle the permission
─────────────────────────────
Reports module card shows 8 labeled toggle switches.
Find "Read" toggle. It says "Read" — no decoding needed.
Click to toggle.
Time: 1 second.

Step 4: Save
────────────
Click Save (per-module).
Time: 1 second.

Total time for one permission change: 5–7 seconds.
```

### User Frustration Points

| # | Frustration | Severity |
|---|-------------|----------|
| 1 | Horizontal scroll to see all role columns | High |
| 2 | Must decode letter codes (CRUDAREI) every time | High |
| 3 | Modal overlay hides context — can't see the module name while editing | Medium |
| 4 | Feature name repeated in every row — visual redundancy | Medium |
| 5 | All 28 modules visible at once — overwhelming | Medium |
| 6 | Form submission saves all changes at once — no per-module save | Medium |

### Estimated Frustration Reduction

| Frustration | After Fix | Reduction |
|-------------|-----------|-----------|
| 1 (horizontal scroll) | Role selector eliminates extra columns | Eliminated |
| 2 (letter codes) | Labeled toggle switches | Eliminated decoding time |
| 3 (modal hides context) | Inline row editor keeps context visible | Eliminated |
| 4 (repeated feature name) | Accordion group heading | −83% visual repetition |
| 5 (28 modules visible) | 4 accordions, 2 expanded by default | −50% visible modules |
| 6 (all-or-nothing save) | Per-module save buttons | Eliminated risk of losing all changes |

### Decision Time

| Decision | Before | After | Improvement |
|----------|--------|-------|-------------|
| "Which role am I editing?" | 2–3 seconds (scan column header) | 0 seconds (role selector shows current) | −100% |
| "What permissions does Admin have on Reports?" | 4–6 seconds (find row + column + decode letters) | 1–2 seconds (select Admin → expand Admins → see toggles) | −75% |
| "How do I change Read from On to Off?" | 2 seconds (decode which letter is R, mentally confirm) | <1 second (toggle "Read" switch) | −80% |

### Error Probability

| Error | Before | After | Why |
|-------|--------|-------|-----|
| Enable wrong permission (decode error) | Medium | Very Low | "CRUDAREI" is easy to misread. Labeled toggles are unambiguous. |
| Edit wrong role's permissions | Low | Very Low | Column headers can be misread. Role selector shows active role clearly. |
| Forget to save changes | Medium | Low | Per-module Save makes it obvious whether changes are persisted. |
| **Overall** | **Medium** | **Very Low** | Fewer decoding steps = fewer errors. |

### Training Time

| Task | Before | After | Improvement |
|------|--------|-------|-------------|
| "Show me how to give Admin read access to Reports" | 3 minutes (explain matrix layout, letter codes, modal, finding the right cell, saving) | 45 seconds (pick role from dropdown, expand group, toggle switch, save) | −75% |

### Information Hierarchy — Module Permissions

#### Level 1 — Always Visible
| Element | Why It Deserves Level 1 |
|---------|------------------------|
| **Role selector** | Without this, the user cannot start the job. |
| **Feature group headings** | Organizes 28 modules into 4 scannable groups. |
| **Module name** | The primary search key. Always visible. |

#### Level 2 — Click Once
| Element | Why Level 2 |
|---------|-------------|
| **Labeled permission toggles** (Create, Read, Update, Delete, Export, Approve, Reveal, Import) | These are what the user came to change. They should be visible as soon as the module card is in view (one click on the accordion). No modal. No extra navigation. |

#### Level 3 — Expand
| Element | Why Level 3 |
|---------|-------------|
| **Administration feature group** | Collapsed by default — less commonly edited. |
| **Integration feature group** | Collapsed by default — rarely edited. |
| **"Reset to Role Default" button** | Per-module. Destructive — one click away, not in the default view. |

#### Level 4 — Audit Only
| Element | Why Level 4 |
|---------|-------------|
| **Permission source indicator** (Role vs Override) | Shown in a tooltip on hover — only needed when auditing why a permission is set. |
| **"Remove all permissions" action** | Destructive — behind confirmation dialog. Only used during role cleanup. |
| **Updated at timestamp** | Show on hover — only needed for audit trails. |

### Things That Should NEVER Be Visible On The Default Screen

| Thing | Why It Should Never Be Default |
|-------|-------------------------------|
| **All N role columns simultaneously** | The user only edits one role at a time. Showing all roles is horizontal-scroll-inducing noise. |
| **Permission letter codes (CRUDAREI)** | Require cognitive decoding every time. Labeled toggles are faster and safer. |
| **All 28 modules expanded** | Overwhelming for a job that targets one module at a time. Collapse by feature group. |

### Can A Brand-New Employee Understand This Page In Under 30 Seconds?

**Before**: Absolutely not. The Super Admin must understand the letter code system, the matrix layout, the modal workflow, and the difference between role permissions and user overrides. Training takes 10+ minutes.

**After**: Yes. "Pick a role from the dropdown. Expand the feature group that has the module you need. Flip the switches you want. Click Save." Training takes 1 minute.

### Why?

Because the new design matches how the user thinks: "I want to change what Admin can do on Reports." Not "I need to find the cell at the intersection of the Reports row and Admin column, decode the letter abbreviation, click to open a modal, find the checkbox, toggle it, and save." The old design is a spreadsheet. The new design is a tool.

### Workflow Connections

```
Module Permissions ──→ Single module updated ──→ User testing (verify access works)
                       │
                       └──→ Users Show → Permissions (verify specific user's access)
                       └──→ Roles → Show (verify role template)
```

Module Permissions is the upstream of all access control. When it is hard to use, every downstream verification step takes longer.

---

## 6. Job E: Audit What A User Can Access

### User Job

> "Jane says she can't access Hosting. I need to check what permissions she actually has."

Super Admin only. Frequency: weekly.

### Workflow Before

```
Step 1: Users Index → Find Jane
─────────────────────────────────
Scan 8-column table. Find Jane.
Established time: 3–5 seconds.

Step 2: Click Permissions button
─────────────────────────────────
There are 6 action buttons. Permissions is purple (shield icon).
Click it.
Time: 1–2 seconds to locate.

Step 3: View users/permissions.blade.php
──────────────────────────────────────────
A 363-line permissions page loads with:
  - Summary stats (roles count, accessible modules, denied modules, etc.)
  - Full permission matrix (28 rows × 8 permission columns)
  - Each cell shows ✓/✗ with source (Role / User Override / None)
  - User override form to add/remove overrides
Time to find the "Hosting" row: 3–5 seconds.
Time to check the "Read" column: 1–2 seconds.
```

### User Frustration Points

| # | Frustration | Severity |
|---|-------------|----------|
| 1 | The permissions page is 363 lines — too much information for a quick check | High |
| 2 | Permission matrix shows all 8 columns even when user only cares about Read | Medium |
| 3 | The Users Create page also has a permission matrix — the same overwhelming pattern repeats | Medium |

### Workflow After

The Users Index simplification (5 columns, 2 inline actions) makes Step 1 faster. The Permissions page itself is not in scope for this audit, but the pattern of simplification (feature group accordions, one role at a time) should apply there too.

**For the audit job specifically:** The existing Users Show page already has a Permission Matrix (lines 79–151 of show.blade.php). This matrix could benefit from the same accordion pattern: collapse by feature group, default Infrastructure expanded.

### Information Hierarchy — Users Show Permission Matrix (Audit View)

#### Level 1 — Always Visible
- Summary stat cards (Roles count, Accessible Modules, Overrides count)
- Module name (in the matrix)

#### Level 2 — Click Once
- Feature group accordion headers

#### Level 3 — Expand
- Individual permission cells within each module row

#### Level 4 — Audit Only
- Source indicators (Role vs User Override) — show on hover

### Things That Should NEVER Be Visible On The Default Screen

The matrix is an audit tool. For the daily "check one permission" job, showing all 28 rows expanded is overwhelming. Feature group accordions should apply here too.

### Can A Brand-New Employee Understand This Page In Under 30 Seconds?

**Yes**, because the 30 Second Rule requires understanding *where they are, what the page is for, and the primary next action* — not understanding every advanced permission concept.

- **Where they are**: "I am looking at user Jane's permissions."
- **What the page is for**: "I can see which modules Jane can access and whether she has Read, Create, etc."
- **Primary next action**: "If I need to check a specific module, I expand the section it's in."

A new employee does not need to understand inheritance chains (Role vs User Override) within 30 seconds. That is a secondary concept discovered during use. The page passes the 30 Second Rule because the primary purpose and next action are clear within seconds.

### Workflow Connections

```
Users Index ──→ Users Show ──→ Permission Matrix
                     │
                     └──→ Users Permissions (edit overrides)
                     └──→ Module Permissions (role-wide changes)
```

The audit job (check a permission) flows naturally into the edit job (change a permission). Both need the same simplification patterns.

---

## 7. Pilot Recommendation

### What To Build First

**Hosting Index simplification.**

### Why This Job, Not This Page

We are not choosing a page. We are choosing a job. Job A (Find a hosting → Access cPanel) is the highest-frequency job in the system:

| Metric | Value |
|--------|-------|
| Roles that do this job daily | 3 (Super Admin, Admin, IT Support) |
| Estimated daily executions | 30–50 |
| Time saved per execution | 3–5 seconds |
| Daily time saved across all roles | 2–4 minutes |
| Yearly time saved (250 working days) | 8–16 hours |
| Training time reduction per new hire | 90 seconds |
| Misclick risk reduction | Low → Very Low |
| Implementation cost | 30 minutes, 4 column removals |

### What Success Looks Like

```
Before:
  "I need example.com's cPanel."
  Open hosting index → scan 10 columns → find "example.com" → click View →
  scroll past Overview → find Access section → click cPanel URL.
  10 seconds.

After:
  "I need example.com's cPanel."
  Open hosting index → see "example.com" in 5-column table → click View →
  cPanel URL is right there. Click it.
  4 seconds.
```

The user does less thinking, less searching, less scrolling, less clicking. Fewer mistakes. Less training. This is the goal.

### Implementation Order

| Phase | Job | Pages Affected | Estimated Effort | Cumulative Value |
|-------|-----|----------------|-----------------|------------------|
| **1** | Find hosting → Access cPanel | Hosting Index | 30 min | Highest-frequency job improved |
| 2 | Find hosting → Access cPanel | Hosting Show (reorder sections) | 2 hrs | Job A fully optimized |
| 3 | Suspend a user | Users Index | 1 hr | Safety improvement |
| 4 | Change module permissions | Module Permissions | 4+ hrs | Cognitive load reduction |

> **Stop**: Awaiting approval of Revision 3 before any implementation.
