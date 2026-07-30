# Deep Comprehensive Audit — Office System
## Phase-by-Phase: alphaspacepro.online (BASE-UPDATED) vs Roundcube-main (FORK)

---

## PHASE 0: Environment & Prerequisites

**OpenCode ko kaho:**
> "Mujhe dono projects ka environment batado:
>
> BASE: C:\Inetpub\wwwroot\alphaspacepro.online
> FORK: C:\Inetpub\wwwroot\Roundcube-main\Roundcube-main
>
> Yeh sab check karo:
> 1. PHP version — 'php -v' command se
> 2. PHP extensions — 'php -m' se (especially: openssl, pdo, mbstring, fileinfo, curl, gd, xml, json, tokenizer, bcmath, intl, sockets)
> 3. MySQL version — 'mysql -V' se
> 4. Composer version — 'composer -V' se
> 5. Node version — 'node -v' se
> 6. NPM version — 'npm -v' se
> 7. Dono projects ka total size — folder properties se
> 8. SnappyMail installed hai ya nahi — check karo public/webmail/index.php exists ya nahi dono mein
> 9. .env file read karo dono ki (bina secrets leak kiye sirf structure batao — konse variables hain)
> 10. IIS/Apache check karo — 'iisreset /status' ya check web.config exists ya nahi
>
> Ek report file banao C:\Inetpub\wwwroot\AUDIT-00-ENVIRONMENT.md"

---

## PHASE 1: File System Deep Scan

**OpenCode ko kaho (Part A):**
> "BASE: C:\Inetpub\wwwroot\alphaspacepro.online
> FORK: C:\Inetpub\wwwroot\Roundcube-main\Roundcube-main
>
> DONO projects ki COMPLETE directory tree banao (sirf files, folders nahi):
> 1. Get-ChildItem -Recurse -File | Select-Object FullName | Sort-Object FullName
> 2. Isay 2 files mein save karo: BASE-filelist.txt, FORK-filelist.txt
> 3. Phir compare karo:
>    - Total files count dono mein
>    - Files sirf BASE mein
>    - Files sirf FORK mein
>    - Files dono mein common
>
> Report: C:\Inetpub\wwwroot\AUDIT-01A-FILES.md"

**OpenCode ko kaho (Part B):**
> "Ab common files ka SHA256 hash karo:
> 1. Get-FileHash -Algorithm SHA256 use karo har common file par
> 2. Compare hashes
> 3. Batao:
>    - Common files with IDENTICAL hash = kitni
>    - Common files with DIFFERENT hash = kitni + list (full path)
>    - Different files ka size difference bhi batao
>
> Report: C:\Inetpub\wwwroot\AUDIT-01B-HASHES.md"

**OpenCode ko kaho (Part C):**
> "Jo bhi files different hain (Part B se), unka CONTENT diff karo:
> Har file ke liye:
> - Line-by-line diff (context ke saath, 3 lines upar neeche)
> - Batao: exactly kya badla hai (new function? removed code? different logic?)
> - Batao: yeh change safe hai merge time?
>
> Report: C:\Inetpub\wwwroot\AUDIT-01C-DIFFS.md"

**OpenCode ko kaho (Part D):**
> "Sirf BASE mein mojood files ka analysis:
> 1. Har file ka type batao (controller, model, view, migration, config, JS, CSS, etc.)
> 2. Yeh files kyun BASE mein hain aur FORK mein nahi?
>    - Naya feature?
>    - SnappyMail runtime?
>    - Cache/temp files?
>    - Documentation?
> 3. Kya yeh files merge ke baad bhi rehni chahiye?
>    - YES — important yahan rahe gi
>    - NO — delete kar do
>    - MAYBE — decide karna hai
>
> Same analysis Sirf FORK mein mojood files ka bhi karo.
>
> Report: C:\Inetpub\wwwroot\AUDIT-01D-UNIQUE.md"

---

## PHASE 2: Database — Complete Schema Deep Dive

