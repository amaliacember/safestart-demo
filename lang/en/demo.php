<?php

// Shared English text: banner, start page, intro, results. Same keys as
// lang/nl/demo.php. Shared file: change it only after telling the group.
// Each module has its own text in lang/en/<module>.php.

return [

    'app_title' => 'SafeStart – demo',
    'banner' => 'Demo – made-up data, nothing is kept',
    'back' => 'Back',
    'language_label' => 'Language',

    'locales' => [
        'nl' => 'Nederlands',
        'en' => 'English',
    ],

    'start' => [
        'title' => 'SafeStart demo',
        'lead' => 'SafeStart helps social work organisations support clients whose phone is being misused by an ex-partner. This demo has five short parts. The interviewer tells you which one to open.',
        'privacy' => 'All data is made up. Nothing is kept after you close the page.',
        'modules_label' => 'Modules',
        'module_number' => 'Module :n',
        'coming_soon' => 'Coming soon',
    ],

    // Titles and one-line summaries for the start page and the results.
    'modules' => [
        'caseworker' => [
            'title' => 'Caseworker check',
            'summary' => 'Handle three made-up cases with the quick check, the scan and the advice.',
        ],
        'handover' => [
            'title' => 'Phone handover and data transfer',
            'summary' => 'Go through handover day with a made-up client: what moves over, new accounts, the old phone.',
        ],
        'vault' => [
            'title' => 'Evidence vault',
            'summary' => 'Save harassing messages as evidence and export an evidence package.',
        ],
        'organisation' => [
            'title' => 'Organisation side',
            'summary' => 'As a CAW manager, look at the subscription, the budget and the cost calculator.',
        ],
        'donor' => [
            'title' => 'Phone donor and refurb partner',
            'summary' => 'See how a company donates used phones and what happens to them.',
        ],
        'template' => [
            'title' => 'Template module',
            'summary' => 'An example module to copy.',
        ],
    ],

    'coming_soon' => [
        'title' => 'This module is coming soon',
        'lead' => 'This part of the demo is still being built. Ask the interviewer for the right link.',
        'to_start' => 'Go to the start page',
    ],

    // The shared intro before every module.
    'intro' => [
        'privacy' => 'All data is made up. Nothing is kept after you close the page.',
        'code_label' => 'Session code',
        'code_hint' => 'The interviewer gives you this, for example C3.',
        'code_required' => 'Enter the session code the interviewer gave you.',
        'code_invalid' => 'A session code is up to 10 letters, digits or dashes.',
        'start' => 'Start the demo',
    ],

    'progress' => [
        'step' => 'step :n of :total',
        'not_needed' => 'not needed',
        'done' => 'done',
        'current' => 'current step',
    ],

    // The shared results screen. A module can replace any key under
    // "results" by putting the same key in its own lang file.
    'results' => [
        'title' => 'Results',
        'lead' => 'Copy your results into the chat of the call, or download them. Then close the session.',
        'module' => 'Module :n: :title',
        'col_task' => 'Task',
        'col_time' => 'Time',
        'col_without_wait' => 'Without waiting',
        'col_outcome' => 'Outcome',
        'col_back' => 'Back',
        'unfinished' => 'not finished',
        'total' => 'Total',
        'target' => 'All tasks in under :minutes minutes: :answer',
        'yes' => 'yes',
        'no' => 'no',
        'times_note' => 'Time in minutes:seconds.',
        'text_label' => 'Text for the chat',
        'copy' => 'Copy results',
        'copied' => 'Copied. Paste it into the chat.',
        'copy_failed' => 'Copying did not work. Select the text above and copy it yourself.',
        'download' => 'Download results (JSON)',
        'other_module' => 'Do another module',
        'close' => 'Close the session',
        'close_note' => 'Closing erases everything. Copy or download your results first.',
        'empty_title' => 'No results yet',
        'empty_lead' => 'Results appear here after you finish a module.',
        'to_start' => 'Go to the start page',
        'text' => [
            'heading' => 'SafeStart demo – session :code',
            'unfinished' => ':task: not finished',
            'without_wait' => 'without waiting :time',
            'back' => 'back clicks: :back',
            'total' => 'Total: :time',
            'outcomes' => 'Outcomes: :list',
        ],
    ],

];
