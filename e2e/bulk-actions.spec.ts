import { test, expect } from '@playwright/test';

test.describe('Bulk Actions', () => {

    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.fill('input[name="email"]', 'admin@tyro.project');
        await page.fill('input[name="password"]', 'tyro');
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/\/dashboard/);
    });

    test('bulk delete from features page', async ({ page }) => {
        await page.goto('/features');
        const checkboxes = page.locator('input[type="checkbox"][name="selected_items[]"]');
        const count = await checkboxes.count();
        if (count > 0) {
            await checkboxes.first().check();
            await page.selectOption('select[name="bulk_action"]', 'delete');
            page.once('dialog', dialog => dialog.accept());
            await page.locator('button:has-text("Apply")').click();
        }
        await expect(page).toHaveURL(/\/features/);
    });
});