**OpenCode ko kaho (Part A — Migrations):**
> "BASE: C:\Inetpub\wwwroot\alphaspacepro.online\database\migrations\
> FORK: C:\Inetpub\wwwroot\Roundcube-main\Roundcube-main\database\migrations\
>
> HAR migration file ka content read karo dono ki.
> Compare karo:
>
> A. Basic Count:
>    - Total migrations BASE mein
>    - Total migrations FORK mein
>    - Exact match migrations (same filename + same content)
>    - Same name but different content
>    - Sirf BASE mein
>    - Sirf FORK mein
>
> B. Har migration ka purpose batao:
>    - Konsi table ban rahi hai
>    - Konsi column add/change ho raha hai
>    - Koi index bana raha hai?
>    - Koi foreign key?
>    - Koi data seed kar raha hai?
>
> C. Table-wise mapping:
>    users -> kin kin migrations mein change hua?
>    domains -> kin kin migrations mein?
>    email_accounts -> ?
>    aur baqi tables...
>
> D. Conflict check:
>    - Koi table 2 alag migrations mein bana raha hai? (like sessions table)
>    - Koi same column 2 bar add ho raha?
>    - Koi incompatible change?
>
> Report: C:\Inetpub\wwwroot\AUDIT-02A-MIGRATIONS.md"

**OpenCode ko kaho (Part B — Schema Reconstruction):**
> "Migrations se complete schema reconstruct karo. Har table ki final shape batao:
>
> Table: users
> - Column name, type, nullable? default? index? foreign?
> - Jo Base mein hai jo Fork mein nahi
> - Jo Fork mein hai jo Base mein nahi
>
> Table: domains
> - Same format
> - Kya Base updated version mein IMAP/SMTP columns hain? Konsi?
>
> Table: email_accounts
> - Same
>
> Aur baqi saari tables (activity_log, notifications, login_audits, roles, privileges, features, modules, module_role_permissions, user_module_permissions, cache, jobs, sessions, personal_access_tokens, webmail_tokens, email_account_user)
>
> Har table ke liye:
> - Total columns
> - Common columns
> - Sirf Base columns
> - Sirf Fork columns
> - Indexes comparison
>
> Agar koi Extra table Base mein hai (expiry_trackers, vendors, etc.) to unka full schema bhi banao.
>
> Report: C:\Inetpub\wwwroot\AUDIT-02B-SCHEMA.md"

**OpenCode ko kaho (Part C — Seeders):**
> "BASE: C:\Inetpub\wwwroot\alphaspacepro.online\database\seeders\
> FORK: C:\Inetpub\wwwroot\Roundcube-main\Roundcube-main\database\seeders\
>
> Har seeder file compare karo:
> - Same hain ya different?
> - Kya data different hai? (roles, permissions, features — exact values compare karo)
> - Koi seeder sirf ek mein hai?
>
> Report: C:\Inetpub\wwwroot\AUDIT-02C-SEEDERS.md"

**OpenCode ko kaho (Part D — Factories):**
> "BASE: C:\Inetpub\wwwroot\alphaspacepro.online\database\factories\
> FORK: C:\Inetpub\wwwroot\Roundcube-main\Roundcube-main\database\factories\
>
> Compare karo sab factories.
>
> Report: C:\Inetpub\wwwroot\AUDIT-02D-FACTORIES.md"

---

## PHASE 3: Architecture Deep Dive

**OpenCode ko kaho (Part A — Routes Complete Map):**
> "BASE: C:\Inetpub\wwwroot\alphaspacepro.online\routes\
> FORK: C:\Inetpub\wwwroot\Roundcube-main\Roundcube-main\routes\
>
> Saare route files padho (web.php, api.php, console.php, channels.php):
>
> A. Route-by-route comparison:
>    Har route ke liye:
>    - Method (GET/POST/PUT/DELETE)
>    - URI
>    - Controller@action
>    - Middleware
>    - Name
>    - Base mein hai? Fork mein hai?
>    - Same hain? Different?
>
> B. Route groups:
>    - Kis group mein konsi routes hain
>    - Middleware applied on groups
>    - Prefixes
>
> C. API routes specifically:
>    - Saare API endpoints list karo
>    - Versioning hai ya nahi
>    - Authentication method
>
> D. Console routes:
>    - Saare artisan commands list karo
>    - Schedule hai koi?
>
> Ek complete route table banao jisme dono ka comparison ho.
>
> Report: C:\Inetpub\wwwroot\AUDIT-03A-ROUTES.md"

**OpenCode ko kaho (Part B — Middleware Chain):**
> "BASE: C:\Inetpub\wwwroot\alphaspacepro.online\app\Http\Middleware\
> FORK: C:\Inetpub\wwwroot\Roundcube-main\Roundcube-main\app\Http\Middleware\
>
> 1. Middleware files compare karo (sab padho):
>    - AddSecurityHeaders.php
>    - CheckPasswordExpiry.php
>    - CheckSuspended.php
>    - ConcurrentSession.php
>    - SessionIpBinding.php
>    - Koi aur middleware hai?
>
> 2. Har middleware ka logic batao:
>    - Kya check karta hai?
>    - Kya action leta hai? (abort? redirect? log only?)
>    - Koi difference hai dono versions mein?
>
> 3. Kernel.php compare karo:
>    - global middleware
>    - route middleware
>    - middleware groups (web, api)
>
> Report: C:\Inetpub\wwwroot\AUDIT-03B-MIDDLEWARE.md"

