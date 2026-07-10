# BUSINESS WORKFLOW MAPPING

## Current Navigation vs. Actual Business Workflows

---

## The Core Problem

The current navigation represents **database tables**, not **business workflows**. A user navigating by workflow intent cannot find their task efficiently.

**Example — IT operator workflow: "Set up a new website"**

Current path:
```
Infrastructure → Service Providers (create vendor)
Infrastructure → Hosting (create hosting account)  
Infrastructure → Domains (register domain)
Infrastructure → Domain Emails (create mailbox)
Infrastructure → VPS (if needed)
Credentials → Shared Credentials (store passwords)
```

**6 menu items from 2 groups** for one business workflow. The user must know WHICH infrastructure items are involved and navigate to each separately. There is no "New Website" workflow entry point.

---

## Identified Business Workflows (Not Represented in Navigation)

### Workflow 1: Onboard New Vendor
| Step | Current Nav Item | Group |
|------|-----------------|-------|
| 1. Create provider record | Service Providers | Infrastructure |
| 2. Create services | Hosting / Domains / VPS / VoIP | Infrastructure |
| 3. Configure vendor credentials | Shared Credentials | Credentials |
| 4. Set renewal reminders | Renewals | Infrastructure |

**Missing:** A "Vendor Onboarding" entry or view. Currently 4 separate navigation targets.

### Workflow 2: Register/Renew a Domain
| Step | Current Nav Item | Group |
|------|-----------------|-------|
| 1. Check domain availability | N/A (no UI) | — |
| 2. Create domain record | Domains | Infrastructure |
| 3. Create email accounts | Domain Emails | Infrastructure |
| 4. Set renewal reminder | Renewals | Infrastructure |
| 5. Track expiry | Renewals | Infrastructure |

**Missing:** Domain lifecycle (registration → active → renewal → expiry) as a continuous workflow. Currently split across Domains (registration), Domain Emails (setup), and Renewals (expiry). The user must manually connect these.

### Workflow 3: Provision Hosting
| Step | Current Nav Item | Group |
|------|-----------------|-------|
| 1. Select provider | Service Providers | Infrastructure |
| 2. Create hosting record | Hosting | Infrastructure |
| 3. Create related domains | Domains | Infrastructure |
| 4. Set up DNS | N/A (no UI) | — |
| 5. Store control panel password | Shared Credentials | Credentials |

**Missing:** A "Provisioning" view that links provider → hosting → domain → credentials as a single flow.

### Workflow 4: Credential Rotation / Security Review
| Step | Current Nav Item | Group |
|------|-----------------|-------|
| 1. Review all stored credentials | Shared Credentials | Credentials |
| 2. Check password age/recent changes | N/A (no UI) | — |
| 3. Update credentials in vault | Shared Credentials | Credentials |
| 4. Log the change | N/A (auto-logged in Activity) | — |

**Missing:** Credential lifecycle management. No "last rotated" date, no "stale password" indicator, no bulk rotation workflow.

### Workflow 5: Asset Lifecycle
| Step | Current Nav Item | Group |
|------|-----------------|-------|
| 1. Acquire asset | Assets | Infrastructure |
| 2. Assign to user | Assets (assignment) | Infrastructure |
| 3. Track maintenance | N/A (no UI) | — |
| 4. Return / dispose | Assets | Infrastructure |

**Missing:** Asset lifecycle stages visible from navigation. "Assign", "Return" are actions buried in asset detail pages.

### Workflow 6: Incident Response / Password Retrieval
| Step | Current Nav Item | Group |
|------|-----------------|-------|
| 1. Find affected service | Depends on service type | Infrastructure |
| 2. Retrieve credentials | Password reveal (web route) | Infrastructure detail page |
| 3. Log the event | N/A (auto-logged in Activity Logs) | Administration |

**Missing:** Emergency access workflow. No "Emergency Credential Access" entry point. Password retrieval requires navigating to the specific service detail page first.

### Workflow 7: Employee Offboarding
| Step | Current Nav Item | Group |
|------|-----------------|-------|
| 1. Locate user | Users | Administration |
| 2. Find assigned assets | Assets (by user filter) | Infrastructure |
| 3. Reassign tasks | Tasks (by assignee) | Operations |
| 4. Revoke credentials | N/A (no per-user credential report) | — |
| 5. Suspend/delete user | Users | Administration |

**Missing:** Cross-cutting employee offboarding workflow. Requires 3+ groups and 4+ navigation targets.

---

## What Each Current Menu Item Actually Represents (Workflow View)

