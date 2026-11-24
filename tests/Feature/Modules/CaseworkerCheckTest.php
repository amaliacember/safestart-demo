<?php

use App\Livewire\Modules\CaseworkerCheck;
use App\Livewire\Results;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

/*
| Module 1 start to finish, through Livewire's test helper. The clock is
| moved with $this->travel(), so case times are exact.
*/

const REFERENCE = [
    'A' => ['q1' => 'yes', 'q2' => 'yes', 'q3' => 'no', 'q4' => 'no', 'q5' => 'no', 'q6' => 'yes'],
    'B' => ['q1' => 'no', 'q2' => 'no', 'q3' => 'yes', 'q4' => 'no', 'q5' => 'no', 'q6' => 'no'],
    'C' => ['q1' => 'yes', 'q2' => 'unknown', 'q3' => 'no', 'q4' => 'yes', 'q5' => 'yes', 'q6' => 'no'],
];

beforeEach(function () {
    $this->freezeTime();
});

function startDemo(string $code = 'C3')
{
    return Livewire::test(CaseworkerCheck::class)
        ->set('sessionCode', $code)
        ->call('start')
        ->assertSet('screen', 'scenario');
}

/**
 * Plays one case from the scenario card to the route screen.
 * Each step takes 10 seconds.
 */
function playToRoute($demo, array $answers)
{
    $demo->call('startCase')->assertSet('screen', 'case-number');
    test()->travel(10)->seconds();
    $demo->call('saveCaseNumber')->assertSet('screen', 'check');

    foreach ($answers as $question => $answer) {
        $demo->set("answers.{$question}", $answer);
    }

    test()->travel(10)->seconds();

    return $demo->call('submitCheck')->assertHasNoErrors()->assertSet('screen', 'route');
}

/** Runs the scan with a full 3.5 second animation. */
function runScan($demo)
{
    $demo->call('continueFromRoute')->assertSet('screen', 'scan')
        ->call('startScan')->assertSet('scanState', 'running');
    test()->travel(3500)->milliseconds();

    return $demo->call('finishScan')->assertSet('scanState', 'done');
}

/** Plays all three cases with the reference answers, up to the results. */
function playAllCases(string $code = 'C3')
{
    $demo = startDemo($code);
    playToRoute($demo, REFERENCE['A']);
    $demo->call('back')->call('submitCheck');   // one back click on A
    runScan($demo)->call('continueFromScan')->call('continueFromAdvice')->call('orderPackage');
    playToRoute($demo, REFERENCE['B'])->call('continueFromRoute')->call('continueFromAdvice');
    playToRoute($demo, REFERENCE['C']);
    runScan($demo)->call('continueFromScan')->call('continueFromAdvice');

    return $demo;
}

/** The logged tasks of this module: ['A' => [...], 'B' => [...]]. */
function demoLog(): array
{
    return session('safestart.log')['modules']['caseworker']['tasks'];
}

describe('intro', function () {
    // The banner is in the page layout, so these go through a real request.
    it('shows the demo banner and starts in Dutch', function () {
        $this->get('/m/caseworker')
            ->assertOk()
            ->assertSee('<html lang="nl">', escape: false)
            ->assertSee('Demo – fictieve gegevens, er wordt niets bewaard')
            ->assertSee('SafeStart casusdemo')
            ->assertSee('Sessiecode');
    });

    it('requires a valid session code', function (string $code, string $message) {
        Livewire::test(CaseworkerCheck::class)
            ->set('sessionCode', $code)
            ->call('start')
            ->assertHasErrors('sessionCode')
            ->assertSee($message)
            ->assertSet('screen', 'intro');
    })->with([
        'empty' => ['', 'Vul de sessiecode in'],
        'too long' => ['ABCDEFGHIJK', 'maximaal 10'],
        'spaces' => ['C 3', 'maximaal 10'],
        'markup' => ['<b>x</b>', 'maximaal 10'],
    ]);

    it('stores the language in the session, never in the URL', function () {
        Livewire::test(CaseworkerCheck::class)
            ->call('switchLocale', 'en')
            ->assertRedirect('/m/caseworker');

        expect(session('locale'))->toBe('en');
    });

    it('ignores an unknown language', function () {
        Livewire::test(CaseworkerCheck::class)->call('switchLocale', 'fr')->assertNoRedirect();

        expect(session('locale'))->toBeNull();
    });

    it('shows English text and <html lang="en"> after switching', function () {
        $this->withSession(['locale' => 'en'])->get('/m/caseworker')
            ->assertSee('<html lang="en">', escape: false)
            ->assertSee('Demo – made-up data, nothing is kept')
            ->assertSee('Session code');
    });
});