**OpenCode ko kaho (Part C — Controller Mapping):**
> "BASE: C:\Inetpub\wwwroot\alphaspacepro.online\app\Http\Controllers\
> FORK: C:\Inetpub\wwwroot\Roundcube-main\Roundcube-main\app\Http\Controllers\
>
> 1. Saare controllers ki list banao (Recurse)
> 2. Har controller mein saare methods list karo
> 3. Har method ka signature batao (public/protected/private, parameters, return type)
> 4. Compare karo:
>    - Same controller, same methods? 
>    - Koi extra method Base mein?
>    - Koi extra method Fork mein?
>    - Same method ka logic different hai?
>
> Web controllers ka separate analysis aur API controllers ka separate.
>
> Report: C:\Inetpub\wwwroot\AUDIT-03C-CONTROLLERS.md"

**OpenCode ko kaho (Part D — Model Relationships & Casts):**
> "BASE: C:\Inetpub\wwwroot\alphaspacepro.online\app\Models\
> FORK: C:\Inetpub\wwwroot\Roundcube-main\Roundcube-main\app\Models\
>
> Har model file padho. Har model ke liye batao:
>
> 1. Fillable/guarded fields
> 2. Casts (type casting)
> 3. Relationships (belongsTo, hasMany, belongsToMany etc.)
>    - Kaun si related model se
>    - Foreign key kya hai
>    - Local key kya hai
> 4. Scopes (local/global)
> 5. Accessors/Mutators
> 6. Events (boot methods)
> 7. Traits used
> 8. SoftDeletes hai ya nahi
>
> Models to check:
> - User.php
> - Domain.php
> - EmailAccount.php
> - LoginAudit.php
> - Role.php
> - Privilege.php
> - Feature.php
> - Module.php
> - Koi extra model? (ExpiryTracker? Vendor? etc)
>
> Har model ka dono versions mein comparison karo.
>
> Report: C:\Inetpub\wwwroot\AUDIT-03D-MODELS.md"

**OpenCode ko kaho (Part E — Services Layer):**
> "BASE: C:\Inetpub\wwwroot\alphaspacepro.online\app\Services\
> FORK: C:\Inetpub\wwwroot\Roundcube-main\Roundcube-main\app\Services\
>
> Har service file padho:
> - AuthService.php
> - DashboardService.php
> - EmailStatService.php
> - SmtpAutoDiscover.php
> - LoginLockoutService.php
> - CsvExportService.php
> - Koi extra service? (ExpiryTrackerService? RenewalService?)
>
> Har service ka:
> - Methods list
> - Method signatures
> - Logic summary
> - Dono mein comparison
>
> Report: C:\Inetpub\wwwroot\AUDIT-03E-SERVICES.md"

**OpenCode ko kaho (Part F — Events & Listeners):**
> "BASE: C:\Inetpub\wwwroot\alphaspacepro.online\app\Events\
> FORK: C:\Inetpub\wwwroot\Roundcube-main\Roundcube-main\app\Events\
> Same for Listeners: app\Listeners\
>
> 1. Saare events list karo
> 2. Har event ka:
>    - Class name
>    - Properties (kya data carry karta hai)
>    - Base mein hai? Fork mein hai?
> 3. Saare listeners list karo
> 4. EventServiceProvider padho — kaunsa event kis listener se mapped hai
>
> Report: C:\Inetpub\wwwroot\AUDIT-03F-EVENTS.md"

**OpenCode ko kaho (Part G — Jobs & Queue):**
> "BASE: C:\Inetpub\wwwroot\alphaspacepro.online\app\Jobs\
> FORK: C:\Inetpub\wwwroot\Roundcube-main\Roundcube-main\app\Jobs\
>
> Har job file padho:
> - EmailSyncJob.php
> - Koi aur job?
>
> Har job ka:
> - Queue name
> - ShouldQueue ya synchronous?
> - handle() method logic
> - Retry/tries settings
> - Timeout
>
> Queue config bhi dekho (config/queue.php)
>
> Report: C:\Inetpub\wwwroot\AUDIT-03G-JOBS.md"

