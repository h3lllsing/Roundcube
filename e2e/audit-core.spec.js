import { test, expect } from '@playwright/test';
import { login, isOnLoginPage, clickSidebarLink } from './helpers/auth.js';

function isDesktop(page) {
  const vp = page.viewportSize();
  return vp && vp.width >= 1024;
}

test.describe('AUTHENTICATION', () => {
  test.use({ storageState: { cookies: [], origins: [] } });

  test('A1: Login page loads', async ({ page }) => {
    await page.goto('login', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('input[name="email"]')).toBeVisible({ timeout: 10000 });
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('button[type="submit"]')).toBeVisible();
  });

  test('A2: Login succeeds and redirects to dashboard', async ({ page }) => {
    await login(page);
    await expect(page).toHaveURL(/dashboard/);
    await expect(page.locator('h1').first()).toBeVisible();
  });

  test('A3: Logout via UI works', async ({ page }) => {
    test.skip(!isDesktop(page), 'Logout button behind hamburger on tablet/mobile');
    await login(page);
    const logoutForm = page.locator('form[action*="logout"]');
    await expect(logoutForm).toBeAttached();
    const btn = logoutForm.locator('button[type="submit"]');
    await btn.click();
    await page.waitForLoadState('domcontentloaded');
    expect(await isOnLoginPage(page)).toBe(true);
  });

  test('A4: Guest redirected to login for protected pages', async ({ page }) => {
    await page.goto('dashboard', { waitUntil: 'domcontentloaded' });
    expect(await isOnLoginPage(page)).toBe(true);
  });
});

test.describe('SIDEBAR NAVIGATION', () => {
  async function sidebarText(page) {
    return page.locator('aside a, nav a').allTextContents();
  }

  test('B1: Sidebar contains Dashboard', async ({ page }) => {
    await page.goto('dashboard', { waitUntil: 'domcontentloaded' });
    const texts = await sidebarText(page);
    expect(texts.join(' ').toLowerCase()).toContain('dashboard');
  });

  test('B2: Dashboard link text is visible', async ({ page }) => {
    await page.goto('dashboard', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('aside a:has-text("Dashboard")').first()).toBeVisible();
  });

  test('B3: Sidebar infrastructure links', async ({ page }) => {
    await page.goto('dashboard', { waitUntil: 'domcontentloaded' });
    const texts = await sidebarText(page);
    const joined = texts.join(' ').toLowerCase();
    expect(joined).toContain('hosting');
    expect(joined).toContain('domain');
    expect(joined).toContain('vps');
    expect(joined).toContain('credential');
  });

  test('B4: Sidebar has Users and Roles', async ({ page }) => {
    await page.goto('dashboard', { waitUntil: 'domcontentloaded' });
    const texts = await sidebarText(page);
    const joined = texts.join(' ').toLowerCase();
    expect(joined).toContain('users');
    expect(joined).toContain('roles');
  });

  test('B5: Navigate via sidebar to Hosting', async ({ page }) => {
    test.skip(!isDesktop(page), 'Sidebar clicks only on desktop');
    await page.goto('dashboard', { waitUntil: 'domcontentloaded' });
    await clickSidebarLink(page, 'Hosting');
    await expect(page).toHaveURL(/hostings/);
  });

  test('B6: Navigate via sidebar to Users', async ({ page }) => {
    test.skip(!isDesktop(page), 'Sidebar clicks only on desktop');
    await page.goto('dashboard', { waitUntil: 'domcontentloaded' });
    await clickSidebarLink(page, 'Users');
    await expect(page).toHaveURL(/users/);
  });

  test('B7: Navigate via sidebar to Monitoring', async ({ page }) => {
    test.skip(!isDesktop(page), 'Sidebar clicks only on desktop');
    await page.goto('dashboard', { waitUntil: 'domcontentloaded' });
    await clickSidebarLink(page, 'Monitoring');
    await expect(page).toHaveURL(/monitoring/);
  });
});
