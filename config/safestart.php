<?php

/*
|--------------------------------------------------------------------------
| SafeStart demo settings
|--------------------------------------------------------------------------
|
| Everything a teammate may want to change lives here: the questions, the
| routing rules, the three made-up cases and the prices. All visible text
| lives in lang/nl/demo.php and lang/en/demo.php, under the same keys.
|
| Nothing in this file is real. The scan results are scripted.
|
*/

return [

    'locales' => ['nl', 'en'],

    /*
    | The six quick-check questions. The text is in lang/{nl,en}/demo.php under
    | "questions.q1" etc. Each question is answered yes / no / unknown.
    */
    'questions' => ['q1', 'q2', 'q3', 'q4', 'q5', 'q6'],

    'answers' => ['yes', 'no', 'unknown'],

    /*
    | Routing rules.
    |
    | A rule is a list of conditions:
    |   'q2'             the answer to q2 counts as yes
    |   'not:q4'         the answer to q4 does not count as yes
    |   ['any' => [...]] at least one of the conditions holds
    |   ['all' => [...]] every condition holds
    |
    | "unknown_counts_as_yes" decides how a "don't know" answer is treated
    | inside that rule. For scan and shared we play it safe and treat it as yes.
    |
    | Routes are checked in this order. If none applies, the route is "none".
    */
    'rules' => [
        // Possible spyware: the ex had the phone, the phone acts strangely, or
        // the ex knows things that shared accounts or location apps do not explain.
        'scan' => [
            'unknown_counts_as_yes' => true,
            'when' => ['any' => ['q2', 'q6', ['all' => ['q1', 'not:q4', 'not:q5']]]],
        ],
        // Harassment by message: save evidence, block, report.
        'message' => [
            'unknown_counts_as_yes' => false,
            'when' => ['any' => ['q3']],
        ],
        // Shared accounts and location: separate them, change passwords.
        'shared' => [
            'unknown_counts_as_yes' => true,
            'when' => ['any' => ['q4', 'q5']],
        ],
    ],

    /*
    | When a new phone package is advised. Same condition format, plus:
    |   'scan_found'     the (scripted) scan found something
    |   'route:shared'   the "shared" route applies
    |
    | Here "don't know" does NOT count as yes: the package is only advised
    | when the caseworker is sure the ex had the phone or passwords (q2).
    */
    'package' => [
        'unknown_counts_as_yes' => false,
        'when' => ['any' => ['scan_found', ['all' => ['q2', 'route:shared']]]],
    ],

    /*
    | The six case steps, in order, for the "step X of 6" indicator.
    | "scan" and "order" are skipped when they do not apply; the indicator
    | then marks them as "not needed".
    */
    'steps' => ['case-number', 'check', 'route', 'scan', 'advice', 'order'],

    /*
    | The three made-up cases, shown in this order. The story text is in
    | lang/{nl,en}/demo.php under "scenarios.A" etc.
    |
    | reference_answers: what a careful reader of the story would answer.
    |                    Only used by the tests, never shown to participants.
    | expected_routes:   the routes the story calls for. The results compare
    |                    what the participant reached with this:
    |                      exact  = the same routes
    |                      extra  = all expected routes plus more (for example
    |                               a cautious scan after a "don't know")
    |                      missed = at least one expected route is absent
    |                    For C, q2 is unclear in the story on purpose: a yes or
    |                    "don't know" adds a scan, which shows up as "extra".
    | scan_findings:     the scripted scan result. Empty means "nothing found".
    |                    App names are invented and must never be real products.
    */
    'scenarios' => [
        'A' => [
            'reference_answers' => ['q1' => 'yes', 'q2' => 'yes', 'q3' => 'no', 'q4' => 'no', 'q5' => 'no', 'q6' => 'yes'],
            'expected_routes' => ['scan'],
            'scan_findings' => [
                ['name' => 'Kalvo Sync', 'finding' => 'location'],
                ['name' => 'Pernik System Service', 'finding' => 'messages'],
            ],
        ],
        'B' => [
            'reference_answers' => ['q1' => 'no', 'q2' => 'no', 'q3' => 'yes', 'q4' => 'no', 'q5' => 'no', 'q6' => 'no'],
            'expected_routes' => ['message'],
            'scan_findings' => [],
        ],
        'C' => [
            'reference_answers' => ['q1' => 'yes', 'q2' => 'unknown', 'q3' => 'no', 'q4' => 'yes', 'q5' => 'yes', 'q6' => 'no'],
            'expected_routes' => ['shared'],
            'scan_findings' => [],
        ],
    ],

    'defaults' => [
        'case_number' => 'DEMO-0417',
    ],

    // Input checks. Only letters, digits and dashes, so nothing personal fits.
    'patterns' => [
        'session_code' => '/^[A-Za-z0-9-]{1,10}$/',
        'case_number' => '/^[A-Za-z0-9-]{1,20}$/',
    ],

    'timing' => [
        // Length of the fake "scanning" progress bar.
        'scan_animation_ms' => 3500,
        // Success target from the experiment: all 3 cases in under 10 minutes.
        'target_total_minutes' => 10,
    ],

    // Shown on the order screen. Amounts in euro.
    'pricing' => [
        'package_eur' => 350,
        'subscription_eur_per_year' => 1800,
        'delivery_working_days' => 5,
    ],

];
