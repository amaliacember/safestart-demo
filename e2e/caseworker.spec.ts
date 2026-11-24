import { test, expect, type Page } from '@playwright/test';
import { readFileSync } from 'node:fs';
import { BANNER, chooseLanguage, onScreen, screenshotter, watchForProblems, withoutHerdStudio } from './helpers';

/*
 * Module 1: plays all three made-up cases start to finish, in Dutch and
 * English, on the mobile (390 px) and desktop (1280 px) projects, and saves a
 * screenshot of every screen to docs/screenshots/caseworker/.
 *
 * It also fails on any Content-Security-Policy violation or JavaScript error.
 */

type Lang = 'nl' | 'en';

// The button texts, as in lang/{nl,en}/caseworker.php and demo.php. Using the real text also
// checks that the labels are there.
const TEXT = {
    nl: {
        start: 'Start de demo', startCase: 'Start casus', toCheck: 'Naar de snelle check',
        showRoute: 'Toon de route', toScan: 'Naar de scan', toAdvice: 'Naar het advies',
        startScan: 'Start de scan', skip: 'Overslaan', toOrder: 'Naar de bestelling',
        order: 'Bestellen (demo – er wordt niets besteld)', closeCase: 'Sluit de casus af',
        back: 'Terug', copy: 'Kopieer resultaten', download: 'Download resultaten (JSON)',
        closeSession: 'Sluit de sessie', copied: 'Gekopieerd. Plak het in de chat.',
        outcomes: { exact: 'juist', extra: 'extra route', missed: 'route gemist' },
    },
    en: {
        start: 'Start the demo', startCase: 'Start case', toCheck: 'Go to the quick check',
        showRoute: 'Show the route', toScan: 'Go to the scan', toAdvice: 'Go to the advice',
        startScan: 'Start the scan', skip: 'Skip', toOrder: 'Go to the order',
        order: 'Order (demo – nothing is ordered)', closeCase: 'Close the case',
        back: 'Back', copy: 'Copy results', download: 'Download results (JSON)',
        closeSession: 'Close the session', copied: 'Copied. Paste it into the chat.',
        outcomes: { exact: 'exact', extra: 'extra route', missed: 'route missed' },
    },
} as const;

// What a careful caseworker answers for each story.
const ANSWERS: Record<'A' | 'B' | 'C', Record<string, 'yes' | 'no' | 'unknown'>> = {
    A: { q1: 'yes', q2: 'yes', q3: 'no', q4: 'no', q5: 'no', q6: 'yes' },
    B: { q1: 'no', q2: 'no', q3: 'yes', q4: 'no', q5: 'no', q6: 'no' },
    C: { q1: 'yes', q2: 'unknown', q3: 'no', q4: 'yes', q5: 'yes', q6: 'no' },
};

async function answer(page: Page, answers: Record<string, string>) {
    for (const [question, value] of Object.entries(answers)) {
        await page.locator(`label:has(input[name="${question}"][value="${value}"])`).click();
    }
}

