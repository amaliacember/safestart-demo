# Contributing to the SafeStart demo

These are the rules for everyone who works on the demo, whether you write the
code yourself or with a tool. Read [`docs/TEAM.md`](docs/TEAM.md) first: it has
the module list and owners, the module briefs and the task lists. The
[README](README.md) explains how to run the demo and how to make your module
from the template.

## What we are building

A clickable prototype for a student project (Thomas More, course "The Digital
Startup", team DreamTeam). SafeStart is a service for CAW (Flemish social work
organisations): people leaving a controlling relationship get a stalkerware
scan, a clean refurbished phone with new accounts, guided data transfer and an
encrypted evidence vault. The organisation pays; the survivor uses it for free
through a caseworker.

The demo has **five modules, one per team member**. A professional
(caseworker, manager, company IT contact) opens one module during a video call
and does a few made-up tasks. The app times each task, counts back clicks and
records an outcome. The interviewer asks the questions out loud; the app does
not collect answers to questions.

**Everything is fake.** No real scan, no real orders, no real people. Every
screen says so.

Keep the code plain, idiomatic Laravel that anyone in the team can explain.
Write comments that explain *why*, not what the code obviously does.

## Who changes what

You only change **your own module files**:

```
app/Livewire/Modules/<Name>.php                 your Livewire class (extends Module)
config/safestart/<module>.php                   your made-up data
lang/nl/<module>.php, lang/en/<module>.php      all your text
resources/views/modules/<module>/*.blade.php    one Blade file per screen (+ a page frame)
tests/Feature/Modules/<Name>Test.php            start-to-finish Livewire test
e2e/<module>.spec.ts                            Playwright run + screenshots
docs/screenshots/<module>/                      screenshots made by that run
```

Your key and class name are in the module table in `docs/TEAM.md`. Helper
classes that only your module uses go in `app/Support/<Module>/` (see
`app/Support/Caseworker/`).

**Shared files.** Change these only after telling the group; Amalia merges them:
- `app/Livewire/Modules/Module.php` (base class), `app/Livewire/StartPage.php`, `app/Livewire/Results.php`, `app/Livewire/ComingSoon.php`
- `app/Support/SessionLog.php`, `app/Support/Modules.php`, `app/Support/ResultsText.php`
- `app/Http/*` (middleware, download controller), `app/Providers/*`, `bootstrap/app.php`, `routes/web.php`
- `config/safestart/demo.php`, `config/safestart/modules.php`, `lang/{nl,en}/demo.php`
- `resources/views/layouts/*`, `resources/views/components/*`, `resources/views/livewire/*`, `resources/views/modules/intro.blade.php`
- `resources/css/app.css`, `resources/js/app.js`, `e2e/helpers.ts`, `.github/*`, `CONTRIBUTING.md`, `docs/TEAM.md`

If your module needs a shared file to change, propose it to the group instead
of editing it on your module branch.

## How to work

1. Work on your own branch: `module-<nr>-<name>` (for example `module-2-handover`).
2. Start from the template module (`app/Livewire/Modules/Template.php` and its
   files; the README has the steps).
3. Plan before you build: write down your screens, your timed tasks and your
   text (Dutch and English) first. If you use an AI assistant, ask it for a
   plan for your module only and check it against these rules before letting
   it build.
4. Build in this order: config and lang files, then the screens one by one,
   then the feature test, then your Playwright spec.
5. Click through it yourself on a phone and a laptop, in Dutch and English.
6. `php artisan test` green, then push and open a pull request to `main`.
   GitHub runs the tests on it; one other member reviews it.

## Rules for every module

1. **No database, no accounts, no login.** Sessions, cache and queue use
   file/sync drivers.
2. **No third-party anything at runtime:** no analytics, no CDN, no Google
   Fonts, no embeds, no trackers, no new UI libraries. Fonts are self-hosted
   (`@fontsource/*`).
3. **Nothing is stored for the team.** Results live only in the participant's
   Laravel session (`SessionLog`). At the end they copy or download their
   results; "Close" clears the session. No logging of answers to files, no
   forms that send or store data.
4. **No personal data.** The only identifier is a session code (e.g. `C3`) the
   interviewer gives. Never ask for names, emails or real case details. Never
   put the session code in a URL.
5. **Professionals only.** No wording addressed to survivors.
6. **Nothing may look real.** The banner on every screen (it is in the shared
   layout) says "Demo – fictieve gegevens, er wordt niets bewaard" / "Demo –
   made-up data, nothing is kept".
7. **2 to 4 screens per module.** All text in `lang/nl` and `lang/en` (same
   keys); views never contain hard-coded text; data lives in your config file.
8. **Every module ends on the shared results screen** by calling
   `$this->finish()`.
9. **No new packages** (Composer or npm) without asking the group, and never
   debug or dev tools in the app (no Debugbar, no Telescope).

