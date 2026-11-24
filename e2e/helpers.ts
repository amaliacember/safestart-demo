import { expect, type Page } from '@playwright/test';
import { createHash } from 'node:crypto';

/*
 * Shared helpers for every module's Playwright run. Shared file: change it
 * only after telling the group.
 */

/*
 * Herd Studio (a local developer tool) injects scripts into every page Herd
 * serves. It is not part of the app. Here we block its script file, and
 * return the CSP hashes of its inline scripts so only those exact scripts
 * are ignored. Any other CSP violation still fails the test. Without Herd
 * (php artisan serve) this finds nothing and changes nothing.
 */
export async function withoutHerdStudio(page: Page): Promise<string[]> {
    await page.route('**/herd-studio/**', (route) => route.abort());

    const html = await (await page.request.get('/')).text();
    return [...html.matchAll(/<script>(\(function\(\)\{if\(window\.__HERD_EARLY_ERRORS__.*?)<\/script>/gs)]
        .map((m) => 'sha256-' + createHash('sha256').update(m[1]).digest('base64'));
}

/**
 * Collects Content-Security-Policy violations and script errors for the
 * whole test. End your test with:
 *   expect(problems, 'CSP violations or script errors').toEqual([]);
 */
export async function watchForProblems(page: Page): Promise<string[]> {
    const herdHashes = await withoutHerdStudio(page);
    const problems: string[] = [];

    page.on('console', (m) => {
        if (m.type() !== 'error') return;
        const fromHerd = m.location().url.includes('/herd-studio/')
            || herdHashes.some((hash) => m.text().includes(hash));
        if (!fromHerd) problems.push(m.text());
    });
    page.on('pageerror', (e) => problems.push(e.message));

    return problems;
}

/**
 * Saves numbered full-page screenshots to
 * docs/screenshots/<module>/<project>-<lang>-<nn>-<name>.png
 */
export function screenshotter(page: Page, module: string, project: string, lang: string) {
    let n = 0;
    return async (name: string) => {
        await page.waitForLoadState('networkidle');
        await page.screenshot({
            path: `docs/screenshots/${module}/${project}-${lang}-${String(++n).padStart(2, '0')}-${name}.png`,
            fullPage: true,
            animations: 'disabled',
        });
    };
}

/** Waits until the given screen shows (the data-screen attribute of the page). */
export const onScreen = (page: Page, screen: string) =>
    expect(page.locator('[data-screen]')).toHaveAttribute('data-screen', screen);

/** On a module intro or the start page: switch to English if asked. */
export async function chooseLanguage(page: Page, lang: 'nl' | 'en') {
    if (lang === 'en') {
        await page.getByRole('button', { name: 'English' }).click();
        await expect(page.locator('html')).toHaveAttribute('lang', 'en');
    }
}

/** Fills in the session code on a module intro and presses Start. */
export async function startModule(page: Page, lang: 'nl' | 'en', code: string) {
    await page.getByLabel(lang === 'nl' ? 'Sessiecode' : 'Session code').fill(code);
    await page.getByRole('button', { name: lang === 'nl' ? 'Start de demo' : 'Start the demo', exact: true }).click();
}

export const BANNER = {
    nl: 'Demo – fictieve gegevens, er wordt niets bewaard',
    en: 'Demo – made-up data, nothing is kept',
} as const;
