import { chromium } from 'playwright';
const browser = await chromium.launch({ headless: true });

const viewports = [
  { label: '1920x1080', w: 1920, h: 1080 },
  { label: '1440x900',  w: 1440, h: 900  },
  { label: '768x1024',  w: 768,  h: 1024 },
  { label: '390x844',   w: 390,  h: 844  },
];

for (const vp of viewports) {
  const page = await browser.newPage({ baseURL: 'http://localhost/unknow/public/', viewport: { width: vp.w, height: vp.h } });
  await page.goto('login', { waitUntil: 'domcontentloaded', timeout: 15000 });
  await page.fill('input[name="email"]', 'admin@tyro.project');
  await page.fill('input[name="password"]', 'password');
  await page.click('button[type="submit"]');
  await page.waitForURL('**/dashboard', { timeout: 15000 });
  await page.waitForTimeout(2000);

  const kpiInfo = await page.evaluate(() => {
    const kpi = document.querySelector('.mb-6.grid');
    if (!kpi) return { found: false };
    const cards = kpi.querySelectorAll(':scope > div');
    const results = [];
    for (const card of cards) {
      const label = card.querySelector('p')?.textContent?.trim() || '?';
      const value = card.querySelector('p + p')?.textContent?.trim() || '?';
      const rect = card.getBoundingClientRect();
      const container = kpi.getBoundingClientRect();
      const overflows = rect.right > container.right + 2;
      results.push({ label, value, w: Math.round(rect.width), h: Math.round(rect.height), overflows });
    }
    const gridStyle = getComputedStyle(kpi);
    return {
      count: results.length,
      columns: gridStyle.gridTemplateColumns,
      gap: gridStyle.gap,
      cards: results,
      hasSSL: results.some(r => /ssl/i.test(r.label)),
    };
  });

  console.log(`\n--- KPI: ${vp.label} ---`);
  if (!kpiInfo.found) { console.log('  KPI strip not found'); continue; }
  console.log(`  Cards: ${kpiInfo.count}, Columns: ${kpiInfo.columns}, Gap: ${kpiInfo.gap}`);
  console.log(`  Has "SSL" card: ${kpiInfo.hasSSL}`);
  let overflow = false;
  kpiInfo.cards.forEach(c => {
    console.log(`    ${c.label.padEnd(20)} w=${c.w}px h=${c.h}px overflow=${c.overflows}`);
    if (c.overflows) overflow = true;
  });
  console.log(`  Overflow: ${overflow ? 'YES' : 'NONE'}`);
  if (overflow) process.exit(1);
}

// Dark mode
const darkPage = await browser.newPage({ baseURL: 'http://localhost/unknow/public/', viewport: { width: 1440, height: 900 } });
await darkPage.goto('login', { waitUntil: 'domcontentloaded', timeout: 15000 });
await darkPage.fill('input[name="email"]', 'admin@tyro.project');
await darkPage.fill('input[name="password"]', 'password');
await darkPage.click('button[type="submit"]');
await darkPage.waitForURL('**/dashboard', { timeout: 15000 });
await darkPage.evaluate(() => { document.documentElement.classList.add('dark'); localStorage.setItem('darkMode', 'true'); });
await darkPage.waitForTimeout(1500);

const darkKpi = await darkPage.evaluate(() => {
  const kpi = document.querySelector('.mb-6.grid');
  if (!kpi) return null;
  const cards = kpi.querySelectorAll(':scope > div');
  const results = [];
  for (const card of cards) {
    const label = card.querySelector('p')?.textContent?.trim() || '?';
    const bgColor = getComputedStyle(card).background;
    results.push({ label, bg: bgColor.includes('gradient') ? 'gradient' : bgColor.substring(0,40) });
  }
  return { count: results.length, cards: results };
});
console.log(`\n--- KPI DARK ---`);
console.log(`  Cards: ${darkKpi?.count}`);
darkKpi?.cards.forEach(c => console.log(`    ${c.label.padEnd(20)} bg=${c.bg}`));

console.log('\nKPI CHECK PASSED');
await browser.close();