describe('scenario A (Sara)', function () {
    it('goes scan -> two apps -> package -> order, and times the case', function () {
        $demo = startDemo()->assertSee('Sara');

        playToRoute($demo, REFERENCE['A'])->assertSee('Scan aangeraden');
        runScan($demo)->assertSee('Kalvo Sync')->assertSee('Pernik System Service');

        test()->travel(10)->seconds();
        $demo->call('continueFromScan')->assertSet('screen', 'advice')->assertSee('Nieuw gsm-pakket aangeraden');
        $demo->call('continueFromAdvice')->assertSet('screen', 'order')->assertSee('€ 350')->assertSee('€ 1.800');
        $demo->call('orderPackage')
            ->assertSet('screen', 'scenario')
            ->assertSet('scenarioIndex', 1)
            ->assertSee('Er is niets besteld');

        $case = demoLog()['A'];
        expect($case['finished_at'] - $case['started_at'])->toBe(33_500)
            ->and($case['wait_ms'])->toBe(3_500)
            ->and($case['outcome'])->toBe('exact')
            ->and($case['details'])->toMatchArray([
                'case_number' => 'DEMO-0417',
                'routes' => ['scan'],
                'scan_found' => true,
                'package_advised' => true,
                'package_ordered' => true,
            ]);
    });

    it('can close the case without ordering', function () {
        $demo = startDemo();
        playToRoute($demo, REFERENCE['A']);
        runScan($demo)->call('continueFromScan')->call('continueFromAdvice');

        $demo->call('closeWithoutOrder')->assertSet('screen', 'scenario');

        expect(demoLog()['A']['details'])->package_advised->toBeTrue()->package_ordered->toBeFalse();
    });

    it('counts only the watched part when the scan is skipped', function () {
        $demo = startDemo();
        playToRoute($demo, REFERENCE['A'])->call('continueFromRoute')->call('startScan');
        test()->travel(800)->milliseconds();
        $demo->call('finishScan');

        expect(demoLog()['A']['wait_ms'])->toBe(800);
    });
});

describe('scenario B (Lena)', function () {
    it('has the message route only and no package', function () {
        $demo = startDemo();
        playToRoute($demo, REFERENCE['A']);
        runScan($demo)->call('continueFromScan')->call('continueFromAdvice')->call('orderPackage');

        playToRoute($demo, REFERENCE['B'])
            ->assertSee('Intimidatie via berichten')
            ->assertDontSee('Scan aangeraden');

        $demo->call('continueFromRoute')
            ->assertSet('screen', 'advice')
            ->assertSee('Geen nieuw gsm-pakket nodig')
            // The step indicator marks scan and order as skipped.
            ->assertSee('niet nodig');

        $demo->call('continueFromAdvice')
            ->assertSet('screen', 'scenario')
            ->assertSet('scenarioIndex', 2);

        expect(demoLog()['B'])
            ->outcome->toBe('exact')
            ->wait_ms->toBe(0)
            ->and(demoLog()['B']['details'])
            ->routes->toBe(['message'])
            ->package_advised->toBeFalse();
    });
});

