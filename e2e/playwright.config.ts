import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
    testDir: '.',
    testMatch: '*.spec.ts',
    fullyParallel: false,
    workers: 1,
    retries: 1,
    use: {
        baseURL: 'http://localhost:8000',
        trace: 'on-first-retry',
    },
    webServer: {
        command: 'powershell -ExecutionPolicy Bypass -File e2e/start-server.ps1',
        url: 'http://localhost:8000',
        cwd: '..',
        reuseExistingServer: true,
    },
    projects: [
        { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
    ],
});
