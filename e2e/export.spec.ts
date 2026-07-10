import { test, expect } from '@playwright/test';

test.describe('Export', () => {

    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.fill('input[name="email"]', 'admin@tyro.project');
        await page.fill('input[name="password"]', 'tyro');
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/\/dashboard/);
    });

    test('export page loads with type selector', async ({ page }) => {
        await page.goto('/export');
        await expect(page.locator('h1')).toContainText(/export/i);
        await expect(page.locator('select[name="type"]')).toBeVisible();
    });
});
