# Office System Audit Guide — OpenCode Prompts

## Dono Portal Office System Mein Set Hone Ke Baad

Jab aap dono portals (alphaspacepro.online + Roundcube-main) office system mein copy/ready kar chuke hon, to **opencode ko yeh exact prompts dena**:

---

## AUDIT 1: Complete File Comparison

**OpenCode ko kaho:**
> "Compare karo do projects ko:
> 1. C:\Inetpub\wwwroot\alphaspacepro.online  (yeh BASE/updated hai)
> 2. C:\Inetpub\wwwroot\Roundcube-main\Roundcube-main  (yeh FORK hai)
>
> Sab karo:
> - Dono mein total files count karo
> - Common files dhundo
> - Hash compare karo (SHA256) jo files common hain
> - Files jo sirf BASE mein hain unki list banao
> - Files jo sirf FORK mein hain unki list banao
> - Jo files DIFFERENT hain unka content diff karo line-by-line
>
> Detailed report banao jisme yeh sab ho."

---

## AUDIT 2: Database Schema Comparison

**OpenCode ko kaho:**
> "Dono projects ki database migrations compare karo:
> 1. C:\Inetpub\wwwroot\alphaspacepro.online\database\migrations\
> 2. C:\Inetpub\wwwroot\Roundcube-main\Roundcube-main\database\migrations\
>
> Batao:
> - Konsi migrations dono mein COMMON hain
> - Konsi migrations sirf BASE mein hain
> - Konsi migrations sirf FORK mein hain
> - Koi table conflict hai? (same table 2 alag migrations mein)
> - Koi column type mismatch hai?"

---

## AUDIT 3: Routes & API Comparison

**OpenCode ko kaho:**
> "routes/web.php compare karo dono projects ki:
> 1. C:\Inetpub\wwwroot\alphaspacepro.online\routes\web.php
> 2. C:\Inetpub\wwwroot\Roundcube-main\Roundcube-main\routes\web.php
>
> Batao:
> - Total routes dono mein
> - Konsi routes COMMON hain
> - Konsi routes sirf BASE mein hain
> - Konsi routes sirf FORK mein hain
> - API routes bhi compare karo (routes/api.php)"

---

## AUDIT 4: Expiry Tracker & Vendor Features Check

**OpenCode ko kaho:**
> "BASE project (C:\Inetpub\wwwroot\alphaspacepro.online) mein in features ko dhundo:
> 1. Expiry Tracker — koi ExpiryTrackerController? ExpiryTracker model? expiry_trackers table migration?
> 2. Vendor Management — koi VendorController? Vendor model? vendors table?
> 3. Gmail Integration — koi Gmail-specific code? Gmail API integration?
> 4. SSL/Certificate Expiry — koi SSL expiry tracking code?
> 5. Domain Expiry Dates — domain table mein expires_at ya renewal_date field?
>
> Har feature ke liye batao:
> - Controller hai ya nahi (path batao)
> - Model hai ya nahi
> - Migration/tables hain ya nahi
> - Views hain ya nahi
> - Routes hain ya nahi
> - Seeders hain ya nahi
>
> Phir yeh same checks FORK project (C:\Inetpub\wwwroot\Roundcube-main\Roundcube-main) par bhi karo."

---

## AUDIT 5: Complete Feature Inventory

**OpenCode ko kaho:**
> "Dono projects ka complete feature inventory banao. Har feature ke liye batao ki BASE mein hai ya FORK mein hai ya dono mein:
>
> Modules to check:
> 1. Authentication (login, logout, password change, password expiry)
> 2. User Management (CRUD, suspend, soft delete, assign roles)
> 3. Domain Management (CRUD, IMAP/SMTP settings, bulk import, search)
> 4. Email Account Management (CRUD, assign, revoke, impersonate, sync)
> 5. Webmail (SnappyMail launch, notifications)
> 6. Notifications (CRUD, polling, mark read, mark all read)
> 7. IMAP Sync (job, status, IDLE worker)
> 8. RBAC (roles, privileges, modules, features, permissions)
> 9. Activity/Audit Logs (view, filter, export)
> 10. Queue Monitor (view, retry failed)
> 11. Search (global search, filters)
> 12. Dashboard (stats, charts, widgets)
> 13. Help Center (articles, categories)
> 14. API (auth, dashboard, domains, email accounts, health)
> 15. Exports (CSV export)
> 16. Security (middleware, encryption, session binding, lockout)
> 17. Expiry Tracker (if exists)
> 18. Vendor Management (if exists)
> 19. Configuration (all config files)
>
> Har feature ka status batao: Present/Not Present/Partial."

---

## AUDIT 6: Merge Suitability Check

**OpenCode ko kaho:**
> "Base project (C:\Inetpub\wwwroot\alphaspacepro.online) and Fork project (C:\Inetpub\wwwroot\Roundcube-main\Roundcube-main) ko merge karne se pehle yeh checks karo:
>
> 1. .env file — dono mein variables match karte hain?
> 2. composer.json — dependencies same hain?
> 3. package.json — npm packages same hain?
> 4. config/ folder — har config file same hai ya different?
> 5. public/ folder — assets same hain?
> 6. resources/js/ — JS files same hain?
> 7. resources/css/ — CSS files same hain?
> 8. lang/ — translations same hain?
> 9. app/Enums/ — enums same hain?
> 10. app/Helpers/ — helpers same hain?
>
> Batao: Merge safe hai ya nahi? Kya conflicts honge?"

---

## Ek Saath Karna Ho To

**Sab audits ek saath opencode ko kaho:**
> "Mere paas 2 projects hain:
> - BASE: C:\Inetpub\wwwroot\alphaspacepro.online
> - FORK: C:\Inetpub\wwwroot\Roundcube-main\Roundcube-main
>
> Yeh sab karo:
> 1. File-by-file hash comparison (SHA256)
> 2. Content diff of different files
> 3. Database migrations compare
> 4. Routes compare (web.php + api.php)
> 5. Feature inventory (har module present hai ya nahi)
> 6. Expiry tracker, vendor, Gmail features dhundo
> 7. Config files compare
> 8. Merge safety check
>
> Detailed report banao sab ka ek sath."

---

## Result Files

Har audit ke baad, yeh files save ho jayengi:
- `OFFICE-AUDIT-COMPARISON.md` — file comparison
- `OFFICE-AUDIT-DATABASE.md` — DB schema diff
- `OFFICE-AUDIT-ROUTES.md` — routes/api diff
- `OFFICE-AUDIT-FEATURES.md` — feature inventory
- `OFFICE-AUDIT-MERGE-CHECK.md` — merge readiness

---

Office system mein yeh step karne ke baad mujhe batao. Phir hum decide karein ge merge karna hai ya nahi.