**OpenCode ko kaho (Part H — Notifications):**
> "BASE: C:\Inetpub\wwwroot\alphaspacepro.online\app\Notifications\
> FORK: C:\Inetpub\wwwroot\Roundcube-main\Roundcube-main\app\Notifications\
>
> Har notification file padho:
> - NewEmailArrived.php
> - EmailSyncFailed.php
> - Koi aur?
>
> Har notification ka:
> - Channels (mail, database, broadcast)
> - ShouldQueue ya nahi
> - toMail() / toArray() content
> - Comparison dono mein
>
> Report: C:\Inetpub\wwwroot\AUDIT-03H-NOTIFICATIONS.md"

**OpenCode ko kaho (Part I — Policies):**
> "BASE: C:\Inetpub\wwwroot\alphaspacepro.online\app\Policies\
> FORK: C:\Inetpub\wwwroot\Roundcube-main\Roundcube-main\app\Policies\
>
> Har policy file padho aur compare karo:
> - DomainPolicy.php
> - EmailAccountPolicy.php
> - UserPolicy.php
> - ActivityLogPolicy.php
> - LoginAuditPolicy.php
> - Koi aur?
>
> Har policy ka:
> - Kaun se actions authorize karti hai
> - Logic
> - Dono mein same ya different?
>
> AuthServiceProvider bhi check karo — kis model ko kis policy se map kiya hai
>
> Report: C:\Inetpub\wwwroot\AUDIT-03I-POLICIES.md"

---

## PHASE 4: Feature Deep-Dive Audit

**OpenCode ko kaho (Part A — Authentication Flow):**
> "Donon projects mein authentication flow trace karo:
>
> 1. LoginController ya AuthController:
>    - Login form kahan submit hota hai?
>    - Validation rules
>    - Authentication logic (credentials check)
>    - Suspended user check — Base mein kya hota hai? Fork mein kya?
>    - Login audit log
>    - Session creation
>    - Redirect logic
>
> 2. Password flow:
>    - Password expiry check (CheckPasswordExpiry middleware)
>    - Password change form
>    - Password validation rules
>    - Password history / hash chain (LoginAudit mein hash_chain)
>
> 3. Logout:
>    - Session destroy
>    - Audit log
>
> 4. Impersonation:
>    - Admin kisi user ko impersonate kaise karta hai?
>    - Webmail token generation
>
> Compare karo Base vs Fork step-by-step.
>
> Report: C:\Inetpub\wwwroot\AUDIT-04A-AUTH.md"

**OpenCode ko kaho (Part B — Domain Management):**
> "Domain module ka complete flow:
>
> 1. Domain CRUD:
>    - index(): filters, pagination, search
>    - create(): form fields
>    - store(): validation rules (StoreDomainRequest)
>    - show(): kya dikhata hai
>    - edit(): form fields
>    - update(): validation rules (UpdateDomainRequest)
>    - destroy(): soft delete
>    - forceDelete(): permanent delete
>    - restore(): restore from trash
>
> 2. Mail Server Settings:
>    - IMAP/SMTP fields hain ya nahi Base mein?
>    - Konsi fields? (host, port, encryption, username_suffix)
>    - Validation rules
>    - Encryption (cast in model)
>
> 3. Bulk Import:
>    - Bulk import controller method hai?
>    - CSV format
>    - Validation
>
> 4. Domain Status:
>    - DomainStatus enum
>    - active/suspended/expired logic
>    - Expiry date field hai ya nahi?
>
> 5. Cache Management:
>    - Cache::forget() use hota hai update par?
>
> Har point ka Base vs Fork comparison.
>
> Report: C:\Inetpub\wwwroot\AUDIT-04B-DOMAINS.md"

**OpenCode ko kaho (Part C — Email Accounts):**
> "Email Account module complete flow:
>
> 1. CRUD:
>    - index(): filters, pagination
>    - create(): form fields — fillFromDomain JS hai?
>    - store(): validation
>    - show(): kya dikhata hai
>    - edit(): form fields
>    - update(): validation
>    - destroy/forceDelete/restore
>
> 2. Assignment:
>    - assign() method — user ko email account kaise assign hota hai
>    - revoke() method
>    - email_account_user pivot table
>
> 3. Impersonation:
>    - impersonate() method
>    - Webmail token generation with expires_at
>    - How user logs into SnappyMail
>
> 4. Sync:
>    - sync_enabled field
>    - EmailSyncJob — IMAP sync logic
>    - last_sync_at tracking
>    - sync status display
>
> 5. Password Reset:
>    - Webmail password reset functionality
>
> 6. Domain Settings JSON:
>    - Controller returns domainSettings JSON ya nahi?
>
> Compare Base vs Fork.
>
> Report: C:\Inetpub\wwwroot\AUDIT-04C-EMAILACCOUNTS.md"

