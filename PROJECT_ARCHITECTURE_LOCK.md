# OpsPilot v1.1 — Master Constitution & Delivery Rules (LOCKED)

**Status:** FINAL. Do not challenge unless explicitly requested.

---

## PROJECT STATUS

OpsPilot v1.0 is LIVE.

Architecture Phase: COMPLETE ✅

Business Rules: LOCKED ✅

Permission Model: LOCKED ✅

Documentation: LOCKED ✅

Implementation Mode: ACTIVE 🚀

This project is now in DELIVERY MODE.

No architecture redesign, business discussions, or product philosophy changes are allowed unless explicitly requested by the Project Owner.

---

# PROJECT PURPOSE (LOCKED)

OpsPilot is NOT:

* ERP
* CRM
* Accounting Software
* Billing Software

OpsPilot IS:

An Internal IT Operations Management Portal that serves as the single operational memory of the IT Department.

Primary Goals:

* Answer operational questions within seconds.
* Reduce duplicate information.
* Improve operational visibility.
* Maintain strict role-based security.
* Keep long-term maintenance simple.
* Provide a single source of truth for IT operations.

---

# BUSINESS REALITY (LOCKED)

## Super Admin

Owns everything.

Responsibilities include:

* Full CRUD
* User Management
* Role Management
* Permission Overrides
* Infrastructure
* Hosting
* Domains
* VPS
* Providers
* Renewals
* Monitoring
* Reports
* Vault
* Assets
* Activity Logs

## IT Management

Needs:

* Hosting
* Domains
* Providers
* Renewals
* Monitoring
* Reports
* Expenses
* Activity

## IT Support

Needs operational information only.

Typical work:

* SMTP
* Email configuration
* Hosting lookup
* Domain lookup
* Credentials (where permitted)

## Developers

Developers consume infrastructure.

They need:

* Hosting
* Related Domains
* Domain Emails
* cPanel Access (if permitted)

They do NOT manage:

* Providers
* Renewals
* Monitoring
* Expenses
* Infrastructure

## Office Management

Needs:

* Expenses
* Reports
* Renewal Tracker

Does NOT manage infrastructure.

---

# PERMISSION MODEL (LOCKED)

Default authorization is Role-Based.

Super Admin may create User Permission Overrides.

Effective Permission = Role Permission × User Override.

Every authorization decision MUST use Effective Permission.

This includes:

* Sidebar
* Navigation
* Resources
* Controllers
* Policies
* API
* Blade
* Buttons
* Widgets
* Actions

Permission bypasses are NEVER allowed.

---

# PRODUCT PHILOSOPHY (LOCKED)

There is NO Master Module.

Independent Business Entities:

* Providers
* Hosting
* Domains
* Domain Emails
* VPS
* VoIP
* Other Services
* Assets
* Vault
* Renewals
* Monitoring
* Tasks

Never merge business entities.

Improve relationships only.

---

# UI PHILOSOPHY (LOCKED)

Every Show Page must become a complete Operational Dashboard.

Standard layout:

Overview → Access → Technical → Relationships → Financial (if applicable) → Dates → Status → Notes → Activity

Related information should always be visible.

---

# COPY BUTTON STANDARD (LOCKED)

Every copy action must:

* Use the same icon.
* Use the same placement.
* Use the same behavior.

Support copying:

* Username
* Password
* Email
* URL
* IP Address
* Server
* API Keys (where applicable)

---

# SSL POLICY (LOCKED)

Do NOT create an SSL module.

Hide SSL fields from forms.

Keep database compatibility.

Display SSL only when a value exists.

---

# RENEWAL POLICY (LOCKED)

Renewals are completely manual.

No hidden automation.

No automatic synchronization.

No background record creation.

---

# CLOUDFLARE STRATEGY (LOCKED)

Cloudflare is NOT a module.

Hosting remains independent.

Domains remain independent.

Relationship only.

Developer visibility: Hosting → Related Domains → Cloudflare Yes/No → Domain Emails → Open Hosting

No infrastructure management.

---

# OPERATIONAL MEMORY REQUIREMENT

OpsPilot must always answer:

* Where is it hosted?
* Which provider owns it?
* Which domain belongs to it?
* Which hosting belongs to it?
* Which emails belong to it?
* When will it expire?
* Who modified it?
* What changed?
* Which credentials are required?

---

# IMPLEMENTATION MODE (LOCKED)

Do NOT:

* Redesign architecture
* Merge modules
* Introduce hidden automation
* Change business philosophy
* Change permission philosophy
* Create unnecessary abstractions

Implementation only.

---

# IMPLEMENTATION ROADMAP

## ✅ Sprint 1 — Standard Show Pages

COMPLETE. 12 modules standardized to sectioned `<x-card>` layout.

