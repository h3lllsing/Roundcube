export const ADMIN_EMAIL = 'admin@tyro.project';
export const ADMIN_PASSWORD = 'password';

export async function login(page) {
  await page.goto('login', { waitUntil: 'domcontentloaded' });
  await page.waitForSelector('input[name="email"]', { timeout: 10000 });
  await page.fill('input[name="email"]', ADMIN_EMAIL);
  await page.fill('input[name="password"]', ADMIN_PASSWORD);
  await page.click('button[type="submit"]');
  await page.waitForURL('**/dashboard', { timeout: 15000 });
}

export async function isOnLoginPage(page) {
  return page.url().includes('/login');
}

export async function clickSidebarLink(page, text) {
  let link = page.locator(`aside a:has-text("${text}"), nav a:has-text("${text}")`).first();
  const isVisible = await link.isVisible().catch(() => false);
  if (!isVisible) {
    const hamburger = page.locator('button[class*="menu"], button[class*="hamburger"], [class*="navbar-toggler"], [aria-label*="menu" i]').first();
    if (await hamburger.isVisible().catch(() => false)) {
      await hamburger.click();
      await page.waitForTimeout(500);
    }
    link = page.locator(`aside a:has-text("${text}"), nav a:has-text("${text}")`).first();
  }
  await link.scrollIntoViewIfNeeded();
  await link.click();
}

export const MAJOR_PAGES = [
  { path: 'dashboard', label: 'Dashboard' },
  { path: 'monitoring', label: 'Monitoring' },
  { path: 'notifications', label: 'Notifications' },
  { path: 'service-providers', label: 'Service Providers' },
  { path: 'hostings', label: 'Hosting' },
  { path: 'domains', label: 'Domains' },
  { path: 'domain-emails', label: 'Domain Emails' },
  { path: 'vps', label: 'VPS' },
  { path: 'voip', label: 'VoIP' },
  { path: 'other-services', label: 'Other Services' },
  { path: 'expiry-trackers', label: 'Renewals' },
  { path: 'assets', label: 'Assets' },
  { path: 'g-mails', label: 'G-Mails' },
  { path: 'vault', label: 'Vault Shared' },
  { path: 'my-vault', label: 'My Vault' },
  { path: 'tasks', label: 'Tasks' },
  { path: 'my-tasks', label: 'My Tasks' },
  { path: 'notes', label: 'Notes' },
  { path: 'calendar', label: 'Calendar' },
  { path: 'users', label: 'Users' },
  { path: 'admin/roles', label: 'Roles' },
  { path: 'admin/role-templates', label: 'Role Templates' },
  { path: 'admin/privileges', label: 'Privileges' },
  { path: 'admin/smtp-profiles', label: 'Mail Settings' },
  { path: 'module-permissions', label: 'Permissions' },
  { path: 'features', label: 'Features' },
  { path: 'modules', label: 'Modules' },
  { path: 'activity-logs', label: 'Activity Logs' },
  { path: 'login-audits', label: 'Login History' },
  { path: 'webhooks', label: 'Integrations' },
  { path: 'tokens', label: 'API Access' },
  { path: 'attachments', label: 'Attachments' },
  { path: 'import', label: 'Import' },
  { path: 'reports', label: 'Reports' },
  { path: 'profile', label: 'My Profile' },
  { path: 'my-permissions', label: 'My Access' },
  { path: 'guide', label: 'Help Center' },
  { path: 'design-system', label: 'Design System' },
];

export const DROPDOWN_PAGES = [
  'hostings', 'domains', 'vps', 'voip', 'service-providers',
  'domain-emails', 'other-services', 'expiry-trackers',
  'assets', 'g-mails', 'vault', 'tasks', 'notes',
  'users', 'admin/roles', 'webhooks', 'login-audits',
];