**OpenCode ko kaho (Part D — Webmail & Notifications):**
> "Webmail and Notification module:
>
> 1. Webmail Launch:
>    - launch.blade.php compare karo
>    - Base version vs Fork version (Fork mein 380 lines vs Base 105)
>    - Notification polling UI — Base mein hai ya nahi?
>    - SnappyMail iframe integration
>
> 2. Notifications:
>    - NotificationController: index(), show(), markAsRead(), markAllAsRead(), destroy()
>    - poll() method — Base mein hai? Fork mein?
>    - Notification routes
>    - NewEmailArrived notification — ShouldQueue hai?
>    - Notification view (resources/views/vendor/notifications/)
>
> Compare Base vs Fork.
>
> Report: C:\Inetpub\wwwroot\AUDIT-04D-WEBMAIL.md"

**OpenCode ko kaho (Part E — RBAC System):**
> "Complete RBAC analysis:
>
> 1. Roles:
>    - Role model, migration, seeder
>    - Kon kon se roles hain?
>    - Soft deletes?
>
> 2. Privileges:
>    - Privilege model
>    - Direct permissions on users
>
> 3. Features & Modules:
>    - Feature model — konsi features hain?
>    - Module model — konse modules hain?
>    - Module vs Feature relationship
>
> 4. Module Role Permissions:
>    - module_role_permissions table
>    - Kis role ki kis module par kya permission hai?
>    - can_reveal field
>
> 5. User Module Permissions:
>    - user_module_permissions table
>    - User-specific overrides
>
> 6. Middleware:
>    - Permission check middleware kaise work karta hai?
>    - Blade directives (kya hain?)
>
> Compare Base vs Fork.
>
> Report: C:\Inetpub\wwwroot\AUDIT-04E-RBAC.md"

**OpenCode ko kaho (Part F — Activity/Audit Logs):**
> "Activity Log module:
>
> 1. ActivityLogController:
>    - index(): filters (causer, subject, event, date range)
>    - show() 
>    - export()
>    - Batch UUID support
>    - Event column
>
> 2. Activity Log Table:
>    - Schema
>    - Blameable trait — kaise log hota hai?
>    - Subject_type, subject_id
>    - Causer_type, causer_id
>
> 3. Login Audit:
>    - LoginAuditController
>    - login_audits table
>    - Hash chain for tamper-proof logging
>    - IP, user_agent, location tracking
>    - Eager loading (->with('user')) — Base mein hai? Fork mein?
>
> Compare Base vs Fork.
>
> Report: C:\Inetpub\wwwroot\AUDIT-04F-AUDIT.md"

**OpenCode ko kaho (Part G — Dashboard & Stats):**
> "Dashboard module:
>
> 1. DashboardController:
>    - index(): kya data return karta hai?
>    - Stats calculations
>
> 2. DashboardService:
>    - Kya kya stats calculate hoti hain?
>
> 3. Dashboard View:
>    - Widgets
>    - Charts (renewalsExpiryChart?)
>    - Stats cards
>    - Recent activity
>    - Assigned email accounts
>    - Renewal Summary card (exists? kya data dikhata hai?)
>
> 4. Dashboard API:
>    - Api/DashboardController
>    - API endpoints for dashboard data
>
> Compare Base vs Fork.
>
> Report: C:\Inetpub\wwwroot\AUDIT-04G-DASHBOARD.md"

**OpenCode ko kaho (Part H — Search & Global Features):**
> "Search module:
>
> 1. SearchController:
>    - search() method — konse models mein search karta hai?
>    - Filters
>    - Results format
>
> 2. Help Center:
>    - config/help-center.php — kaun se articles hain?
>    - Kya expiry-trackers article registered hai? g-mails article?
>    - Actual help file exists ya nahi?
>    - Help view
>
> 3. Queue Monitor:
>    - QueueMonitorController
>    - Failed jobs view
>    - Retry functionality
>
> 4. CSV Export:
>    - CsvExportService
>    - Konsi konsi cheezein export hoti hain?
>
> 5. Command Palette (JS):
>    - Keyboard shortcuts
>    - Searchable commands
>
> Compare Base vs Fork.
>
> Report: C:\Inetpub\wwwroot\AUDIT-04H-SEARCH.md"

