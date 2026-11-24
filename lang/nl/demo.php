<?php

// Gedeelde Nederlandse tekst (Vlaams, informeel "je"): banner, startpagina,
// intro, resultaten. Zelfde sleutels als lang/en/demo.php. Gedeeld bestand:
// pas het alleen aan nadat je het de groep gezegd hebt. Elke module heeft
// haar eigen tekst in lang/nl/<module>.php.

return [

    'app_title' => 'SafeStart – demo',
    'banner' => 'Demo – fictieve gegevens, er wordt niets bewaard',
    'back' => 'Terug',
    'language_label' => 'Taal',

    'locales' => [
        'nl' => 'Nederlands',
        'en' => 'English',
    ],

    'start' => [
        'title' => 'SafeStart demo',
        'lead' => 'SafeStart helpt hulpverleningsorganisaties om cliënten te ondersteunen van wie de gsm misbruikt wordt door een ex-partner. Deze demo heeft vijf korte delen. De interviewer zegt je welk deel je opent.',
        'privacy' => 'Alle gegevens zijn verzonnen. Er wordt niets bewaard nadat je de pagina sluit.',
        'modules_label' => 'Modules',
        'module_number' => 'Module :n',
        'coming_soon' => 'Binnenkort',
    ],

    // Titels en korte samenvattingen voor de startpagina en de resultaten.
    'modules' => [
        'caseworker' => [
            'title' => 'Check door de hulpverlener',
            'summary' => 'Behandel drie verzonnen casussen met de snelle check, de scan en het advies.',
        ],
        'handover' => [
            'title' => 'Overdracht van de gsm en gegevens',
            'summary' => 'Overloop de overdrachtsdag met een verzonnen cliënt: wat meegaat, nieuwe accounts, de oude gsm.',
        ],
        'vault' => [
            'title' => 'Bewijskluis',
            'summary' => 'Bewaar intimiderende berichten als bewijs en exporteer een bewijspakket.',
        ],
        'organisation' => [
            'title' => 'Kant van de organisatie',
            'summary' => 'Bekijk als CAW-verantwoordelijke het abonnement, het budget en de kostencalculator.',
        ],
        'donor' => [
            'title' => 'Gsm-donor en refurbpartner',
            'summary' => 'Bekijk hoe een bedrijf gebruikte gsm\'s schenkt en wat ermee gebeurt.',
        ],
        'template' => [
            'title' => 'Sjabloonmodule',
            'summary' => 'Een voorbeeldmodule om te kopiëren.',
        ],
    ],

    'coming_soon' => [
        'title' => 'Deze module komt binnenkort',
        'lead' => 'Aan dit deel van de demo wordt nog gewerkt. Vraag de interviewer naar de juiste link.',
        'to_start' => 'Naar de startpagina',
    ],

    // De gedeelde intro voor elke module.
    'intro' => [
        'privacy' => 'Alle gegevens zijn verzonnen. Er wordt niets bewaard nadat je de pagina sluit.',
        'code_label' => 'Sessiecode',
        'code_hint' => 'Die krijg je van de interviewer, bijvoorbeeld C3.',
        'code_required' => 'Vul de sessiecode in die je van de interviewer kreeg.',
        'code_invalid' => 'Een sessiecode bestaat uit maximaal 10 letters, cijfers of streepjes.',
        'start' => 'Start de demo',
    ],

    'progress' => [
        'step' => 'stap :n van :total',
        'not_needed' => 'niet nodig',
        'done' => 'klaar',
        'current' => 'huidige stap',
    ],

    // Het gedeelde resultatenscherm. Een module kan elke sleutel onder
    // "results" vervangen door dezelfde sleutel in haar eigen taalbestand.
    'results' => [
        'title' => 'Resultaten',
        'lead' => 'Kopieer je resultaten naar de chat van het gesprek, of download ze. Sluit daarna de sessie.',
        'module' => 'Module :n: :title',
        'col_task' => 'Taak',
        'col_time' => 'Tijd',
        'col_without_wait' => 'Zonder wachttijd',
        'col_outcome' => 'Uitkomst',
        'col_back' => 'Terug',
        'unfinished' => 'niet afgerond',
        'total' => 'Totaal',
        'target' => 'Alle taken in minder dan :minutes minuten: :answer',
        'yes' => 'ja',
        'no' => 'nee',
        'times_note' => 'Tijd in minuten:seconden.',
        'text_label' => 'Tekst voor de chat',
        'copy' => 'Kopieer resultaten',
        'copied' => 'Gekopieerd. Plak het in de chat.',
        'copy_failed' => 'Kopiëren lukte niet. Selecteer de tekst hierboven en kopieer die zelf.',
        'download' => 'Download resultaten (JSON)',
        'other_module' => 'Doe nog een module',
        'close' => 'Sluit de sessie',
        'close_note' => 'Sluiten wist alles. Kopieer of download je resultaten eerst.',
        'empty_title' => 'Nog geen resultaten',
        'empty_lead' => 'Je resultaten verschijnen hier nadat je een module afgerond hebt.',
        'to_start' => 'Naar de startpagina',
        'text' => [
            'heading' => 'SafeStart demo – sessie :code',
            'unfinished' => ':task: niet afgerond',
            'without_wait' => 'zonder wachttijd :time',
            'back' => 'terugklikken: :back',
            'total' => 'Totaal: :time',
            'outcomes' => 'Uitkomsten: :list',
        ],
    ],

];