| Menu Item | Database Entity | Workflow Stage | Workflow Completeness |
|-----------|---------------|----------------|----------------------|
| Dashboard | — | Overview / Monitoring | N/A |
| Notifications | — | Alerts / Triggers | N/A |
| Service Providers | `service_providers` | Procurement / Vendor mgmt | ❌ No contract/renewal linkage |
| Hosting | `hostings` | Service provisioning | ❌ No provider→hosting→domain flow |
| Domains | `domains` | Domain registration | ❌ Split from Domain Emails, Renewals |
| Domain Emails | `domain_emails` | Email provisioning | ❌ Sub-feature of Domains |
| VPS Accounts | `vps` | Server provisioning | ❌ No provider→VPS flow |
| VoIP | `voip` | Telephony provisioning | ❌ No provider→VoIP flow |
| Other Services | `other_services` | Miscellaneous | ❌ No category definition |
| Renewals | `expiry_trackers` | Expiry management | ❌ Cross-cutting, not scoped to Infrastructure |
| Assets | `assets` | Asset lifecycle | ❌ Partial (acquire/assign/return but no maintenance) |
| My Credentials | `vault_entries` (own) | Personal password mgmt | ❌ Split from Shared |
| Shared Credentials | `vault_entries` (shared) | Team password mgmt | ❌ Split from My |
| My Tasks | `tasks` (own) | Personal task mgmt | ❌ Split from All |
| Task Management | `tasks` (all) | Team task mgmt | ❌ Split from My |
| Calendar | — | Date visualization | ❓ Purpose unclear (renewal dates? task dates?) |
| Users | `users` | Identity administration | ✅ Complete |
| Roles | `roles` | Role-based access | ✅ Complete |
| Role Templates | `role_templates` | Permission templates | ✅ Complete |
| Privileges | `privileges` | Permission atoms | ✅ Complete |
| Modules | `modules` | Module configuration | ✅ Complete |
| Permissions | `module_role_permissions` | Module permission matrix | ✅ Complete |
| Features | `features` | Feature grouping | ✅ Complete |
| SMTP Profiles | `smtp_profiles` | Mail transport config | ✅ Complete |
| Activity Logs | `activity_log` | Audit trail | ✅ Complete |
| Login Audits | `login_audits` | Access logging | ✅ Complete |
| Import | — | Bulk data import | ⚠️ One-off tool |
| Attachments | `attachments` | File storage | ⚠️ Cross-cutting utility |
| Webhooks | `webhooks` | Automation | ✅ Complete |
| API Access | `personal_access_tokens` | Token management | ✅ Complete |
| Reports | — | Reporting | ✅ Complete |
| My Profile | `users` | Account settings | ✅ Complete |
| My Access | — | Permission view | ✅ Complete |
| Help Center | — | Documentation | ✅ Complete |

---

## Navigation Organization by Business Domain (Proposed)

The 34 items should be regrouped into business domains that match how IT operators actually work:

### DOMAIN 1: SERVICES (what we operate)
*Was: Infrastructure (partial)*
- Hosting Plans
- Domain Names (includes mailboxes)
- Virtual Servers
- Phone Systems (VoIP)
- SaaS Subscriptions (was Other Services)

### DOMAIN 2: VENDORS (who we buy from)
*Was: Infrastructure → Service Providers + Renewals (partial)*
- Providers & Contracts
- Renewals Calendar

### DOMAIN 3: ASSETS (what we own)
*Was: Infrastructure → Assets*
- Hardware
- Software Licenses

### DOMAIN 4: CREDENTIALS (what we secure)
*Was: Credentials*
- Password Vault (My + Shared unified)

### DOMAIN 5: OPERATIONS (what we do daily)
*Was: Operations*
- Tasks
- Calendar
- Data Import

### DOMAIN 6: CONFIGURATION (how the system is set up)
*Was: Administration (split)*
- Users
- Roles & Permissions
- Module Configuration
- System Settings (Mail, Integrations, API)
- Audit Trail

### DOMAIN 7: REPORTS (what we measure)
*Was: Reports*
- Reports Dashboard

### DOMAIN 8: ACCOUNT (personal)
*Was: Account*
- My Profile
- My Permissions
- Help Center

---

## Workflow Gaps Identified

The current navigation completely lacks entry points for these common IT workflows:

1. **"New site launch"** — requires 5+ navigation steps across 2 groups
2. **"Emergency password access"** — requires navigating to specific service first
3. **"Employee offboarding"** — requires 4+ navigation steps across 3 groups
4. **"Vendor consolidation"** — no vendor-centric view of all services from one provider
5. **"Expiry review"** — Renewals exists but is not cross-referenced to services
6. **"Security audit"** — no credential age report, no stale password list
7. **"Budget review"** — cost data exists per record but no aggregate cost view per vendor

**A workflow-first redesign would require backend changes** (new aggregate views, cross-entity queries). The navigation-only fixes in the previous section address grouping and naming — real workflow support would need new features.
