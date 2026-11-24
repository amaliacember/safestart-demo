<?php

/*
|--------------------------------------------------------------------------
| Shared demo settings (all modules)
|--------------------------------------------------------------------------
|
| Shared file: change it only after telling the group (see docs/TEAM.md).
| Each module has its own file next to this one: config/safestart/<module>.php.
|
*/

return [

    'locales' => ['nl', 'en'],

    // The address Herd serves this site at. `php artisan demo:preflight`
    // checks this page for Herd Studio's injected script before sharing.
    'herd_url' => env('HERD_URL', 'http://safestart-demo.test'),

    // The session code the interviewer gives. Only letters, digits and
    // dashes, so nothing personal fits. It is never put in a URL.
    'session_code_pattern' => '/^[A-Za-z0-9-]{1,10}$/',

];
