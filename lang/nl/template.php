<?php

// Sjabloonmodule: alle Nederlandse tekst (Vlaams, informeel "je").
// Zelfde sleutels als lang/en/template.php. Elke tekst op je schermen komt
// hier, nooit in de Blade-views.

return [

    // Titel in het browsertabblad.
    'title' => 'SafeStart – sjabloonmodule',

    // De gedeelde intro toont deze tekst, boven het veld voor de sessiecode.
    'intro' => [
        'title' => 'Sjabloonmodule',
        'lead' => 'Een voorbeeldmodule met één verzonnen taak, om te kopiëren als je je eigen module bouwt.',
        'how' => [
            'Kies een van de drie opties.',
            'Controleer je keuze en rond af.',
            'Daarna zie je het gedeelde resultatenscherm.',
        ],
    ],

    // Namen van de taken in de resultaten.
    'tasks' => [
        'choose' => 'Kies een manier om af te spreken',
    ],

    'progress' => [
        'steps_label' => 'Stappen in deze module',
    ],

    'steps' => [
        'task' => 'Kiezen',
        'confirm' => 'Controleren',
    ],

    'task' => [
        'title' => 'Kies een manier om af te spreken',
        'situation' => 'Een verzonnen collega vraagt hoe je een eerste gesprek met een nieuwe cliënt plant. Kies wat jij zou doen.',
        'label' => 'Jouw keuze',
        'required' => 'Kies een van de opties.',
        'next' => 'Volgende',
    ],

    'options' => [
        'call' => 'Telefoongesprek',
        'meet' => 'Afspraak op kantoor',
        'letter' => 'Brief met de post',
    ],

    'confirm' => [
        'title' => 'Controleer je keuze',
        'chosen' => 'Je koos:',
        'lead' => 'Wil je iets wijzigen? Ga terug. Anders rond je de module af.',
        'finish' => 'Rond de module af',
    ],

    // Labels van de uitkomsten in de resultaten (sleutels uit Template::outcomes()).
    'outcomes' => [
        'expected' => 'zoals verwacht',
        'other' => 'andere keuze',
    ],

];
