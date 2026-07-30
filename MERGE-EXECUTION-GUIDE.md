# Merge Execution Guide — Office System
## alphaspacepro.online (BASE) ← Roundcube-main (FORK)

Total time: ~10-15 minutes | Risk: ~2%

---

## REQUIREMENTS CHECK (2 min)

Pehle confirm karo:

- [ ] `alphaspacepro.online` folder mojood hai (yeh BASE hai, live system)
- [ ] `Roundcube-main\Roundcube-main` folder mojood hai (yeh FORK hai)
- [ ] PHP 8.x install hai aur `php` command CMD mein kaam karta hai
- [ ] MySQL/MariaDB chal raha hai
- [ ] Database `alphaspacepro` (ya jo bhi naam hai) mojood hai
- [ ] `.env` file mein DB credentials sahi hain

---

## STEP 1: BACKUP (2 min)

Yeh commands **ek ek kar ke** CMD mein paste karo, Enter dabao har baad:

```
REM ---- FOLDER BACKUP ----
xcopy /E /I "C:\Inetpub\wwwroot\alphaspacepro.online" "C:\Inetpub\wwwroot\alphaspacepro-BACKUP-2026-07-30"

REM ---- DATABASE BACKUP ----
"C:\xampp\mysql\bin\mysqldump" -u root alphaspacepro > "C:\Inetpub\wwwroot\alphaspacepro-BACKUP-2026-07-30\database-dump.sql"
```

> Agar IIS hai to path change kar lo: `C:\Inetpub\wwwroot\alphaspacepro.online`
> Agar XAMPP hai to: `C:\xampp\htdocs\alphaspacepro.online`

`php -r "echo 'BACKUP DONE';"` — yeh type karo confirm karne ke liye.

---

## STEP 2: 19 FILES REPLACE — FORK → BASE (2 min)

