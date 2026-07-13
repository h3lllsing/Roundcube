import { chromium } from 'playwright';
const browser = await chromium.launch({ headless: true });
const page = await browser.newPage({ baseURL: 'http://localhost/unknow/public/', viewport: { width: 1920, height: 1080 } });

await page.goto('login', { waitUntil: 'domcontentloaded', timeout: 15000 });
await page.fill('input[name="email"]', 'admin@tyro.project');
await page.fill('input[name="password"]', 'password');
await page.click('button[type="submit"]');
await page.waitForURL('**/dashboard', { timeout: 15000 });
await page.waitForTimeout(2000);

const metrics = await page.evaluate(() => {
  const main = document.querySelector('main#main-content');
  const wrapper = document.querySelector('.fade-in-up');
  const grids = document.querySelectorAll('[class*="grid"][class*="gap"]');

  const results = [];

  // Main content area
  if (main) {
    results.push({
      check: 'Main content width',
      value: main.clientWidth + 'px',
      pass: main.clientWidth > 1500
    });
  }

  // Dashboard wrapper
  if (wrapper) {
    results.push({
      check: 'Dashboard wrapper width',
      value: wrapper.scrollWidth + 'px',
      pass: wrapper.scrollWidth > 1500
    });
  }

  // No max-w-7xl
  results.push({
    check: 'max-w-7xl removed',
    value: document.querySelector('.max-w-7xl') ? 'FOUND' : 'NOT FOUND',
    pass: !document.querySelector('.max-w-7xl')
  });

  // Horizontal overflow
  results.push({
    check: 'Horizontal overflow',
    value: document.documentElement.scrollWidth > window.innerWidth ? 'YES' : 'NO',
    pass: document.documentElement.scrollWidth <= window.innerWidth
  });

  // KPI strip
  const kpiGrid = document.querySelector('[class*="grid-cols-2"][class*="sm:grid-cols-3"]');
  results.push({
    check: 'KPI strip (6-col grid)',
    value: kpiGrid ? kpiGrid.children.length + ' children' : 'NOT FOUND',
    pass: kpiGrid && kpiGrid.children.length >= 4
  });

  // Row 2 — 3-col
  const row2 = document.querySelector('[class*="lg:grid-cols-3"][class*="gap-4"]');
  results.push({
    check: 'Row 2 (3-col: Renewals|Monitoring|Tasks)',
    value: row2 ? row2.children.length + ' widgets' : 'NOT FOUND',
    pass: row2 && row2.children.length >= 2
  });

  // Row 3 — 2-col
  const row3 = document.querySelectorAll('[class*="lg:grid-cols-2"][class*="gap-4"]');
  results.push({
    check: 'Row 3 (2-col: Ops|Assets)',
    value: row3.length >= 1 ? (row3[0].children.length + ' widgets') : 'NOT FOUND',
    pass: row3.length >= 1 && row3[0].children.length >= 1
  });

  // Row 4 — 2-col (Vault|QA)
  results.push({
    check: 'Row 4 (2-col: Vault|QA)',
    value: row3.length >= 2 ? (row3[1].children.length + ' widgets') : row3.length + ' grids found',
    pass: row3.length >= 2 && row3[1].children.length >= 1
  });

  // Right-side whitespace check (allow standard 64px padding: 32px each side from lg:p-8)
  results.push({
    check: 'Content width utilization',
    value: wrapper ? (wrapper.scrollWidth) + '/' + main.clientWidth + 'px (' + Math.round(wrapper.scrollWidth/main.clientWidth*100) + '%)' : 'N/A',
    pass: !wrapper || (main.clientWidth - wrapper.scrollWidth) <= 64
  });

  return results;
});

console.log('=== 1920x1080 VERIFICATION ===\n');
let allPass = true;
for (const m of metrics) {
  const icon = m.pass ? 'PASS' : 'FAIL';
  if (!m.pass) allPass = false;
  console.log(`  ${icon}: ${m.check} — ${m.value}`);
}

console.log(`\nAll checks: ${allPass ? 'PASS' : 'SOME FAILED'}`);

// Take screenshot
await page.screenshot({ path: 'e2e/screenshots/dashboard/desktop-1920.png', fullPage: true });
console.log('Screenshot: e2e/screenshots/dashboard/desktop-1920.png');

await browser.close();
process.exit(allPass ? 0 : 1);
