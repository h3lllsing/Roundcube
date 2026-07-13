import { chromium } from 'playwright';
const browser = await chromium.launch({ headless: true });

const viewports = [
  { label: '1920x1080', w: 1920, h: 1080 },
  { label: '1440x900',  w: 1440, h: 900  },
  { label: '768x1024',  w: 768,  h: 1024 },
  { label: '390x844',   w: 390,  h: 844  },
];

let pass = true;

for (const vp of viewports) {
  const page = await browser.newPage({
    baseURL: 'http://localhost/unknow/public/',
    viewport: { width: vp.w, height: vp.h }
  });

  const consoleErrors = [];
  page.on('console', msg => { if (msg.type() === 'error') consoleErrors.push(msg.text()); });

  await page.goto('login', { waitUntil: 'domcontentloaded', timeout: 15000 });
  await page.fill('input[name="email"]', 'admin@tyro.project');
  await page.fill('input[name="password"]', 'password');
  await page.click('button[type="submit"]');
  await page.waitForURL('**/dashboard', { timeout: 15000 });
  await page.waitForTimeout(2000);

  const results = await page.evaluate(() => {
    const errors = [];

    // 1. KPI strip
    const kpiContainer = document.querySelector('.mb-6');
    if (!kpiContainer) { errors.push('KPI strip not found'); return { errors }; }
    const kpiCards = kpiContainer.querySelectorAll(':scope > div');
    const kpiLabels = Array.from(kpiCards).map(c => c.querySelector('p')?.textContent?.trim() || '?');
    const kpiCount = kpiCards.length;
    const hasSSL = kpiLabels.some(l => /ssl/i.test(l));
    const kpiWidths = Array.from(kpiCards).map(c => Math.round(c.getBoundingClientRect().width));

    if (hasSSL) errors.push('KPI strip still contains SSL card');

    // 2. Monitoring widget — no SSL sections
    const monitoringWidget = Array.from(document.querySelectorAll('h2')).find(h => h.textContent?.includes('Monitoring'));
    if (monitoringWidget) {
      const widget = monitoringWidget.closest('[class*="rounded-2xl"]') || monitoringWidget.closest('.rounded-2xl');
      if (widget) {
        const widgetText = widget.textContent || '';
        if (widgetText.includes('SSL')) errors.push('Monitoring widget still contains SSL references');
      }
    }

    // 3. Horizontal overflow
    const body = document.body;
    const hasOverflow = body.scrollWidth > body.clientWidth;
    if (hasOverflow) errors.push(`Page has horizontal overflow (${body.scrollWidth} > ${body.clientWidth})`);

    // 4. Stat card overlap check
    const statCards = document.querySelectorAll('.stat-card');
    for (const card of statCards) {
      const labelEl = card.querySelector('p');
      const iconContainers = card.querySelectorAll('[class*="rounded-xl"][class*="bg-white"]');
      if (!labelEl) continue;
      const labelRect = labelEl.getBoundingClientRect();
      for (const icon of iconContainers) {
        const iconRect = icon.getBoundingClientRect();
        if (labelRect.right > iconRect.left + 1) {
          const labelText = labelEl.textContent?.trim() || '?';
          errors.push(`Stat card "${labelText}": label (R=${Math.round(labelRect.right)}) past icon (L=${Math.round(iconRect.left)})`);
        }
      }
    }

    return { errors, kpiCount, kpiLabels, kpiWidths, statCardCount: statCards.length };
  });

  console.log(`\n--- FINAL: ${vp.label} ---`);
  if (results.errors.length > 0) {
    pass = false;
    results.errors.forEach(e => console.log(`  FAIL: ${e}`));
  } else {
    console.log(`  PASS`);
  }
  console.log(`  KPI: ${results.kpiCount} cards [${results.kpiLabels?.join(', ')}]`);
  console.log(`  KPI widths: ${JSON.stringify(results.kpiWidths)}`);

  // Check renewal specific
  const renewalCheck = await page.evaluate(() => {
    const renewalCard = Array.from(document.querySelectorAll('h2')).find(h => h.textContent?.includes('Renewal Summary'));
    if (!renewalCard) return { found: false };
    const card = renewalCard.closest('[class*="rounded-2xl"]');
    if (!card) return { found: false };
    const statGrid = card.querySelector('.grid-cols-2');
    if (!statGrid) return { found: false, msg: 'stat grid not found' };
    const sc = statGrid.querySelectorAll('.stat-card');
    const info = [];
    for (const s of sc) {
      const label = s.querySelector('p')?.textContent?.trim() || '?';
      const icon = s.querySelector('[class*="rounded-xl"]:not(:has(p))');
      const r = s.getBoundingClientRect();
      info.push({ label, hasIcon: !!icon, w: Math.round(r.width), h: Math.round(r.height) });
    }
    return { found: true, cards: info, count: info.length };
  });

  if (renewalCheck.found) {
    console.log(`  Renewal stat cards: ${renewalCheck.count} (icons: ${renewalCheck.cards.filter(c => c.hasIcon).length})`);
    renewalCheck.cards.forEach(c => console.log(`    ${c.label.padEnd(18)} w=${c.w}px h=${c.h}px icon=${c.hasIcon}`));
    const allSameH = renewalCheck.cards.every(c => c.h === renewalCheck.cards[0]?.h);
    console.log(`  Consistent height: ${allSameH ? 'YES' : 'NO'}`);
    if (!allSameH) pass = false;
    const anyIcon = renewalCheck.cards.some(c => c.hasIcon);
    console.log(`  Has icons: ${anyIcon}`);
  }

  if (consoleErrors.length > 0) {
    console.log(`  Console errors: ${consoleErrors.length}`);
    consoleErrors.forEach(e => console.log(`    ${e.substring(0, 120)}`));
  }

  await page.screenshot({ path: `e2e/screenshots/dashboard/final-${vp.label}.png`, fullPage: false });
  await page.close();
}