## Sprint 2

Copy Button Standardization

↓

Regression

↓

Browser Verification

↓

Audit Update

↓

STOP

---

## Sprint 3

Relationship Dashboards

Hosting → Domains → Emails → Providers → Renewals → Monitoring

Apply same philosophy to every module.

↓

Regression

↓

Audit

↓

STOP

---

## Sprint 4

Hosting / Domain Visibility

Developer should instantly see: Hosting → Domain → Domain Emails → Cloudflare Status → Hosting Details

↓

STOP

---

## Sprint 5

Role Dashboards

Different landing experience for: Super Admin, IT Management, IT Support, Developer, Office Management

No permission changes. Visibility only.

↓

STOP

---

# DELIVERY PROCESS (MANDATORY)

Every sprint MUST follow:

PRE-FLIGHT → Implementation → Regression Test → Browser Verification → Documentation Update → STOP → Wait for Approval

Never start the next sprint automatically.

---

# PRE-FLIGHT CHECKLIST

Before implementation:

* Review affected modules.
* Review permission impact.
* Review relationships.
* Identify risks.
* Prepare implementation checklist.

---

# IMPLEMENTATION RULES

* Minimal code changes only.
* Stay within sprint scope.
* No unrelated refactoring.
* Reuse existing components.
* Maintain consistency.
* Keep code simple.
* Keep architecture intact.

---

# TESTING RULES

Every sprint must pass:

* Existing functionality
* CRUD
* Relationships
* Policies
* Effective Permissions
* Browser Verification
* Responsive Layout
* No PHP errors
* No JavaScript errors
* No new console warnings

If available, also pass: PHPUnit / Pest, PHPStan / Larastan, Laravel Pint.

---

# PERFORMANCE RULES

Never introduce:

* N+1 queries
* Duplicate queries
* Heavy dashboard widgets
* Unnecessary eager loading
* Expensive loops

Performance regressions are not acceptable.

---

# SECURITY RULES

Never bypass:

* Policies
* Gates
* Effective Permission
* Authorization Middleware
* Activity Logging
* Validation

Security always has priority over convenience.

---

# DATABASE RULES

No destructive migrations.

Never break production compatibility.

Existing production data must remain usable.

Backward compatibility first.

---

# UI RULES

Keep:

* Existing navigation
* Existing design language
* Existing patterns

Avoid:

* Custom JavaScript
* Inconsistent styling
* Random UI patterns

---

# DOCUMENTATION RULES

Project root should contain only:

* README.md
* BUSINESS_RULES.md
* PROJECT_ARCHITECTURE_LOCK.md
* FINAL_RELEASE_AUDIT.md
* CHANGELOG.md
* DEPLOY.md

Archive old discussions under `/docs/archive`.

Reference material under `/docs/reference`.

---

# VERSION CONTROL RULES

Treat every sprint as an independent implementation unit.

Do not mix multiple sprints together.

Complete one sprint before beginning another.

Maintain a clear implementation history through structured progress updates and changelog entries.

---

# BUG HANDLING POLICY

**Critical Bug** → Fix immediately → Update Audit → Continue sprint.

**Non-Critical Bug** → Document → Do NOT fix during the active sprint → Continue implementation.

Never expand sprint scope.

---

# DEFINITION OF DONE

A sprint is complete ONLY when:

* Feature implemented
* Existing functionality preserved
* Permissions verified
* CRUD tested
* Relationships tested
* Browser verified
* No PHP errors
* No JavaScript errors
* No security regressions
* No performance regressions
* FINAL_RELEASE_AUDIT.md updated
* CHANGELOG.md updated
* Ready for approval

---

# AI IMPLEMENTATION RULES

The implementation agent MUST:

* Follow locked architecture.
* Follow business rules.
* Follow permission model.
* Follow sprint scope.
* Keep code maintainable.
* Reuse existing components.
* Prefer simplicity.

The implementation agent MUST NEVER:

* Redesign architecture.
* Merge modules.
* Change business philosophy.
* Change permission philosophy.
* Add hidden automation.
* Introduce unnecessary complexity.
* Refactor unrelated code.
* Modify future sprint features.

---

# CONFLICT RESOLUTION

If implementation conflicts with the locked architecture:

STOP → Explain the conflict → Suggest the smallest possible solution → Wait for approval.

Never redesign without approval.

---

# FINAL RULE

Architecture is LOCKED.

Business Rules are LOCKED.

Permission Model is LOCKED.

Documentation is LOCKED.

Implementation Mode is ACTIVE.

The only objective is disciplined implementation through small, regression-tested, production-safe increments.

No feature creep. No architecture debates. No unnecessary abstractions. No assumptions.

Implement exactly the approved sprint. Stop after completion. Wait for approval before continuing.
