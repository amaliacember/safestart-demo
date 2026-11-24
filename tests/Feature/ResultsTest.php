<?php

use App\Livewire\Modules\Template;
use App\Livewire\Results;
use App\Support\SessionLog;
use Livewire\Livewire;

/*
| The shared results screen and download, which every module ends on.
| These use the template module, so they do not depend on module 1.
*/

function finishTemplate(string $code = 'R7', string $choice = 'meet'): void
{
    Livewire::test(Template::class)
        ->set('sessionCode', $code)->call('start')
        ->set('choice', $choice)->call('next')
        ->call('finishModule');
}

it('says there are no results yet when nothing was done', function () {
    $this->get('/results')
        ->assertOk()
        ->assertSee('Demo – fictieve gegevens, er wordt niets bewaard')
        ->assertSee('Nog geen resultaten');
});

it('shows a finished module with copy text, download and close', function () {
    finishTemplate('R7');

    $this->get('/results')
        ->assertOk()
        ->assertSee('Sjabloonmodule')
        ->assertSee('zoals verwacht')
        ->assertSee('SafeStart demo – sessie R7')
        ->assertSee('Kopieer resultaten')
        ->assertSee('Download resultaten (JSON)')
        ->assertSee('Sluit de sessie')
        // The template has no waiting and no time target.
        ->assertDontSee('Zonder wachttijd')
        ->assertDontSee('minder dan');
});

it('shows every module done in the same session, with one session code', function () {
    $log = SessionLog::begin('C9', 'nl', 0);
    $log->startTask('caseworker', 'A', 0);
    $log->setOutcome('caseworker', 'A', 'exact');
    $log->finishTask('caseworker', 'A', 60_000);
    session(['safestart.log' => $log->toArray()]);

    // The second module keeps the code from the first.
    Livewire::test(Template::class)->assertSet('sessionCode', 'C9')->call('start')
        ->set('choice', 'meet')->call('next')->call('finishModule');

    Livewire::test(Results::class)
        ->assertSeeInOrder(['Module 1: Check door de hulpverlener', 'Sjabloonmodule'])
        ->assertSee('Casus A: Sara: 1:00 (zonder scan 1:00), juist, terugklikken: 0')
        ->assertSee('Casus B: Lena: niet afgerond');
});

it('downloads the results as a JSON file named after the session code', function () {
    finishTemplate('R7');

    $this->get('/results/download')
        ->assertOk()
        ->assertHeader('Content-Disposition', 'attachment; filename="safestart-demo-R7.json"')
        ->assertHeader('Cache-Control', 'no-store, private')
        ->assertJsonPath('demo', true)
        ->assertJsonPath('session_code', 'R7')
        ->assertJsonPath('modules.0.module', 'template')
        ->assertJsonPath('modules.0.tasks.0.outcome', 'expected')
        ->assertJsonPath('modules.0.tasks.0.details.choice', 'meet')
        ->assertJsonPath('modules.0.outcomes', ['expected' => 1, 'other' => 0]);
});

it('returns 404 for the download when there is no session', function () {
    $this->get('/results/download')->assertNotFound();
});

it('wipes everything on Close', function () {
    finishTemplate();
    session(['locale' => 'en']);

    Livewire::test(Results::class)->call('closeSession')->assertRedirect('/');

    expect(session('safestart'))->toBeNull()
        ->and(session('locale'))->toBeNull();
});

it('skips a module in the log that does not exist in this copy of the code', function () {
    $log = SessionLog::begin('C9', 'nl', 0);
    $log->startTask('handover', 'move', 0);
    session(['safestart.log' => $log->toArray()]);

    Livewire::test(Results::class)->assertSee('Nog geen resultaten');
});
