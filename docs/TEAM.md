# SafeStart demo: team plan (experiment 3, MVP)

One demo, five modules, one person per module. Each person builds their module **and** runs 2 test sessions with their own audience. Everything here is a proposal the team agreed on in class on Tue 24 Nov; change it together if something does not work.

| # | Module | Owner | Hypothesis | Test with |
|---|---|---|---|---|
| 1 | Caseworker check | Amalia | H1 caseworkers use the scan and checklist | Caseworkers |
| 2 | Phone handover and data transfer | Noah | H2 survivors accept the replacement phone | Caseworkers |
| 3 | Evidence vault | *to choose* | Police / justice centres accept the evidence package (list no. 3) | Caseworkers, plus a police, lawyer or family justice centre contact if anyone has one |
| 4 | Organisation side | Ana-Maria | H3 CAW offices buy a subscription | CAW managers / team leads |
| 5 | Phone donor and refurb partner | *to choose* | H4 corporate IT partners supply 60 used phones | Company IT or facility managers |

---

## Rules for every module (non-negotiable)

1. **Made-up data only.** Made-up people, made-up phones, made-up companies. Every screen keeps the yellow "Demo" banner.
2. **Professionals only.** Never test with survivors, never write text addressed to survivors.
3. **Nothing is collected.** No forms that store or send data, no database, no analytics. Interest, prices and pledges are asked out loud in the interview, not typed into the app.
4. **2 to 4 screens per module.** Small and finished beats big and broken.
5. **All text in `lang/nl` and `lang/en`.** No text inside Blade views.
6. **Same look.** Use the shared layout, components and Tailwind theme. Do not add new colours, fonts or UI libraries.
7. **Same results.** Every module ends on the shared results screen (time per task, back clicks, outcome) with "Copy results". This is what makes our evidence comparable.
8. **Tests.** At least one Livewire feature test that runs your module start to finish, and your screens in the Playwright run. `php artisan test` must be green before you open a pull request.

Everything in `CLAUDE.md` (security, design, accessibility) applies to all modules. Claude Code reads it automatically.

---

## How the code is organised

Each module lives in its own files, so we never edit the same file at the same time:

```
app/Livewire/Modules/<Name>.php          the module's Livewire class (screens + state)
config/safestart/<module>.php            the module's data: scenarios, options, prices
lang/nl/<module>.php, lang/en/<module>.php   all the module's text
resources/views/modules/<module>/*.blade.php  one Blade file per screen
tests/Feature/Modules/<Name>Test.php     start-to-finish test
e2e/<module>.spec.ts                     Playwright run + screenshots
```

Shared files (only change after telling the group, Amalia merges these):
`resources/views/layouts/*`, `resources/views/components/*`, `resources/css/app.css`, `app/Support/SessionLog.php`, `app/Http/Middleware/*`, `routes/web.php`, `bootstrap/app.php`, `CLAUDE.md`.

The start page lists the five modules. Each module also has its own direct link (`/m/<module>`) so an interviewer can send just their part.

---

## Getting started (each member, about 30 min)

1. Make sure you can run Laravel locally: PHP 8.4+, Composer and Node 22 (Herd, or whatever you used in other courses).
2. Accept the GitHub invite to the repo, then clone it.
3. In the project folder:
   ```
   composer install
   npm install
   cp .env.example .env     (Windows PowerShell: copy .env.example .env)
   php artisan key:generate
   npm run build
   ```
4. Open the site locally: with Herd at `http://<folder-name>.test`, otherwise run `php artisan serve` and open `http://127.0.0.1:8000`. You should see the start page and module 1 working.
5. Make your branch: `git checkout -b module-<nr>-<name>` (e.g. `module-2-handover`).
6. If you use Claude Code: open the folder in VS Code and start with *"Read CLAUDE.md and docs/TEAM.md. I own module <nr>. Propose a plan for my module only. Don't build yet."* If you code yourself, copy `app/Livewire/Modules/Template.php` and its files as a starting point.

## Getting your module into the demo

1. Commit often on your own branch.
2. When your module works: `php artisan test` green, then push and open a **pull request** to `main`.
3. One other member looks at it (click through it, check the rules above). Then merge.
4. After a merge, everyone runs `git pull` on `main` (then `composer install`, `npm install`, `npm run build`) to see the latest version locally. There is no hosting yet; we decide that when the modules are done.

---

## Module briefs

Each brief: what the participant does, what we measure, what counts as "we are right", and the module-specific interview questions. Every session also uses the **four shared questions** at the end of this file.

