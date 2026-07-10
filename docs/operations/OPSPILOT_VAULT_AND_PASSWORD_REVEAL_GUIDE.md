# OpsPilot Vault & Password Reveal Guide

## Overview

OpsPilot has two ways to handle passwords:

1. **Password fields in module records** (e.g., Hosting password, VPS password)
2. **Vault** (secure, encrypted credential storage)

This guide explains when to use each and how password reveal works.

---

## Vault vs Password Fields

### Password Fields in Module Records

Every infrastructure module has a **Password** field:

- Service Providers
- Hosting
- VPS
- VoIP
- Domain Emails
- Other Services
- Expiry Trackers

**Use password fields when:**

- The password is specific to that one record
- You need the password to login to that service regularly
- Only you or your team manages this service

**Example:** Your hosting control panel password. You login weekly to check stats — store it in the Hosting record.

### Vault

The Vault stores credentials separately from any specific record.

**Use Vault when:**

- The credential is shared across multiple services (e.g., AWS root account)
- Multiple team members need the same credential
- The credential is not tied to one specific infrastructure record
- You want better audit tracking (every reveal is logged)

**Example:** Company AWS root credentials, shared database admin password, API keys for third-party services.

### When to Use Which — Quick Decision Guide

```
Is this password specific to one service record?
  Yes → Store in the module's Password field
  No  → Is it shared with the team?
          Yes → Store in Shared Vault
          No  → Store in My Vault (personal credentials)
```

---

## Vault Types

### My Credentials (My Vault)

- Only you can see these
- Good for personal API keys, personal logins
- Located in sidebar → Credentials → My Credentials

### Shared Credentials

- Users with Read permission on the associated module can see these
- Good for team passwords
- Located in sidebar → Credentials → Shared Credentials

**Note:** If you set a Module on a shared Vault entry, users need Read permission on that module to see it. If no module is set, only Super Admin can see it.

---

## Creating a Vault Entry

1. Go to **Credentials → My Credentials** or **Shared Credentials**
2. Click **Create**
3. Fill in:
   - **Service Name:** (required) — e.g., "AWS Root Account"
   - **Service URL:** (optional) — e.g., https://aws.amazon.com
   - **Username:** (optional)
   - **Password:** (encrypted on save)
   - **Module:** (for shared credentials) — controls who can see it
   - **Description:** (optional)
4. Click **Save**

---

## Revealing a Password

When you need to see a password in plain text:

1. Open the Vault entry
2. Click **Reveal Password**
3. The password is shown on screen
4. The system logs: "User X revealed password for Vault entry Y at timestamp Z"

### Important Rules for Reveal:

| Rule | Explanation |
|------|-------------|
| **Who can reveal?** | Super Admin can reveal any Vault entry. Admin/User needs the **Reveal** permission on the module. |
| **Reveal is always logged** | There is no way to view a password without it being logged. |
| **Throttling** | Reveal is limited to 10 attempts per minute per user. |
| **No copy-paste** | The password is shown on screen. You must type it manually or use the Reveal → Copy feature (if available in your browser). |

### What the Activity Log Shows After a Reveal:

```
Event: vault_password_revealed
Description: User Ahmad Raza revealed password for "AWS Root Account"
Causer: Ahmad Raza (ID: 5)
Timestamp: 2026-06-27 14:30:00
```

---

## Reveal on Infrastructure Modules

Some infrastructure modules also have password reveal:

| Module | How to Reveal | Throttled |
|--------|--------------|-----------|
| Hosting | View record → Click password field | Yes (10/min) |
| VPS | View record → Click password field | Yes (10/min) |
| VoIP | View record → Click password | Yes (10/min) |
| VoIP Extension | View record → Click extension password | Yes (10/min) |
| Service Providers | View record → Click password | Yes (10/min) |
| Domain Emails | View record → Click password | Yes (10/min) |
| Other Services | View record → Click password | Yes (10/min) |

**Note:** These reveals are also logged in Activity Logs.

---

## Security Best Practices

### Passwords

- Never share your login password with anyone
- Use strong passwords (minimum 8 characters, mix of letters, numbers, symbols)
- Change passwords periodically
- Do not use the same password across multiple services

### Vault

- Store shared team passwords in Vault, not in individual module records
- Do not store personal passwords (like your email password) in Shared Vault
- Review Vault entries regularly and remove outdated ones
- Check Activity Logs periodically for unusual reveal patterns

### Activity Logs

- Super Admin should check Activity Logs weekly for:
  - Unexpected password reveals
  - Multiple failed reveals
  - Reveals at unusual hours

---

## Common Mistakes

| Mistake | Why It Is a Problem | Correct Approach |
|---------|---------------------|------------------|
| Storing root password in Hosting record | Any user with Hosting access can try to reveal it | Store in Shared Vault |
| Not setting Module on shared Vault entry | Only Super Admin can see it | Set a module so the right team can access |
| Revealing passwords unnecessarily | Every reveal is logged. Too many reveals look suspicious | Reveal only when needed |
| Forgetting Vault password | Passwords are encrypted — they cannot be recovered | Super Admin can create a new entry |
| Copying passwords to insecure places (Sticky Notes, email) | Security risk | Use Vault reveal each time you need the password |
