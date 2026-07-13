# OpsPilot Sidebar Ownership Map

> Source: `resources/views/components/sidebar-nav-groups.blade.php` (148 lines)
> Layout: `resources/views/layouts/admin.blade.php` (passes `$show*` flags)

---

## 1. Sidebar Group Architecture

```
sidebar-nav-groups.blade.php
├── Dashboard              (always shown)
├── Monitoring             ($showMonitoring flag)
├── Notifications          (always shown, badge for unread)
├── Infrastructure         ($show* flags, collapsible)
│   ├── Vendors           ($showProviders)
│   ├── Hosting           ($showHostings)
│   ├── Domains           ($showDomains)
│   ├── Domain Emails     ($showEmails)
│   ├── VPS Accounts      ($showVps)
│   ├── VoIP              ($showVoip)
│   ├── SaaS Subscriptions ($showOtherServices)
│   ├── Renewals          ($showExpiryTrackers)
│   ├── Hardware Assets   ($showAssets)
│   └── G-Mails           ($showGMails)
├── Credentials            ($showVault || $showMyVault, collapsible)
│   ├── My Credentials    ($showMyVault)
│   └── Shared Credentials ($showVault)
├── Operations             (always shown, collapsible)
│   ├── My Tasks          (always — tasks.my)
│   ├── Task Management   (always — tasks.index)
│   ├── Calendar          (always)
│   └── Notes             ($showNotes flag)
├── Administration         (@hasrole('super-admin'), collapsible)
│   ├── Users
│   ├── Roles
│   ├── Role Templates
│   ├── Privileges
│   ├── Modules
│   ├── Permissions
│   ├── Features
│   ├── Mail Settings
│   ├── Audit Trail
│   ├── Login History
│   ├── Import
│   ├── Attachments
│   ├── Integrations
│   └── API Access
├── Reports                (@hasrole('super-admin'), collapsible)
│   └── Reports
└── Account                (always shown, collapsible)
    ├── My Profile
    ├── My Access
    └── Help Center
```

---

## 2. Visibility Flag to Route Mapping

### Infrastructure Group
| Sidebar Label | Flag | Route Name | Controller |
|--------------|------|-----------|-----------|
| Vendors | `$showProviders` | `service-providers.*` | ServiceProviderController |
| Hosting | `$showHostings` | `hostings.*` | HostingController |
| Domains | `$showDomains` | `domains.*` | DomainController |
| Domain Emails | `$showEmails` | `domain-emails.*` | DomainEmailController |
| VPS Accounts | `$showVps` | `vps.*` | VpsController |
| VoIP | `$showVoip` | `voip.*` | VoipController |
| SaaS Subscriptions | `$showOtherServices` | `other-services.*` | OtherServiceController |
| Renewals | `$showExpiryTrackers` | `expiry-trackers.*` | ExpiryTrackerController |
| Hardware Assets | `$showAssets` | `assets.*` | AssetController |
| G-Mails | `$showGMails` | `g-mails.*` | GMailController |

### Credentials Group
| Sidebar Label | Flag | Route Name | Controller |
|--------------|------|-----------|-----------|
| My Credentials | `$showMyVault` | `vault.my` | VaultController |
| Shared Credentials | `$showVault` | `vault.index` | VaultController |

### Operations Group
| Sidebar Label | Flag | Route Name | Controller |
|--------------|------|-----------|-----------|
| My Tasks | always | `tasks.my` | TaskController |
| Task Management | always | `tasks.index` | TaskController |
| Calendar | always | `calendar` | CalendarController |
| Notes | `$showNotes` | `notes.*` | NoteController |

### Administration Group (super-admin only)
| Sidebar Label | Flag | Route Name | Controller |
|--------------|------|-----------|-----------|
| Users | @hasrole | `users.*` | UserController |
| Roles | @hasrole | `roles.*` | RoleController |
| Role Templates | @hasrole | `role-templates.*` | RoleTemplateController |
| Privileges | @hasrole | `privileges.*` | PrivilegeController |
| Modules | @hasrole | `modules.*` | ModuleController |
| Permissions | @hasrole | `module-permissions.*` | ModulePermissionController |
| Features | @hasrole | `features.*` | FeatureController |
| Mail Settings | @hasrole | `smtp-profiles.*` | SmtpProfileController |
| Audit Trail | @hasrole | `activity-logs.*` | ActivityLogController |
| Login History | @hasrole | `login-audits.*` | LoginAuditController |
| Import | @hasrole | `import.*` | ImportController |
| Attachments | @hasrole | `attachments.*` | AttachmentController |
| Integrations | @hasrole | `webhooks.*` | WebhookController |
| API Access | @hasrole | `tokens.*` | TokenController |

