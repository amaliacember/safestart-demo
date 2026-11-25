# AGENTS.md

Instructions for AI coding assistants working on this repository.

## What this is

SafeStart is a student project (Thomas More, "The Digital Startup") for CAW, Flemish social work organisations. It helps people leaving a controlling relationship: a stalkerware scan, a clean phone with new accounts, help moving data, and an evidence vault.
This repository is a clickable Laravel demo (Livewire, Tailwind, Pest, Playwright) that runs on each member's laptop. Professionals click through made-up tasks during an interview; the app times the tasks and shows results they copy into the chat. Everything is fake.

## The five modules

| # | Module | Owner | Key | Class | Hypothesis |
|---|---|---|---|---|---|
| 1 | Caseworker check | Amalia | `caseworker` | `CaseworkerCheck` | Caseworkers use the scan and checklist |
| 2 | Phone handover and data transfer | Noah | `handover` | `PhoneHandover` | Survivors accept the replacement phone |
| 3 | Evidence vault | to choose | `vault` | `EvidenceVault` | Police / justice centres accept the evidence package |
| 4 | Organisation side | Ana-Maria | `organisation` | `OrganisationSide` | CAW offices buy a subscription |
| 5 | Phone donor and refurb partner | to choose | `donor` | `PhoneDonor` | Corporate IT partners supply 60 used phones |

Classes live in `app/Livewire/Modules/`. Each module opens at `/m/<key>`.

## Find your way (save tokens)

Read the map before opening code. Open source files only for what the task touches.

1. `graphify-out/GRAPH_REPORT.md`: the main classes, how the code is grouped, and what connects to what.
2. `graphify-out/wiki/index.md`, then the one article for the area you work on (for example `Module_Base_and_Caseworker_Flow.md`).
3. The doc that answers your question, not all of them:
   - project rules, shared files, security, design: `CONTRIBUTING.md`
   - module briefs, task lists, timeline: `docs/TEAM.md`
   - setup, how it works, tests: `README.md`
4. Only then open the code files the task touches.

The map is a snapshot: Valentyn rebuilds it after each merge to `main`, so recent changes may be missing. Before you edit, check what the map says against the real file; the code wins. Do not open `graph.json` or `graph.html`: they are large, generated and not in git.

## Ask, don't guess

- If the request leaves something open that the user must decide (which module, which screen, the wording of text, task names, made-up data, design choices), ask before writing anything. Never fill it in yourself.
- Ask one question at a time. Offer 2 to 4 concrete options and say which one you recommend and why.
- Do not ask what the repository already answers: check the map and the docs first.
- If you must assume something to keep going, say so in one line ("Assuming module 2, handover"), so the user can correct you.
- When the user's answer conflicts with a hard rule below, say so and do not build it.

## Before you build

1. Ask which module the user owns, if they have not said.
2. Propose a plan for that module only, and wait for approval before changing files.

## Hard rules

- Change only the files of the user's own module.
- Do not change shared files (listed in `CONTRIBUTING.md`) without asking first.
- Made-up data only: invented people, phones, companies and apps.
- Professionals only: no text addressed to survivors.
- No database, no accounts, no login.
- Nothing is stored or sent: no forms that save or submit data, no analytics, no logging of answers. Results live only in the participant's session.
- All text in `lang/nl/<key>.php` and `lang/en/<key>.php` (same keys), never in Blade views.
- Blade `{{ }}` only, never `{!! !!}`. Validate every input on the server.
- No new Composer or npm packages without asking.
- No inline `style=""` or inline scripts: the Content-Security-Policy blocks them.

## How a module is built

Copy the Template module and rename `template` / `Template` to the module's key and class:

```
app/Livewire/Modules/<Class>.php              extends Module; one public method per button
config/safestart/<key>.php                    the module's made-up data
lang/nl/<key>.php, lang/en/<key>.php          all text
resources/views/modules/<key>/*.blade.php     page.blade.php + one file per screen (2 to 4 screens)
tests/Feature/Modules/<Class>Test.php         start-to-finish Livewire test
e2e/<key>.spec.ts                             Playwright run + screenshots in docs/screenshots/<key>/
```

The key and class are already registered in `config/safestart/modules.php`, so no route needs adding. Use the shared components (`<x-module-sheet>`, `<x-step-indicator>`, `<x-actions>`, `<x-tri-choice>`). Every module ends on the shared results screen by calling `$this->finish()`.

## Commands

```bash
php artisan test                         # Pest: unit and feature tests (also runs on GitHub)
npm run test:e2e                         # Playwright: every module, NL + EN, 390 px + 1280 px
npm run test:e2e -- e2e/<key>.spec.ts    # Playwright: one module
php artisan demo:preflight               # check the settings before sharing the demo
```

## Git

- Never commit or push to `main`. Work on the user's branch: `module-<nr>-<name>` (for example `module-2-handover`).
- Commit or push only when the user asks.
- Commit messages: short, imperative, no prefix, like the existing history.
- Work reaches `main` through a pull request that another member reviews. Shared files are merged by Amalia.

## Done means

- `php artisan test` is green.
- The module has a feature test and a Playwright spec, and both pass.
- Checked at 390 px and 1280 px, in Dutch and in English.