### 2a. Controllers (5 files)
Path talash karo: `alphaspacepro.online\app\Http\Controllers\Web\`

In 5 files ko **Roundcube-main se copy karo** → **alphaspacepro.online mein overwrite karo**:

| # | File | Copy from (FORK) | Paste to (BASE) |
|---|------|-------------------|-----------------|
| 1 | AuthController.php | `Roundcube-main\Roundcube-main\app\Http\Controllers\Web\AuthController.php` | `alphaspacepro.online\app\Http\Controllers\Web\AuthController.php` |
| 2 | DomainController.php | `Roundcube-main\Roundcube-main\app\Http\Controllers\Web\DomainController.php` | `alphaspacepro.online\app\Http\Controllers\Web\DomainController.php` |
| 3 | EmailAccountController.php | `Roundcube-main\Roundcube-main\app\Http\Controllers\Web\EmailAccountController.php` | `alphaspacepro.online\app\Http\Controllers\Web\EmailAccountController.php` |
| 4 | NotificationController.php | `Roundcube-main\Roundcube-main\app\Http\Controllers\Web\NotificationController.php` | `alphaspacepro.online\app\Http\Controllers\Web\NotificationController.php` |
| 5 | LoginAuditController.php | `Roundcube-main\Roundcube-main\app\Http\Controllers\Web\LoginAuditController.php` | `alphaspacepro.online\app\Http\Controllers\Web\LoginAuditController.php` |

### 2b. Model (1 file)

| File | Copy from | Paste to |
|------|-----------|----------|
| Domain.php | `Roundcube-main\Roundcube-main\app\Models\Domain.php` | `alphaspacepro.online\app\Models\Domain.php` |

### 2c. Requests (2 files)

| File | Copy from | Paste to |
|------|-----------|----------|
| StoreDomainRequest.php | `Roundcube-main\Roundcube-main\app\Http\Requests\StoreDomainRequest.php` | `alphaspacepro.online\app\Http\Requests\StoreDomainRequest.php` |
| UpdateDomainRequest.php | `Roundcube-main\Roundcube-main\app\Http\Requests\UpdateDomainRequest.php` | `alphaspacepro.online\app\Http\Requests\UpdateDomainRequest.php` |

### 2d. Notification (1 file)

| File | Copy from | Paste to |
|------|-----------|----------|
| NewEmailArrived.php | `Roundcube-main\Roundcube-main\app\Notifications\NewEmailArrived.php` | `alphaspacepro.online\app\Notifications\NewEmailArrived.php` |

### 2e. Provider (1 file)

| File | Copy from | Paste to |
|------|-----------|----------|
| AppServiceProvider.php | `Roundcube-main\Roundcube-main\app\Providers\AppServiceProvider.php` | `alphaspacepro.online\app\Providers\AppServiceProvider.php` |

### 2f. Routes (1 file)

| File | Copy from | Paste to |
|------|-----------|----------|
| web.php | `Roundcube-main\Roundcube-main\routes\web.php` | `alphaspacepro.online\routes\web.php` |

### 2g. Views (5 files)

| # | File | Copy from | Paste to |
|---|------|-----------|----------|
| 1 | domains/create.blade.php | `Roundcube-main\Roundcube-main\resources\views\domains\create.blade.php` | `alphaspacepro.online\resources\views\domains\create.blade.php` |
| 2 | domains/edit.blade.php | `Roundcube-main\Roundcube-main\resources\views\domains\edit.blade.php` | `alphaspacepro.online\resources\views\domains\edit.blade.php` |
| 3 | domains/show.blade.php | `Roundcube-main\Roundcube-main\resources\views\domains\show.blade.php` | `alphaspacepro.online\resources\views\domains\show.blade.php` |
| 4 | email-accounts/create.blade.php | `Roundcube-main\Roundcube-main\resources\views\email-accounts\create.blade.php` | `alphaspacepro.online\resources\views\email-accounts\create.blade.php` |
| 5 | webmail/launch.blade.php | `Roundcube-main\Roundcube-main\resources\views\webmail\launch.blade.php` | `alphaspacepro.online\resources\views\webmail\launch.blade.php` |

### 2h. Public files (2 files)

| # | File | Copy from | Paste to |
|---|------|-----------|----------|
| 1 | .htaccess | `Roundcube-main\Roundcube-main\public\.htaccess` | `alphaspacepro.online\public\.htaccess` |
| 2 | imap-idle-status.php | `Roundcube-main\Roundcube-main\public\webmail\imap-idle-status.php` | `alphaspacepro.online\public\webmail\imap-idle-status.php` |

### 2i. New view file (1 file — direct add, overwrite nahi)

| File | Copy from | Paste to |
|------|-----------|----------|
| bulk-import.blade.php | `Roundcube-main\Roundcube-main\resources\views\domains\bulk-import.blade.php` | `alphaspacepro.online\resources\views\domains\bulk-import.blade.php` |

---

## STEP 3: IMAP WORKER — BASE WALA RAKHO (1 min)

**MAT KARO overwrite.** Base wala `scripts/imap-idle-worker.php` waise ka waisa rahne do. Fork wala COPY MAT KARO.

---

## STEP 4: MIGRATIONS (2 min)

### 4a. 1 Migration DELETE karo (sessions conflict)

Delete karo: `alphaspacepro.online\database\migrations\2026_07_25_000002_create_sessions_table.php`

> Yeh Fork se copy nahi karna. Base ke 0001 migration mein pehle se sessions table ban raha hai.

### 4b. 2 Migrations COPY karo (Roundcube-main → alphaspacepro.online)

| File | Copy from | Paste to |
|------|-----------|----------|
| 1 | `Roundcube-main\Roundcube-main\database\migrations\2026_07_25_000001_add_mail_server_settings_to_domains.php` | `alphaspacepro.online\database\migrations\2026_07_25_000001_add_mail_server_settings_to_domains.php` |
| 2 | `Roundcube-main\Roundcube-main\database\migrations\2026_07_25_000003_add_performance_indexes.php` | `alphaspacepro.online\database\migrations\2026_07_25_000003_add_performance_indexes.php` |

### 4c. Migrate run karo

CMD mein:

```
cd C:\Inetpub\wwwroot\alphaspacepro.online
```

(ya jo bhi path hai)

```
php artisan migrate --pretend
```

Yeh dry-run hai — actual execute nahi karega, sirf dikhayega kya hoga.

Output dekho — sirf 2 migrations dikhni chahiye:
- `2026_07_25_000001_add_mail_server_settings_to_domains`
- `2026_07_25_000003_add_performance_indexes`

Agar 3 dikh gayi (sessions wali bhi) to ruk jao — wapas check karo ke sessions migration delete ki thi ya nahi.

Phir actual run:

```
php artisan migrate
```

---

## STEP 5: CACHES CLEAR (1 min)

```
php artisan optimize:clear
php artisan view:clear
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

