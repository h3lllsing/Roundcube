# MENU SEMANTIC ANALYSIS

## Every Navigation Label: Meaning, Problem, Fix

---

## Top-Level Items

### Dashboard
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Destination |
| Meaning | "Landing page with overview metrics" |
| Is it clear? | ✅ Yes |
| Technical jargon? | ❌ No |
| Business name? | ✅ Yes |
| Recommendation | Keep as-is |

### Notifications
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Destination |
| Meaning | "Inbox for system alerts and updates" |
| Is it clear? | ✅ Yes |
| Technical jargon? | ❌ No |
| Business name? | ✅ Yes |
| Recommendation | Keep as-is |

---

## Infrastructure Group

### Service Providers
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Entity (vendor) |
| Meaning | "Companies that provide services" |
| Is it clear? | ✅ Yes |
| Technical jargon? | ❌ No |
| Business name? | ✅ Yes |
| Category fit | ❌ Not infrastructure — belongs in Vendors/Suppliers |
| Recommendation | Move to a Vendors group |

### Hosting
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Entity (service) |
| Meaning | "Web hosting accounts" |
| Is it clear? | ✅ Yes |
| Technical jargon? | ❌ No |
| Business name? | ✅ Yes |
| Category fit | ⚠️ Infrastructure is acceptable |
| Recommendation | Keep, or rename to "Web Hosting" for clarity |

### Domains
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Entity (service) |
| Meaning | "Registered domain names" |
| Is it clear? | ✅ Yes |
| Technical jargon? | ❌ No |
| Business name? | ✅ Yes |
| Category fit | ⚠️ Infrastructure is acceptable |
| Recommendation | Keep |

### Domain Emails
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Entity (sub-service) |
| Meaning | "Email accounts associated with domains" |
| Is it clear? | ❌ "Domain Emails" is ambiguous (webmail? forwarding? aliases?) |
| Technical jargon? | ✅ Yes — IT term |
| Business name? | ❌ "Mailboxes" or "Email Accounts" is business language |
| Category fit | ❌ Not co-equal with Domains — should be a sub-section |
| Recommendation | Rename to "Mailboxes" and move under Domains (or nest) |

### VPS Accounts
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Entity (service) |
| Meaning | "Virtual private server instances" |
| Is it clear? | ✅ Yes (to IT audience) |
| Technical jargon? | ⚠️ "VPS" is an industry acronym, generally understood |
| Business name? | ✅ Yes |
| Category fit | ⚠️ Infrastructure is acceptable |
| Recommendation | Keep, or shorten to "Servers" for non-technical users |

### VoIP
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Entity (service) |
| Meaning | "Voice over IP phone systems" |
| Is it clear? | ✅ Yes (to IT audience) |
| Technical jargon? | ⚠️ VoIP is an industry acronym |
| Business name? | ✅ Yes |
| Category fit | ⚠️ Infrastructure is acceptable |
| Recommendation | Keep |

### Other Services
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Entity (miscellaneous) |
| Meaning | "Services that don't fit elsewhere" |
| Is it clear? | ❌ "Other what?" |
| Technical jargon? | ❌ Not jargon — worse: categorically empty |
| Business name? | ❌ "Other" is never a business category |
| Category fit | ❌ Fails every IA test |
| Recommendation | **Rename urgently.** Options:
- "SaaS Subscriptions" (if mostly cloud services)
- "Software Services" (if software-as-a-service)
- "Third-Party Apps" (if external platforms)
- Define actual scope and label accordingly |

### Renewals
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Process / workflow |
| Meaning | "Upcoming expiry and renewal dates across services" |
| Is it clear? | ✅ Yes |
| Technical jargon? | ❌ No |
| Business name? | ✅ Excellent |
| Category fit | ❌ Renewals is a TIME-based cross-cutting concern. It does not belong in Infrastructure. |
| Recommendation | Move to a higher-level or cross-cutting group (Operations or dedicated "Renewals" section) |

### Assets
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Entity (resource) |
| Meaning | "IT hardware and software assets" |
| Is it clear? | ✅ Yes |
| Technical jargon? | ❌ No |
| Business name? | ✅ Yes |
| Category fit | ❌ Assets are not infrastructure. Infrastructure is what runs services. Assets are what users consume. |
| Recommendation | Move to a separate "Assets" group |

---

## Credentials Group

### My Credentials
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Destination (personal filter) |
| Meaning | "Passwords and secrets I created" |
| Is it clear? | ✅ Yes |
| Technical jargon? | ❌ No |
| Business name? | ✅ Yes |
| Category fit | ⚠️ Duplicate purpose with Shared Credentials |
| Recommendation | Merge into single "Credentials" with personal/shared filter |

### Shared Credentials
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Destination (team filter) |
| Meaning | "Passwords and secrets shared with my team" |
| Is it clear? | ✅ Yes |
| Technical jargon? | ❌ No |
| Business name? | ✅ Yes |
| Category fit | ⚠️ Duplicate purpose with My Credentials |
| Recommendation | Merge into single "Credentials" |

