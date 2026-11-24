<?php

/*
|--------------------------------------------------------------------------
| The five modules of the demo
|--------------------------------------------------------------------------
|
| Shared file: change it only after telling the group (see docs/TEAM.md).
|
| Every module is listed here from the start, with the Livewire class it
| will have. As long as that class does not exist yet, the start page shows
| the module as "coming soon" and /m/<key> shows a "coming soon" page. As
| soon as a teammate merges app/Livewire/Modules/<Name>.php, the module goes
| live by itself: nobody has to edit this file or routes/web.php.
|
| key      the part after /m/ in the link, and the name of the module's
|          config and lang files (config/safestart/<key>.php, lang/nl/<key>.php)
| number   the module number from docs/TEAM.md
| listed   false keeps a module off the start page (the template)
|
| The titles shown on the start page are in lang/{nl,en}/demo.php under
| "modules.<key>".
|
*/

use App\Livewire\Modules;

return [

    'caseworker' => ['number' => 1, 'class' => Modules\CaseworkerCheck::class, 'listed' => true],
    'handover' => ['number' => 2, 'class' => Modules\PhoneHandover::class, 'listed' => true],
    'vault' => ['number' => 3, 'class' => Modules\EvidenceVault::class, 'listed' => true],
    'organisation' => ['number' => 4, 'class' => Modules\OrganisationSide::class, 'listed' => true],
    'donor' => ['number' => 5, 'class' => Modules\PhoneDonor::class, 'listed' => true],

    // The example to copy. Reachable at /m/template, not on the start page.
    'template' => ['number' => null, 'class' => Modules\Template::class, 'listed' => false],

];
