# SafeStart caseworker demo

A clickable prototype for the Thomas More course "The Digital Startup" (team DreamTeam).

SafeStart is a service idea for CAW: people leaving a controlling relationship
get a stalkerware scan, a clean phone with new accounts, help moving their data,
and an evidence vault. This demo only shows the **caseworker's side**.

During a video call, a caseworker opens a link, handles three made-up cases, and
the app measures how long each case takes and whether they reach the right route.
The interviewer asks follow-up questions out loud. It tests one hypothesis:
*caseworkers will use the SafeStart check in most suspected cases of digital control*
(success: at least 4 of 5 caseworkers finish all 3 cases without help in under 10 minutes).

**Everything is fake.** There is no real scan, no real order and no real person.
Nothing is stored for the team: the results live only in the participant's own
session until they copy or download them and close the session.

## What a session looks like

1. **Intro:** language (Dutch or English), a short explanation, and the session
   code the interviewer gives (for example `C3`). That code is the only identifier.
2. **Three cases** (Sara, Lena, Tom), each with these steps:
   1. New case: a case number (pre-filled `DEMO-0417`).
   2. Quick check: six yes / no / don't know questions.
   3. Route: which route(s) apply, with one line why.
   4. Scan: only when the scan route applies. A fake progress bar, then a scripted result.
   5. Advice: new phone package advised or not.
   6. Order: only when the package is advised. Nothing is ordered.