### Reports Group (super-admin only)
| Sidebar Label | Flag | Route Name | Controller |
|--------------|------|-----------|-----------|
| Reports | @hasrole | `reports.*` | ReportController |

### Account Group
| Sidebar Label | Flag | Route Name | Controller |
|--------------|------|-----------|-----------|
| My Profile | always | `profile` | AuthController@profile |
| My Access | always | `my-permissions` | AuthController@myPermissions |
| Help Center | always | `guide` | HelpController |

### Top-Level Items
| Sidebar Label | Flag | Route Name | Controller |
|--------------|------|-----------|-----------|
| Dashboard | always | `dashboard` | DashboardController |
| Monitoring | `$showMonitoring` | `monitoring.*` | MonitoringOverviewController |
| Notifications | always | `notifications.index` | NotificationController |

---

## 3. User Card (Sidebar Footer)

The `user-card.blade.php` component at the bottom of the sidebar provides:
- **User avatar** (first letter of name)
- **User name + email display**
- **Notifications bell** → `notifications.index`
- **Dark mode toggle** → custom Alpine/Livewire toggle
- **Logout** → POST `logout`

This is consistent with the main sidebar navigation and serves as a persistent bottom bar.

---

## 4. Sidebar Issues & Observations

### Issue 1: `$show*` Flags Origin
The `$show*` flags are passed to `x-sidebar-nav-groups` from `layouts/admin.blade.php`. These must be set in `AppServiceProvider::boot()` or a middleware `share()` method. The flags control BOTH sidebar visibility AND access to the actual routes — but routes themselves only use `auth` + `suspended` middleware, not these flags.

**Risk**: A user could manually navigate to `/hostings` even if `$showHostings` is false, because the route has no permission middleware. The flag only hides the nav link.

### Issue 2: Admin Group Missing Role Template Create
Role templates sidebar item points to `route-templates.index`. The `role-templates.create` and `role-templates.store` routes do NOT exist in routes — line 315-317 shows only `index`, `show`, and `apply`. This appears intentional (role templates are created through the UI by applying templates to roles).

### Issue 3: Module Permissions — No Create/Edit
Module Permissions sidebar item points to `module-permissions.index`. Looking at routes (lines 267-269): index + update (POST) + destroy (DELETE). There are no create/edit routes. Permissions are managed as a bulk config, not individual records.

### Issue 4: Reports Group — Single Item
The Reports collapsible section contains only one item ("Reports"). This is a single-item collapsible group — could be simplified to a top-level link (similar to Monitoring).

### Issue 5: Account Group — Flat Links vs Blade Component
The Help Center link at line 140-144 is a raw `<a>` tag (not using `<x-nav-link>`), which means it won't get the active state styling consistently.

### Issue 6: `active` Logic for Credentials
```php
<x-nav-link href="{{ route('vault.index') }}" :active="request()->routeIs('vault.*') && !request()->routeIs('vault.my')">
```
Shared Credentials uses `vault.*` excluding `vault.my`. My Credentials uses just `vault.my`. This is correct behavior but fragile — if a new vault.* route is added, it would break Shared Credentials highlight logic.

---

## 5. Ownership Summary

| Sidebar Section | Visibility Control | Route Access Control | Notes |
|----------------|-------------------|---------------------|-------|
| Dashboard | None (always) | auth+suspended | Safe |
| Monitoring | `$showMonitoring` | auth+suspended | Nav-level flag, no middleware gate |
| Notifications | None (always) | auth+suspended | Safe |
| Infrastructure | `$show*` per item | auth+suspended | Nav-level only — no route gate |
| Credentials | `$showVault` / `$showMyVault` | auth+suspended | Nav-level only |
| Operations | Always / `$showNotes` | auth+suspended | Nav-level only for Notes |
| Administration | `@hasrole('super-admin')` | role:super-admin | Blade + route middleware aligned |
| Reports | `@hasrole('super-admin')` | role:super-admin | Blade + route middleware aligned |
| Account | None (always) | auth+suspended | Safe |
