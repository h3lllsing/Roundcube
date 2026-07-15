# Credential Reveal Reference

> **Audience:** All Users — **Purpose:** Understand when and how password reveal is granted.

## Overview

Credential reveal is **not** a standalone assignable permission. Instead, the system uses a centralized hierarchy that considers the resource module, your role baseline, and any explicit overrides.

The central gate is `canRevealCredentialsFor()`, which every controller and view delegates to before showing a password.

## Resolution Order

```
canRevealCredentialsFor(module)
│
├─ User is Super Admin? ───────────────────→ GRANTED
│
├─ Resource has no linked module? ─────────→ DENIED (non-SA only)
│
└─ Check canOnModule(module, 'reveal')
   │
   ├─ User has an explicit can_reveal
   │  override (Allow or Deny)? ───────────→ that value wins
   │
   ├─ Any assigned role has can_reveal
   │  enabled? ────────────────────────────→ GRANTED
   │
   ├─ User has an explicit can_reveal=Deny
   │  override? ───────────────────────────→ DENIED (blocks auto-grant)
   │
   ├─ User has Access (can_read) from
   │  role or override? ───────────────────→ GRANTED (auto-grant)
   │
   └─ Otherwise ───────────────────────────→ DENIED
```

## Step by Step

### 1. Super Admin Bypass

If you have the Super Admin role, you can reveal any password for any resource. No further checks apply.

### 2. Null Module

If a resource has no linked module (for example, a personal Vault entry created without associating it to a domain or hosting), reveal is denied for non-Super Admin users. The system has no module context to evaluate permissions against.

### 3. Resource Module — Not Vault Module

The reveal check runs against the **resource's own module**, not a separate "Vault module." For example, when revealing a hosting password from the Hostings module, the system checks your permissions on the Hostings module — it never asks for Vault-module access.

### 4. Explicit User Override

A Super Admin may have set an explicit Allow or Deny for `can_reveal` on your individual user permissions. If set, this value wins immediately regardless of what your roles say.

### 5. Role Baseline (OR Merge)

If any of your roles has `can_reveal` enabled for the resource module, reveal is granted. Because permissions are OR-merged across all roles, one role granting reveal is sufficient.

### 6. Auto-Grant from Access

If you have **Access** (`can_read`) on the resource module — either from a role or a user override — reveal is automatically granted. The system considers that being able to view a resource implies being able to reveal its password.

This auto-grant is blocked if a Super Admin has explicitly set `can_reveal` to Deny on your user override. In that case, the explicit Deny takes priority and Access alone is not enough.

## Vault Entry Access vs Reveal

The system distinguishes between *accessing* a Vault entry (viewing its metadata) and *revealing* its password:

| Action | Gate | Owner Bypass? |
|--------|------|---------------|
| View Vault entry metadata | `canAccessVault()` | Yes — you can always view your own entries |
| Reveal password | `canRevealCredentialsFor()` | No — even entry owners cannot reveal without permission |

**Key distinction:** You may see a Vault entry in your list (because you own it or have module Access), but you cannot reveal its password unless the reveal hierarchy grants it. There is no owner bypass for reveal.

## Linked vs Standalone Vault Entries

### Linked Entry (has a resource module)

When a Vault entry is linked to a resource module (e.g., you save a hosting password as a Vault entry), the reveal check runs against that linked module. If you can reveal on the Hostings module, you can reveal that linked Vault entry too.

### Standalone / Personal Entry (no module)

When a Vault entry has no linked module (a personal credential not associated with any tracked resource), only Super Admin can reveal it. Non-Super Admin users can see the entry in their list but cannot reveal its password.

## Where Reveal Is Used

The reveal check protects password display in the following modules:

- Vault (shared and personal entries)
- Hostings
- VPS
- VoIP (main passwords and extension passwords)
- Service Providers
- Domain Emails
- Other Services
- G-Mails
- Assets (AnyDesk passwords)

Every reveal action is logged as an activity event (`revealed`), triggers a database notification, and fires a webhook if configured. All password data is encrypted at rest using Laravel's `Crypt::encryptString()` and decrypted only at the moment of reveal.
