import { test, expect } from '@playwright/test';
import { BANNER, chooseLanguage, onScreen, screenshotter, startModule, watchForProblems } from './helpers';

/*
 * The template module in a real browser, in Dutch and English, on phone and
 * laptop. Copy this file for your module: change the link, the button texts
 * and the screens. Screenshots go to docs/screenshots/template/.
 */

// Button texts, as in lang/{nl,en}/template.php.
const TEXT = {
    nl: { option: 'Afspraak op kantoor', next: 'Volgende', back: 'Terug', finish: 'Rond de module af', outcome: 'zoals verwacht' },
    en: { option: 'Meeting at the office', next: 'Next', back: 'Back', finish: 'Finish the module', outcome: 'as expected' },
} as const;

for (const lang of ['nl', 'en'] as const) {
    test(`template module in ${lang.toUpperCase()}`, async ({ page }, info) => {
        const t = TEXT[lang];
        const button = (name: string) => page.getByRole('button', { name, exact: true });
        const problems = await watchForProblems(page);
        const shot = screenshotter(page, 'template', info.project.name, lang);

        await page.goto('/m/template');
        await chooseLanguage(page, lang);
        await expect(page.getByText(BANNER[lang])).toBeVisible();
        await shot('intro');

        await startModule(page, lang, 'T1');
        await onScreen(page, 'task');
        await expect(page.getByRole('heading', { level: 1 })).toBeFocused();

        // Next without a choice shows the error.
        await button(t.next).click();
        await expect(page.locator('#choice-error')).toBeVisible();
        await shot('task-error');

        await page.getByText(t.option).click();
        await shot('task');
        await button(t.next).click();
        await onScreen(page, 'confirm');
        await shot('confirm');

        // One counted back click, then finish.
        await button(t.back).click();
        await onScreen(page, 'task');
        await button(t.next).click();
        await button(t.finish).click();

        await onScreen(page, 'results');
        await expect(page.locator('#results-text')).toHaveValue(new RegExp(t.outcome));
        await shot('results');

        expect(problems, 'CSP violations or script errors').toEqual([]);
    });
}
