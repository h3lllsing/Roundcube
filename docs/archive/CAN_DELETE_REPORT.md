# CAN_DELETE_REPORT

> Generated: 2026-07-03
> All items moved to `_can_delete/` (quarantine). Nothing deleted.

## Summary

| Category | Files | Size |
|---|---|---|
| **reports** (audits, plans, .txt notes) | 297 | 3.23 MB |
| **logs** (runtime API + Laravel logs) | 31 | 49.19 MB |
| **cache** (compiled views, bootstrap cache, data cache) | 328 | 3.91 MB |
| **temp** (coverage reports, test artifacts, docker configs, scripts) | 260 | 17.76 MB |
| **backups** (.env.example.bak) | 1 | < 0.01 MB |
| **Total** | **917** | **74.11 MB** |

---

## Reports (`_can_delete/reports/`)

297 files, 3.23 MB.

Audit reports, design system docs, migration plans, sprint plans, patch reports, phase plans, implementation reports, verification reports, txt notes. All non-essential markdown and text files that are:

- AI-generated audit/review documents
- Migration and implementation plans
- Sprint tracking documents
- Design system proposals and scorecards
- Patch release reports
- Architecture proposals
- Route lists and feature comparisons

**Excluded (kept at root):** `01_*.md`–`18_*.md` system guides (loaded by HelpService at runtime), `README.md`, `CHANGELOG.md`, `CONTRIBUTING.md`, `INSTALLATION.md`, `USER_GUIDE.md`, `DEPLOY.md`, `DEPLOYMENT_GUIDE.md`, `SECURITY_BASELINE.md`.

---

## Logs (`_can_delete/logs/`)

31 files, 49.19 MB.

| Pattern | Description |
|---|---|
| `api-2026-*.log` (30 files) | Daily API request logs |
| `laravel.log` (1 file, 18.6 MB) | Default Laravel log |

All runtime-generated. Laravel will recreate them as needed.

---

## Cache (`_can_delete/cache/`)

328 files, 3.91 MB.

| Source | Files | Contents |
|---|---|---|
| `storage/framework/views/*.php` | ~290 | Compiled Blade templates |
| `storage/framework/cache/data/*` | ~36 | Framework cache entries |
| `bootstrap/cache/packages.php` | 1 | Package manifest |
| `bootstrap/cache/services.php` | 1 | Service manifest |

All regenerated automatically or via `php artisan`.

---

## Temp (`_can_delete/temp/`)

260 files, 17.76 MB.

| Item | Size | Reason |
|---|---|---|
| `coverage/` | 17.4 MB | HTML code coverage report (233 files) |
| `deploy/` | small | Deployment reference files |
| `docs/` | small | Additional documentation |
| `e2e/` | small | End-to-end test fixtures |
| `scripts/` | small | Test helper scripts |
| `.sixth/` | empty | Tool config placeholder |
| `.phpunit.result.cache` | 220 KB | PHPUnit result cache |
| `phpstan*.neon` | small | PHPStan config (regenerable) |
| `phpunit.xml` | small | PHPUnit config (in version control) |
| `docker-compose.yml`, `Dockerfile`, `.dockerignore` | small | Docker configs |
| `phpunit_stderr.txt`, `phpunit_output.txt` | small | Test output |
| `PHASE0_PROTOTYPE.html` | small | Early HTML prototype |
| `full_routes.txt`, `route_list.txt` | small | Generated route dumps |
| `.editorconfig` | small | IDE config |
| `.env.e2e`, `.env.testing` | small | Alternate env files |
| `deploy.sh` | small | Deploy script |

---

## Backups (`_can_delete/backups/`)

1 file, < 0.01 MB.

- `.env.example.bak` — staged backup of `.env.example`, abandoned.

---

## Restoration

To restore any or all items:

```powershell
# Everything
Move-Item -Path "_can_delete/reports/*" -Destination "." -Force
Move-Item -Path "_can_delete/logs/*" -Destination "storage/logs/" -Force
Move-Item -Path "_can_delete/cache/*" -Destination "storage/framework/views/" -Force
Move-Item -Path "_can_delete/backups/*" -Destination "." -Force

# Temp items back to original locations manually
```

## Post-Move Commands

```bash
php artisan view:clear
php artisan view:cache
npm run build
```