**OpenCode ko kaho (Part I — Expiry Tracker — EXTRA FEATURE CHECK):**
> "BASE project mein EXTRA features dhondo jo Fork mein nahi hain:
>
> 1. Expiry Tracker:
>    - App\Models\ExpiryTracker.php exists?
>    - App\Models\ExpiryTrackerNotification.php?
>    - App\Http\Controllers\Web\ExpiryTrackerController.php?
>    - App\Http\Controllers\Api\ExpiryTrackerController.php?
>    - App\Http\Requests\StoreExpiryTrackerRequest.php?
>    - App\Http\Requests\UpdateExpiryTrackerRequest.php?
>    - App\Services\ExpiryTrackerService.php?
>    - App\Services\ExpiryNotificationService.php?
>    - App\Services\RenewalNotificationService.php?
>    - App\Services\RenewalSyncService.php?
>    - App\Console\Commands\SendEmailReminders.php?
>    - Database migration for expiry_trackers table?
>    - Database migration for expiry_tracker_notifications table?
>    - Views for expiry tracker?
>    - Routes for expiry tracker?
>    - Dashboard\RenewalsWidget.php?
>    - Reports\RenewalReports.php?
>
> Jo bhi exist karta hai, uska POORA code read karo aur summary banao:
>    - Kya karta hai?
>    - Kaise work karta hai?
>    - Kis kis file se interact karta hai?
>    - Complete feature flow diagram (text mein)
>
> 2. Vendor Management:
>    - Koi vendor-related file exist karti hai?
>    - Vendor model? Controller? Migration?
>
> 3. Gmail Integration:
>    - Gmail-specific code?
>    - Google API integration?
>    - OAuth handling?
>
> 4. SSL/Certificate Management:
>    - SSL expiry tracking
>    - Certificate management
>
> 5. Koi aur extra feature jo Fork mein nahi?
>
> Report: C:\Inetpub\wwwroot\AUDIT-04I-EXTRA.md"

**OpenCode ko kaho (Part J — Views Complete Map):**
> "BASE: C:\Inetpub\wwwroot\alphaspacepro.online\resources\views\
> FORK: C:\Inetpub\wwwroot\Roundcube-main\Roundcube-main\resources\views\
>
> 1. Complete view directory tree dono ki
> 2. Common views → same ya different?
> 3. Sirf Base views
> 4. Sirf Fork views
> 5. Har component ka purpose batao (components/ folder)
>
> Compare karo:
> - layouts/
> - components/
> - domains/ (saari blades)
> - email-accounts/ (saari blades)
> - webmail/
> - notifications/
> - dashboard/
> - auth/
> - users/
> - help/
> - queue/
> - search/
> - activity-log/
> - login-audit/
> - errors/
> - vendor/
>
> Report: C:\Inetpub\wwwroot\AUDIT-04J-VIEWS.md"

---

## PHASE 5: Security Deep Audit

**OpenCode ko kaho:**
> "Donon projects ki security audit karo:
>
> 1. .env file:
>    - APP_KEY set hai?
>    - APP_DEBUG = false?
>    - DB credentials
>    - Koi hardcoded secret?
>
> 2. Authentication:
>    - Rate limiting (LoginLockoutService)
>    - Suspended user handling (Base vs Fork — exact diff)
>    - Session IP binding
>    - Concurrent session control
>    - Password expiry enforcement
>    - Sanctum token expiry
>
> 3. Middleware check:
>    - AddSecurityHeaders — kaun se headers add karta hai? (CSP, HSTS, X-Frame, etc.)
>    - CheckSuspended — Base mein log karta hai ya block? Fork mein?
>    - CheckPasswordExpiry — kitne din?
>    - ConcurrentSession — kaam karta hai?
>    - SessionIpBinding — IP change detect karta hai?
>
> 4. CSRF:
>    - All POST forms have @csrf?
>    - API uses token-based auth?
>
> 5. XSS:
>    - Blade {{ }} vs {!! !!} usage
>    - Koi raw output hai?
>
> 6. SQL Injection:
>    - DB::raw() or DB::statement() usage
>    - Raw SQL vs Eloquent
>    - Prepared statements
>
> 7. Encryption:
>    - Encrypted cast — kaun se fields encrypt hain?
>    - SMTP passwords encrypt hain?
>
> 8. File Upload:
>    - Koi file upload feature?
>    - Validation?
>    - Storage permissions?
>
> Har point ka Base vs Fork comparison.
>
> Report: C:\Inetpub\wwwroot\AUDIT-05-SECURITY.md"

