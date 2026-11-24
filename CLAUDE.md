# SafeStart demo (MVP, experiment 3)

## What this is
A clickable prototype for a student project (Thomas More, course "The Digital Startup", team DreamTeam). SafeStart is a service for CAW (Flemish social work organisations): people leaving a controlling relationship get a stalkerware scan, a clean refurbished phone with new accounts, guided data transfer and an encrypted evidence vault. The organisation pays; the survivor uses it for free through a caseworker.

The demo has **five modules, one per team member**. A professional (caseworker, manager, company IT contact) opens one module during a video call and does a few made-up tasks; the app times each task, counts back clicks and records an outcome. The interviewer asks the questions out loud; the app does not collect answers to questions.

**Read `docs/TEAM.md` first.** It has the module list and owners, the module briefs, the rules for every module, the Git workflow and the task lists.

**Everything is fake.** No real scan, no real orders, no real people. Every screen says so.

The team knows Laravel but not TypeScript. Keep the code plain, idiomatic Laravel that anyone can explain to teammates. Comment the *why*, not the obvious.

## Working in a team: who changes what
Each person only changes **their own module files**:
```
app/Livewire/Modules/<Name>.php                 the module's Livewire class (extends Module)
config/safestart/<module>.php                   the module's made-up data
lang/nl/<module>.php, lang/en/<module>.php      all the module's text
resources/views/modules/<module>/*.blade.php    one Blade file per screen (+ a page frame)
tests/Feature/Modules/<Name>Test.php            start-to-finish Livewire test
e2e/<module>.spec.ts                            Playwright run + screenshots
docs/screenshots/<module>/                      screenshots made by that run
```
Module-specific helper classes go in `app/Support/<Module>/` (see `app/Support/Caseworker/`).

**Shared files.** Change these only after telling the group; Amalia merges them:
- `app/Livewire/Modules/Module.php` (base class), `app/Livewire/StartPage.php`, `app/Livewire/Results.php`, `app/Livewire/ComingSoon.php`
- `app/Support/SessionLog.php`, `app/Support/Modules.php`, `app/Support/ResultsText.php`
- `app/Http/*` (middleware, download controller), `app/Providers/*`, `bootstrap/app.php`, `routes/web.php`
- `config/safestart/demo.php`, `config/safestart/modules.php`, `lang/{nl,en}/demo.php`
- `resources/views/layouts/*`, `resources/views/components/*`, `resources/views/livewire/*`, `resources/views/modules/intro.blade.php`
- `resources/css/app.css`, `resources/js/app.js`, `e2e/helpers.ts`, `.github/*`, `CLAUDE.md`, `docs/TEAM.md`

If a module needs something shared to change, say so and propose it, don't edit it inside a module branch.

When asked to build a module: work only on that module's branch (`module-<nr>-<name>`), start from the template (`app/Livewire/Modules/Template.php` and its files), propose a plan first and wait for approval.

## Hard rules
1. **No database, no accounts, no auth.** Sessions, cache and queue use file/sync drivers.
2. **No third-party anything at runtime:** no analytics, no CDN, no Google Fonts, no embeds, no trackers, no new UI libraries. Fonts are self-hosted (`@fontsource/*`).
3. **Nothing is stored for the team.** Results live only in the participant's Laravel session (`SessionLog`). At the end they copy or download their results; "Close" clears the session. No logging of answers to files, no forms that send or store data.
4. **No personal data.** The only identifier is a session code (e.g. `C3`) the interviewer gives. Never ask for names, emails or real case details. Never put the session code in a URL.
5. **The audience is professionals.** No wording addressed to survivors.
6. Must not suggest anything works for real. Banner on every screen (it is in the shared layout): "Demo – fictieve gegevens, er wordt niets bewaard" / "Demo – made-up data, nothing is kept".
7. **2 to 4 screens per module.** All text in `lang/nl` and `lang/en` (same keys); views never contain hard-coded text; data lives in the module's config file.
8. **Every module ends on the shared results screen** by calling `$this->finish()`.

## Stack (TALL)
- Latest Laravel, no starter kit. Livewire 4 for the screens (CSP-safe mode, so Alpine only through named `Alpine.data()` components in `resources/js/app.js`), Tailwind 4 with our own theme tokens.
- Tests: Pest (unit + Livewire feature tests) and Playwright for end-to-end runs and screenshots.
- Runs locally only, in Herd (`http://safestart-demo.test`) or with `php artisan serve` (`http://127.0.0.1:8000`). There is no hosting yet. GitHub Actions runs `php artisan test` on every pull request and push to `main`; nothing is deployed.
- No debug or dev packages in the app (no Debugbar, no Telescope).

