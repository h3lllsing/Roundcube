# Alphaspace BRAC Portal - Windows Server Deployment Guide

## Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                     Windows Server 10.10.10.24              │
│                                                             │
│  ┌──────────────┐    ┌──────────────────┐                   │
│  │   IIS 10.0   │    │ PHP 8.3.32 NTS   │                   │
│  │  (Roundcube  │───▶│   FastCGI        │                   │
│  │   Portal)    │    │ (php-cgi.exe)    │                   │
│  │  Port 80     │    └──────────────────┘                   │
│  └──────┬───────┘                                           │
│         │                                                   │
│         ▼                                                   │
│  ┌──────────────────────────────────────────────────┐       │
│  │         C:\roundcube\ (Deployment Root)          │       │
│  │  ┌──────────┐  ┌──────────┐  ┌────────────────┐ │       │
│  │  │  public/ │  │ storage/ │  │  public/       │ │       │
│  │  │ (Laravel)│  │ (cache,  │  │  webmail/      │ │       │
│  │  │          │  │  logs)   │  │  (SnappyMail)  │ │       │
│  │  └──────────┘  └──────────┘  └────────────────┘ │       │
│  └──────────────────────────────────────────────────┘       │
│                                                             │
│  ┌──────────────┐    ┌──────────────────┐                   │
│  │  NSSM Service│    │  MySQL 8.0       │                   │
│  │ ImapIdleWorker│   │  roundportal DB  │                   │
│  └──────────────┘    └──────────────────┘                   │
└─────────────────────────────────────────────────────────────┘
```

---

## 1. Prerequisites Installation

### 1.1 PHP 8.3.32 NTS x64

Download from: https://windows.php.net/downloads/releases/php-8.3.32-nts-Win32-vs16-x64.zip

```
Extract to: C:\php\
php.ini required extensions:
  extension=curl
  extension=fileinfo
  extension=gd
  extension=intl
  extension=mbstring
  extension=mysqli
  extension=openssl
  extension=pdo_mysql
  extension= sockets
  extension=imap (php_imap.dll - see below)
  extension_dir = "C:\php\ext"

[Date]
date.timezone = UTC
```

**php_imap.dll**: PHP 8.3.32 NTS x64 ke liye alag se download karo.
- https://github.com/CHH/php_imap/releases ya DLL kahi aur se
- `C:\php\ext\php_imap.dll` pe rakh do
- php.ini me `extension=imap` uncomment karo
- Verify: `php -m | findstr imap` → "imap" dikhna chahiye

### 1.2 MySQL 8.0

```
Database: roundportal
User: root
Password: admin123
Host: 127.0.0.1 (not localhost for TCP)
```

### 1.3 IIS 10.0 with CGI

```
Server Manager → Add Roles and Features → Web Server (IIS)
  → Application Development → CGI (select)
  → Security → Basic Authentication (optional)
  → Management Tools → IIS Management Console
```

### 1.4 URL Rewrite Module 2.0

Download: https://www.iis.net/downloads/microsoft/url-rewrite
Install the MSI. Verify in IIS Manager → server-level Modules → "RewriteModule".

### 1.5 PHP for IIS (FastCGI)

```
IIS Manager → server-level → Handler Mappings → Add Module Mapping
  Request path: *.php
  Module: FastCgiModule
  Executable: C:\php\php-cgi.exe
  Name: PHP via FastCGI
  → Request Restrictions → check "File" only → UNCHECK "Invoke handler only if request is mapped to"
```

---

## 2. Application Deployment

### 2.1 Clone / Copy Code

```
C:\roundcube\  (web root)
├── public\          → IIS site root
├── storage\         → writable (cache, logs, sessions)
├── bootstrap\cache\ → writable
├── vendor\          → composer dependencies
├── .env
└── scripts\         → IMAP worker, deployment scripts
```

### 2.2 Environment (.env)

```
APP_NAME=Alphaspace
APP_ENV=local
APP_DEBUG=true
APP_URL=http://10.10.10.24

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=roundportal
DB_USERNAME=root
DB_PASSWORD=admin123

