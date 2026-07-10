import { test, expect } from '@playwright/test';

test.describe('User Management', () => {

    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.fill('input[name="email"]', 'admin@tyro.project');
        await page.fill('input[name="password"]', 'tyro');
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/\/dashboard/);
    });

    test('users index page loads', async ({ page }) => {
        await page.goto('/users');
        await expect(page.locator('h1')).toContainText(/users/i);
    });

    test('user create page loads', async ({ page }) => {
        await page.goto('/users/create');
        await expect(page.locator('h1')).toContainText(/create/i);
        await expect(page.locator('input[name="name"]')).toBeVisible();
        await expect(page.locator('input[name="email"]')).toBeVisible();
    });
});
