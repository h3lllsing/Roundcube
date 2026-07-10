# SEEDER PRODUCTION RISK EVIDENCE

## Allegation: DemoDataSeeder runs in production during `php artisan migrate --seed`

> Check if DemoDataSeeder runs in production during php artisan migrate --seed.

---

## VERDICT: PROVEN — BLOCK RELEASE

| Item | Value |
|------|-------|
| **Risk level** | **HIGH** |
| **Blocker** | **YES** |
| **Code path** | `DatabaseSeeder.php:33` |
| **Guard fails on** | `production` environment |

---

## EVIDENCE

### `database/seeders/DatabaseSeeder.php`
```php
// Lines 16-36
public function run(): void
{
    $this->call([TyroSeeder::class]);

    User::updateOrCreate(
        ['email' => 'test@example.com'],
        ['name' => 'Test User', 'password' => bcrypt('password')],
    );

    $this->call(AssetCategorySeeder::class);
    $this->call(AssetTypeSeeder::class);
    $this->call(FeatureModuleSeeder::class);
    $this->call(RolePermissionSeeder::class);
    $this->call(RoleTemplateSeeder::class);

    if (! app()->environment('testing')) {          // <--- LINE 33
        $this->call(DemoDataSeeder::class);
    }
}
```

### Guard analysis
- `! app()->environment('testing')` → `true` for `production`, `local`, `staging`, etc.
- **Only `testing` is excluded** — `production` is NOT excluded.

### What DemoDataSeeder creates in production

From `database/seeders/DemoDataSeeder.php`:

| Entity | Records | Details |
|--------|---------|---------|
| User (admin) | 1 | `admin@tyro.project`, auto-created + assigned `super-admin` role (line 25-29) |
| User (test) | 1 | `test@example.com` (if not already existing) |
| Service Providers | 3 | DigitalOcean, Namecheap, Google Workspace with passwords |
| Hosting entries | 2 | With passwords, cPanel URLs, service provider references |
| VPS entries | 2 | With IPs, passwords, OS details |
| Domains | 2 | With DNS servers parked |
| VoIP entries | 2 | With phone numbers, extensions, passwords |
| Domain Emails | 2 | With passwords |
| Other Services | 2 | Slack Premium, GitHub Enterprise with passwords |
| Expiry Trackers | 2 | With renewal dates, costs |
| Notes | 2 | |
| Tasks | 2 | |
| Vault entries | 2 | AWS root credentials, GitHub PAT — plaintext until encrypted |

### Risk scenarios
1. **Production deploy via `--seed`**: Demo data mixed with real data
2. **Security exposure**: Demo admin account `admin@tyro.project` with known password
3. **Data integrity**: FirstOrCreate may not run if records already exist, but partial execution still contaminates

---

## FIX REQUIRED

Change line 33 of `database/seeders/DatabaseSeeder.php` from:
```php
if (! app()->environment('testing')) {
```
to:
```php
if (! app()->environment('testing', 'production')) {
```

---

## CONCLUSION

**PROVEN.** DemoDataSeeder runs in all environments except `testing`. A `php artisan migrate --seed` on production creates demo admin accounts, passwords, and business data.
