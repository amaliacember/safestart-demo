# Graph Report - safestart-demo  (2026-11-25)

## Corpus Check
- Corpus is ~35,379 words - fits in a single context window. You may not need a graph.

## Summary
- 665 nodes · 1120 edges · 74 communities (26 shown, 48 thin omitted)
- Extraction: 90% EXTRACTED · 10% INFERRED · 0% AMBIGUOUS · INFERRED: 116 edges (avg confidence: 0.85)
- Token cost: 88,806 input · 0 output

## Community Hubs (Navigation)
- Module Base and Caseworker Flow
- Session Log and Its Tests
- Results Text and Start Page
- Caseworker Check Tests
- Frontend Packages and E2E Helpers
- Composer Dependencies
- Triage Unit Tests
- CI Tests Workflow
- Demo Preflight Command
- Scan Simulator
- App Service Provider and Rate Limit
- Results Feature Tests
- Session Log Wait Timing
- Project Docs and Module Contract
- Security and Sharing Settings
- Security Headers and Locale Middleware
- Caseworker Triage Logic
- Hard Rules and Privacy
- Template Module Tests
- Caseworker Config Test Helpers
- Laravel Bootstrap
- Experiment 3 and Business Model
- Frontend Alpine Components
- Module 4 Organisation Side (H3)
- Rate Limit Tests
- Results Download Controller
- Definition of Done and Testing
- Module 2 Phone Handover (H2)
- Test Base Setup
- Order Screen
- Scan Screen
- Module 5 Refurb Partner (H4)
- Module 3 Evidence Vault
- Base Controller
- Back Action Partial
- Results View
- Advice Screen
- Route Screen
- Design and Accessibility Rules
- Confirm Screen
- Caseworker Page View
- Template Page View
- Language Toggle
- Scenario Screen

## God Nodes (most connected - your core abstractions)
1. `SessionLog` - 47 edges
2. `CaseworkerCheck` - 37 edges
3. `Module` - 35 edges
4. `startDemo()` - 19 edges
5. `answers()` - 17 edges
6. `playToRoute()` - 16 edges
7. `Template` - 15 edges
8. `playThreeTasks()` - 12 edges
9. `Triage` - 11 edges
10. `demoLog()` - 11 edges

## Surprising Connections (you probably didn't know these)
- `robots.txt: disallow all crawlers` --semantically_similar_to--> `X-Robots-Tag: noindex, nofollow`  [INFERRED] [semantically similar]
  public/robots.txt → CONTRIBUTING.md
- `Changing module 1 via config and lang` --references--> `Module 1: Caseworker check`  [INFERRED]
  README.md → docs/TEAM.md
- `{closure#1}()` --calls--> `Triage`  [EXTRACTED]
  tests/Unit/Caseworker/TriageTest.php → app/Support/Caseworker/Triage.php
- `H3: CAW offices buy a subscription` --references--> `CAW (Flemish social work organisations)`  [INFERRED]
  docs/TEAM.md → README.md
- `robots.txt: disallow all crawlers` --conceptually_related_to--> `Settings for sharing (production .env)`  [INFERRED]
  public/robots.txt → README.md

## Import Cycles
- None detected.

## Hyperedges (group relationships)
- **Five SafeStart demo modules, one per member** — docs_team_module_1_caseworker_check, docs_team_module_2_phone_handover, docs_team_module_3_evidence_vault, docs_team_module_4_organisation_side, docs_team_module_5_phone_donor, docs_team_shared_results_screen [EXTRACTED 1.00]
- **Nothing-stored, no-personal-data privacy rules** — readme_session_only_storage, contributing_no_personal_data, contributing_no_third_party_runtime, contributing_demo_banner, agents_hard_rules [INFERRED 0.85]
- **Demo security hardening controls** — contributing_content_security_policy, contributing_rate_limiting, contributing_input_validation, contributing_x_robots_tag, contributing_locked_state, public_robots_disallow_all [INFERRED 0.85]

## Communities (74 total, 48 thin omitted)

### Community 0 - "Module Base and Caseworker Flow"
Cohesion: 0.07
Nodes (3): CaseworkerCheck, Module, Template

