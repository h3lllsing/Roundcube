import { chromium } from 'playwright';
import { mkdirSync } from 'fs';
mkdirSync('e2e/screenshots/dashboard', { recursive: true });

const BASE = 'http://localhost/unknow/public/';
const SS = 'e2e/screenshots/dashboard';

async function main() {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ baseURL: BASE });

  const errors = [];
  page.on('pageerror', e => errors.push('PAGE_ERROR: ' + e.message));
  page.on('console', msg => { if (msg.type() === 'error') errors.push('CONSOLE_ERROR: ' + msg.text()); });
  page.on('response', resp => { if (resp.status() >= 500) errors.push(`HTTP_${resp.status()}: ${resp.url()}`); });

  // Login
  await page.goto('login', { waitUntil: 'domcontentloaded', timeout: 15000 });
  await page.fill('input[name="email"]', 'admin@tyro.project');
  await page.fill('input[name="password"]', 'password');
  await page.click('button[type="submit"]');
  await page.waitForURL('**/dashboard', { timeout: 15000 });
  await page.waitForTimeout(2000);

  console.log('=== DASHBOARD UX REDESIGN VERIFICATION ===\n');

  // ======== DESKTOP 1440x900 ========
  console.log('--- DESKTOP (1440x900) ---');
  await page.setViewportSize({ width: 1440, height: 900 });
  await page.goto('dashboard', { waitUntil: 'domcontentloaded', timeout: 15000 });
  await page.waitForTimeout(2000);

  await page.screenshot({ path: `${SS}/desktop-full.png`, fullPage: true });
  console.log('  Screenshot: desktop-full.png');

  // Check no max-w-7xl constraint
  const container = page.locator('.fade-in-up').first();
  const containerBox = await container.boundingBox();
  console.log(`  Content width: ${containerBox?.width.toFixed(0)}px (should be ~1376px at 1440px viewport)`);
  const noConstriction = containerBox && containerBox.width > 1200;
  console.log(`  Full width used: ${noConstriction ? 'YES' : 'NO'}`);

  // Check KPI strip exists
  const kpiCards = page.locator('.grid-cols-2\\.sm\\:grid-cols-3\\.lg\\:grid-cols-6 > div');
  let kpiCount = 0;
  try { kpiCount = await kpiCards.count(); } catch (e) {}
  console.log(`  KPI cards: ${kpiCount}`);

  // Check 3-column row exists
  const row2Grid = page.locator('.grid-cols-1\\.lg\\:grid-cols-3').first();
  const row2Count = await row2Grid.locator('> .rounded-2xl, > [class*="rounded-2xl"]').count();
  console.log(`  Row 2 (Renewals|Monitoring|Tasks): ${row2Count} cards`);

  // Check 2-column row exists
  const row3Grid = page.locator('.grid-cols-1\\.lg\\:grid-cols-2').first();
  const row3Count = await row3Grid.locator('> .rounded-2xl, > [class*="rounded-2xl"]').count();
  console.log(`  Row 3 (Ops|Assets): ${row3Count} cards`);

  // Check page header
  const h1 = await page.locator('h1').first().textContent();
  console.log(`  Title: ${h1}`);

  // Check all existing links still work
  const viewAllLinks = await page.locator('a:has-text("View All"), a:has-text("View Full Report")').count();
  console.log(`  View All / Report links: ${viewAllLinks}`);

  // Check charts render
  const canvases = await page.locator('canvas').count();
  console.log(`  Chart canvases: ${canvases}`);

  // ======== TABLET 768x1024 ========
  console.log('\n--- TABLET (768x1024) ---');
  await page.setViewportSize({ width: 768, height: 1024 });
  await page.goto('dashboard', { waitUntil: 'domcontentloaded', timeout: 15000 });
  await page.waitForTimeout(2000);
  await page.screenshot({ path: `${SS}/tablet-full.png`, fullPage: true });
  console.log('  Screenshot: tablet-full.png');

  const hasOverflow768 = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
  console.log(`  Horizontal overflow: ${hasOverflow768}`);

  const cardsVisible768 = await page.locator('.rounded-xl').count();
  console.log(`  Cards visible: ${cardsVisible768}`);

  // ======== MOBILE 390x844 ========
  console.log('\n--- MOBILE (390x844) ---');
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('dashboard', { waitUntil: 'domcontentloaded', timeout: 15000 });
  await page.waitForTimeout(2000);
  await page.screenshot({ path: `${SS}/mobile-full.png`, fullPage: true });
  console.log('  Screenshot: mobile-full.png');

  const hasOverflow390 = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth);
  console.log(`  Horizontal overflow: ${hasOverflow390}`);

  const cardsVisible390 = await page.locator('.rounded-xl').count();
  console.log(`  Cards visible: ${cardsVisible390}`);

  // ======== DARK MODE ========
  console.log('\n--- DARK MODE (1440x900) ---');
  await page.setViewportSize({ width: 1440, height: 900 });
  await page.goto('dashboard', { waitUntil: 'domcontentloaded', timeout: 15000 });
  await page.evaluate(() => { document.documentElement.classList.add('dark'); localStorage.setItem('darkMode', 'true'); });
  await page.waitForTimeout(1500);
  await page.screenshot({ path: `${SS}/desktop-dark.png`, fullPage: true });
  console.log('  Screenshot: desktop-dark.png');

  const canvasDark = await page.locator('canvas').count();
  console.log(`  Chart canvases in dark: ${canvasDark}`);

  const kpiDark = page.locator('.rounded-xl.border').first();
  const kpiDarkColor = await kpiDark.evaluate(el => getComputedStyle(el).borderColor);
  console.log(`  KPI card border renders: ${kpiDarkColor.length > 0}`);

  // ======== ERROR SUMMARY ========
  console.log('\n--- ERRORS ---');
  if (errors.length === 0) console.log('  None');
  else for (const e of errors) console.log(`  ${e}`);

  await browser.close();

  console.log('\n=== VERIFICATION COMPLETE ===');
}

main();
