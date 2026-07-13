import { chromium } from 'playwright';
const browser = await chromium.launch({ headless: true });

const viewports = [
  { label: '1920x1080', w: 1920, h: 1080, ss: 'renewal-1920' },
  { label: '1440x900',  w: 1440, h: 900,  ss: 'renewal-1440' },
  { label: '768x1024',  w: 768,  h: 1024, ss: 'renewal-768' },
  { label: '390x844',   w: 390,  h: 844,  ss: 'renewal-390' },
];

let allPass = true;

for (const vp of viewports) {
  const page = await browser.newPage({
    baseURL: 'http://localhost/unknow/public/',
    viewport: { width: vp.w, height: vp.h }
  });

  await page.goto('login', { waitUntil: 'domcontentloaded', timeout: 15000 });
  await page.fill('input[name="email"]', 'admin@tyro.project');
  await page.fill('input[name="password"]', 'password');
  await page.click('button[type="submit"]');
  await page.waitForURL('**/dashboard', { timeout: 15000 });
  await page.waitForTimeout(2000);

  // Console errors
  const consoleErrors = [];
  page.on('console', msg => { if (msg.type() === 'error') consoleErrors.push(msg.text()); });

  const results = await page.evaluate(() => {
    const renewalCard = document.querySelector('.rounded-2xl');
    if (!renewalCard) return { found: false, msg: 'Renewal card not found' };

    const statGrid = renewalCard.querySelector('.grid-cols-2\\.sm\\:grid-cols-4');
    // Tailwind classes may be rendered differently; try parent approach
    const heading = Array.from(renewalCard.querySelectorAll('h2')).find(h => h.textContent?.includes('Renewal Summary'));
    if (!heading) return { found: false, msg: 'Renewal Summary heading not found' };

    // The stat grid is the next sibling of the heading's parent flex container
    const flexRow = heading.closest('.flex');
    const grid = flexRow?.parentElement?.querySelector('.grid-cols-2');
    if (!grid) return { found: false, msg: 'Stat card grid not found' };

    const statCards = grid.querySelectorAll(':scope > .stat-card');
    if (statCards.length !== 4) return { found: false, msg: `Expected 4 stat cards, got ${statCards.length}` };

    const details = [];
    let heights = [];
    let anyOverlap = false;

    for (const card of statCards) {
      const labelEl = card.querySelector('p');
      const iconContainer = card.querySelector('.rounded-xl.bg-white\\/60');
      const valueEl = card.querySelector('.text-2xl');
      if (!labelEl || !valueEl) continue;

      const labelText = labelEl.textContent?.trim() || '?';
      const cardRect = card.getBoundingClientRect();
      const labelRect = labelEl.getBoundingClientRect();
      const valueRect = valueEl.getBoundingClientRect();

      // Check: label right edge doesn't extend past card right edge
      const labelClip = labelRect.right > cardRect.right + 1;

      // Check: label bottom doesn't exceed card bottom
      const labelBottomClip = labelRect.bottom > cardRect.bottom + 1;

      // Check: label ends before icon starts
      let iconOverlap = false;
      if (iconContainer) {
        const iconRect = iconContainer.getBoundingClientRect();
        iconOverlap = labelRect.right > iconRect.left;
      }

      // Check: value bottom doesn't exceed card bottom
      const valueClip = valueRect.bottom > cardRect.bottom + 1;

      // Height consistency
      heights.push(Math.round(cardRect.height));

      const item = {
        label: labelText,
        value: valueEl.textContent?.trim(),
        cardW: Math.round(cardRect.width),
        cardH: Math.round(cardRect.height),
        labelW: Math.round(labelRect.width),
        labelH: Math.round(labelRect.height),
        labelClip,
        labelBottomClip,
        iconOverlap,
        valueClip,
        hasIcon: !!iconContainer
      };
      details.push(item);
      if (labelClip || labelBottomClip || iconOverlap || valueClip) anyOverlap = true;
    }

    return { found: true, details, heights, anyOverlap };
  });

  console.log(`\n========================================`);
  console.log(`RENEWAL STAT CARDS — ${vp.label}`);
  console.log(`========================================`);

  if (!results.found) {
    console.log(`  NOT FOUND: ${results.msg}`);
    allPass = false;
    continue;
  }

  for (const d of results.details) {
    const issues = [];
    if (d.labelClip) issues.push('label-clipped(R)');
    if (d.labelBottomClip) issues.push('label-clipped(B)');
    if (d.iconOverlap) issues.push(`icon-overlap(label-R=${d.labelW}px ↔ icon)`);
    if (d.valueClip) issues.push('value-clipped');
    const status = issues.length === 0 ? 'OK' : `ISSUE: ${issues.join(', ')}`;
    console.log(`  ${d.label.padEnd(18)} w=${d.cardW}px h=${d.cardH}px labelW=${d.labelW}px icon=${d.hasIcon} ${status}`);
  }

  console.log(`  Heights: ${JSON.stringify(results.heights)}`);
  const allSameH = results.heights.length === 4 && results.heights.every(h => h === results.heights[0]);
  console.log(`  Consistent height: ${allSameH ? 'YES' : 'NO (diff: ' + (Math.max(...results.heights) - Math.min(...results.heights)) + 'px)'}`);
  console.log(`  Overlap: ${results.anyOverlap ? 'YES — BUG' : 'NONE — CLEAN'}`);
  if (results.anyOverlap) allPass = false;

  await page.screenshot({ path: `e2e/screenshots/dashboard/${vp.ss}.png`, fullPage: false });

  if (consoleErrors.length > 0) {
    console.log(`  Console errors: ${consoleErrors.length}`);
    consoleErrors.forEach(e => console.log(`    ${e.substring(0, 120)}`));
  }

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

const darkResults = await darkPage.evaluate(() => {
  const renewalCard = Array.from(document.querySelectorAll('h2')).find(h => h.textContent?.includes('Renewal Summary'));
  if (!renewalCard) return { found: false };
  const grid = renewalCard.closest('.flex')?.parentElement?.querySelector('.grid-cols-2');
  if (!grid) return { found: false };
  const statCards = grid.querySelectorAll(':scope > .stat-card');
  const details = [];
  for (const card of statCards) {
    const labelEl = card.querySelector('p');
    const valueEl = card.querySelector('.text-2xl');
    if (!labelEl || !valueEl) continue;
    const labelText = labelEl.textContent?.trim() || '?';
    const iconContainer = card.querySelector('.rounded-xl');
    const cardRect = card.getBoundingClientRect();
    const labelRect = labelEl.getBoundingClientRect();
    let iconOverlap = false;
    if (iconContainer) {
      const iconRect = iconContainer.getBoundingClientRect();
      iconOverlap = labelRect.right > iconRect.left;
    }
    const color = getComputedStyle(labelEl).color;
    details.push({
      label: labelText,
      w: Math.round(cardRect.width),
      h: Math.round(cardRect.height),
      iconOverlap,
      labelColor: color
    });
  }
  return { found: true, details, count: details.length };
});

console.log(`\n========================================`);
console.log(`RENEWAL STAT CARDS — DARK MODE (1440)`);
console.log(`========================================`);
if (!darkResults.found) {
  console.log('  NOT FOUND');
} else {
  let overlap = false;
  for (const d of darkResults.details) {
    const s = d.iconOverlap ? 'OVERLAP' : 'OK';
    if (d.iconOverlap) overlap = true;
    console.log(`  ${d.label.padEnd(18)} w=${d.w}px h=${d.h}px iconOverlap=${d.iconOverlap} color=${d.labelColor} ${s}`);
  }
  if (overlap) { allPass = false; console.log('  DARK MODE OVERLAP — BUG'); }
}

await darkPage.screenshot({ path: 'e2e/screenshots/dashboard/renewal-dark.png' });
await browser.close();

console.log(`\n${'='.repeat(40)}`);
console.log(allPass ? 'ALL RENEWAL VISUAL CHECKS PASSED' : 'SOME CHECKS FAILED');
process.exit(allPass ? 0 : 1);
