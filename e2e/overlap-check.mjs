import { chromium } from 'playwright';
const browser = await chromium.launch({ headless: true });

const viewports = [
  { label: '1920x1080', w: 1920, h: 1080, ss: 'overlap-1920' },
  { label: '1440x900',  w: 1440, h: 900,  ss: 'overlap-1440' },
  { label: '768x1024',  w: 768,  h: 1024, ss: 'overlap-768' },
  { label: '390x844',   w: 390,  h: 844,  ss: 'overlap-390' },
];

for (const vp of viewports) {
  const page = await browser.newPage({ baseURL: 'http://localhost/unknow/public/', viewport: { width: vp.w, height: vp.h } });

  await page.goto('login', { waitUntil: 'domcontentloaded', timeout: 15000 });
  await page.fill('input[name="email"]', 'admin@tyro.project');
  await page.fill('input[name="password"]', 'password');
  await page.click('button[type="submit"]');
  await page.waitForURL('**/dashboard', { timeout: 15000 });
  await page.waitForTimeout(2000);

  // Check stat cards for overlap
  const statCards = await page.evaluate(() => {
    const cards = document.querySelectorAll('.stat-card');
    const results = [];
    for (const card of cards) {
      const label = card.querySelector('p');
      const icon = card.querySelector('svg');
      const value = card.querySelector('.text-2xl') || card.querySelector('[class*="font-bold"]');
      if (!label) continue;
      
      const labelText = label.textContent?.trim() || '?';
      const cardRect = card.getBoundingClientRect();
      const labelRect = label.getBoundingClientRect();
      const valueRect = value?.getBoundingClientRect();

      // Check if label text is clipped or extends outside card
      const labelOverflows = labelRect.right > cardRect.right;
      const valueVisible = valueRect ? (valueRect.bottom <= cardRect.bottom + 5) : true;
      const cardWidth = cardRect.width;

      results.push({
        label: labelText,
        cardW: Math.round(cardRect.width),
        labelOverflows,
        valueVisible,
        hasIcon: !!icon
      });
    }
    return results;
  });

  console.log(`\n--- ${vp.label} ---`);
  let overlapFound = false;
  for (const sc of statCards) {
    if (sc.labelOverflows || !sc.valueVisible) {
      overlapFound = true;
      console.log(`  OVERLAP: "${sc.label}" card=${sc.cardW}px labelOverflows=${sc.labelOverflows}`);
    }
  }
  if (!overlapFound) {
    console.log(`  ${statCards.length} stat cards — NO overlap`);
  }
  statCards.forEach(sc => {
    console.log(`    ${sc.label.padEnd(18)} w=${sc.cardW}px icon=${sc.hasIcon} overflow=${sc.labelOverflows}`);
  });

  // Also check Renewal widget specifically
  const renewalStats = statCards.filter(s => /tracker|manual|auto|failed|today/i.test(s.label));
  if (renewalStats.length > 0) {
    const minCard = Math.min(...renewalStats.map(s => s.cardW));
    console.log(`  Renewal stat cards: ${renewalStats.length}, min width: ${minCard}px`);
    const anyOverlap = renewalStats.some(s => s.labelOverflows);
    console.log(`  Renewal overlap: ${anyOverlap ? 'YES — BUG' : 'NONE — FIXED'}`);
  }

  await page.screenshot({ path: `e2e/screenshots/dashboard/${vp.ss}.png`, fullPage: false });
  await page.close();
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

const darkCards = await darkPage.evaluate(() => {
  const cards = document.querySelectorAll('.stat-card');
  return Array.from(cards).map(c => {
    const p = c.querySelector('p');
    const color = p ? getComputedStyle(p).color : '?';
    return { text: p?.textContent?.trim(), color };
  });
});
console.log('\n--- DARK MODE ---');
console.log(`  ${darkCards.length} stat cards rendered`);
const allReadable = darkCards.every(c => c.color && c.color !== 'rgba(0, 0, 0, 0)');
console.log(`  All readable: ${allReadable}`);

await darkPage.screenshot({ path: 'e2e/screenshots/dashboard/overlap-dark.png' });
console.log('  Screenshot: overlap-dark.png');

await browser.close();
