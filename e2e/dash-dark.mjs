import { chromium } from 'playwright';
const b = await chromium.launch({ headless: true });
const p = await b.newPage({ baseURL: 'http://localhost/unknow/public/', viewport: { width: 1440, height: 900 } });

await p.goto('login', { waitUntil: 'domcontentloaded', timeout: 15000 });
await p.fill('input[name="email"]', 'admin@tyro.project');
await p.fill('input[name="password"]', 'password');
await p.click('button[type="submit"]');
await p.waitForURL('**/dashboard', { timeout: 15000 });
await p.waitForTimeout(2000);

await p.evaluate(() => { document.documentElement.classList.add('dark'); localStorage.setItem('darkMode', 'true'); });
await p.waitForTimeout(1000);

const kpiText = await p.evaluate(() => {
  const cards = document.querySelectorAll('[class*="rounded-xl"][class*="border"]');
  const results = [];
  for (const c of cards) {
    const label = c.querySelector('p');
    if (label && /Failed|Offline|Overdue|Active|Reveals|SSL/i.test(label.textContent || '')) {
      const color = getComputedStyle(label).color;
      results.push({ text: label.textContent?.trim(), color });
    }
  }
  return results.slice(0, 6);
});
console.log('Dark KPI:', JSON.stringify(kpiText, null, 2));

const widgetText = await p.evaluate(() => {
  const h2s = document.querySelectorAll('h2');
  return Array.from(h2s).slice(0, 5).map(h => {
    const color = getComputedStyle(h).color;
    return { text: h.textContent?.trim(), color };
  });
});
console.log('Dark headings:', JSON.stringify(widgetText, null, 2));

await b.close();
