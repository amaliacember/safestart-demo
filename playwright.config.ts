import { defineConfig } from '@playwright/test';

/*
 * End-to-end runs against the site running on your laptop.
 * With Herd:            npm run test:e2e
 * With artisan serve:   DEMO_URL=http://127.0.0.1:8000 npm run test:e2e
 * One module only:      npm run test:e2e -- e2e/template.spec.ts
 */
export default defineConfig({
    testDir: './e2e',
    timeout: 120_000,
    // One at a time: the runs share one local server.
    workers: 1,
    reporter: 'list',
    use: {
        baseURL: process.env.DEMO_URL ?? 'http://safestart-demo.test',
        trace: 'retain-on-failure',
        browserName: 'chromium',
    },
    projects: [
        {
            name: 'mobile',
            use: { viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true, deviceScaleFactor: 2 },
        },
        {
            name: 'desktop',
            use: { viewport: { width: 1280, height: 800 } },
        },
    ],
});
