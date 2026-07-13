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

  const result = await page.evaluate(() => {
    const issues = [];

    // 1. Body overflow
    if (document.body.scrollWidth > document.body.clientWidth + 5) {
      issues.push(`Body overflow: ${document.body.scrollWidth} > ${document.body.clientWidth}`);
    }

    // 2. KPI: find the actual KPI grid (gap-3.mb-6.grid)
    const allGrids = document.querySelectorAll('.mb-6.grid');
    let kpiGrid = null;
    for (const g of allGrids) {
      if (g.querySelector(':scope > div') && g.textContent?.includes('Failed')) {
        kpiGrid = g;
        break;
      }
    }

    if (kpiGrid) {
      const kpiCards = kpiGrid.querySelectorAll(':scope > div');
      const kpiLabels = Array.from(kpiCards).map(c => c.querySelector('p')?.textContent?.trim() || '?');
      const hasSSL = kpiLabels.some(l => /ssl/i.test(l));
      const kpiCount = kpiCards.length;
      if (hasSSL) issues.push('KPI still contains SSL');
      if (kpiCount !== 5) issues.push(`KPI has ${kpiCount} cards (expected 5): [${kpiLabels.join(', ')}]`);

      // Check Failed Today and Offline for emphasis classes
      for (const card of kpiCards) {
        const label = card.querySelector('p')?.textContent?.trim() || '';
        const classAttr = card.getAttribute('class') || '';
        if ((label === 'Failed Today' || label === 'Offline') && !classAttr.includes('ring')) {
          issues.push(`${label} KPI missing ring emphasis`);
        }
      }
    } else {
      issues.push('KPI grid not found');
    }

    // 3. No stat-card text overflow beyond card boundary
    const statCards = document.querySelectorAll('.stat-card');
    for (const card of statCards) {
      const label = card.querySelector('p');
      if (!label) continue;
      const cardR = card.getBoundingClientRect();
      const labelR = label.getBoundingClientRect();
      if (labelR.right > cardR.right + 2) {
        issues.push(`Stat card "${label.textContent?.trim()}": text (R=${Math.round(labelR.right)}) past card (R=${Math.round(cardR.right)})`);
      }
    }

    // 4. Quick Actions grid
    const quickActionsCard = Array.from(document.querySelectorAll('h2')).find(h => h.textContent?.includes('Quick Actions'));
    if (quickActionsCard) {
      const container = quickActionsCard.closest('.rounded-2xl');
      if (container) {
        const grid = container.querySelector('.grid');
        if (grid) {
          const buttons = grid.querySelectorAll(':scope > a');
          if (buttons.length === 0) issues.push('Quick Actions grid empty');
          // Check each button has an icon svg
          for (const btn of buttons) {
            const svg = btn.querySelector('svg');
            if (!svg) issues.push(`Quick Action "${btn.textContent?.trim()}" missing icon`);
          }
        } else {
          issues.push('Quick Actions missing grid layout');
        }
      }
    }

    // 5. Monitoring widget — no SSL references
    const monWidget = Array.from(document.querySelectorAll('h2')).find(h => h.textContent?.includes('Monitoring'));
    if (monWidget) {
      const container = monWidget.closest('.rounded-2xl');
      if (container && container.textContent?.includes('SSL')) {
        issues.push('Monitoring widget still has SSL text');
      }
    }

    return { issues, statCardCount: statCards.length };
  });

  console.log(`\n--- POLISH: ${vp.label} ---`);
  if (result.issues.length === 0) {
    console.log(`  PASS (${result.statCardCount} stat cards)`);
  } else {
    pass = false;
    result.issues.forEach(i => console.log(`  FAIL: ${i}`));
  }

  if (consoleErrors.length > 0) {
    console.log(`  Console errors: ${consoleErrors.length}`);
    consoleErrors.forEach(e => console.log(`    ${e.substring(0, 120)}`));
  }

  await page.screenshot({ path: `e2e/screenshots/dashboard/polish-${vp.label}.png`, fullPage: false });
  await page.close();
}

// DARK MODE
const darkPage = await browser.newPage({ baseURL: 'http://localhost/unknow/public/', viewport: { width: 1440, height: 900 } });
await darkPage.goto('login', { waitUntil: 'domcontentloaded', timeout: 15000 });
await darkPage.fill('input[name="email"]', 'admin@tyro.project');
await darkPage.fill('input[name="password"]', 'password');
await darkPage.click('button[type="submit"]');
await darkPage.waitForURL('**/dashboard', { timeout: 15000 });
await darkPage.evaluate(() => { document.documentElement.classList.add('dark'); localStorage.setItem('darkMode', 'true'); });
await darkPage.waitForTimeout(1500);

const darkResult = await darkPage.evaluate(() => {
  const issues = [];
  const grids = document.querySelectorAll('.mb-6.grid');
  let kpiGrid = null;
  for (const g of grids) {
    const first = g.querySelector(':scope > div p');
    if (first && first.textContent?.includes('Failed')) { kpiGrid = g; break; }
  }
  if (kpiGrid) {
    const cards = kpiGrid.querySelectorAll(':scope > div');
    const labels = Array.from(cards).map(c => c.querySelector('p')?.textContent?.trim() || '?');
    if (labels.some(l => /ssl/i.test(l))) issues.push('Dark: SSL in KPI');
    for (const card of cards) {
      const label = card.querySelector('p')?.textContent?.trim() || '';
      const cls = card.getAttribute('class') || '';
      if ((label === 'Failed Today' || label === 'Offline') && !cls.includes('ring')) {
        issues.push(`Dark: ${label} missing ring`);
      }
    }
  } else {
    issues.push('Dark: KPI not found');
  }
  const body = document.body;
  if (body.scrollWidth > body.clientWidth + 5) issues.push('Dark: body overflow');
  return issues;
});

console.log(`\n--- POLISH: DARK MODE ---`);
if (darkResult.length === 0) {
  console.log('  PASS');
} else {
  pass = false;
  darkResult.forEach(i => console.log(`  FAIL: ${i}`));
}

await darkPage.screenshot({ path: 'e2e/screenshots/dashboard/polish-dark.png' });
await browser.close();

console.log(`\n${'='.repeat(50)}`);
console.log(pass ? 'ALL POLISH CHECKS PASSED' : 'SOME CHECKS FAILED');
process.exit(pass ? 0 : 1);