describe('scenario C (Tom)', function () {
    function playToTom()
    {
        $demo = startDemo();
        playToRoute($demo, REFERENCE['A']);
        runScan($demo)->call('continueFromScan')->call('continueFromAdvice')->call('orderPackage');
        playToRoute($demo, REFERENCE['B'])->call('continueFromRoute')->call('continueFromAdvice');

        return $demo->assertSet('scenarioIndex', 2)->assertSee('Tom');
    }

    it('with q2 "don\'t know": shared plus a scan (extra), nothing found, no package, then the results', function () {
        $demo = playToTom();
        playToRoute($demo, REFERENCE['C'])->assertSee('Gedeelde accounts en locatie')->assertSee('Scan aangeraden');
        runScan($demo)->assertSee('Geen verdachte apps gevonden');
        $demo->call('continueFromScan')->assertSee('Geen nieuw gsm-pakket nodig');
        $demo->call('continueFromAdvice')->assertRedirect('/results');

        expect(demoLog()['C'])->outcome->toBe('extra')
            ->and(demoLog()['C']['details']['package_advised'])->toBeFalse();
    });

    it('with q2 no: shared only (exact), no scan', function () {
        $demo = playToTom();
        playToRoute($demo, [...REFERENCE['C'], 'q2' => 'no'])->assertDontSee('Scan aangeraden');
        $demo->call('continueFromRoute')->assertSet('screen', 'advice');

        expect(demoLog()['C']['outcome'])->toBe('exact');
    });

    it('with q2 yes: package advised', function () {
        $demo = playToTom();
        playToRoute($demo, [...REFERENCE['C'], 'q2' => 'yes']);
        runScan($demo)->call('continueFromScan')
            ->assertSee('Nieuw gsm-pakket aangeraden')
            ->assertSee('toegang tot de gsm');
    });
});

describe('quick check', function () {
    it('requires every question and clears an error once answered', function () {
        $demo = startDemo()->call('startCase')->call('saveCaseNumber');

        $demo->set('answers.q1', 'yes')
            ->call('submitCheck')
            ->assertHasErrors(['answers.q2', 'answers.q6'])
            ->assertHasNoErrors('answers.q1')
            ->assertSee('Nog niet alle vragen zijn beantwoord')
            ->assertSet('screen', 'check');

        $demo->set('answers.q2', 'no')->assertHasNoErrors('answers.q2')->assertHasErrors('answers.q3');
    });

    it('rejects an answer that is not yes, no or don\'t know', function () {
        $demo = startDemo()->call('startCase')->call('saveCaseNumber');
        foreach (REFERENCE['A'] as $q => $a) {
            $demo->set("answers.{$q}", $a);
        }

        $demo->set('answers.q3', 'maybe')->call('submitCheck')->assertHasErrors('answers.q3');
    });

    it('ignores extra keys sent by the browser', function () {
        $demo = startDemo();
        $demo->set('answers.q99', 'yes');
        playToRoute($demo, REFERENCE['A']);

        expect(array_keys(demoLog()['A']['details']['answers']))->toBe(['q1', 'q2', 'q3', 'q4', 'q5', 'q6']);
    });
});

describe('case number', function () {
    it('is pre-filled and validated', function (string $value) {
        startDemo()->call('startCase')
            ->assertSet('caseNumber', 'DEMO-0417')
            ->set('caseNumber', $value)
            ->call('saveCaseNumber')
            ->assertHasErrors('caseNumber')
            ->assertSet('screen', 'case-number');
    })->with(['', 'Jan Janssens', 'DEMO-0417-0417-0417-X', 'a@b.be']);
});

