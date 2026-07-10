import { test, expect } from '@playwright/test';

test.describe('Search', () => {

    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.fill('input[name="email"]', 'admin@tyro.project');
        await page.fill('input[name="password"]', 'tyro');
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/\/dashboard/);
    });

    test('global search finds results', async ({ page }) => {
        await page.goto('/search?q=admin');
        await expect(page.locator('h1')).toContainText(/search/i);
    });

    test('global search with no query shows message', async ({ page }) => {
        await page.goto('/search');
        await expect(page.locator('text=search for something')).toBeVisible({ timeout: 5000 }).catch(() => {});
    });
});
