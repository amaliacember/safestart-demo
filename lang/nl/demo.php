<?php

// Alle Nederlandse tekst van de demo (Vlaams, informeel "je").
// Zelfde sleutels als lang/en/demo.php. Wil je een tekst aanpassen? Doe het hier.

return [

    'app_title' => 'SafeStart – casusdemo',
    'banner' => 'Demo – fictieve gegevens, er wordt niets bewaard',
    'back' => 'Terug',

    'intro' => [
        'title' => 'SafeStart casusdemo',
        'lead' => 'Je behandelt drie verzonnen casussen, zoals je dat in je werk met SafeStart zou doen.',
        'how' => [
            'Neem je tijd. Er is geen goed of fout.',
            'Denk hardop: zeg wat je ziet en wat je twijfels zijn.',
            'Na de drie casussen zie je je resultaten. Die kopieer of download je zelf.',
        ],
        'privacy' => 'Alle gegevens zijn verzonnen. Er wordt niets bewaard nadat je de pagina sluit.',
        'language_label' => 'Taal',
        'code_label' => 'Sessiecode',
        'code_hint' => 'Die krijg je van de interviewer, bijvoorbeeld C3.',
        'code_required' => 'Vul de sessiecode in die je van de interviewer kreeg.',
        'code_invalid' => 'Een sessiecode bestaat uit maximaal 10 letters, cijfers of streepjes.',
        'start' => 'Start de demo',
    ],

    'locales' => [
        'nl' => 'Nederlands',
        'en' => 'English',
    ],

    'progress' => [
        'case' => 'Casus :n van :total',
        'step' => 'stap :n van :total',
        'steps_label' => 'Stappen in deze casus',
        'not_needed' => 'niet nodig',
        'done' => 'klaar',
        'current' => 'huidige stap',
    ],

    'steps' => [
        'case-number' => 'Nieuwe casus',
        'check' => 'Snelle check',
        'route' => 'Route',
        'scan' => 'Scan',
        'advice' => 'Advies',
        'order' => 'Pakket bestellen',
    ],

    'scenarios' => [
        'A' => [
            'name' => 'Sara',
            'body' => 'Sara (34) is drie weken geleden bij haar partner weggegaan. Hij stuurt haar berichten over plekken waar ze geweest is, terwijl ze dat aan niemand verteld heeft. Voor ze vertrok, nam hij vaak haar gsm en kende hij haar pincode. Haar batterij is de laatste tijd snel leeg, en ze delen geen accounts, abonnementen of locatie-apps.',
        ],
        'B' => [
            'name' => 'Lena',
            'body' => 'Lena (27) heeft het account van haar ex geblokkeerd. Sindsdien krijgt ze elke dag berichten van nieuwe, onbekende accounts, die duidelijk van hem komen. Hij heeft haar gsm nooit in handen gehad, en ze delen geen accounts of abonnementen. In de berichten staat niets wat hij niet zou mogen weten, en haar gsm werkt normaal.',
        ],
        'C' => [
            'name' => 'Tom',
            'body' => 'Tom (41) woont sinds een maand apart van zijn ex. Hij zit nog op haar familieabonnement, en ze gebruiken samen één Google-account voor foto\'s. Zijn ex weet welke foto\'s hij neemt en waar hij geweest is, en begint daar zelf over. Of ze zijn code kende, weet Tom niet zeker; zijn gsm doet verder niets vreemds.',
        ],
    ],

    'scenario' => [
        'title' => 'Casus :letter: :name',
        'lead' => 'Lees de situatie. Klik daarna op "Start casus": vanaf dan loopt de tijd.',
        'start' => 'Start casus',
        'continue' => 'Verder met de casus',
    ],

    'notices' => [
        'case_closed' => 'Vorige casus afgesloten.',
        'case_closed_ordered' => 'Vorige casus afgesloten. Er is niets besteld: dit is een demo.',
    ],

    'case_number' => [
        'title' => 'Nieuwe casus',
        'lead' => 'Geef de casus een nummer. SafeStart bewaart alleen dit nummer, nooit een naam of andere gegevens van je cliënt.',
        'label' => 'Casusnummer',
        'hint' => 'Alleen letters, cijfers en streepjes.',
        'required' => 'Vul een casusnummer in.',
        'invalid' => 'Een casusnummer bestaat uit maximaal 20 letters, cijfers of streepjes.',
        'next' => 'Naar de snelle check',
    ],

    'check' => [
        'title' => 'Snelle check',
        'lead' => 'Beantwoord de zes vragen over je cliënt. Twijfel je? Kies "Weet niet".',
        'answer_required' => 'Kies een antwoord.',
        'errors' => 'Nog niet alle vragen zijn beantwoord.',
        'next' => 'Toon de route',
    ],

    'questions' => [
        'q1' => 'Weet de ex-partner dingen die hij of zij niet hoort te weten, zoals waar je cliënt was of wat je cliënt zei?',
        'q2' => 'Had de ex-partner de voorbije maanden de gsm, pincode of wachtwoorden van je cliënt?',
        'q3' => 'Krijgt je cliënt ongewenste berichten of oproepen, ook van nieuwe of onbekende accounts?',
        'q4' => 'Delen ze een locatie-app, familieabonnement of gsm-abonnement?',
        'q5' => 'Delen ze een Apple ID, Google-account of cloud (foto\'s, back-ups)?',
        'q6' => 'Is de gsm veranderd: batterij snel leeg, onbekende apps, instellingen die vanzelf wijzigen?',
    ],

    'answers' => [
        'yes' => 'Ja',
        'no' => 'Nee',
        'unknown' => 'Weet niet',
    ],

    'route' => [
        'title' => 'Route',
        'lead' => 'Op basis van je antwoorden geldt deze route:|Op basis van je antwoorden gelden deze routes:',
        'next_scan' => 'Naar de scan',
        'next_advice' => 'Naar het advies',
    ],

    'routes' => [
        'scan' => [
            'title' => 'Scan aangeraden',
            'why' => 'Er zijn tekenen van mogelijke spyware op de gsm.',
        ],
        'message' => [
            'title' => 'Intimidatie via berichten',
            'why' => 'Bewaar bewijs, blokkeer de afzenders en meld het.',
        ],
        'shared' => [
            'title' => 'Gedeelde accounts en locatie',
            'why' => 'Splits de accounts, wijzig wachtwoorden en verlaat familieabonnementen.',
        ],
        'none' => [
            'title' => 'Geen duidelijk digitaal signaal',
            'why' => 'Noteer het en doe de check later opnieuw.',
        ],
    ],

    'scan' => [
        'title' => 'Scan',
        'connect_lead' => 'Verbind de gsm van je cliënt met de kabel van de SafeStart-scanner en start de scan.',
        'connect_note' => 'In deze demo is er geen echte scan. Het resultaat ligt vooraf vast.',
        'start' => 'Start de scan',
        'running' => 'Scan bezig…',
        'progress_label' => 'Voortgang van de scan',
        'skip' => 'Overslaan',
        'found_title' => 'Twee verdachte apps gevonden',
        'found_lead' => 'Deze apps kunnen meekijken. Verwijder ze nog niet: ze kunnen als bewijs dienen.',
        'none_title' => 'Geen verdachte apps gevonden',
        'none_lead' => 'De scan vond niets. Ga verder met de andere routes.',
        'scripted' => 'Verzonnen resultaat, vooraf vastgelegd voor deze demo.',
        'next' => 'Naar het advies',
    ],

    'findings' => [
        'location' => 'Stuurt de locatie van de gsm door.',
        'messages' => 'Leest berichten en meldingen mee.',
    ],

    'advice' => [
        'title' => 'Advies',
        'advised' => 'Nieuw gsm-pakket aangeraden',
        'not_advised' => 'Geen nieuw gsm-pakket nodig',
        'reasons' => [
            'advised_scan' => 'De scan vond spyware. Een schone gsm met nieuwe accounts is de veiligste weg.',
            'advised_access' => 'De ex-partner had toegang tot de gsm of wachtwoorden, en er zijn gedeelde accounts.',
            'not_advised' => 'Volg de stappen van de route. Een nieuwe gsm is nu niet nodig.',
        ],
        'keeps_title' => 'Wat je cliënt meeneemt naar een nieuwe gsm',
        'moves' => 'Foto\'s en contacten gaan mee.',
        'stays' => 'Oude accounts gaan niet mee: je cliënt krijgt nieuwe.',
        'next_order' => 'Naar de bestelling',
        'close_case' => 'Sluit de casus af',
    ],

    'order' => [
        'title' => 'Pakket bestellen',
        'lead' => 'Een schone, opgeknapte gsm met nieuwe accounts, en begeleiding bij het overzetten.',
        'price_label' => 'Prijs',
        'price' => '€ :package per pakket, binnen een abonnement van € :subscription per jaar',
        'delivery_label' => 'Levering',
        'delivery' => 'Binnen :days werkdagen',
        'funding_label' => 'Betaald door',
        'funding' => 'Projectsubsidies van het CAW',
        'submit' => 'Bestellen (demo – er wordt niets besteld)',
        'skip' => 'Afsluiten zonder bestelling',
    ],

    'outcomes' => [
        'exact' => 'juist',
        'extra' => 'extra route',
        'missed' => 'route gemist',
    ],

    'results' => [
        'title' => 'Resultaten',
        'lead' => 'Kopieer je resultaten naar de chat van het gesprek, of download ze. Sluit daarna de sessie.',
        'col_case' => 'Casus',
        'col_time' => 'Tijd',
        'col_without_scan' => 'Zonder scan',
        'col_outcome' => 'Route',
        'col_back' => 'Terug',
        'unfinished' => 'niet afgerond',
        'total' => 'Totaal',
        'outcomes_line' => ':exact juist, :extra extra route, :missed gemist',
        'target' => 'Alle casussen in minder dan :minutes minuten: :answer',
        'yes' => 'ja',
        'no' => 'nee',
        'times_note' => 'Tijd in minuten:seconden. "Zonder scan" laat de wachttijd van de scan weg.',
        'text_label' => 'Tekst voor de chat',
        'copy' => 'Kopieer resultaten',
        'copied' => 'Gekopieerd. Plak het in de chat.',
        'copy_failed' => 'Kopiëren lukte niet. Selecteer de tekst hierboven en kopieer die zelf.',
        'download' => 'Download resultaten (JSON)',
        'close' => 'Sluit de sessie',
        'close_note' => 'Sluiten wist alles. Kopieer of download je resultaten eerst.',
        'text' => [
            'heading' => 'SafeStart demo – sessie :code',
            'case' => 'Casus :scenario (:name): :time (zonder scan :without_scan), route: :routes – :outcome, terugklikken: :back',
            'case_unfinished' => 'Casus :scenario (:name): niet afgerond',
            'total' => 'Totaal: :time (zonder scan :without_scan)',
            'outcomes' => 'Routes: :exact juist, :extra extra, :missed gemist',
            'back' => 'Terugklikken: :back',
            'target' => 'Alles onder :minutes minuten: :answer',
        ],
    ],

];