NOTIFICATION_API_TOKEN=dev-secret-token-change-in-production
```

### 2.3 Composer Install (on dev machine, copy vendor)

```
cd C:\roundcube
composer install --no-dev --optimize-autoloader
```

### 2.4 Migrations

```
C:\php\php.exe C:\roundcube\artisan migrate
```

### 2.5 Storage Links

```
C:\php\php.exe C:\roundcube\artisan storage:link
```

### 2.6 Cache

```
C:\php\php.exe C:\roundcube\artisan view:cache
C:\php\php.exe C:\roundcube\artisan config:cache
C:\php\php.exe C:\roundcube\artisan route:cache
```

### 2.7 Permissions (CRITICAL)

```
icacls C:\roundcube\storage /grant "IIS_IUSRS:(OI)(CI)(F)" /T
icacls C:\roundcube\bootstrap\cache /grant "IIS_IUSRS:(OI)(CI)(F)" /T
icacls C:\roundcube\public\webmail\data /grant "IIS_IUSRS:(OI)(CI)(F)" /T
icacls C:\roundcube\storage\app\webmail /grant "IIS_IUSRS:(OI)(CI)(F)" /T
```

---

## 3. IIS Site Configuration

### 3.1 Create Site

```
Open IIS Manager
Sites → Add Website:
  Site name: RoundcubePortal
  Physical path: C:\roundcube\public
  Binding type: http
  IP address: All Unassigned
  Port: 80
```

### 3.2 Default Document

Add `index.php` as the first/default document for the site.

### 3.3 web.config (already in public/)

```xml
<?xml version="1.0" encoding="UTF-8"?>
<configuration>
    <system.webServer>
        <rewrite>
            <rules>
                <rule name="Laravel" stopProcessing="true">
                    <match url="^(.*)$" ignoreCase="false" />
                    <conditions logicalGrouping="MatchAll">
                        <add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true" />
                        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true" />
                    </conditions>
                    <action type="Rewrite" url="index.php" logRewrittenUrl="true" />
                </rule>
            </rules>
        </rewrite>
        <defaultDocument>
            <files>
                <add value="index.php" />
            </files>
        </defaultDocument>
    </system.webServer>
</configuration>
```

### 3.4 Stop Default Web Site

Default Web Site port 80 pe hai to use stop karo (ya port change karo).

---

## 4. SnappyMail Webmail

### 4.1 Location

```
C:\roundcube\public\webmail\
```

### 4.2 SSO Plugin

The portal includes a custom SnappyMail plugin at:
```
webmail/plugins/roundcube-portal-auth/
```

Plugin registered in SnappyMail config:
```
C:\roundcube\public\webmail\data\_data_\_default_\configs\application.ini
[plugins]
enable = On
enabled_list = "roundcube-portal-auth"
```

### 4.3 Permissions

Webmail data directory must be writable by IIS:
```
icacls C:\roundcube\public\webmail\data /grant "IIS_IUSRS:(OI)(CI)(F)" /T
```

---

## 5. IMAP IDLE Worker (NSSM Service)

### 5.1 Install NSSM

Download: https://nssm.cc/download
Extract `nssm.exe` to `C:\Windows\System32\`

### 5.2 Install Service

```
nssm install ImapIdleWorker
  Application: C:\php\php.exe
  Arguments: C:\roundcube\scripts\imap-idle-worker.php
  Startup directory: C:\roundcube
  Log on: Local System account
  Startup type: Automatic (Delayed Start)
```

Or use the provided script:
```
C:\php\php.exe C:\roundcube\scripts\install-worker-service.ps1
```

### 5.3 Start/Stop

```
nssm start ImapIdleWorker
nssm stop ImapIdleWorker
nssm status ImapIdleWorker
```

### 5.4 Verify

```
Get-Content C:\roundcube\storage\app\webmail\cache\imap-worker-heartbeat.json
# Should show: {"alive":true,"timestamp":...,"accounts":3}
```

---

## 6. Live Notification System

### How it works

```
New email arrives → IMAP IDLE Worker detects it
  → POST /new-mail-notification (with API token)
  → NewEmailNotificationController creates Laravel notification
  → Stored in notifications table
  → User's unread_notification_count increments
  → Webmail view polls GET /notifications/poll every 30s
  → Notification bell shows unread count badge
  → Click bell → dropdown with recent notifications
  → Click notification → opens that email account in webmail