---

## Operations Group

### My Tasks
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Destination (personal filter) |
| Meaning | "Tasks assigned to me" |
| Is it clear? | ✅ Yes |
| Technical jargon? | ❌ No |
| Business name? | ✅ Yes |
| Category fit | ⚠️ Duplicate with Task Management |
| Recommendation | Merge into single "Tasks" |

### Task Management
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Destination (all items) |
| Meaning | "All tasks in the system" |
| Is it clear? | ✅ "Task Management" is clear |
| Technical jargon? | ❌ No |
| Business name? | ✅ Yes |
| Category fit | ⚠️ Duplicate with My Tasks |
| Recommendation | Merge into single "Tasks" |

### Calendar
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Tool / view |
| Meaning | "Date-based view of events, renewals, and tasks" |
| Is it clear? | ❌ "Calendar" alone is too vague. Calendar of WHAT? |
| Technical jargon? | ❌ No |
| Business name? | ⚠️ Acceptable but needs context |
| Category fit | ⚠️ Calendar is a view type, not an operation |
| Recommendation | Rename to "Timeline" or ensure title contextualizes content |

---

## Administration Group (Super-Admin)

### Users
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Entity (identity) |
| Meaning | "System user accounts" |
| Is it clear? | ✅ Yes |
| Technical jargon? | ❌ No |
| Business name? | ✅ Yes |
| Category fit | ✅ Belongs in identity management |
| Recommendation | Keep |

### Roles
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Entity (access control) |
| Meaning | "Named sets of permissions" |
| Is it clear? | ✅ Yes |
| Technical jargon? | ❌ No |
| Business name? | ✅ Yes |
| Category fit | ⚠️ Should be merged with Permissions, Privileges, Role Templates |
| Recommendation | Merge into "Roles & Permissions" |

### Role Templates
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Entity (configuration) |
| Meaning | "Predefined role configurations" |
| Is it clear? | ❓ "Templates" for what? |
| Technical jargon? | ⚠️ Borderline |
| Business name? | ⚠️ Tolerable |
| Category fit | ❌ Too granular for top-level; should be under Roles |
| Recommendation | Merge into "Roles & Permissions" |

### Privileges
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Entity (access control atom) |
| Meaning | "Individual permission actions" |
| Is it clear? | ❓ Same meaning as "Permissions" — redundant |
| Technical jargon? | ✅ Yes — Tyro package exposed |
| Business name? | ❌ Unclear differentiation from Permissions |
| Category fit | ❌ Redundant with Permissions |
| Recommendation | Merge into "Roles & Permissions" |

### Modules
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Entity (configuration) |
| Meaning | "Application modules/sections" |
| Is it clear? | ❌ "Modules" is internal architecture |
| Technical jargon? | ✅ Yes |
| Business name? | ❌ "Applications" or "Sections" would be clearer |
| Category fit | ⚠️ Belongs with Features |
| Recommendation | Keep under config section, but rename to "Sections" or "Applications" |

### Permissions
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Entity (access control) |
| Meaning | "Module-level permission matrix" |
| Is it clear? | ❓ Generic name — Permissions for what? |
| Technical jargon? | ⚠️ Borderline |
| Business name? | ⚠️ Acceptable with context |
| Category fit | ❌ Redundant with Privileges |
| Recommendation | Merge into "Roles & Permissions" |

### Features
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Entity (configuration) |
| Meaning | "Feature groupings for modules" |
| Is it clear? | ❌ "Features" of what? |
| Technical jargon? | ✅ Yes |
| Business name? | ❌ "Capabilities" or "Feature Groups" would be clearer |
| Category fit | ⚠️ Belongs with Modules |
| Recommendation | Merge with Modules into "Module Configuration" |

### SMTP Profiles
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Entity (configuration) |
| Meaning | "Email server connection settings" |
| Is it clear? | ❌ "SMTP" is an acronym, not a business term |
| Technical jargon? | ✅ Yes — protocol acronym |
| Business name? | ❌ "Mail Settings" or "Email Configuration" |
| Category fit | ❌ Not identity-related; should be in System settings |
| Recommendation | Rename to "Mail Settings" and move to System group |

### Activity Logs
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | History / audit |
| Meaning | "Record of system changes and actions" |
| Is it clear? | ✅ Yes |
| Technical jargon? | ⚠️ "Activity" is acceptable; "Logs" is slightly technical |
| Business name? | ⚠️ "Audit Trail" or "Change History" is more business-appropriate |
| Category fit | ✅ Belongs in audit section |
| Recommendation | Rename to "Audit Trail" |

### Login Audits
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | History / audit |
| Meaning | "Record of user login attempts" |
| Is it clear? | ✅ Yes (with slight jargon) |
| Technical jargon? | ⚠️ "Audit" is acceptable |
| Business name? | ✅ "Login History" would be clearer |
| Category fit | ✅ Belongs with Activity Logs in audit section |
| Recommendation | Merge with Activity Logs into "Audit Trail" or rename to "Login History" |

