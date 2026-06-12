import { test, expect } from '@playwright/test';

test.describe('Dashboard', () => {

    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.fill('input[name="email"]', 'admin@tyro.project');
        await page.fill('input[name="password"]', 'tyro');
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/\/dashboard/);
    });

    test('displays stat cards', async ({ page }) => {
        await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
    });

    test('shows tasks chart', async ({ page }) => {
        await expect(page.locator('#tasksStatusChart')).toBeVisible();
    });

    test('shows services chart', async ({ page }) => {
        await expect(page.locator('#servicesTypeChart')).toBeVisible();
    });

    test('shows recent activity', async ({ page }) => {
        await expect(page.getByRole('heading', { name: 'Recent Activity' })).toBeVisible();
    });

    test('sidebar navigation links work', async ({ page }) => {
        const links = ['Features', 'Modules', 'Tasks', 'Domains', 'VPS', 'Users'];
        for (const label of links) {
            await page.getByRole('link', { name: label, exact: true }).first().click();
            await expect(page.locator('h1')).toContainText(label);
            await page.goBack();
        }
    });

});