## Architecture
```
app/
  Livewire/StartPage.php            GET /            language + the five modules
  Livewire/ComingSoon.php           GET /m/<key>     for a module that is not merged yet
  Livewire/Results.php              GET /results     shared results, copy, download link, close
  Livewire/Modules/Module.php       base class: shared intro, state in the session, timing helpers, finish()
  Livewire/Modules/CaseworkerCheck.php   module 1
  Livewire/Modules/Template.php     the example module to copy (GET /m/template, not on the start page)
  Support/SessionLog.php            session code + per module, per task: time, screens, back clicks, wait time, outcome, details
  Support/Modules.php               reads config/safestart/modules.php; a module is live once its class exists
  Support/ResultsText.php           plain text for "Copy results"
  Support/Caseworker/               module 1's Triage and ScanSimulator
  Http/Controllers/ResultsDownloadController.php   GET /results/download (JSON)
  Http/Middleware/SecurityHeaders.php, SetLocale.php
config/safestart/
  demo.php                          locales, session code pattern
  modules.php                       the five modules (+ template): key, number, class
  <module>.php                      each module's data
lang/{nl,en}/
  demo.php                          shared text; <module>.php for each module
resources/views/
  layouts/app.blade.php             page around everything (banner, fonts, scripts)
  components/                       banner, step indicator, module sheet, actions bar, tri-choice, language toggle
  livewire/                         start page, coming soon, results
  modules/intro.blade.php           the shared intro
  modules/<module>/                 each module's screens
routes/web.php                      /, /m/<key> for every module, /results, /results/download. Nothing else.
```
A module defines `key()`, `tasks()`, optionally `outcomes()` (with a colour: good/warn/bad), `targetMinutes()`, `waits()`, `taskLabel()`, `taskDetail()`; then `afterIntro()`, `renderScreen()` and one public method per button. It uses `startTask`, `enterScreen`, `countBack`, `waitStarted/waitEnded`, `record`, `setOutcome`, `finishTask` and `finish()` from the base class. Every action checks it is on the right screen; state that the browser must not change is `#[Locked]`.

## Design direction
A calm, serious work tool for social workers, like good public-service or clinical software. Same look in every module: use the shared layout, components and theme tokens. Do not add colours, fonts or UI libraries.
- **Avoid:** purple/blue gradients, glassmorphism, glowing shadows, emoji, stock illustrations, hero sections, everything in rounded cards, default Tailwind colours.
- **Palette** (tokens in `resources/css/app.css`): warm off-white paper, deep ink green as main colour, amber for warnings and the banner. WCAG AA contrast for every pair.
- **Type:** Source Serif 4 for headings, Atkinson Hyperlegible for body. Self-hosted.
- **Layout:** feels like a case file (`<x-module-sheet>` with `<x-step-indicator>`). One main action per screen, tap targets min 44 px, mobile first.
- **Accessibility:** keyboard use, visible focus, labels, `<html lang>` follows the language, respects `prefers-reduced-motion`. The `h1` of each screen has `id="screen-title" tabindex="-1"` so focus moves to it after a screen change.
- No inline `style=""` attributes and no inline scripts: the Content-Security-Policy blocks them.
- After building, screenshot every screen with Playwright at 390 px and 1280 px, look at them, and fix anything generic, cramped or misaligned.

## Security checklist (must all pass, for every module)
- `SecurityHeaders` middleware (shared): Content-Security-Policy with a per-request nonce (`default-src 'self'; script-src 'self' 'nonce-…'; style-src 'self' 'nonce-…'; img-src 'self' data:; font-src 'self'; connect-src 'self'; object-src 'none'; base-uri 'none'; form-action 'self'; frame-ancestors 'none'`), `X-Content-Type-Options: nosniff`, `Referrer-Policy: no-referrer`, `Permissions-Policy` denying camera/mic/geolocation, `X-Robots-Tag: noindex, nofollow`, no `X-Powered-By`. Livewire runs in CSP-safe mode, so no `'unsafe-eval'`. Playwright runs fail on any CSP violation.
- Blade `{{ }}` only, never `{!! !!}` with user input. Validate every field on the server: session code `[A-Za-z0-9-]{1,10}`, other free-text fields as strict as possible (letters, digits, dashes). Use `Rule::in(...)` for choices and `Arr::only` for arrays from the browser.
- Every route is rate limited (`throttle:demo`: 120/min per session, 600/min per IP), including Livewire's update route.
- Only the routes listed above exist; no welcome page, no `/up`, no storage route.
- Proxy headers are trusted only from loopback, and only protocol/host/port (never `X-Forwarded-For`).
- `composer audit` and `npm audit` report 0 vulnerabilities; lockfiles committed; no unused packages. Ask before adding any package.
- Settings for sharing outside a laptop (not used now): `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_DRIVER=file`, `CACHE_STORE=file`, `QUEUE_CONNECTION=sync`, `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true`, `SESSION_HTTP_ONLY=true`, `SESSION_SAME_SITE=strict`, `LOG_LEVEL=warning`, assets from `npm run build`. `php artisan demo:preflight` checks all of these. `.env` is never committed; `.env.example` holds local values.

## Definition of done (per module)
- The module works start to finish on phone and laptop, in Dutch and English, and ends on the shared results screen.
- Copy and download results match what happened.
- `php artisan test` green (CI checks it on the pull request), your Playwright spec passes, both audits clean.
- Screenshots of every screen in `docs/screenshots/<module>/`.
- Dutch text checked by a Dutch-speaking (Flemish) teammate.