3. **Results:** time per case (with and without the scan's waiting time),
   whether the route was **exact**, had an **extra** route (for example a cautious
   scan) or **missed** one, back clicks, and totals. The participant copies the
   text into the call chat or downloads `safestart-demo-<code>.json`, then
   closes the session, which erases everything.

## Run it locally in Herd

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
npm run build
```

For development, set `APP_ENV=local` and `APP_DEBUG=true` in `.env`, and use
`npm run dev` while you work on the look. Open http://safestart-demo.test.

## Sharing a session with `herd share`

Do these steps before **every** session, in this order.

1. **Run the preflight check.**

   ```bash
   php artisan config:clear
   php artisan demo:preflight
   ```

   It checks that debug is off, the app runs in production mode, the Vite dev
   server is stopped, the assets are built, the session is encrypted and only
   sent over HTTPS, the rest of the `.env` security settings, and that Herd
   Studio does not inject anything into the page. Every failed check says what
   to change. Fix it and run the check again until it says
   **All checks passed**. Do not share before that.

   The usual fixes:
   - Stop `npm run dev` (Ctrl+C) and run `npm run build`.
   - Put the sharing values from `.env.example` in your `.env` (keep your own
     `APP_KEY`), then `php artisan config:clear`.
   - Turn Herd Studio off (see below).

2. **Start sharing:** `herd share`. Give the participant the HTTPS link it shows.
   The link never contains the session code: the interviewer says the code out loud.
   The first time, Expose asks for a token: see "Expose token" below.

3. **After the session:** stop sharing with Ctrl+C in the `herd share` window.
   For development, set `APP_ENV=local` and `APP_DEBUG=true` again.

### Expose token (once)

`herd share` uses Expose. Make a free account at https://expose.dev, copy your
token from https://expose.dev/dashboard, and run:

```bash
"%USERPROFILE%\.config\herd\bin\expose.exe" token YOUR_TOKEN
```

### Turning Herd Studio off

Herd Studio is a Herd developer tool. When it is on, Herd's own web server adds
a script (`/herd-studio/loader.js`) and an "Edit with Herd" button to every page,
including pages viewed through `herd share`. That breaks our "no extra scripts"
rule, so it must be off during a session.

- **For the whole of Herd:** open Herd → **Settings** → **Herd Studio** → turn
  off **Enable Herd Studio**.
- **For this site only:** open Herd → **Sites** → select **safestart-demo** →
  in the panel on the right, under Studio, turn off **Show the Herd Studio badge**.

Check that it worked (no output means it is off):

```bash
curl -s http://safestart-demo.test/ | grep herd-studio
```

`php artisan demo:preflight` runs the same check.

## Changing questions, scenarios, prices or text

You never need to touch PHP logic for these.

| What | Where |
|---|---|
| Question **text**, button labels, all other text | `lang/nl/demo.php` and `lang/en/demo.php` (same keys in both) |
| The **list** of questions | `config/safestart.php` → `questions` |
| Routing rules ("which answers lead to a scan?") | `config/safestart.php` → `rules` and `package` (the format is explained in the file) |
| The three scenarios: story, expected route, scripted scan result | Story text in `lang/*/demo.php` → `scenarios`; the rest in `config/safestart.php` → `scenarios` |
| Prices and delivery time | `config/safestart.php` → `pricing` |
| Scan animation length, 10-minute target | `config/safestart.php` → `timing` |

After a change, run `php artisan test`: the tests check every rule and every scenario.

## How it works

### The short version

It is a normal Laravel app with **one page** (`/`). That page is a **Livewire
component**. If you have not used Livewire: think of it as a Blade view with a
PHP class behind it.

- The class (`app/Livewire/CaseDemo.php`) holds the state of the page in public
  properties, for example `$screen = 'check'` and `$answers`.
- The view (`resources/views/livewire/case-demo.blade.php`) shows the screen
  that `$screen` points at.
- A button like `<button wire:click="submitCheck">` means: when clicked, the
  browser sends a small request, Laravel runs `submitCheck()` on the class, the
  view is rendered again, and only the changed HTML is swapped in. No page
  reload, and no JavaScript to write.

```
 browser                         Laravel
 ───────                         ───────
 click "Show the route"  ──────► CaseDemo::submitCheck()
                                   ├─ validate the 6 answers
                                   ├─ Triage: answers → routes
                                   ├─ SessionLog: save answers, routes, outcome, time
                                   └─ $screen = 'route'
 new HTML swapped in     ◄────── render case-demo.blade.php → screens/route.blade.php
```

### The files that matter

```
app/
  Livewire/CaseDemo.php          The whole flow: one method per button, and which screen comes next.
  Support/Triage.php             Answers -> routes and package advice. Plain PHP, rules come from config.
  Support/ScanSimulator.php      Returns the scripted scan result for a scenario. No randomness.
  Support/SessionLog.php         Records times, back clicks and outcomes; builds the results summary.
  Support/ResultsText.php        The plain-text results for the "Copy" button.
  Http/Controllers/ResultsDownloadController.php   GET /results/download -> the JSON file.
  Http/Middleware/SecurityHeaders.php              Strict browser security headers on every page.
  Http/Middleware/SetLocale.php                    Applies the chosen language from the session.
  Console/Commands/DemoPreflight.php               php artisan demo:preflight
config/safestart.php             Questions, rules, scenarios, prices, timing.
lang/nl/demo.php, lang/en/demo.php   All text.
resources/views/
  layouts/app.blade.php          The HTML page around everything (banner, fonts, scripts).
  livewire/case-demo.blade.php   The frame: step indicator, case-file sheet, current screen.
  screens/*.blade.php            One file per screen (intro, scenario, check, route, scan, ...).
  components/*.blade.php         Banner, step indicator, the yes/no/don't-know control, action bar.
resources/css/app.css            Tailwind and our colour and font tokens.
resources/js/app.js              The two small browser bits: scan progress bar and copy button.
routes/web.php                   The only two routes: / and /results/download.
```

### Where the data lives (and does not)

- Everything about a run is kept in the participant's **Laravel session**
  (`safestart.state` for the screen they are on, `safestart.log` for the
  results). Sessions are encrypted files on the laptop running Herd.
- A page reload continues where the participant was.
- **Close** on the results screen wipes the session. Nothing is written to a
  database (there is none), a log file or anywhere else.
- Times come from the server clock, never from the participant's browser.
- The only identifier is the session code. Names are never asked for; the
  session code and case number only accept letters, digits and dashes.

### Security in short

- Strict Content-Security-Policy with a fresh random nonce per page: only our
  own scripts, styles and fonts can load. Livewire runs in its CSP-safe mode,
  so no `unsafe-eval` is needed.
- No CDN, no web fonts from elsewhere, no analytics: fonts are bundled from
  `@fontsource` packages.
- Cookies are encrypted, HTTPS-only, HttpOnly and SameSite=strict when sharing.
- Rate limit: 120 requests per minute per session, 600 per minute per IP
  (everyone behind the tunnel shares one IP).
- Proxy headers are trusted only from the tunnel on this machine, and only for
  protocol, host and port. A visitor cannot fake their IP address.

## Tests

```bash
php artisan test        # Pest: unit and Livewire feature tests
npm run test:e2e        # Playwright: all 3 cases, NL + EN, 390 px + 1280 px
```

- `tests/Unit`: every routing rule, every scenario, the session log maths.
- `tests/Feature`: the full flow through the component, security headers, rate
  limit, the download, and the preflight command.
- `e2e/flow.spec.ts`: clicks through all cases in a real browser, checks the
  download and copy text against what happened, fails on any security policy
  error, and saves a screenshot of every screen to `docs/screenshots`
  (used in the report). For another address: `DEMO_URL=https://... npm run test:e2e`.

## Known limits

- Livewire registers a few routes of its own besides our two (its update
  endpoint, its script file, and file upload/preview endpoints). The upload
  and preview endpoints reject every request without a signed URL, and this
  app never creates one. The update endpoint has the same rate limit as ours.
- "Copy results" uses the clipboard API on HTTPS (as through `herd share`). On
  plain `http://safestart-demo.test` it falls back to selecting the text.

## Dutch text

All Dutch text is in `lang/nl/demo.php`. **It still needs a check by a
Dutch-speaking (Flemish) teammate**, especially the three scenario stories and
the six questions.
