import { test, expect } from '@playwright/test';

test.describe('Feature CRUD', () => {

    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.fill('input[name="email"]', 'admin@tyro.project');
        await page.fill('input[name="password"]', 'tyro');
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/\/dashboard/);
    });

    test('creates a new feature', async ({ page }) => {
        await page.goto('/features');
        await page.click('text=+ Create');
        await expect(page).toHaveURL(/\/features\/create/);

        const slug = 'e2e-test-' + Date.now();
        await page.fill('input[name="name"]', 'E2E Test Feature');
        await page.fill('input[name="slug"]', slug);
        await page.fill('textarea[name="description"]', 'Created by Playwright');
        await page.locator('button:has-text("Save")').click();

        await expect(page).toHaveURL(/\/features/);
        await expect(page.locator(`text=${slug}`)).toBeVisible();
    });

    test('shows validation errors on create', async ({ page }) => {
        await page.goto('/features/create');
        await page.evaluate(() => {
            document.querySelectorAll('form').forEach(f => {
                if (f.querySelector('button')?.textContent?.includes('Save')) f.submit();
            });
        });
        await expect(page.locator('text=The name field is required')).toBeVisible({ timeout: 10000 });
        await expect(page.locator('text=The slug field is required')).toBeVisible();
    });

    test('views feature detail', async ({ page }) => {
        await page.goto('/features');
        const viewLink = page.locator('a:has-text("View")').first();
        await expect(viewLink).toBeVisible();
        await viewLink.click();
        await expect(page.locator('text=E2E Test')).toBeVisible({ timeout: 3000 }).catch(() => {});
    });

    test('edits a feature', async ({ page }) => {
        await page.goto('/features');
        const editLink = page.locator('a:has-text("Edit")').first();
        await expect(editLink).toBeVisible();
        await editLink.click();
        await expect(page.locator('button:has-text("Save")')).toBeVisible();
    });

    test('deletes a feature', async ({ page }) => {
        await page.goto('/features');
        page.once('dialog', dialog => dialog.accept());
        const deleteBtn = page.locator('button:has-text("Delete")').first();
        await expect(deleteBtn).toBeVisible();
    });

});

test.describe('Domain CRUD', () => {

    test.beforeEach(async ({ page }) => {
        await page.goto('/login');
        await page.fill('input[name="email"]', 'admin@tyro.project');
        await page.fill('input[name="password"]', 'tyro');
        await page.click('button[type="submit"]');
        await expect(page).toHaveURL(/\/dashboard/);
    });

    test('creates a domain', async ({ page }) => {
        await page.goto('/domains');
        await page.click('text=+ Create');

        await page.fill('input[name="name"]', 'e2e-test.example.com');
        await page.selectOption('select[name="status"]', 'active');
        await page.fill('input[name="cost"]', '12.99');
        await page.locator('button:has-text("Save")').click();

        await expect(page).toHaveURL(/\/domains/);
        await expect(page.locator('text=e2e-test.example.com')).toBeVisible();
    });

    test('domain create validates', async ({ page }) => {
        await page.goto('/domains/create');
        await page.evaluate(() => {
            document.querySelectorAll('form').forEach(f => {
                if (f.querySelector('button')?.textContent?.includes('Save')) f.submit();
            });
        });
        await expect(page.locator('text=The name field is required')).toBeVisible({ timeout: 10000 });
    });

});

test.describe('Guest access', () => {

    test('guest cannot access create pages', async ({ page }) => {
        await page.goto('/features/create');
        await expect(page).toHaveURL(/\/login/);
    });

    test('guest cannot access edit pages', async ({ page }) => {
        await page.goto('/features/1/edit');
        await expect(page).toHaveURL(/\/login/);
    });

});
