<?php

// Template module: all English text. Same keys as lang/nl/template.php.
// Every text on your screens goes here, never in the Blade views.

return [

    // Browser tab title.
    'title' => 'SafeStart – template module',

    // The shared intro shows these, above the session code field.
    'intro' => [
        'title' => 'Template module',
        'lead' => 'An example module with one made-up task, to copy when you build your own.',
        'how' => [
            'Pick one of three options.',
            'Check your choice and finish.',
            'You then see the shared results screen.',
        ],
    ],

    // Task names in the results.
    'tasks' => [
        'choose' => 'Choose a way to meet',
    ],

    'progress' => [
        'steps_label' => 'Steps in this module',
    ],

    'steps' => [
        'task' => 'Choose',
        'confirm' => 'Check',
    ],

    'task' => [
        'title' => 'Choose a way to meet',
        'situation' => 'A made-up colleague asks how to plan a first talk with a new client. Pick what you would do.',
        'label' => 'Your choice',
        'required' => 'Choose one of the options.',
        'next' => 'Next',
    ],

    'options' => [
        'call' => 'Phone call',
        'meet' => 'Meeting at the office',
        'letter' => 'Letter by post',
    ],

    'confirm' => [
        'title' => 'Check your choice',
        'chosen' => 'You chose:',
        'lead' => 'Want to change it? Go back. Otherwise finish the module.',
        'finish' => 'Finish the module',
    ],

    // Outcome labels in the results (keys from Template::outcomes()).
    'outcomes' => [
        'expected' => 'as expected',
        'other' => 'other choice',
    ],

];
