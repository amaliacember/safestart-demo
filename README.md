# SafeStart caseworker demo

A clickable prototype for the Thomas More course "The Digital Startup" (team DreamTeam).
During a video call, a caseworker opens a link, works through three made-up cases,
and the app measures how long each case takes and whether they reach the right route.

**Everything is fake.** There is no real scan, no real order and no real person.
Nothing is stored for the team: the results live only in the participant's own
session until they copy or download them and close the session.

> The rest of this README (how to change questions, scenarios and prices, and an
> architecture overview) is added in step 6.

## Run it locally in Herd

```bash
composer install
npm install
cp .env.example .env          # then, for development: APP_ENV=local, APP_DEBUG=true
php artisan key:generate
npm run build                 # or `npm run dev` while you work on the look
```

Open http://safestart-demo.test.

## Sharing a session with `herd share`

Do these steps before **every** session, in this order.

1. **Run the preflight check.**

   ```bash
   php artisan config:clear
   php artisan demo:preflight
   ```

   It checks that debug is off, the app runs in production mode, the Vite dev
   server is stopped, the assets are built, the session is encrypted and only
   sent over HTTPS, and that Herd Studio does not inject anything into the
   page. Every failed check says what to change. Fix it and run the check
   again until it says **All checks passed**. Do not share before that.

   The usual fixes:
   - Stop `npm run dev` (Ctrl+C) and run `npm run build`.
   - Put the sharing values from `.env.example` in your `.env` (keep your own
     `APP_KEY`), then `php artisan config:clear`.
   - Turn Herd Studio off (see below).

2. **Start sharing:** `herd share`. Give the participant the HTTPS link it shows.
   The link never contains the session code: the interviewer says the code out loud.

3. **After the session:** stop sharing with Ctrl+C in the `herd share` window.
   For development, set `APP_ENV=local` and `APP_DEBUG=true` again.

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

## Tests

```bash
php artisan test        # Pest: unit and Livewire feature tests
npm run test:e2e        # Playwright: all 3 cases, NL + EN, 390 px + 1280 px
```

The Playwright run saves a screenshot of every screen in `docs/screenshots`.
It runs against http://safestart-demo.test; use `DEMO_URL=https://... npm run test:e2e`
for another address.

## Dutch text

All Dutch text is in `lang/nl/demo.php`. **It still needs a check by a
Dutch-speaking (Flemish) teammate.**