### Community 1 - "Session Log and Its Tests"
Cohesion: 0.08
Nodes (34): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}(), {closure#8}() (+26 more)

### Community 2 - "Results Text and Start Page"
Cohesion: 0.06
Nodes (17): ComingSoon, Results, StartPage, Modules, ResultsText, Shared files (Amalia merges), Amalia, H1: caseworkers use the scan and checklist (+9 more)

### Community 3 - "Caseworker Check Tests"
Cohesion: 0.10
Nodes (28): {closure#10}(), {closure#11}(), {closure#13}(), {closure#15}(), {closure#16}(), {closure#17}(), {closure#19}(), {closure#20}() (+20 more)

### Community 4 - "Frontend Packages and E2E Helpers"
Cohesion: 0.08
Nodes (33): ANSWERS, Lang, TEXT, BANNER, chooseLanguage(), onScreen(), screenshotter(), startModule() (+25 more)

### Community 5 - "Composer Dependencies"
Cohesion: 0.05
Nodes (41): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+33 more)

### Community 6 - "Triage Unit Tests"
Cohesion: 0.11
Nodes (17): answers(), {closure#10}(), {closure#11}(), {closure#12}(), {closure#15}(), {closure#16}(), {closure#17}(), {closure#18}() (+9 more)

### Community 8 - "CI Tests Workflow"
Cohesion: 0.08
Nodes (24): concurrency, cancel-in-progress, group, jobs, pest, name, on, pull_request (+16 more)

### Community 9 - "Demo Preflight Command"
Cohesion: 0.13
Nodes (10): DemoPreflight, {closure#10}(), {closure#11}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#7}(), {closure#8}() (+2 more)

### Community 10 - "Scan Simulator"
Cohesion: 0.15
Nodes (3): ScanSimulator, {closure#1}(), {closure#28}()

### Community 11 - "App Service Provider and Rate Limit"
Cohesion: 0.20
Nodes (3): AppServiceProvider, {closure#1}(), GET /trusted proxies/_proxy()

### Community 12 - "Results Feature Tests"
Cohesion: 0.23
Nodes (7): {closure#2}(), {closure#3}(), {closure#4}(), {closure#6}(), {closure#7}(), finishTemplate(), {closure#1}()

### Community 13 - "Session Log Wait Timing"
Cohesion: 0.24
Nodes (4): {closure#1}(), {closure#3}(), {closure#4}(), {closure#7}()

### Community 14 - "Project Docs and Module Contract"
Cohesion: 0.31
Nodes (7): AGENTS.md (AI assistant instructions), Contributing to the SafeStart demo, Module-owned files, SafeStart team plan (experiment 3, MVP), Module class contract (key, tasks, outcomes, targetMinutes, waits, remember, afterIntro, renderScreen), SafeStart Demo (clickable prototype), Template module (/m/template)

### Community 15 - "Security and Sharing Settings"
Cohesion: 0.22
Nodes (10): Server-side input validation, #[Locked] state and screen checks, Rate limiting (throttle:demo), Security requirements, X-Robots-Tag: noindex, nofollow, robots.txt: disallow all crawlers, php artisan demo:preflight, Herd Studio (+2 more)

### Community 18 - "Hard Rules and Privacy"
Cohesion: 0.25
Nodes (3): Content-Security-Policy with per-request nonce, Demo banner (made-up data, nothing is kept), Stack: Laravel, Livewire 4 CSP-safe, Alpine.data, Tailwind 4, Pest, Playwright

### Community 19 - "Template Module Tests"
Cohesion: 0.32
Nodes (3): {closure#3}(), {closure#4}(), templateTask()

### Community 20 - "Caseworker Config Test Helpers"
Cohesion: 0.25
Nodes (8): caseworkerConfig(), {closure#1}(), {closure#30}(), {closure#31}(), {closure#32}(), {closure#33}(), {closure#34}(), {closure#35}()

### Community 22 - "Experiment 3 and Business Model"
Cohesion: 0.33
Nodes (7): Business Model Canvas (BMC), Four shared interview questions, Interviewee invite message, Pricing model (EUR 1,800/year + EUR 350 per package), Documentation experiment 3 report, Interview test sessions (2 per member), Experiment 3 timeline (25 Nov - 2 Dec)

### Community 23 - "Frontend Alpine Components"
Cohesion: 0.60
Nodes (4): finish(), init(), show(), skip()

### Community 26 - "Module 4 Organisation Side (H3)"
Cohesion: 0.40
Nodes (5): Ana-Maria, H3: CAW offices buy a subscription, Module 4: Organisation side, CAW (Flemish social work organisations), SafeStart service

### Community 27 - "Rate Limit Tests"
Cohesion: 0.70
Nodes (4): asSession(), {closure#1}(), {closure#2}(), {closure#3}()

### Community 29 - "Definition of Done and Testing"
Cohesion: 0.50
Nodes (4): Definition of done, Dutch text review by Flemish teammate, GitHub Actions tests workflow, Test suite (Pest + Playwright)

### Community 30 - "Module 2 Phone Handover (H2)"
Cohesion: 0.50
Nodes (4): H2: survivors accept the replacement phone, Module 2: Phone handover and data transfer, Noah, Sara (made-up client, case A)

### Community 35 - "Module 5 Refurb Partner (H4)"
Cohesion: 0.67
Nodes (3): H4: corporate IT partners supply 60 used phones, Module 5: Phone donor and refurb partner, Refurb flow statuses (pick-up to CAW delivery in 5 days)

### Community 36 - "Module 3 Evidence Vault"
Cohesion: 0.67
Nodes (3): Hypothesis: police / justice centres accept the evidence package, Module 3: Evidence vault, SHA-256 evidence hash

## Knowledge Gaps
- **31 isolated node(s):** `back`, `switchLocale`, `closeSession`, `continueFromAdvice`, `orderPackage` (+26 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 265 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **48 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `SessionLog` connect `Session Log and Its Tests` to `Module Base and Caseworker Flow`, `Results Text and Start Page`, `Results Feature Tests`, `Session Log Wait Timing`, `Results Download Controller`?**
  _High betweenness centrality (0.136) - this node is a cross-community bridge._
- **Are the 16 inferred relationships involving `answers()` (e.g. with `{closure#10}()` and `{closure#11}()`) actually correct?**
  _`answers()` has 16 INFERRED edges - model-reasoned connections that need verification._
- **What connects `back`, `switchLocale`, `closeSession` to the rest of the system?**
  _31 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `Module Base and Caseworker Flow` be split into smaller, more focused modules?**
  _Cohesion score 0.06582952815829528 - nodes in this community are weakly interconnected._
- **Why does `CaseworkerCheck` connect `Module Base and Caseworker Flow` to `Results Text and Start Page`, `Caseworker Check Tests`?**
  _High betweenness centrality (0.094) - this node is a cross-community bridge._
- **Should `Session Log and Its Tests` be split into smaller, more focused modules?**
  _Cohesion score 0.07901668129938542 - nodes in this community are weakly interconnected._
- **Why does `Module` connect `Module Base and Caseworker Flow` to `Session Log and Its Tests`, `Results Text and Start Page`?**
  _High betweenness centrality (0.071) - this node is a cross-community bridge._