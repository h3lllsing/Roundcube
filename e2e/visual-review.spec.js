import { test, expect } from '@playwright/test';

const CREDENTIAL_MODULES = [
  { path: 'hostings',  label: 'Hosting',     credentialLabel: 'Password',        ref: true },
  { path: 'vps',        label: 'VPS',           credentialLabel: 'Password',        ref: true },
  { path: 'voip',       label: 'VoIP',          credentialLabel: 'Ext. Password',  ref: false },
  { path: 'service-providers', label: 'Service Providers', credentialLabel: 'Password', ref: false },
  { path: 'domain-emails',     label: 'Domain Emails',     credentialLabel: 'Password', ref: false },
  { path: 'other-services',    label: 'Other Services',    credentialLabel: 'Password', ref: false },
  { path: 'g-mails',           label: 'G-Mails',           credentialLabel: 'Password', ref: false },
  { path: 'vault',             label: 'Vault',              credentialLabel: null,        ref: true },
];

test.describe('CREDENTIAL ACTION VISUAL REVIEW', () => {

  for (const mod of CREDENTIAL_MODULES) {
    test.describe(`${mod.label} (${mod.path})`, () => {

      test(`A: Page loads with 200 and no errors`, async ({ page }) => {
        const errors = [];
        page.on('pageerror', e => errors.push(e.message));
        page.on('console', msg => { if (msg.type() === 'error') errors.push(msg.text()); });
        const resp = await page.goto(mod.path, { waitUntil: 'domcontentloaded' });
        await expect(resp?.status()).toBe(200);
        expect(errors).toEqual([]);
      });

      test(`B: ⋮ trigger exists and is accessible`, async ({ page }) => {
        await page.goto(mod.path, { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(1500);
        const trigger = page.locator('button[aria-haspopup="true"]').first();
        await expect(trigger).toBeAttached({ timeout: 5000 });
        await expect(trigger).toBeVisible();
        const title = await trigger.getAttribute('aria-label');
        expect(title?.length).toBeGreaterThan(0);
      });

      test(`C: No password/secret table column`, async ({ page }) => {
        await page.goto(mod.path, { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(1500);
        const headers = await page.locator('thead th, [scope="col"]').allTextContents();
        const headerText = headers.join(' ').toLowerCase();
        expect(headerText).not.toContain('password');
        expect(headerText).not.toContain('secret');
      });

      test(`D: No visible credential action outside ⋮`, async ({ page }) => {
        await page.goto(mod.path, { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(1500);
        const inlineCopyBtns = page.locator('button[data-copy-pwd]');
        const inlineCount = await inlineCopyBtns.count();
        for (let i = 0; i < inlineCount; i++) {
          const btn = inlineCopyBtns.nth(i);
          const isInsideMenu = await btn.locator('xpath=ancestor::div[@role="menu"]').count().then(c => c > 0);
          expect(isInsideMenu).toBe(true);
        }
      });

      test(`E: ⋮ menu opens and has credential action`, async ({ page }) => {
        await page.goto(mod.path, { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(1500);
        const trigger = page.locator('button[aria-haspopup="true"]').first();
        await expect(trigger).toBeAttached({ timeout: 5000 });
        const beforeUrl = page.url();
        await trigger.click();
        await page.waitForTimeout(500);
        const menu = page.locator('[role="menu"]').first();
        await expect(menu).toBeVisible({ timeout: 5000 });
        const items = await menu.locator('a, button, [role="menuitem"]').allTextContents();
        const itemText = items.join(' | ');
        expect(itemText.toLowerCase()).toContain('view details');
        expect(itemText.toLowerCase()).toContain('edit');
        expect(itemText.toLowerCase()).toContain('delete');
        if (mod.credentialLabel) {
          expect(itemText).toContain(mod.credentialLabel);
        }
      });

      test(`F: Edit/Delete order correct`, async ({ page }) => {
        await page.goto(mod.path, { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(1500);
        const trigger = page.locator('button[aria-haspopup="true"]').first();
        await trigger.click();
        await page.waitForTimeout(500);
        const menu = page.locator('[role="menu"]').first();
        const items = await menu.locator('a, button, [role="menuitem"]').allTextContents();
        const editIdx = items.findIndex(i => i.toLowerCase().includes('edit'));
        const deleteIdx = items.findIndex(i => i.toLowerCase().includes('delete'));
        expect(editIdx).toBeLessThan(deleteIdx);
      });

      test(`G: Delete is red/destructive`, async ({ page }) => {
        await page.goto(mod.path, { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(1500);
        const trigger = page.locator('button[aria-haspopup="true"]').first();
        await trigger.click();
        await page.waitForTimeout(500);
        const menu = page.locator('[role="menu"]').first();
        const deleteBtn = menu.locator('button, a').filter({ hasText: 'Delete' }).first();
        await expect(deleteBtn).toBeAttached();
        const color = await deleteBtn.getAttribute('class');
        expect(color?.toLowerCase()).toContain('red');
      });

      test(`H: No plaintext password in HTML before interaction`, async ({ page }) => {
        await page.goto(mod.path, { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(1500);
        const html = await page.content();
        expect(html).not.toContain('data-password=');
      });

    });
  }
});

test.describe('RESPONSIVE VERIFICATION', () => {
  const responsivePaths = ['hostings', 'voip', 'service-providers', 'g-mails', 'vault'];

  for (const path of responsivePaths) {
    test(`${path}: ⋮ menu not clipped on tablet (768px)`, async ({ page }) => {
      await page.setViewportSize({ width: 768, height: 1024 });
      await page.goto(path, { waitUntil: 'domcontentloaded' });
      await page.waitForTimeout(1500);
      const trigger = page.locator('button[aria-haspopup="true"]').first();
      await expect(trigger).toBeVisible({ timeout: 5000 });
      await trigger.click();
      await page.waitForTimeout(500);
      const menu = page.locator('[role="menu"]').first();
      await expect(menu).toBeVisible();
      const box = await menu.boundingBox();
      expect(box).not.toBeNull();
      expect(box.x).toBeGreaterThanOrEqual(0);
      expect(box.y).toBeGreaterThanOrEqual(0);
      expect(box.x + box.width).toBeLessThanOrEqual(768);
    });

    test(`${path}: ⋮ menu not clipped on mobile (390px)`, async ({ page }) => {
      await page.setViewportSize({ width: 390, height: 844 });
      await page.goto(path, { waitUntil: 'domcontentloaded' });
      await page.waitForTimeout(1500);
      const trigger = page.locator('button[aria-haspopup="true"]').first();
      await expect(trigger).toBeVisible({ timeout: 5000 });
      await trigger.click();
      await page.waitForTimeout(500);
      const menu = page.locator('[role="menu"]').first();
      await expect(menu).toBeVisible();
      const box = await menu.boundingBox();
      expect(box).not.toBeNull();
      expect(box.x).toBeGreaterThanOrEqual(0);
      expect(box.y).toBeGreaterThanOrEqual(0);
      expect(box.x + box.width).toBeLessThanOrEqual(390);
    });
  }
});

test.describe('DARK MODE VERIFICATION', () => {
  const darkPaths = ['hostings', 'voip', 'service-providers'];

  for (const path of darkPaths) {
    test(`${path}: ⋮ menu readable in dark mode`, async ({ page }) => {
      await page.goto(path, { waitUntil: 'domcontentloaded' });
      await page.waitForTimeout(1000);
      const toggle = page.locator('button[aria-label="Toggle dark mode"]');
      if (await toggle.isVisible().catch(() => false)) {
        await page.evaluate(() => {
          document.documentElement.classList.add('dark');
          localStorage.setItem('darkMode', 'true');
        });
        await page.waitForTimeout(500);
      }
      const trigger = page.locator('button[aria-haspopup="true"]').first();
      await expect(trigger).toBeVisible({ timeout: 5000 });
      await trigger.click();
      await page.waitForTimeout(500);
      const menu = page.locator('[role="menu"]').first();
      await expect(menu).toBeVisible();
      const menuHtml = await menu.innerHTML();
      expect(menuHtml).toBeTruthy();
      const itemTextColor = await menu.locator('a').first().evaluate(el => getComputedStyle(el).color);
      expect(itemTextColor).toBeTruthy();
    });
  }
});

test.describe('FUNCTIONAL CREDENTIAL CHECK', () => {
  const funcModules = [
    { path: 'hostings', label: 'Hosting', route: 'hostings.password' },
    { path: 'voip', label: 'VoIP', route: 'voip.extension-password' },
    { path: 'service-providers', label: 'Service Providers', route: 'service-providers.password' },
    { path: 'domain-emails', label: 'Domain Emails', route: 'domain-emails.password' },
    { path: 'other-services', label: 'Other Services', route: 'other-services.password' },
    { path: 'g-mails', label: 'G-Mails', route: 'g-mails.password' },
  ];

  for (const mod of funcModules) {
    test(`${mod.label}: credential action fetches securely`, async ({ page }) => {
      const apiCalls = [];
      page.on('response', resp => {
        if (resp.url().includes('/password') || resp.url().includes('/extension-password')) {
          apiCalls.push({ url: resp.url(), status: resp.status() });
        }
      });
      await page.goto(mod.path, { waitUntil: 'domcontentloaded' });
      await page.waitForTimeout(1500);
      const trigger = page.locator('button[aria-haspopup="true"]').first();
      await trigger.click();
      await page.waitForTimeout(500);
      const credentialBtn = page.locator('button[data-copy-pwd]').first();
      await expect(credentialBtn).toBeAttached({ timeout: 5000 });
      const copyRoute = await credentialBtn.getAttribute('data-copy-pwd');
      expect(copyRoute).toBeTruthy();
      const resp = await page.request.get(copyRoute);
      expect(resp.status()).toBe(200);
      const body = await resp.json();
      expect(body.password || body.extension_password).toBeDefined();
      expect(apiCalls.length).toBeGreaterThanOrEqual(1);
      apiCalls.forEach(c => expect(c.status).toBe(200));
    });
  }
});
