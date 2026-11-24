<?php

// All English text of the demo. Same keys as lang/nl/demo.php.

return [

    'app_title' => 'SafeStart – case demo',
    'banner' => 'Demo – made-up data, nothing is kept',
    'back' => 'Back',

    'intro' => [
        'title' => 'SafeStart case demo',
        'lead' => 'You will handle three made-up cases, the way you would with SafeStart in your work.',
        'how' => [
            'Take your time. There are no right or wrong answers.',
            'Think out loud: say what you see and what you are unsure about.',
            'After the three cases you see your results. You copy or download them yourself.',
        ],
        'privacy' => 'All data is made up. Nothing is kept after you close the page.',
        'language_label' => 'Language',
        'code_label' => 'Session code',
        'code_hint' => 'The interviewer gives you this, for example C3.',
        'code_required' => 'Enter the session code the interviewer gave you.',
        'code_invalid' => 'A session code is up to 10 letters, digits or dashes.',
        'start' => 'Start the demo',
    ],

    'locales' => [
        'nl' => 'Nederlands',
        'en' => 'English',
    ],

    'progress' => [
        'case' => 'Case :n of :total',
        'step' => 'step :n of :total',
        'steps_label' => 'Steps in this case',
        'not_needed' => 'not needed',
        'done' => 'done',
        'current' => 'current step',
    ],

    'steps' => [
        'case-number' => 'New case',
        'check' => 'Quick check',
        'route' => 'Route',
        'scan' => 'Scan',
        'advice' => 'Advice',
        'order' => 'Order package',
    ],

    'scenarios' => [
        'A' => [
            'name' => 'Sara',
            'body' => 'Sara (34) left her partner three weeks ago. He sends her messages about places she has been, although she told no one. Before she left, he often took her phone and knew her PIN. Lately her battery drains fast, and they share no accounts, plans or location apps.',
        ],
        'B' => [
            'name' => 'Lena',
            'body' => 'Lena (27) blocked her ex\'s account. Since then she gets messages every day from new, unknown accounts that clearly come from him. He never had her phone, and they share no accounts or plans. The messages contain nothing he should not know, and her phone works normally.',
        ],
        'C' => [
            'name' => 'Tom',
            'body' => 'Tom (41) has lived apart from his ex for a month. He is still on her family plan, and they use one Google account for photos. His ex knows which photos he takes and where he has been, and brings it up herself. Tom is not sure whether she knew his code; his phone does nothing else unusual.',
        ],
    ],

    'scenario' => [
        'title' => 'Case :letter: :name',
        'lead' => 'Read the situation. Then select "Start case": the clock starts from there.',
        'start' => 'Start case',
        'continue' => 'Continue the case',
    ],

    'notices' => [
        'case_closed' => 'Previous case closed.',
        'case_closed_ordered' => 'Previous case closed. Nothing was ordered: this is a demo.',
    ],

    'case_number' => [
        'title' => 'New case',
        'lead' => 'Give the case a number. SafeStart only stores this number, never a name or other details of your client.',
        'label' => 'Case number',
        'hint' => 'Letters, digits and dashes only.',
        'required' => 'Enter a case number.',
        'invalid' => 'A case number is up to 20 letters, digits or dashes.',
        'next' => 'Go to the quick check',
    ],

    'check' => [
        'title' => 'Quick check',
        'lead' => 'Answer the six questions about your client. Not sure? Choose "Don\'t know".',
        'answer_required' => 'Choose an answer.',
        'errors' => 'Not all questions are answered yet.',
        'next' => 'Show the route',
    ],

    'questions' => [
        'q1' => 'Does the ex-partner know things they should not know, such as where your client was or what they said?',
        'q2' => 'Did the ex-partner have your client\'s phone, PIN or passwords in recent months?',
        'q3' => 'Does your client get unwanted messages or calls, also from new or unknown accounts?',
        'q4' => 'Do they share a location app, family plan or phone contract?',
        'q5' => 'Do they share an Apple ID, Google account or cloud (photos, backups)?',
        'q6' => 'Has the phone changed: battery drains fast, unknown apps, settings that change by themselves?',
    ],

    'answers' => [
        'yes' => 'Yes',
        'no' => 'No',
        'unknown' => 'Don\'t know',
    ],

    'route' => [
        'title' => 'Route',
        'lead' => 'Based on your answers, this route applies:|Based on your answers, these routes apply:',
        'next_scan' => 'Go to the scan',
        'next_advice' => 'Go to the advice',
    ],

    'routes' => [
        'scan' => [
            'title' => 'Scan advised',
            'why' => 'There are signs of possible spyware on the phone.',
        ],
        'message' => [
            'title' => 'Harassment by message',
            'why' => 'Save evidence, block the senders and report it.',
        ],
        'shared' => [
            'title' => 'Shared accounts and location',
            'why' => 'Separate the accounts, change passwords and leave family plans.',
        ],
        'none' => [
            'title' => 'No clear digital sign',
            'why' => 'Note it and do the check again later.',
        ],
    ],

    'scan' => [
        'title' => 'Scan',
        'connect_lead' => 'Connect your client\'s phone to the SafeStart scanner cable and start the scan.',
        'connect_note' => 'There is no real scan in this demo. The result is fixed in advance.',
        'start' => 'Start the scan',
        'running' => 'Scanning…',
        'progress_label' => 'Scan progress',
        'skip' => 'Skip',
        'found_title' => 'Two suspicious apps found',
        'found_lead' => 'These apps can watch the phone. Do not remove them yet: they may serve as evidence.',
        'none_title' => 'No suspicious apps found',
        'none_lead' => 'The scan found nothing. Continue with the other routes.',
        'scripted' => 'Made-up result, fixed in advance for this demo.',
        'next' => 'Go to the advice',
    ],

    'findings' => [
        'location' => 'Forwards the phone\'s location.',
        'messages' => 'Reads messages and notifications.',
    ],

    'advice' => [
        'title' => 'Advice',
        'advised' => 'New phone package advised',
        'not_advised' => 'No new phone package needed',
        'reasons' => [
            'advised_scan' => 'The scan found spyware. A clean phone with new accounts is the safest way forward.',
            'advised_access' => 'The ex-partner had access to the phone or passwords, and accounts are shared.',
            'not_advised' => 'Follow the route above. A new phone is not needed now.',
        ],
        'keeps_title' => 'What your client takes to a new phone',
        'moves' => 'Photos and contacts move over.',
        'stays' => 'Old accounts do not move: your client gets new ones.',
        'next_order' => 'Go to the order',
        'close_case' => 'Close the case',
    ],

    'order' => [
        'title' => 'Order package',
        'lead' => 'A clean, refurbished phone with new accounts, and guidance with moving data over.',
        'price_label' => 'Price',
        'price' => '€:package per package, as part of a €:subscription per year subscription',
        'delivery_label' => 'Delivery',
        'delivery' => 'Within :days working days',
        'funding_label' => 'Paid by',
        'funding' => 'CAW project subsidies',
        'submit' => 'Order (demo – nothing is ordered)',
        'skip' => 'Close without ordering',
    ],

    'outcomes' => [
        'exact' => 'exact',
        'extra' => 'extra route',
        'missed' => 'route missed',
    ],

    'results' => [
        'title' => 'Results',
        'lead' => 'Copy your results into the chat of the call, or download them. Then close the session.',
        'col_case' => 'Case',
        'col_time' => 'Time',
        'col_without_scan' => 'Without scan',
        'col_outcome' => 'Route',
        'col_back' => 'Back',
        'unfinished' => 'not finished',
        'total' => 'Total',
        'outcomes_line' => ':exact exact, :extra extra route, :missed missed',
        'target' => 'All cases in under :minutes minutes: :answer',
        'yes' => 'yes',
        'no' => 'no',
        'times_note' => 'Time in minutes:seconds. "Without scan" leaves out the scan\'s waiting time.',
        'text_label' => 'Text for the chat',
        'copy' => 'Copy results',
        'copied' => 'Copied. Paste it into the chat.',
        'copy_failed' => 'Copying did not work. Select the text above and copy it yourself.',
        'download' => 'Download results (JSON)',
        'close' => 'Close the session',
        'close_note' => 'Closing erases everything. Copy or download your results first.',
        'text' => [
            'heading' => 'SafeStart demo – session :code',
            'case' => 'Case :scenario (:name): :time (without scan :without_scan), route: :routes – :outcome, back clicks: :back',
            'case_unfinished' => 'Case :scenario (:name): not finished',
            'total' => 'Total: :time (without scan :without_scan)',
            'outcomes' => 'Routes: :exact exact, :extra extra, :missed missed',
            'back' => 'Back clicks: :back',
            'target' => 'All under :minutes minutes: :answer',
        ],
    ],

];
