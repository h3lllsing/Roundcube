import { chromium } from 'playwright';
const browser = await chromium.launch({ headless: true });
const page = await browser.newPage({ baseURL: 'http://localhost/unknow/public/' });

await page.goto('login', { waitUntil: 'domcontentloaded', timeout: 15000 });
await page.fill('input[name="email"]', 'admin@tyro.project');
await page.fill('input[name="password"]', 'password');
await page.click('button[type="submit"]');
await page.waitForURL('**/dashboard', { timeout: 15000 });
await page.waitForTimeout(2000);

const html = await page.content();

const hasFailedToday = html.includes('Failed Today');
const hasOffline = html.includes('>Offline<');
const hasOverdueTasks = html.includes('Overdue Tasks');
const hasActiveServices = html.includes('Active Services');
console.log('KPI: FailedToday=' + hasFailedToday + ' Offline=' + hasOffline + ' Overdue=' + hasOverdueTasks + ' ActiveSvc=' + hasActiveServices);

const hasRenewal = html.includes('Renewal Summary');
const hasMonitoring = html.includes('>Monitoring<');
const hasTasks = html.includes('>Tasks<');
const hasVault = html.includes('Vault Summary');
const hasOps = html.includes('Operations Summary');
const hasQA = html.includes('Quick Actions');
const hasAsset = html.includes('Asset Summary');
console.log('Sections: Renewal=' + hasRenewal + ' Monitor=' + hasMonitoring + ' Tasks=' + hasTasks + ' Ops=' + hasOps + ' Asset=' + hasAsset + ' Vault=' + hasVault + ' QA=' + hasQA);

const hasMaxW7xl = html.includes('max-w-7xl mx-auto');
const hasWfull = html.includes('class="w-full fade-in-up"') || html.includes("class='w-full fade-in-up'");
console.log('Layout: max-w-7xl=' + hasMaxW7xl + ' w-full=' + hasWfull);

const chartCount = (html.match(/<canvas /g) || []).length;
console.log('Charts: ' + chartCount);

const has6col = html.includes('lg:grid-cols-6');
const has3col = html.includes('lg:grid-cols-3');
const has2col = html.includes('lg:grid-cols-2');
console.log('Grids: 6col=' + has6col + ' 3col=' + has3col + ' 2col=' + has2col);

const renewalCount = (html.match(/Renewal Summary/g) || []).length;
const monitoringCount = (html.match(/>Monitoring</g) || []).length;
console.log('NoDupes: Renewal=' + renewalCount + ' Monitoring=' + monitoringCount);

const viewAllCount = (html.match(/View All/g) || []).length + (html.match(/View Full Report/g) || []).length;
console.log('ViewAllLinks: ' + viewAllCount);

await browser.close();
