<?php

/*
|--------------------------------------------------------------------------
| Template module: its made-up data
|--------------------------------------------------------------------------
|
| Put your module's data here (scenarios, options, prices), never in the
| views or the class, so a teammate can change it without touching PHP
| logic. Read it with config('safestart.template.options').
|
| The text for each option is in lang/{nl,en}/template.php under "options".
|
*/

return [

    // The three options on the task screen.
    'options' => ['call', 'meet', 'letter'],

    // The option that counts as "expected" in the results.
    'expected' => 'meet',

];