---

## PHASE 6: Code Quality Audit

**OpenCode ko kaho:**
> "Donon projects ki code quality check:
>
> 1. PHPStan:
>    - phpstan.neon compare karo
>    - phpstan-baseline.neon compare karo — kitni errors suppress hain? kya errors hain?
>    - Level kya hai?
>
> 2. Test Coverage:
>    - tests/ directory structure compare
>    - Feature tests vs Unit tests
>    - E2E tests (e2e/ folder)
>    - Number of test files
>    - Test classes comparison
>    - PHPUnit config comparison
>
> 3. Code Style:
>    - pint.json compare
>    - Laravel conventions follow hoti hain?
>    - Naming conventions (camelCase methods, snake_case DB columns)
>
> 4. Type Safety:
>    - Method return types declared hain?
>    - Parameter types declared hain?
>    - Property types declared hain?
>
> 5. Error Handling:
>    - Try-catch blocks
>    - Custom exceptions?
>    - Logging practices
>
> 6. Vite/Assets:
>    - vite.config.js comparison
>    - package.json comparison
>    - Build process
>
> Report: C:\Inetpub\wwwroot\AUDIT-06-QUALITY.md"

---

## PHASE 7: Performance Audit

**OpenCode ko kaho:**
> "Donon projects ki performance check:
>
> 1. N+1 Query Check:
>    - Controllers mein eager loading hai ya lazy loading?
>    - ->with() usage vs ->load() usage
>    - Jo bhi ->with nahi hai, wahan N+1 ho sakta hai
>
> 2. Database Indexes:
>    - Indexes on foreign keys?
>    - Indexes on frequently queried columns?
>    - Composite indexes?
>    - Performance indexes migration (Fork wali) — kya indexes bana rahi hai?
>
> 3. Cache Usage:
>    - Cache::remember() usage
>    - Cache::forget() on update
>    - Config cache, route cache, view cache enabled hai?
>    - Query cache?
>
> 4. Queue:
>    - Queue connection (sync? database? redis?)
>    - Failed job handling
>    - Job retry/tries/timeout
>
> 5. IMAP Performance:
>    - EmailStatService — batch fetch hai ya individual?
>    - EmailSyncJob — har account ka alag job? ya ek hi job?
>    - IMAP IDLE worker — blocking ya non-blocking?
>
> 6. Asset Optimization:
>    - Vite build hai ya not?
>    - CSS/JS minified hai?
>    - Images optimized?
>
> Report: C:\Inetpub\wwwroot\AUDIT-07-PERFORMANCE.md"

---

## PHASE 8: Configuration & Dependency Audit

**OpenCode ko kaho:**
> "Donon projects ki configuration check:
>
> 1. composer.json:
>    - Require packages — version differences?
>    - PHP version requirement same?
>    - Scripts section same?
>
> 2. package.json:
>    - NPM packages — version differences?
>    - Dev dependencies same?
>
> 3. config/ folder:
>    - Har config file compare karo (18 files)
>    - app.php, auth.php, database.php, mail.php, queue.php, session.php, logging.php, cache.php, filesystems.php, domains.php, webmail.php, rbac.php, services.php, cors.php, hashing.php, broadcasting.php, view.php, trustedproxy.php
>    - renewals.php — Base mein hai? Fork mein?
>    - help-center.php — same hai?
>
> 4. Laravel version check:
>    - composer.json me laravel/framework version
>    - artisan --version
>
> 5. Bootstrap:
>    - bootstrap/app.php compare
>    - bootstrap/providers.php compare
>
> Report: C:\Inetpub\wwwroot\AUDIT-08-CONFIG.md"

---

## PHASE 9: Server & Deployment Audit

**OpenCode ko kaho:**
> "Server aur deployment check:
>
> 1. IIS Configuration:
>    - public/web.config hai ya nahi dono mein?
>    - Content compare
>    - URL rewrite rules
>
> 2. Apache Configuration:
>    - public/.htaccess compare
>    - Rewrite rules
>    - Security headers
>
> 3. Deployment Scripts:
>    - deploy.bat compare
>    - deploy.sh compare
>    - sync-to-server.ps1 (Fork mein hai?)
>    - watch-and-sync.ps1
>    - install-worker-service.ps1
>
> 4. Scheduled Tasks:
>    - routes/console.php — kya scheduled hain?
>    - Schedule in Kernel?
>    - Windows Scheduled Task setup?
>
> 5. Worker Scripts:
>    - scripts/imap-idle-worker.php compare
>    - SETUP_WORKER.md compare
>
> 6. Diagnostics:
>    - check_smtp.php compare
>    - check-imap.php (Fork mein hai?)
>    - test-imap.php (Fork mein hai?)
>    - public/webmail/imap-idle-status.php compare
>
> Report: C:\Inetpub\wwwroot\AUDIT-09-SERVER.md"