### Module 1: Caseworker check (Amalia), done
Quick check of 6 questions, route, scripted scan, advice, order package. 3 made-up cases.
- **Measure:** time per case, route outcome (exact / extra / missed), back clicks.
- **We are right if:** at least 4 of 5 finish all 3 cases without help in under 10 minutes, and at least 3 of 5 say they would use it in more than half of their suspected cases.
- **Questions:** In how many of your suspected cases would you use this check? Was the route right for each case? What did it miss?

### Module 2: Phone handover and data transfer (Noah)
The caseworker sits with a made-up client on handover day and goes through: (1) what moves to the new phone (photos, contacts, chosen apps) and what stays behind; (2) a new-accounts checklist (new email, new Apple/Google account, new passwords); (3) what to tell the client about the old phone (keep it as evidence, switch it off, don't reset it).
- **Why:** in our interviews the barrier was starting over with all new accounts and fear of losing photos, not the phone itself.
- **Measure:** time to finish the handover, steps where they hesitate, back clicks.
- **We are right if:** at least 4 of 5 finish without help, and at least 3 of 5 say most of their clients would accept the phone when it is explained like this.
- **Questions:** Would your clients accept a new phone if you explained it like this? What would make them say no? Which step would you skip or change in real life? Who should hand over the phone: you, or someone else?

### Module 3: Evidence vault (to choose)
The caseworker helps a made-up client save 3 harassing messages (fake screenshots) into the vault, adds a short note to each, and exports an evidence package (a preview of a PDF-style summary: date, time, sender account, the screenshot, a hash showing it was not changed).
- **Why:** P1's problem was harassment by message. Proof was not on survivors' minds at the time, so caseworkers may be the ones who need this.
- **Measure:** time to save and export, steps where they hesitate.
- **We are right if:** at least 3 of 5 say the exported package would be usable when going to the police or a lawyer. If we reach someone from police, a lawyer or a family justice centre: they confirm the format is usable, or tell us exactly what is missing.
- **Questions:** Would this package be accepted when filing a police report? What is missing? How do you collect this kind of proof today? Who in your organisation would use this?

### Module 4: Organisation side (Ana-Maria)
A CAW manager sees: (1) the subscription (€1,800/year) and what it includes; (2) packages used this year against the budget (€350 per package); (3) a small calculator: number of cases per year → yearly cost, with a note on subsidy funding; (4) a "request a pilot" button (demo, nothing is sent).
- **Measure:** whether they understand the cost model without help, what they enter in the calculator, their answers on budget.
- **We are right if:** at least 2 of 3–4 managers say the price fits a project or subsidy budget and they would discuss a pilot. (Caseworkers can answer some of this too, but count that as weaker evidence.)
- **Questions:** Is €1,800 a year plus €350 per package realistic for your organisation? From which budget would this come? Who decides on a purchase like this, and how long does that take? What would you need to see before starting a pilot?

### Module 5: Phone donor and refurb partner (to choose)
A company IT or facility manager sees: (1) how donating used phones works (pick-up, certified data wiping with a certificate per device, refurbishing); (2) a made-up impact page ("your 20 phones became 14 SafeStart packages"); (3) the refurb partner view: status of a phone from pick-up to delivered at a CAW office within 5 working days.
- **Measure:** whether they understand the flow without help, and (asked out loud, not typed in) whether their company would donate and roughly how many phones a year.
- **We are right if:** at least 3 of 5 say their company would donate, and the total they mention adds up to a meaningful part of the 60 phones a year in our hypothesis.
- **Questions:** Would your company donate old phones to this? How many does your company replace per year? What would you need (data wiping certificate, tax receipt, publicity)? Who would have to approve it?

---

## Task lists: what each module owner does

### Part A: tasks for every module owner

**Wed 25 Nov: set up and plan on paper**
- [ ] Check you can run Laravel locally (PHP 8.4+, Composer, Node 22). Accept the GitHub invite, clone the repo, follow "Getting started" until the start page works on your laptop.
- [ ] Read `CLAUDE.md` and this file, at least the rules and your module brief.
- [ ] Write your **made-up case(s)** in 3–4 plain sentences: who, what happened, what the professional has to do. No real people or stories.
- [ ] Sketch your **2–4 screens** on paper or in a note: what is on each screen, which button goes where. Mark which parts are timed as a "task" (for example "task 1 = choose what moves to the new phone").
- [ ] Write **all the text** for your screens in Dutch and English (a shared doc is fine; Claude Code can put it in the `lang` files later).
- [ ] Write your **success line** for the test card: "We are right if at least … of … ".
- [ ] Do the **research** listed for your module in Part B, so the content is correct.
- [ ] Make a list of **4 possible interviewees** (you need 2; people cancel). Message them today with the template below, and plan a 20-minute call between Fri 27 and Mon 30 Nov.

**Thu 26 Nov: build**
- [ ] Create your branch `module-<nr>-<name>`.
- [ ] Ask Claude Code for a plan for your module only (prompt in "Getting started"). Before saying go, check: only your own module files, no database, no forms that store or send data, all text in `lang`.
- [ ] Build in this order: config (your made-up data) and lang files first, then screens one by one, then the feature test, then add your module to the Playwright run.
- [ ] Click through it yourself on your **phone and laptop**, in Dutch and English. Fix anything confusing.
- [ ] Run `php artisan test`. Green means you can open a pull request.

**Fri 27 Nov: merge and try it out**
- [ ] Open your pull request **before 12:00**. Review one other member's pull request (click through it, check the rules).
- [ ] After the merge, pull `main` and let a teammate try your module **once** on your laptop, as if it were a real session. Note what confused them and fix small things.

**Fri 27 – Mon 30 Nov: sessions (2 per person)**
- [ ] Use the link the team agreed on (where we host it is decided on Fri 27). Open it yourself before the call to check it works.
- [ ] Start: explain it's a student project with made-up data, ask for a photo without faces, ask them not to share real client details.
- [ ] Send the link. Let them think out loud; don't help. Write down where they hesitate.
- [ ] Ask your module questions, then the four shared questions. Take notes word for word where you can.
- [ ] Ask them to press "Copy results" and paste it in the chat. Save it as `<module>-<initials>.txt` in the team folder, together with your notes.
- [ ] Take the photo (screen or setting, no faces).
- [ ] Fill in your row of the interviewee table: initials, role, type of organisation, how you reached them.

**Tue 1 Dec: write up**
- [ ] Write your part of "Documentation experiment 3" (see "What each member writes in the report").
- [ ] Bring your results and 3 key learnings to the team for the shared learning card and conclusions.

**Invite message (adapt as you like)**
> Hi [name], I'm a student Applied Computer Science at Thomas More. For a course we're developing SafeStart, a service that helps social work organisations support clients whose phone is being misused by an ex-partner. Could I show you a short demo (made-up data only) and ask a few questions? It takes about 20 minutes, online or in person, any time between Fri 27 and Mon 30 Nov. Your feedback goes into our course report anonymously. Thank you!

### Part B: module-specific tasks

**Module 2: Phone handover and data transfer (Noah)**
- [ ] Reuse "Sara" from module 1 case A as the client, so the demo tells one story: check → scan → new phone → handover.
- [ ] Make the list of **what moves to the new phone** (photos, contacts, chosen chat apps, …) and **what stays behind** (old accounts, apps the ex had access to, backups shared with the ex). For each item, write one line on why.
- [ ] Write the **new-accounts checklist** (about 8–10 items: new email, new Apple/Google account, new passwords, two-step verification, check location sharing is off, …).
- [ ] Research what reliable sources advise about **the old phone** (keep it as possible evidence, don't factory-reset it, switch it off or keep it away from the client). Note your sources for the report.
- [ ] Decide the 3 timed tasks: (1) choose what moves, (2) go through the account checklist, (3) give the advice about the old phone.
- [ ] Add one moment where the client hesitates ("I'm scared to lose my photos" or "I don't want to start over") and the caseworker picks an answer. This tests P2's barrier.
- [ ] Recruitment: your audience is caseworkers, like modules 1 and 3. Agree with Amalia (and the module 3 owner) who contacts whom, so we don't ask the same person three times. One caseworker can do two modules in one 30-minute call if the teacher agrees.

**Module 3: Evidence vault (to choose)**
- [ ] Make **3 fake harassing messages**: made-up names, obviously fake accounts, not too graphic. Build them as chat bubbles in Blade, not as images of real apps or logos.
- [ ] Decide what the **evidence package** contains for each message: date and time, platform, sender account, the message, the caseworker's note, and a **SHA-256 hash** (computed for real in PHP) to show it wasn't changed afterwards.
- [ ] Research what kind of proof is useful when **filing a complaint for harassment (belaging) with the Belgian police**, and what a family justice centre or lawyer would want to see. Note your sources.
- [ ] Build the export as a **print-friendly preview page** (the browser's print can make a PDF). No real files are created or stored.
- [ ] Decide the timed tasks: (1) save 3 messages to the vault, (2) add notes, (3) export the package.
- [ ] Recruitment: caseworkers (coordinate with modules 1 and 2), plus try **today** to reach one police officer, lawyer or family justice centre worker through teachers, family or friends. They take longest to answer.

**Module 4: Organisation side (Ana-Maria)**
- [ ] Take the prices and costs from our **BMC** (€1,800/year subscription, €350 per package) and check they match what's in the BMC revenue and cost parts. If they don't match, tell the group before building.
- [ ] Design the **calculator**: inputs (cases per year, share of cases that need a phone package) → yearly cost and cost per client helped. Write out 2 example calculations by hand so you can check the code against them.
- [ ] Research how CAW is funded (Flemish government, project subsidies) so the subsidy note on the screen is correct. Note your sources.
- [ ] Make a **made-up organisation** for the dashboard: name, number of caseworkers, packages used this year, budget left.
- [ ] Decide the timed tasks: (1) find what the subscription includes, (2) work out next year's cost with the calculator, (3) find where to request a pilot.
- [ ] Recruitment: managers or team leads are the **hardest group to reach**, so start today. Try the teacher's contacts, the general address of a CAW, LinkedIn and people you know in social work. If you only reach caseworkers, that's fine, but mark it as weaker evidence in the report.

**Module 5: Phone donor and refurb partner (to choose)**
- [ ] Research roughly **how often companies replace work phones**, what **certified data wiping** means (a certificate per device), and whether similar phone donation programmes already exist in Belgium (so we don't claim to be the first). Note your sources.
- [ ] Write the **refurb flow** as statuses: picked up → wiped (certificate) → tested → refurbished → ready → delivered to a CAW office (within 5 working days, as in our hypothesis).
- [ ] Make the **impact numbers**: donated phones → usable SafeStart packages. Pick a made-up yield (for example 7 out of 10 phones are usable) and label it as an assumption.
- [ ] Make a **made-up company** (name, sector, number of employees, phones donated).
- [ ] Decide the timed tasks: (1) find out how donating works, (2) find what happens to the data on the phones, (3) find the status of a donated batch.
- [ ] Recruitment: company IT or facility managers. Try parents' and friends' employers, student-job employers, LinkedIn, and **Thomas More's own IT department**. Ask the donation and "how many phones" questions out loud and write the numbers in your notes.

---

## Four shared questions (every session, every module)

1. Was anything unclear or hard to find? Where did you hesitate? *(usability)*
2. In your own words, what does SafeStart do, and for whom? *(understanding the value proposition)*
3. Would your organisation pay for this, or take part in it? At what price or under what conditions? *(willingness to pay)*
4. What one thing would you change or add? *(suggestions)*

## Sessions

- 2 sessions per member, about 20 minutes, on a video call or in person. The participant opens the link on their own device and thinks out loud; you watch and don't help.
- Ask permission for a photo without faces (you need 2 per member for the report) and ask them not to share real client details.
- At the end they press "Copy results" and paste them in the chat. Save them in the team folder as `<module>-<initials>.txt`.

## Timeline

| When | What |
|---|---|
| Wed 25 Nov | Amalia: module system, shared results screen, template module, GitHub repo ready. Others: write your screens, text and made-up data on paper; start finding your 2 interviewees. |
| Thu 26 Nov | Everyone builds their module on their branch. |
| Fri 27 Nov (noon) | Pull requests merged. Everyone pulls `main` and checks all modules locally. Team decides where we host it for the sessions. |
| Fri 27 – Mon 30 Nov | Sessions (2 per member), with photos. |
| Tue 1 Dec | Each member writes their module part of "Documentation experiment 3" (what it is, screenshots, questions, interviewees, 2 photos). Together: test card 3, learning card 3, conclusions, BMC changes. |
| Wed 2 Dec 23:59 | Hand-in (report on Canvas, explainer video on More ICE). |

## What each member writes in the report (experiment 3)
- What your module is and which hypothesis it tests (3–4 lines).
- 2–3 screenshots (from `docs/screenshots`).
- Your module questions plus the four shared questions.
- Who you interviewed and why (role, organisation type, how you reached them; initials only).
- 2 photos of your sessions.
- Your results (paste the copied results) and what you learned, for the shared learning card.