for (const lang of ['nl', 'en'] as const) {
    test(`all three cases in ${lang.toUpperCase()}`, async ({ page }, info) => {
        const t = TEXT[lang];
        const button = (name: string) => page.getByRole('button', { name, exact: true });
        const problems = await watchForProblems(page);
        const shot = screenshotter(page, 'caseworker', info.project.name, lang);

        // Intro ----------------------------------------------------------
        await page.goto('/m/caseworker');
        await chooseLanguage(page, lang);
        await expect(page.getByText(BANNER[lang])).toBeVisible();
        await shot('intro');

        await button(t.start).click();
        await expect(page.locator('#session-code-error')).toBeVisible();
        await shot('intro-error');

        await page.getByLabel(lang === 'nl' ? 'Sessiecode' : 'Session code').fill('E2E');
        await button(t.start).click();

        // Case A: Sara -- scan finds two apps, package ordered -------------
        await onScreen(page, 'scenario');
        await expect(page.getByText(BANNER[lang])).toBeVisible();
        await shot('A-scenario');
        await button(t.startCase).click();
        await onScreen(page, 'case-number');
        await expect(page.getByRole('heading', { level: 1 })).toBeFocused();
        await shot('A-case-number');

        // One back click, which must show up in the results.
        await button(t.back).click();
        await onScreen(page, 'scenario');
        await page.locator('.btn-primary').click();
        await button(t.toCheck).click();
        await onScreen(page, 'check');

        await button(t.showRoute).click();
        await expect(page.getByRole('alert')).toBeVisible();
        await shot('A-check-errors');
        await answer(page, ANSWERS.A);
        await expect(page.getByRole('alert')).toHaveCount(0);
        await shot('A-check');

        await button(t.showRoute).click();
        await onScreen(page, 'route');
        await shot('A-route');
        await button(t.toScan).click();
        await onScreen(page, 'scan');
        await shot('A-scan-connect');
        await button(t.startScan).click();
        await expect(page.getByRole('progressbar')).toBeVisible();
        await page.waitForTimeout(1000);
        await shot('A-scan-running');
        await expect(button(t.toAdvice)).toBeVisible({ timeout: 8000 });
        await expect(page.getByText('Kalvo Sync')).toBeVisible();
        await shot('A-scan-result');
        await button(t.toAdvice).click();
        await onScreen(page, 'advice');
        await shot('A-advice');
        await button(t.toOrder).click();
        await onScreen(page, 'order');
        await shot('A-order');
        await button(t.order).click();

        // Case B: Lena -- message route only --------------------------------
        await onScreen(page, 'scenario');
        await shot('B-scenario');
        await button(t.startCase).click();
        await button(t.toCheck).click();
        await answer(page, ANSWERS.B);
        await shot('B-check');
        await button(t.showRoute).click();
        await onScreen(page, 'route');
        await expect(button(t.toScan)).toHaveCount(0);
        await shot('B-route');
        await button(t.toAdvice).click();
        await onScreen(page, 'advice');
        await shot('B-advice');
        await button(t.closeCase).click();

        // Case C: Tom -- "don't know" on q2 adds a scan, skipped ------------
        await onScreen(page, 'scenario');
        await shot('C-scenario');
        await button(t.startCase).click();
        await button(t.toCheck).click();
        await answer(page, ANSWERS.C);
        await shot('C-check');
        await button(t.showRoute).click();
        await onScreen(page, 'route');
        await shot('C-route');
        await button(t.toScan).click();
        await button(t.startScan).click();
        await button(t.skip).click();
        await expect(button(t.toAdvice)).toBeVisible();
        await shot('C-scan-result');
        await button(t.toAdvice).click();
        await onScreen(page, 'advice');
        await shot('C-advice');
        await button(t.closeCase).click();

        // Shared results ------------------------------------------------------
        await onScreen(page, 'results');
        await expect(page).toHaveURL(/\/results$/);
        await shot('results');

        const text = await page.locator('#results-text').inputValue();
        expect(text).toContain('E2E');

        const [download] = await Promise.all([
            page.waitForEvent('download'),
            page.getByRole('link', { name: t.download }).click(),
        ]);
        expect(download.suggestedFilename()).toBe('safestart-demo-E2E.json');
        const json = JSON.parse(readFileSync(await download.path(), 'utf8'));

        // The download matches what happened.
        const module = json.modules[0];
        expect(module.module).toBe('caseworker');
        expect(module.all_tasks_finished).toBe(true);
        expect(module.tasks.map((c: any) => c.outcome)).toEqual(['exact', 'exact', 'extra']);
        expect(module.tasks.map((c: any) => c.details.routes)).toEqual([['scan'], ['message'], ['scan', 'shared']]);
        expect(module.tasks.map((c: any) => c.back_clicks)).toEqual([1, 0, 0]);
        expect(module.tasks[0].details.package_ordered).toBe(true);
        expect(module.tasks[0].wait_ms).toBeGreaterThanOrEqual(3000);
        expect(module.tasks[2].wait_ms).toBeLessThan(3000);
        expect(module.total_time_without_wait_ms).toBeLessThan(module.total_time_ms);

        // ...and so does the copy text.
        for (const c of module.tasks) {
            expect(text).toContain(t.outcomes[c.outcome as 'exact' | 'extra' | 'missed']);
        }

        await button(t.copy).click();
        await expect(page.getByText(t.copied)).toBeVisible();

        // Close wipes the session and goes back to the start page.
        await button(t.closeSession).click();
        await onScreen(page, 'start');
        expect((await page.request.get('/results/download')).status()).toBe(404);

        expect(problems, 'CSP violations or script errors').toEqual([]);
    });
}

test('keyboard only: intro to quick check', async ({ page }) => {
    await withoutHerdStudio(page);
    await page.goto('/m/caseworker');

    // Tab to the session code field, type, Enter submits the form.
    const field = page.getByLabel('Sessiecode');
    while (!(await field.evaluate((el) => el === document.activeElement))) {
        await page.keyboard.press('Tab');
    }
    await page.keyboard.type('K1');
    await page.keyboard.press('Enter');
    await onScreen(page, 'scenario');

    // Focus moved to the new heading; Tab reaches "Start casus".
    await page.keyboard.press('Tab');
    await expect(page.getByRole('button', { name: 'Start casus' })).toBeFocused();
    await page.keyboard.press('Enter');
    await onScreen(page, 'case-number');
    await expect(page.getByRole('heading', { level: 1 })).toBeFocused();
});
