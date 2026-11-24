# SafeStart caseworker demo (MVP, experiment 3)

## What this is
A clickable prototype for a student project (Thomas More, course "The Digital Startup", team DreamTeam). SafeStart is a service for CAW (Flemish social work organisations): people leaving a controlling relationship get a stalkerware scan, a clean refurbished phone with new accounts, guided data transfer and an encrypted evidence vault. The organisation pays; the survivor uses it for free through a caseworker.

This demo shows the **caseworker side only**. During a video call, a caseworker opens a link on their own device (phone or laptop), works through 3 made-up cases, and the app measures how long each case takes and whether they reach the right route. The interviewer asks follow-up questions out loud; the app does not collect answers to questions.

It tests this hypothesis: *caseworkers will use the SafeStart check in most suspected cases of digital control.* Success: at least 4 of 5 caseworkers finish all 3 cases without help in under 10 minutes total.

**Everything is fake.** No real scan, no real orders, no real people. Every screen says so.

The owner (Amalia) knows Laravel but not TypeScript. Keep the code plain, idiomatic Laravel that she can explain to teammates. Comment the *why*, not the obvious.

## Hard rules
1. **No database, no accounts, no auth.** Sessions, cache and queue use file/sync drivers. Remove anything that needs a database (default sqlite session/cache, users migration, welcome page).
2. **No third-party anything at runtime:** no analytics, no CDN, no Google Fonts, no embeds, no trackers. Fonts self-hosted (`@fontsource/*`, bundled by Laravel's Vite setup).
3. **Nothing is stored for the team.** The session log lives only in the participant's Laravel session. At the end they copy or download their results; then the session is cleared. No logging of answers to files.
4. **No personal data.** The only identifier is a session code (e.g. `C3`) the interviewer gives. Never ask for names, emails or real case details. Never put the session code in the URL.
5. **The audience is professionals.** No wording addressed to survivors.
6. Must not suggest the scan works for real. Banner on every screen: "Demo – fictieve gegevens, er wordt niets bewaard" / "Demo – made-up data, nothing is kept".

## Stack (TALL)
- Latest Laravel, **no starter kit** (no auth scaffolding). Livewire for the screens, Alpine (bundled with Livewire) only for small client-side bits (progress bar, copy button), Tailwind for styling with a **custom theme** (our own colours and fonts; do not use the default palette).
- Tests: Pest (unit + Livewire feature tests) and Playwright for end-to-end runs and screenshots against the local Herd URL.
- Runs locally in Laravel Herd. Shared with participants through `herd share` (Expose tunnel) only during a session. No other hosting.
- No debug or dev packages in the shared build (no Debugbar, no Telescope).

## Architecture
```
app/
  Livewire/CaseDemo.php          one component; state machine: $screen, $scenarioIndex, $answers, $locale
  Support/Triage.php             pure class: answers -> routes + package advice. No Livewire, no session.
  Support/ScanSimulator.php      returns the scripted scan result for a scenario. No randomness.
  Support/SessionLog.php         timestamps per screen, back clicks, chosen routes; builds the summary
  Http/Middleware/SecurityHeaders.php
  Http/Middleware/SetLocale.php
config/
  safestart.php                  the 6 questions (keys), routing rules, 3 scenarios, prices
lang/
  nl/demo.php                    all Dutch text (Flemish Dutch, informal "je")
  en/demo.php                    all English text (same keys)
resources/
  views/livewire/case-demo.blade.php
  views/screens/*.blade.php      one partial per screen: intro, scenario, case-number, check, route, scan, advice, order, results
  views/components/              banner, step indicator, buttons, yes/no/don't-know control
  css/app.css                    Tailwind + theme tokens
routes/web.php                   GET / -> CaseDemo, GET /results/download -> JSON download. Nothing else.
tests/
  Unit/TriageTest.php            every rule, every scenario's expected route
  Unit/SessionLogTest.php
  Feature/CaseDemoTest.php       Livewire::test runs each scenario start to finish
e2e/flow.spec.ts                 Playwright: all 3 scenarios, mobile (390 px) + desktop (1280 px), screenshot every screen
```
Questions, rules, scenarios and prices live in `config/safestart.php`; all text lives in `lang/`. Views never contain hard-coded text, so a teammate can change a question without touching PHP logic.

## Screens and flow
**Intro** – language toggle (NL default, EN), short explanation ("you will handle 3 made-up cases, take your time, think out loud"), session code field, start button. States: made-up data, nothing is kept after you close the page.

Then for each of the 3 scenarios:
1. **Scenario card** – the made-up situation in 3–4 plain sentences. "Start case" starts the timer for this case.
2. **New case** – case number field (pre-filled `DEMO-0417`, editable). Explain: SafeStart only stores a case number, never a name.
3. **Quick check** – the 6 questions, all on one screen, each yes / no / don't know. All required.
4. **Route** – which route(s) apply, one line why each:
   - *Scan advised* (possible spyware)
   - *Harassment by message* (save evidence, block, report)
   - *Shared accounts and location* (separate accounts, change passwords, leave family plans)
   - *No clear digital sign* (note it, check again later)
5. **Scan** (only if scan applies) – "connect the phone" step, progress bar (3–4 s, skippable, Alpine), then the scripted result. Invented app names only, never real stalkerware product names.
6. **Advice** – new phone package advised or not; what the client keeps (photos and contacts move; old accounts do not).
7. **Order package** (only if advised) – €350 per package as part of a €1,800/year subscription (from config), delivery within 5 working days, funded by CAW project subsidies. Button "Order (demo – nothing is ordered)".
8. Case done -> timer stops -> next scenario.

**Results** – per case: time, route matched expected (yes/no), back clicks; total time. "Copy results" (Alpine, clipboard, plain text for the call chat) and "Download results" (JSON `safestart-demo-<code>.json`). "Close" clears the session.

Back button on every case screen (counted). No dead ends.

## Triage rules (`config/safestart.php`)
Questions (caseworker answers about the client):
- q1 Does the ex-partner know things they should not know (where the client was, what they said)?
- q2 Did the ex-partner have the client's phone, PIN or passwords in recent months?
- q3 Does the client get unwanted messages or calls, also from new or unknown accounts?
- q4 Do they share a location app, family plan or phone contract?
- q5 Do they share an Apple ID, Google account or cloud (photos, backups)?
- q6 Has the phone changed: battery drains fast, unknown apps, settings changed by itself?

Rules ("don't know" counts as yes for scan and shared):
- scan: q2 OR q6 OR (q1 AND not q4 AND not q5)
- message: q3
- shared: q4 OR q5
- none: no route above applies
- package advised: scan finds something OR (q2 AND shared)

## Scenarios
Plain wording, no graphic detail.
- **A – "Sara"**: ex knows where she has been, had her phone and PIN before she left, battery drains fast. Expected: scan; scripted result finds 2 suspicious apps; package advised.
- **B – "Lena"**: daily messages from new accounts after blocking the old one; ex never had her phone; no shared accounts. Expected: message only; no scan; no package.
- **C – "Tom"**: still on the ex's family plan, shares a Google account, ex comments on photos and places Tom visited. Expected: shared accounts; package only if the participant marks q2 yes (q2 left unclear in the story on purpose).

## Design direction
Its own look. A calm, serious work tool for social workers, like good public-service or clinical software. It must not look like a generic AI-made site.
- **Avoid:** purple/blue gradients, glassmorphism, glowing shadows, emoji, stock illustrations, hero sections, everything in rounded cards, default Tailwind colours, Inter as the only font.
- **Palette:** warm off-white background, deep ink green or navy as main colour, amber accent for warnings and the demo banner. WCAG AA contrast for every pair. Define as Tailwind theme tokens.
- **Type:** sturdy serif for headings (e.g. Source Serif 4), very legible sans for body (e.g. Atkinson Hyperlegible). Self-hosted.
- **Layout:** feels like a case file. Step indicator ("Case 2 of 3 – step 3 of 6"), whitespace, one main action per screen, tap targets min 44 px. Mobile first.
- **Accessibility:** keyboard use, visible focus, labels, `<html lang>` follows the language, respects `prefers-reduced-motion`.
- Use the frontend-design skill. After building, screenshot every screen with Playwright at 390 px and 1280 px, look at them, and fix anything generic, cramped or misaligned.

## Security checklist (must all pass)
- `.env` for sharing: `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_DRIVER=file`, `CACHE_STORE=file`, `QUEUE_CONNECTION=sync`, `SESSION_SECURE_COOKIE=true`, `SESSION_HTTP_ONLY=true`, `SESSION_SAME_SITE=strict`, `LOG_LEVEL=warning`. `.env` is in `.gitignore`; provide `.env.example`.
- Trust the tunnel's proxy headers (`trustProxies` in `bootstrap/app.php`) so URLs and cookies are HTTPS through `herd share`.
- Assets served from `npm run build`, not the Vite dev server, when sharing.
- `SecurityHeaders` middleware: Content-Security-Policy with a per-request nonce (`default-src 'self'; script-src 'self' 'nonce-…'; style-src 'self' 'nonce-…'; img-src 'self' data:; font-src 'self'; connect-src 'self'; object-src 'none'; base-uri 'none'; form-action 'self'; frame-ancestors 'none'`), `X-Content-Type-Options: nosniff`, `Referrer-Policy: no-referrer`, `Permissions-Policy` denying camera/mic/geolocation, `X-Robots-Tag: noindex, nofollow`. Pass the nonce to Livewire's scripts/styles. If the installed Livewire/Alpine version needs `'unsafe-eval'`, use Livewire's CSP-safe mode if available; otherwise document in the README why it is needed.
- Blade `{{ }}` only, never `{!! !!}` with user input. Session code validated `[A-Za-z0-9-]{1,10}`, case number `[A-Za-z0-9-]{1,20}`.
- Rate limit both routes (`throttle:60,1`).
- Only the two routes exist; no default welcome page, no `/up` exposure beyond what is needed.
- `composer audit` and `npm audit` report 0 vulnerabilities; lockfiles committed; no unused packages.

## Working steps for Claude Code
1. Read this file. Propose a plan and file tree, then wait for approval.
2. Config, `Triage`, `SessionLog` and their Pest tests first. All green before any UI.
3. Livewire component and screens, one at a time. Dutch and English for every key.
4. Feature tests, then Playwright runs on both viewports, screenshot review, design fixes.
5. Run the security checklist; report each item pass/fail.
6. README: what it is, how to run locally in Herd, how to share a session with `herd share` (build first, production `.env`, stop sharing afterwards), how to change questions/scenarios/prices, a short architecture overview a teammate can follow.

## Definition of done
- All 3 scenarios complete on phone and laptop, in Dutch and English, through a `herd share` link.
- Copy and download results work and match what happened.
- All tests pass, both audits clean, security checklist all pass.
- Screenshots of every screen in `docs/screenshots` (needed for the report).
- Dutch text flagged in the README for a check by a Dutch-speaking teammate.
