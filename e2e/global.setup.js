import { chromium } from '@playwright/test';

export default async function globalSetup() {
  const browser = await chromium.launch();
  const page = await browser.newPage();
  await page.goto('http://localhost/unknow/public/login', { waitUntil: 'domcontentloaded' });
  await page.fill('input[name="email"]', 'admin@tyro.project');
  await page.fill('input[name="password"]', 'password');
  await page.click('button[type="submit"]');
  await page.waitForURL('**/dashboard', { timeout: 15000 });
  await page.context().storageState({ path: 'e2e/auth-state.json' });
  await browser.close();
}
