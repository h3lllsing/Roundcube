import { chromium } from 'playwright';
const browser = await chromium.launch({ headless: true });
const page = await browser.newPage({ baseURL: 'http://localhost/unknow/public/', viewport: { width: 1440, height: 900 } });

await page.goto('login', { waitUntil: 'domcontentloaded', timeout: 15000 });
await page.fill('input[name="email"]', 'admin@tyro.project');
await page.fill('input[name="password"]', 'password');
await page.click('button[type="submit"]');
await page.waitForURL('**/dashboard', { timeout: 15000 });
await page.waitForTimeout(2000);

// Check the main wrapper div is w-full not max-w-7xl
const wrapper = await page.evaluate(() => {
  const w = document.querySelector('.fade-in-up');
  if (!w) return null;
  return { tag: w.tagName, class: w.className, width: w.scrollWidth, parentWidth: w.parentElement?.scrollWidth };
});
console.log('Wrapper:', JSON.stringify(wrapper));

// Check ladning content uses full width
const main = await page.evaluate(() => {
  const m = document.querySelector('main#main-content');
  if (!m) return null;
  return { width: m.scrollWidth, clientW: m.clientWidth };
});
console.log('Main:', JSON.stringify(main));

// Check KPI strip renders with 6 cards
const kpiLabel = await page.evaluate(() => {
  const els = document.querySelectorAll('[class*="rounded-xl"][class*="border"]');
  const kpis = [];
  for (const el of els) {
    const label = el.querySelector('p');
    if (label && /Failed|Offline|Overdue|Active|Reveals|SSL/i.test(label.textContent || '')) {
      kpis.push(label.textContent?.trim() || '');
    }
  }
  return kpis;
});
console.log('KPI cards:', JSON.stringify(kpiLabel));
console.log('KPI count:', kpiLabel.length);

// Check widget section headers
const headers = await page.evaluate(() => {
  const h2s = document.querySelectorAll('h2');
  return Array.from(h2s).map(h => h.textContent?.trim()).filter(Boolean);
});
console.log('Section headers:', JSON.stringify(headers));

// Verify widget counts
console.log('Renewal Summary count:', headers.filter(h => h.includes('Renewal')).length);
console.log('Monitoring count:', headers.filter(h => h.includes('Monitoring')).length);
console.log('Tasks count:', headers.filter(h => h.includes('Tasks')).length);

// Check all grid structures
const gridHtml = await page.evaluate(() => {
  const fade = document.querySelector('.fade-in-up');
  if (!fade) return [];
  const children = Array.from(fade.children);
  const result = [];
  for (const child of children) {
    if (child.tagName === 'DIV' && child.className.includes('grid-cols')) {
      result.push({
        tag: child.tagName,
        class: child.className,
        childCount: child.children.length,
        firstChildTag: child.children[0]?.tagName || '?',
      });
    }
  }
  return result;
});
console.log('Grids:', JSON.stringify(gridHtml, null, 2));

console.log('\nAll cards (includes KPI + widget cards):');
const allCards = await page.evaluate(() => document.querySelectorAll('[class*="rounded-2xl"]').length);
console.log('  Rounded-2xl cards:', allCards);

const allLinks = await page.evaluate(() => {
  return Array.from(document.querySelectorAll('a')).map(a => ({ href: a.getAttribute('href'), text: a.textContent?.trim().substring(0, 60) }));
});
const brokenLinks = allLinks.filter(l => l.href && l.href.startsWith('http') && l.href.includes('localhost') && l.href.includes('/unknow/public/'));
console.log('Internal links checked:', brokenLinks.length);

await browser.close();