## Stack

- Latest Laravel, no starter kit. Livewire 4 for the screens, in its CSP-safe
  mode: Alpine only through named `Alpine.data()` components in
  `resources/js/app.js`, never inline JavaScript in HTML attributes.
- Tailwind 4 with our own theme tokens in `resources/css/app.css`.
- Tests: Pest (unit and Livewire feature tests) and Playwright (end-to-end
  runs and screenshots).
- Runs locally only, with Herd (`http://safestart-demo.test`) or
  `php artisan serve` (`http://127.0.0.1:8000`). There is no hosting yet.
  GitHub Actions runs `php artisan test` on every pull request and push to
  `main`; nothing is deployed.

## How a module is built

Every module class extends `Module` (`app/Livewire/Modules/Module.php`). It
defines:

- `key()`, `tasks()`, and optionally `outcomes()` (each with a colour: good,
  warn or bad), `targetMinutes()`, `waits()`, `taskLabel()`, `taskDetail()`;
- `afterIntro()` (what happens after "Start"), `renderScreen()` (which view),
  and one public method per button.

It uses the helpers from the base class: `startTask`, `enterScreen`,
`countBack`, `waitStarted` / `waitEnded`, `record`, `setOutcome`,
`finishTask` and `finish()`.

Every action checks that it is on the right screen, so a crafted request
cannot skip ahead. State the browser must not change is marked `#[Locked]`.
The README has a file map and a diagram of one click.

## Design

A calm, serious work tool for social workers, like good public-service or
clinical software. Every module looks the same: use the shared layout,
components and theme tokens. Do not add colours, fonts or UI libraries.

- **Avoid:** purple/blue gradients, glassmorphism, glowing shadows, emoji,
  stock illustrations, hero sections, everything in rounded cards, default
  Tailwind colours.
- **Palette:** warm off-white paper, deep ink green as main colour, amber for
  warnings and the banner. Every colour pair meets WCAG AA contrast.
- **Type:** Source Serif 4 for headings, Atkinson Hyperlegible for body text.
- **Layout:** it feels like a case file: `<x-module-sheet>` with
  `<x-step-indicator>`. One main action per screen, tap targets at least
  44 px, mobile first.
- After building, look at the Playwright screenshots of every screen at
  390 px and 1280 px, and fix anything cramped, misaligned or generic.

## Accessibility

- Everything works with the keyboard, with a visible focus outline.
- Every field has a label; errors are linked to their field.
- `<html lang>` follows the chosen language.
- Motion respects `prefers-reduced-motion`.
- The `h1` of each screen has `id="screen-title" tabindex="-1"`, so focus moves
  to it after a screen change.

## Security (must all pass, for every module)

- The shared `SecurityHeaders` middleware sends a Content-Security-Policy with
  a fresh nonce per request (`default-src 'self'; script-src 'self' 'nonce-…';
  style-src 'self' 'nonce-…'; img-src 'self' data:; font-src 'self';
  connect-src 'self'; object-src 'none'; base-uri 'none'; form-action 'self';
  frame-ancestors 'none'`), plus `X-Content-Type-Options: nosniff`,
  `Referrer-Policy: no-referrer`, a `Permissions-Policy` that denies camera,
  microphone and location, `X-Robots-Tag: noindex, nofollow`, and no
  `X-Powered-By`. No `'unsafe-eval'`.
- So: no inline `style=""` attributes and no inline scripts; the policy blocks
  them. The Playwright runs fail on any policy violation.
- Blade `{{ }}` only, never `{!! !!}` with user input.
- Check every field on the server. Session code: `[A-Za-z0-9-]{1,10}`. Other
  free-text fields as strict as possible (letters, digits, dashes). Use
  `Rule::in(...)` for choices and `Arr::only(...)` for arrays from the browser.
- Every route is rate limited (`throttle:demo`: 120 per minute per session,
  600 per minute per IP), including Livewire's update route.
- Only the routes in `routes/web.php` exist: no welcome page, no `/up`, no
  storage route.
- Proxy headers are trusted only from this machine, and only for protocol,
  host and port (never `X-Forwarded-For`).
- `composer audit` and `npm audit` report 0 vulnerabilities; lockfiles are
  committed; no unused packages.
- `.env` is never committed; `.env.example` holds local values. The settings
  for sharing the demo beyond a laptop are in the README ("Settings for
  sharing"); `php artisan demo:preflight` checks them.

## Done means

- Your module works start to finish on phone and laptop, in Dutch and English,
  and ends on the shared results screen.
- Copy and download results match what happened.
- `php artisan test` is green (GitHub checks it on your pull request), your
  Playwright spec passes, and both audits are clean.
- Screenshots of every screen are in `docs/screenshots/<module>/`.
- A Dutch-speaking (Flemish) teammate checked your Dutch text.
