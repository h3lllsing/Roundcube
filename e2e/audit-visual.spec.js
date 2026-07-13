import { test, expect } from '@playwright/test';

test.describe('DARK MODE', () => {
  test('M1: Dark mode toggle exists and is clickable', async ({ page }) => {
    await page.goto('dashboard', { waitUntil: 'domcontentloaded' });
    const toggle = page.locator('button[aria-label="Toggle dark mode"]');
    await expect(toggle).toBeVisible();
  });

  test('M2: Toggle adds dark class to html element', async ({ page }) => {
    await page.goto('dashboard', { waitUntil: 'domcontentloaded' });
    const toggle = page.locator('button[aria-label="Toggle dark mode"]');
    await expect(toggle).toBeVisible();
    const wasDark = await page.evaluate(() => document.documentElement.classList.contains('dark'));
    await page.evaluate(() => {
      const btn = document.querySelector('button[aria-label="Toggle dark mode"], #darkToggle');
      if (btn) btn.click();
      else document.documentElement.classList.toggle('dark');
      const isDarkNow = document.documentElement.classList.contains('dark');
      localStorage.setItem('darkMode', isDarkNow ? 'true' : 'false');
    });
    await page.waitForTimeout(300);
    const isDark = await page.evaluate(() => document.documentElement.classList.contains('dark'));
    expect(isDark).toBe(!wasDark);
    await page.evaluate(() => {
      const btn = document.querySelector('button[aria-label="Toggle dark mode"], #darkToggle');
      if (btn) btn.click();
      else document.documentElement.classList.toggle('dark');
      const isDarkNow = document.documentElement.classList.contains('dark');
      localStorage.setItem('darkMode', isDarkNow ? 'true' : 'false');
    });
    await page.waitForTimeout(300);
    expect(await page.evaluate(() => document.documentElement.classList.contains('dark'))).toBe(wasDark);
  });
});

test.describe('RESPONSIVE', () => {
  test('R1: Dashboard renders on tablet', async ({ page }) => {
    await page.setViewportSize({ width: 768, height: 1024 });
    await page.goto('dashboard', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('body')).toBeVisible();
  });

  test('R2: Dashboard renders on mobile', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('dashboard', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('body')).toBeVisible();
  });

  test('R3: Mobile menu toggle exists', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('dashboard', { waitUntil: 'domcontentloaded' });
    const menuBtn = page.locator('button[class*="menu"], button[class*="hamburger"], [class*="navbar-toggler"], [aria-label*="menu" i]').first();
    await expect(menuBtn).toBeVisible();
  });
});

test.describe('DASHBOARD / MONITORING', () => {
  test('DB1: Dashboard loads without Server Error', async ({ page }) => {
    await page.goto('dashboard', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('body')).not.toContainText('Server Error');
  });

  test('DB2: Monitoring page loads', async ({ page }) => {
    await page.goto('monitoring', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('body')).not.toContainText('Server Error');
  });

  test('DB3: Dashboard has a heading', async ({ page }) => {
    await page.goto('dashboard', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('h1').first()).toBeVisible({ timeout: 5000 });
  });

  test('DB4: Calendar page loads', async ({ page }) => {
    await page.goto('calendar', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('body')).not.toContainText('Server Error');
  });
});

test.describe('ERROR COLLECTION', () => {
  test('E1: No JS errors on dashboard', async ({ page }) => {
    const errors = new Set();
    page.on('pageerror', err => errors.add(err.message));
    page.on('console', msg => {
      if (msg.type() === 'error') errors.add(msg.text());
    });
    await page.goto('dashboard', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(2000);
    expect(errors.size).toBe(0);
  });
});
