import { test, expect } from '@playwright/test';
import { BANNER, chooseLanguage, onScreen, screenshotter, watchForProblems } from './helpers';

/*
 * The shared start page and a "coming soon" page. Screenshots go to
 * docs/screenshots/start/.
 */

for (const lang of ['nl', 'en'] as const) {
    test(`start page in ${lang.toUpperCase()}`, async ({ page }, info) => {
        const problems = await watchForProblems(page);
        const shot = screenshotter(page, 'start', info.project.name, lang);

        await page.goto('/');
        await chooseLanguage(page, lang);
        await onScreen(page, 'start');
        await expect(page.getByText(BANNER[lang])).toBeVisible();
        await expect(page.getByRole('navigation').getByRole('listitem')).toHaveCount(5);
        await shot('start');

        // Module 1 is a link; module 2 is not merged yet.
        await page.getByRole('link', { name: lang === 'nl' ? 'Check door de hulpverlener' : 'Caseworker check' }).click();
        await onScreen(page, 'intro');
        await expect(page).toHaveURL(/\/m\/caseworker$/);

        await page.goto('/m/handover');
        await onScreen(page, 'coming-soon');
        await shot('coming-soon');

        expect(problems, 'CSP violations or script errors').toEqual([]);
    });
}
