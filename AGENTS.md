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

## Before you build

1. Read `CONTRIBUTING.md` (the project rules) and `docs/TEAM.md` (module briefs and task lists).
2. Ask which module the user owns, if they have not said.
3. Propose a plan for that module only, and wait for approval before changing files.

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

The key and class are already registered in `config/safestart/modules.php`, so no route needs adding. Use the shared components (`<x-module-sheet>`, `<x-step-indicator>`, `<x-actions>`, `<x-tri-choice>`) and the base class helpers (`startTask`, `enterScreen`, `countBack`, `record`, `setOutcome`, `finishTask`). Every module ends on the shared results screen by calling `$this->finish()`.

## Done means

- `php artisan test` is green.
- The module has a feature test and a Playwright spec, and both pass.
- Checked at 390 px and 1280 px, in Dutch and in English.