---

## PHASE 10: Merge Readiness & Final Report

**OpenCode ko kaho:**
> "Phases 0-9 ki saari reports padho. Merge readiness report banao:
>
> ## MERGE SUMMARY
>
> | Category | Status |
> |----------|--------|
> | Identical files | XX% (XX/XX) |
> | Different files | XX files |
> | Files only in Base | XX files |
> | Files only in Fork | XX files |
>
> ## CONFLICT MATRIX
>
> | Conflict Type | Severity | Files | Solution |
> |--------------|----------|-------|----------|
> | Session table duplicate | HIGH | 1 migration | Delete Fork's migration |
> | Same file, different logic | MEDIUM | XX files | Manual merge needed |
> | Missing files | LOW | XX files | Copy from Fork |
> | Extra Base features | LOW | XX files | Preserve |
>
> ## WHAT FORK HAS THAT BASE DOESN'T
> - List feature by feature
> - Impact of each
>
> ## WHAT BASE HAS THAT FORK DOESN'T
> - List extra features (expiry tracker, etc.)
> - Impact of each
>
> ## MERGE STRATEGY
> - Which base to use? (BASE ya UPDATED-BASE?)
> - Step-by-step plan
> - Risk assessment (LOW/MEDIUM/HIGH)
> - Estimated time
>
> ## FINAL RECOMMENDATION
> - Merge recommended ya nahi?
> - If yes, exactly kaise?
> - If no, alternative?
>
> Report: C:\Inetpub\wwwroot\AUDIT-10-FINAL-MERGE-READINESS.md"

---

## HOW TO RUN

**Option A: Phase-by-Phase (Recommended)**

Ek phase karo, report check karo, phir agla phase.

> OpenCode ko kaho: "Phase 0 karo" → report bane gi → aap check karo → "Phase 1A karo" → ...

**Option B: All at Once**

> OpenCode ko kaho: "Phase 1A, 1B, 1C, 1D ek sath karo" (jo independent hain)

**Option C: Custom**

Sirf specific phases jin mein interest hai.

---

## FINAL OUTPUT FILES (Reports)

```
C:\Inetpub\wwwroot\
├── AUDIT-00-ENVIRONMENT.md
├── AUDIT-01A-FILES.md
├── AUDIT-01B-HASHES.md
├── AUDIT-01C-DIFFS.md
├── AUDIT-01D-UNIQUE.md
├── AUDIT-02A-MIGRATIONS.md
├── AUDIT-02B-SCHEMA.md
├── AUDIT-02C-SEEDERS.md
├── AUDIT-02D-FACTORIES.md
├── AUDIT-03A-ROUTES.md
├── AUDIT-03B-MIDDLEWARE.md
├── AUDIT-03C-CONTROLLERS.md
├── AUDIT-03D-MODELS.md
├── AUDIT-03E-SERVICES.md
├── AUDIT-03F-EVENTS.md
├── AUDIT-03G-JOBS.md
├── AUDIT-03H-NOTIFICATIONS.md
├── AUDIT-03I-POLICIES.md
├── AUDIT-04A-AUTH.md
├── AUDIT-04B-DOMAINS.md
├── AUDIT-04C-EMAILACCOUNTS.md
├── AUDIT-04D-WEBMAIL.md
├── AUDIT-04E-RBAC.md
├── AUDIT-04F-AUDIT.md
├── AUDIT-04G-DASHBOARD.md
├── AUDIT-04H-SEARCH.md
├── AUDIT-04I-EXTRA.md
├── AUDIT-04J-VIEWS.md
├── AUDIT-05-SECURITY.md
├── AUDIT-06-QUALITY.md
├── AUDIT-07-PERFORMANCE.md
├── AUDIT-08-CONFIG.md
├── AUDIT-09-SERVER.md
└── AUDIT-10-FINAL-MERGE-READINESS.md
```

TOTAL: 34 detailed reports, 10 phases, har angle covered. Koi bhi surprise nahi bachega.