Phir rebuild:

```
php artisan optimize
php artisan route:cache
php artisan config:cache
php artisan view:cache
```

---

## STEP 6: TESTING (5-10 min)

### 6a. Route check
```
php artisan route:list
```

Confirm karo yeh 2 extra routes mojood hain:
- `GET|HEAD  notifications/poll ................ polls.notifications`
- `GET|HEAD  domains/{domain}/bulk-import ...... domains.bulk-import`
- `POST      domains/{domain}/bulk-import ...... domains.bulk-import.store`

### 6b. Domain CRUD test
1. Browser mein login karo
2. **Domains → Create** — IMAP/SMTP fields dikhni chahiye
3. Save karo — domain save hona chahiye
4. **Edit** karo — IMAP/SMTP fields filled honi chahiye
5. **Show** karo — "Bulk Import" button dikhna chahiye

### 6c. Email Account test
1. **Email Accounts → Create** — domain select karo
2. "Fill from Domain" button dikhna chahiye aur kam karna chahiye

### 6d. Notification test
1. Webmail launch page kholo
2. Page load hona chahiye, notification polling UI dikhna chahiye

### 6e. Login test
1. Suspended user se login karo — error aana chahiye (Fork ka security fix)
2. Normal user se login karo — sab theek hona chahiye

### 6f. Webmail test
1. SnappyMail launch karo
2. Login karo, email read/send karo — sab kaam karna chahiye

---

## STEP 7: AGAR KUCH GARBAR HO JAYE (Recovery)

```
REM ---- FOLDER RESTORE ----
rmdir /S /Q "C:\Inetpub\wwwroot\alphaspacepro.online"
xcopy /E /I "C:\Inetpub\wwwroot\alphaspacepro-BACKUP-2026-07-30" "C:\Inetpub\wwwroot\alphaspacepro.online"

REM ---- DATABASE RESTORE ----
"C:\xampp\mysql\bin\mysql" -u root alphaspacepro < "C:\Inetpub\wwwroot\alphaspacepro-BACKUP-2026-07-30\database-dump.sql"
```

Ise **2 minutes** mein sab wapas original ho jayega.

---

## COMPLETE CHECKLIST

- [ ] STEP 1: Folder backup le liya? `C:\Inetpub\wwwroot\alphaspacepro-BACKUP-2026-07-30`
- [ ] STEP 1: Database backup le liya? `database-dump.sql`
- [ ] STEP 2: 19 files copy kar di? (check karo count = 19)
- [ ] STEP 3: IMAP worker Base wala rakha? (Fork wala copy nahi kiya)
- [ ] STEP 4a: `2026_07_25_000002_create_sessions_table.php` delete kiya?
- [ ] STEP 4b: 2 migrations copy ki?
- [ ] STEP 4c: `php artisan migrate` successful?
- [ ] STEP 5: Caches clear + rebuild ki?
- [ ] STEP 6: Domain CRUD test OK?
- [ ] STEP 6: Email Account test OK?
- [ ] STEP 6: Webmail test OK?
- [ ] STEP 6: Suspended user block OK?
- [ ] **Backup delete karna hai?** — 1 hafta confirm ho jaye tab delete karo

---

## OFFICE SYSTEM KE LIYE ALAG: IIS web.config

Agar office system IIS hai (jo abhi SnappyMail chala raha hai) to yeh extra step karo:

```
copy "Roundcube-main\Roundcube-main\public\web.config" "alphaspacepro.online\public\web.config"
```

Yeh IIS rewrite rules hain — zaroori nahi lekin recommended hai agar IIS use kar rahe ho.

---

## KHATAM.
Koi masla ho to mujhe batao. Wapas ghar aao to batana kya hua.