```

### Routes

| Method | URL | Purpose |
|--------|-----|---------|
| POST | `/new-mail-notification` | Worker sends notification (CSRF exempt, token-auth) |
| GET | `/notifications/poll` | Returns JSON: `{unread_count, notifications[]}` |
| GET | `/notifications` | Full notification list page |
| POST | `/notifications/{id}/read` | Mark single as read |
| POST | `/notifications/read-all` | Mark all as read |

### Configuration

`.env` me:
```
NOTIFICATION_API_TOKEN=dev-secret-token-change-in-production
```

Worker sends this token as `X-API-Token` header.

---

## 7. Domain Mail Settings

### Migration

`2026_07_25_000001_add_mail_server_settings_to_domains.php` adds 7 columns to `domains`:
- `imap_host`, `imap_port`, `imap_encryption`
- `smtp_host`, `smtp_port`, `smtp_encryption`, `smtp_username`

### Feature

- Admin sets mail server per domain (collapsible section in create/edit)
- When creating email account, domain selection auto-fills IMAP/SMTP
- "Test Connection" button uses `imap_open()` (requires php_imap)
- New domains immediately visible via `Cache::forget('domains:active')`

---

## 8. File Sync (Dev → Server)

### Manual Sync

```
C:\php\php.exe sync-to-server.ps1
```

### Auto-Watcher

```
C:\php\php.exe watch-and-sync.ps1
```

Watches local changes and FTPS to server with 2 second debounce.

---

## 9. Troubleshooting

### 9.1 White Page / 500 Error

```
# Check Laravel log
Get-Content C:\roundcube\storage\logs\laravel.log -Tail 20

# Check PHP error log
Get-Content C:\php\logs\php_error.log -Tail 20

# Check IIS event log
Get-EventLog -LogName Application -Source "php*" -Newest 5 | Format-Table TimeGenerated, Message -AutoSize -Wrap
```

### 9.2 URL Rewrite Not Working

```
# Verify module is installed
& "C:\Windows\System32\inetsrv\appcmd.exe" list config -section:system.webServer/globalModules
# Look for "RewriteModule"

# Verify rule is applied
& "C:\Windows\System32\inetsrv\appcmd.exe" list config "RoundcubePortal" -section:system.webServer/rewrite/rules
```

### 9.3 SnappyMail 500 on SSO

```
# Check SnappyMail logs
Get-ChildItem C:\roundcube\public\webmail\data\_data_\_default_\logs\

# Check webmail data permissions
icacls C:\roundcube\public\webmail\data
# Must include IIS_IUSRS:(OI)(CI)(F)
```

### 9.4 IMAP Worker Not Connecting

```
# Check PHP IMAP extension
C:\php\php.exe -m | findstr imap

# Check worker logs
Get-Content C:\roundcube\storage\app\webmail\logs\imap-idle-worker.log -Tail 20

# Check heartbeat file
Get-Content C:\roundcube\storage\app\webmail\cache\imap-worker-heartbeat.json
```

### 9.5 Notification Not Appearing

```
# Send test notification
curl -X POST http://10.10.10.24/new-mail-notification ^
  -H "X-API-Token: dev-secret-token-change-in-production" ^
  -H "Content-Type: application/json" ^
  -d "{\"email\":\"user@domain.com\",\"subject\":\"Test\",\"from\":\"test@test.com\",\"account_id\":1}"

# Check notifications in browser at /notifications
# Check /notifications/poll returns JSON
```

---

## 10. Quick Checklist After Fresh Deploy

- [ ] PHP installed at `C:\php\`, php_imap enabled
- [ ] MySQL running, database `roundportal` created
- [ ] `.env` configured with correct DB creds and APP_URL
- [ ] `composer install --no-dev --optimize-autoloader` done
- [ ] `php artisan migrate` run
- [ ] `php artisan storage:link` created
- [ ] IIS site `RoundcubePortal` → `C:\roundcube\public` port 80
- [ ] URL Rewrite Module installed and web.config present
- [ ] `index.php` added as default document (first in list)
- [ ] Default Web Site stopped (if using port 80)
- [ ] Permissions: `icacls ... /grant IIS_IUSRS:(OI)(CI)(F)` on:
  - `storage/`, `bootstrap/cache/`, `public/webmail/data/`, `storage/app/webmail/`
- [ ] SnappyMail accessible at `http://10.10.10.24/webmail/`
- [ ] Worker installed as NSSM `ImapIdleWorker` and running
- [ ] Login test: `admin@localhost` / `admin123`
- [ ] Webmail test: click any email account → loads SnappyMail via SSO
- [ ] Send/receive test email
- [ ] Notification bell visible in webmail, clicking shows notifications
