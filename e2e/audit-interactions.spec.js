import { test, expect } from '@playwright/test';
import { DROPDOWN_PAGES } from './helpers/auth.js';

function isDesktop(page) {
  const vp = page.viewportSize();
  return vp && vp.width >= 1024;
}

test.describe('THREE-DOT ACTION MENUS', () => {
  for (const path of DROPDOWN_PAGES) {
    test(`D: ${path} action menu opens and has items`, async ({ page }) => {
      test.skip(!isDesktop(page), 'Three-dot menus tested on desktop only');
      test.skip(path === 'login-audits', 'login-audits page has no action buttons');
      await page.goto(path, { waitUntil: 'domcontentloaded' });
      const actionBtn = page.locator('button[aria-label$=" actions"]').first();
      const btnCount = await actionBtn.count();
      test.skip(btnCount === 0, `No action buttons on ${path}`);
      await expect(actionBtn).toBeAttached({ timeout: 5000 });
      await actionBtn.scrollIntoViewIfNeeded();
      await actionBtn.click();
      await page.waitForTimeout(300);
      const menu = page.locator('[role="menu"]').first();
      await expect(menu).toBeVisible({ timeout: 5000 });
      const items = await menu.locator('a, button').allTextContents();
      expect(items.length).toBeGreaterThanOrEqual(2);
    });
  }
});

test.describe('CREDENTIAL UI', () => {
  test('C: Hosting detail page has credential controls', async ({ page }) => {
    await page.goto('hostings', { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(2000);
    const links = await page.locator('a[href*="hosting"]').all();
    let dest = null;
    for (const link of links) {
      const text = await link.textContent();
      const href = await link.getAttribute('href');
      if (href && !/create|edit/.test(href) && !/create|edit/i.test(text || '')) {
        dest = href.replace('http://localhost/unknow/public/', '');
        break;
      }
    }
    if (!dest) {
      throw new Error('Could not find hosting detail link on hostings page');
    }
    await page.goto(dest, { waitUntil: 'domcontentloaded' });
    await expect(page.locator('body')).not.toContainText('Server Error');
    const reveal = page.locator('button[title*="assword"], button:has-text("Show"), [class*="reveal"]').first();
    const copy = page.locator('button[title*="Copy"], [data-copy-pwd], button:has-text("Password")').first();
    const hasCtrl = (await reveal.count() > 0) || (await copy.count() > 0);
    expect(hasCtrl).toBe(true);
  });
});

test.describe('FILTER / SEARCH PAGES', () => {
  test('F1: Hosting page loads', async ({ page }) => {
    await page.goto('hostings', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('body')).toBeVisible();
  });

  test('F2: Users page loads', async ({ page }) => {
    await page.goto('users', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('body')).toBeVisible();
  });
});

test.describe('PAGINATION', () => {
  test('PG1: Desktop Next is visible and navigates', async ({ page }) => {
    test.skip(!isDesktop(page), 'Pagination tested on desktop only');
    await page.goto('hostings', { waitUntil: 'domcontentloaded' });
    const next = page.locator('a[rel="next"]:visible').first();
    await expect(next).toBeVisible({ timeout: 5000 });
    const before = page.url();
    await next.click();
    await page.waitForLoadState('domcontentloaded');
    expect(page.url()).toContain('page=2');
    expect(page.url()).not.toBe(before);
    await expect(page.locator('body')).not.toContainText('Server Error');
    await expect(page.locator('a[rel="prev"]:visible').first()).toBeVisible();
  });
});

test.describe('FORM VALIDATION', () => {
  test('V1: User create form renders without server error', async ({ page }) => {
    await page.goto('users/create', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('body')).not.toContainText('Server Error');
  });
});
