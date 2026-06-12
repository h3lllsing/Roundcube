import { test, expect } from '@playwright/test';

const ADMIN_EMAIL = 'admin@tyro.project';
const ADMIN_PASSWORD = 'tyro';

test.describe('Authentication', () => {

    test('redirects guest to login', async ({ page }) => {
        await page.goto('/dashboard');
        await expect(page).toHaveURL(/\/login/);
    });

    test('shows login page', async ({ page }) => {
        await page.goto('/login');
        await expect(page.getByRole('heading', { name: /sign in/i })).toBeVisible();
    });

    test('logs in with valid credentials', async ({ page }) => {
        await page.goto('/login');
        await page.fill('input[name="email"]', ADMIN_EMAIL);
        await page.fill('input[name="password"]', ADMIN_PASSWORD);
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/\/dashboard/);
        await expect(page.getByRole('heading', { name: 'Dashboard' })).toBeVisible();
    });

    test('shows error with wrong credentials', async ({ page }) => {
        await page.goto('/login');
        await page.fill('input[name="email"]', ADMIN_EMAIL);
        await page.fill('input[name="password"]', 'wrong-password');
        await page.click('button[type="submit"]');
        await expect(page.getByText('The provided credentials')).toBeVisible();
    });

    test('logs out successfully', async ({ page }) => {
        await page.goto('/login');
        await page.fill('input[name="email"]', ADMIN_EMAIL);
        await page.fill('input[name="password"]', ADMIN_PASSWORD);
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/\/dashboard/);

        await page.click('button[title="Sign out"]');
        await expect(page).toHaveURL(/\/login/);
    });

});
