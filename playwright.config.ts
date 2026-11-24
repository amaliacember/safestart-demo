import { defineConfig } from '@playwright/test';

/*
 * End-to-end runs against the site as Herd serves it.
 * Run with: npm run test:e2e
 * Another URL (for example a herd share link): DEMO_URL=https://... npm run test:e2e
 */
export default defineConfig({
    testDir: './e2e',
    timeout: 120_000,
    // One at a time: the runs share one machine and one Herd.
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
