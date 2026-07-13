import { chromium } from 'playwright';
import { mkdirSync } from 'fs';
mkdirSync('e2e/screenshots', { recursive: true });

const BASE = 'http://localhost/unknow/public/';

async function main() {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ baseURL: BASE, viewport: { width: 1440, height: 900 } });
  const page = await context.newPage();

  const errors = [];
  page.on('pageerror', e => errors.push('PAGE_ERROR: ' + e.message));
  page.on('console', msg => { if (msg.type() === 'error') errors.push('CONSOLE_ERROR: ' + msg.text()); });

  // Login
  await page.goto('login', { waitUntil: 'domcontentloaded', timeout: 15000 });
  await page.fill('input[name="email"]', 'admin@tyro.project');
  await page.fill('input[name="password"]', 'password');
  await page.click('button[type="submit"]');
  await page.waitForURL('**/dashboard', { timeout: 15000 });

  console.log('Logged in OK');

  const MODULES = [
    { path: 'hostings',          label: 'Hosting',             cred: 'Password' },
    { path: 'vps',               label: 'VPS',                 cred: 'Password' },
    { path: 'voip',              label: 'VoIP',                cred: 'Ext. Password' },
    { path: 'service-providers', label: 'Service Providers',   cred: 'Password' },
    { path: 'domain-emails',     label: 'Domain Emails',       cred: 'Password' },
    { path: 'other-services',    label: 'Other Services',      cred: 'Password' },
    { path: 'g-mails',           label: 'G-Mails',             cred: 'Password' },
    { path: 'vault',             label: 'Vault',               cred: null },
  ];

  // ============ CHECK 1: Page loads ============
  console.log('\n--- CHECK 1: Page loads & basic integrity ---');
  for (const mod of MODULES) {
    await page.goto(mod.path, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(1000);
    const url = page.url();
    const html = await page.content();
    const loaded = url.includes(mod.path);
    const noPwdCol = !(await page.locator('thead th').allTextContents()).some(h => /password|secret/i.test(h));
    const noPlainPwd = !html.includes('data-password=');
    const hasTriggers = (await page.locator('button[aria-haspopup="true"]').count()) > 0;
    const icon = loaded && noPwdCol && noPlainPwd && hasTriggers ? 'OK' : 'ISSUE';
    console.log(`  ${icon} ${mod.label}: loaded=${loaded} noPwdCol=${noPwdCol} noPlainPwd=${noPlainPwd} hasTriggers=${hasTriggers}`);
  }

  // ============ CHECK 2: Menu content for rows WITH and WITHOUT credentials ============
  console.log('\n--- CHECK 2: Menu content — credential vs no-credential rows ---');
  for (const mod of MODULES) {
    await page.goto(mod.path, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(1000);

    const triggers = page.locator('button[aria-haspopup="true"]');
    const triggerCount = await triggers.count();
    let foundCredRow = false;
    let foundNoCredRow = false;
    let allItemsWithCred = [];
    let allItemsWithoutCred = [];

    for (let i = 0; i < Math.min(triggerCount, 5); i++) {
      const trigger = triggers.nth(i);
      await trigger.scrollIntoViewIfNeeded();
      await trigger.click({ timeout: 5000 });
      await page.waitForTimeout(500);
      const menu = page.locator('[role="menu"]').first();
      const items = await menu.locator('a, button, [role="menuitem"]').allTextContents();
      
      const hasCred = mod.cred ? items.some(it => it.includes(mod.cred)) : false;
      const hasDelete = items.some(it => /delete/i.test(it));
      const hasEdit = items.some(it => /edit/i.test(it));
      const hasView = items.some(it => /view details|details|show/i.test(it.toLowerCase()));

      await page.keyboard.press('Escape');
      await page.waitForTimeout(200);

      if (hasCred && !foundCredRow) {
        foundCredRow = true;
        allItemsWithCred = items;
        console.log(`  ${mod.label}[row${i}]: WITH cred — ${items.join(' | ')}`);
      }
      if (!hasCred && !foundNoCredRow) {
        foundNoCredRow = true;
        allItemsWithoutCred = items;
        console.log(`  ${mod.label}[row${i}]: NO cred  — ${items.join(' | ')}`);
      }
      if (foundCredRow && foundNoCredRow) break;
    }

    if (!foundCredRow && mod.cred) {
      console.log(`  ${mod.label}: ⚠ NO row with credential found (all ${triggerCount} rows lack stored password)`);
    }
  }

  // ============ CHECK 3: Functional credential fetch ============
  console.log('\n--- CHECK 3: Functional credential fetch ---');
  for (const mod of MODULES.filter(m => m.cred)) {
    await page.goto(mod.path, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(1000);
    const triggers = page.locator('button[aria-haspopup="true"]');
    const count = await triggers.count();
    let tested = false;

    for (let i = 0; i < Math.min(count, 5); i++) {
      const trigger = triggers.nth(i);
      await trigger.scrollIntoViewIfNeeded();
      await trigger.click({ timeout: 5000 });
      await page.waitForTimeout(500);
      const menu = page.locator('[role="menu"]').first();
      const items = await menu.locator('a, button, [role="menuitem"]').allTextContents();
      const hasCred = items.some(it => it.includes(mod.cred));
      
      if (hasCred) {
        const copyBtn = page.locator('button[data-copy-pwd]').first();
        if ((await copyBtn.count()) > 0) {
          const route = await copyBtn.getAttribute('data-copy-pwd');
          const resp = await page.request.get(route);
          const body = await resp.json();
          const pwd = body.password || body.extension_password;
          console.log(`  ${mod.label}: route=${route} status=${resp.status()} hasPwd=${!!pwd} len=${pwd?.length || 0}`);
          tested = true;
        }
        await page.keyboard.press('Escape');
        await page.waitForTimeout(200);
        break;
      }
      await page.keyboard.press('Escape');
      await page.waitForTimeout(200);
    }
    if (!tested) console.log(`  ${mod.label}: ⚠ Cannot test — no row with credential data found`);
  }

  // ============ CHECK 4: Dark mode ============
  console.log('\n--- CHECK 4: Dark mode ---');
  for (const path of ['hostings', 'voip', 'service-providers']) {
    await page.goto(path, { waitUntil: 'domcontentloaded', timeout: 15000 });
    await page.waitForTimeout(500);
    await page.evaluate(() => { document.documentElement.classList.add('dark'); localStorage.setItem('darkMode', 'true'); });
    await page.waitForTimeout(300);
    const trigger = page.locator('button[aria-haspopup="true"]').first();
    if ((await trigger.count()) > 0) {
      await trigger.click({ timeout: 5000 });
      await page.waitForTimeout(500);
      const menu = page.locator('[role="menu"]').first();
      const mCount = await menu.count();
      const ss = `e2e/screenshots/${path}-dark-mode.png`;
      await page.screenshot({ path: ss });
      console.log(`  ${path}: menu=${mCount} screenshot=${ss}`);
      await page.keyboard.press('Escape');
    }
  }

  // ============ CHECK 5: Responsive (viewport only) ============
  console.log('\n--- CHECK 5: Responsive triggers ---');
  for (const mod of MODULES.slice(0, 3)) {
    for (const [label, vp] of [['768px', 768], ['390px', 390]]) {
      await page.setViewportSize({ width: vp, height: 900 });
      await page.goto(mod.path, { waitUntil: 'domcontentloaded', timeout: 15000 });
      await page.waitForTimeout(1000);
      const tCount = await page.locator('button[aria-haspopup="true"]').count();
      const hasOverflow = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
      console.log(`  ${mod.label} @${label}: triggers=${tCount} overflow=${hasOverflow}`);
    }
  }

  // ============ Report ============
  console.log('\n--- ERRORS ---');
  if (errors.length === 0) console.log('  No page errors, no console errors');
  else for (const e of errors) console.log(`  ${e}`);

  await browser.close();
  console.log('\nDone.');
}

main();