describe('back button', function () {
    it('goes back one step and counts each click', function () {
        $demo = startDemo();
        playToRoute($demo, REFERENCE['A']);

        $demo->call('back')->assertSet('screen', 'check')
            ->call('back')->assertSet('screen', 'case-number')
            ->call('back')->assertSet('screen', 'scenario')
            ->assertSee('Verder met de casus');

        expect(demoLog()['A']['back_clicks'])->toBe(3);
    });

    it('keeps the timer running when the case is continued', function () {
        $demo = startDemo()->call('startCase');
        $started = demoLog()['A']['started_at'];

        test()->travel(5)->seconds();
        $demo->call('back')->call('startCase');

        expect(demoLog()['A']['started_at'])->toBe($started);
    });

    it('cancels a running scan animation', function () {
        $demo = startDemo();
        playToRoute($demo, REFERENCE['A'])->call('continueFromRoute')->call('startScan');
        test()->travel(1)->seconds();

        $demo->call('back')->assertSet('screen', 'route')->assertSet('scanState', 'idle');

        expect(demoLog()['A']['wait_ms'])->toBe(1_000);
    });

    it('goes from advice back to route when there was no scan', function () {
        $demo = startDemo();
        playToRoute($demo, [...REFERENCE['A'], 'q2' => 'no', 'q6' => 'no', 'q1' => 'no', 'q3' => 'yes'])
            ->call('continueFromRoute')->assertSet('screen', 'advice')
            ->call('back')->assertSet('screen', 'route');
    });
});

describe('protection', function () {
    it('does not let the browser change the screen', function () {
        startDemo()->set('screen', 'order');
    })->throws(CannotUpdateLockedPropertyException::class);

    it('does not let the browser skip the scan result', function () {
        $demo = startDemo();
        playToRoute($demo, REFERENCE['A'])->call('continueFromRoute');

        $demo->call('continueFromScan')->assertSet('screen', 'scan');
        $demo->call('finishScan')->assertSet('scanState', 'idle');
    });

    it('ignores actions meant for another screen', function () {
        startDemo()
            ->call('orderPackage')->call('closeWithoutOrder')->call('submitCheck')->call('continueFromAdvice')
            ->assertSet('screen', 'scenario');
    });
});

describe('session', function () {
    it('resumes where the participant was after a reload', function () {
        $demo = startDemo('R1');
        playToRoute($demo, REFERENCE['A']);

        Livewire::test(CaseworkerCheck::class)
            ->assertSet('screen', 'route')
            ->assertSet('sessionCode', 'R1')
            ->assertSee('Scan aangeraden');
    });

    it('starts at the intro again after the module is finished', function () {
        playAllCases();

        Livewire::test(CaseworkerCheck::class)
            ->assertSet('screen', 'intro')
            ->assertSet('sessionCode', 'C3');
    });
});

describe('results', function () {
    it('ends on the shared results with per-case times, outcomes and the copy text', function () {
        playAllCases('C3')->assertRedirect('/results');

        Livewire::test(Results::class)
            ->assertSee('Resultaten')
            ->assertSee('Module 1: Check door de hulpverlener')
            ->assertSee('Zonder scan')
            ->assertSee('extra route')
            ->assertSee('2 juist, 1 extra route, 0 route gemist')
            ->assertSee('SafeStart demo – sessie C3')
            ->assertSee('Casus A: Sara: 0:24 (zonder scan 0:20), route: Scan aangeraden, juist, terugklikken: 1', escape: false)
            ->assertSee('Totaal: 1:07 (zonder scan 1:00), terugklikken: 1')
            ->assertSee('Alle casussen in minder dan 10 minuten: ja');
    });

    it('downloads the module\'s results as JSON', function () {
        playAllCases('C3');

        $this->get('/results/download')
            ->assertOk()
            ->assertHeader('Content-Disposition', 'attachment; filename="safestart-demo-C3.json"')
            ->assertJsonPath('modules.0.module', 'caseworker')
            ->assertJsonPath('modules.0.outcomes', ['exact' => 2, 'extra' => 1, 'missed' => 0])
            ->assertJsonPath('modules.0.tasks.0.details.routes', ['scan'])
            ->assertJsonPath('modules.0.tasks.0.back_clicks', 1)
            ->assertJsonPath('modules.0.under_time_target', true);
    });
});