### Import
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Tool / action |
| Meaning | "Bulk import data from CSV" |
| Is it clear? | ❓ "Import" what? |
| Technical jargon? | ❌ No |
| Business name? | ⚠️ "Data Import" would be clearer |
| Category fit | ❌ Not an administration function — belongs in Operations/Tools |
| Recommendation | Move to Operations group, rename to "Data Import" |

### Attachments
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Entity (cross-cutting) |
| Meaning | "Files attached to records" |
| Is it clear? | ✅ Yes |
| Technical jargon? | ❌ No |
| Business name? | ✅ Yes |
| Category fit | ❌ Attachments are associated with specific records — a standalone list is an edge case |
| Recommendation | Keep as utility, or move to Operations |

### Webhooks
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Entity (configuration) |
| Meaning | "Outgoing HTTP notifications on events" |
| Is it clear? | ❌ "Webhook" is developer jargon |
| Technical jargon? | ✅ Yes — developer term |
| Business name? | ❌ "Integrations" or "Automations" |
| Category fit | ❌ Not identity-related; should be in System/Integrations |
| Recommendation | Rename to "Integrations" and move to System group |

### API Access
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Entity (configuration) |
| Meaning | "API tokens for external access" |
| Is it clear? | ❓ "API Access" — what can I do here? |
| Technical jargon? | ✅ Yes — developer term |
| Business name? | ❌ "Developer Tokens" or "Integration Keys" |
| Category fit | ❌ Not identity-related; should be in System/Integrations |
| Recommendation | Rename to "Developer Access" and move to System group |

---

## Reports Group

### Reports
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Destination |
| Meaning | "Analytics and reporting dashboard" |
| Is it clear? | ✅ Yes |
| Technical jargon? | ❌ No |
| Business name? | ✅ Yes |
| Category fit | ✅ Single group with single item — should be expanded or integrated |
| Recommendation | Keep as group, add more report types over time |

---

## Account Group

### My Profile
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Destination (personal) |
| Meaning | "My name, email, password settings" |
| Is it clear? | ✅ Yes |
| Technical jargon? | ❌ No |
| Business name? | ✅ Yes |
| Recommendation | Keep |

### My Access
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Destination (personal) |
| Meaning | "List of my module permissions" |
| Is it clear? | ✅ Yes — good label |
| Technical jargon? | ❌ No |
| Business name? | ✅ Yes |
| Recommendation | Keep |

### Help Center
| Dimension | Assessment |
|-----------|-----------|
| Semantic type | Destination |
| Meaning | "User guide and documentation" |
| Is it clear? | ✅ Yes |
| Technical jargon? | ❌ No |
| Business name? | ✅ Yes |
| Recommendation | Keep |

---

## Summary: Labels That Need Immediate Change

| Current Label | Problem | Recommended Label |
|---------------|---------|-------------------|
| **Other Services** | IA violation — "Other" is not a category | SaaS Subscriptions |
| **Domain Emails** | Technical name, wrong hierarchy | Mailboxes (nest under Domains) |
| **SMTP Profiles** | Protocol acronym | Mail Settings |
| **Webhooks** | Developer jargon | Integrations |
| **API Access** | Developer jargon | Developer Tokens |
| **Activity Logs** | Technical logs terminology | Audit Trail |
| **Login Audits** | Slightly technical | Login History |
| **My Tasks** | Redundant duplicate | Merge into Tasks |
| **Task Management** | Redundant duplicate | Merge into Tasks |
| **My Credentials** | Redundant duplicate | Merge into Credentials |
| **Shared Credentials** | Redundant duplicate | Merge into Credentials |
| **Privileges** | Undifferentiated from Permissions | Merge into Roles & Permissions |
| **Role Templates** | Too granular | Merge into Roles & Permissions |
| **Permissions** | Generic name | Merge into Roles & Permissions |
| **Modules** | Internal architecture | Applications / Sections |
| **Features** | Unclear scope | (Merge with Modules) |
| **Import** | Incomplete label | Data Import |
| **Renewals** | Wrong parent group | Move to Operations |

**Total labels needing change: 18 of 34 (53%)**

---

## Naming Convention Analysis

| Feature | Current Convention | Issue | Recommended Convention |
|---------|-------------------|-------|----------------------|
| Entity labels | Plural (Domains, Assets, Users) | ✅ Correct | Keep |
| Action labels | Missing | ❌ Import (should be "Data Import") | Action + noun |
| Personal vs. shared | Prefix (My X, Shared X) | ❌ Creates duplicate entries | Remove prefix; use filter |
| Technical acronyms | SMTP, VoIP, VPS, API | ⚠️ Mixed | Expand or use business terms |
| Group labels | Noun-based (Infrastructure, Operations) | ✅ Correct | Keep but rename categories |