// DARK MODE
const darkPage = await browser.newPage({
  baseURL: 'http://localhost/unknow/public/',
  viewport: { width: 1440, height: 900 }
});
await darkPage.goto('login', { waitUntil: 'domcontentloaded', timeout: 15000 });
await darkPage.fill('input[name="email"]', 'admin@tyro.project');
await darkPage.fill('input[name="password"]', 'password');
await darkPage.click('button[type="submit"]');
await darkPage.waitForURL('**/dashboard', { timeout: 15000 });
await darkPage.evaluate(() => { document.documentElement.classList.add('dark'); localStorage.setItem('darkMode', 'true'); });
await darkPage.waitForTimeout(1500);

const darkResult = await darkPage.evaluate(() => {
  const errors = [];
  const kpi = document.querySelector('.mb-6');
  if (!kpi) { errors.push('KPI strip not found'); return { errors }; }
  const cards = kpi.querySelectorAll(':scope > div');
  const labels = Array.from(cards).map(c => c.querySelector('p')?.textContent?.trim() || '?');
  const hasSSL = labels.some(l => /ssl/i.test(l));
  if (hasSSL) errors.push('Dark: SSL found in KPI');
  const statCards = document.querySelectorAll('.stat-card');
  for (const card of statCards) {
    const labelEl = card.querySelector('p');
    const iconContainers = card.querySelectorAll('[class*="rounded-xl"][class*="bg-white"]');
    if (!labelEl) continue;
    const lr = labelEl.getBoundingClientRect();
    for (const icon of iconContainers) {
      const ir = icon.getBoundingClientRect();
      if (lr.right > ir.left + 1) {
        errors.push(`Dark overlap: ${labelEl.textContent?.trim()}`);
      }
    }
  }
  return { errors, kpiCount: cards.length, kpiLabels: labels };
});
console.log(`\n--- FINAL: DARK MODE 1440 ---`);
if (darkResult.errors.length > 0) {
  pass = false;
  darkResult.errors.forEach(e => console.log(`  FAIL: ${e}`));
} else {
  console.log(`  PASS`);
}
console.log(`  KPI: ${darkResult.kpiCount} cards [${darkResult.kpiLabels?.join(', ')}]`);
await darkPage.screenshot({ path: 'e2e/screenshots/dashboard/final-dark.png' });
await browser.close();

console.log(`\n${'='.repeat(40)}`);
console.log(pass ? 'FINAL DASHBOARD VISUAL CHECK PASSED' : 'SOME CHECKS FAILED');
process.exit(pass ? 0 : 1);
