import { test, expect } from '@playwright/test';
import { MAJOR_PAGES } from './helpers/auth.js';

test.describe('PAGE LOAD AUDIT', () => {
  for (const p of MAJOR_PAGES) {
    test(`P: ${p.label} (${p.path}) loads with 200 and no errors`, async ({ page }) => {
      const jsErrors = [];
      page.on('pageerror', err => jsErrors.push(err.message));
      page.on('console', msg => {
        if (msg.type() === 'error') jsErrors.push(msg.text());
      });

      const resp = await page.goto(p.path, { waitUntil: 'domcontentloaded' });
      await expect(resp?.status()).toBe(200);
      const body = page.locator('body');
      await expect(body).not.toContainText('Server Error', { timeout: 3000 });
      await expect(body).not.toContainText('Whoops', { timeout: 1000 });

      const h1 = page.locator('h1').first();
      if (await h1.count().then(c => c > 0)) {
        await expect(h1).toBeVisible({ timeout: 3000 });
      }

      expect(jsErrors.length, `JS errors: ${JSON.stringify(jsErrors)}`).toBe(0);
    });
  }
});
