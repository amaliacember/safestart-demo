<?php

use App\Livewire\Modules\Template;
use App\Livewire\Results;
use Livewire\Livewire;

/*
| The template module start to finish. Copy this file for your own module:
| rename it, use your own class and screens, and check what your module
| saves in the session (session('safestart.log')).
*/

beforeEach(function () {
    $this->freezeTime();
});

/** The logged task of the template module. */
function templateTask(): array
{
    return session('safestart.log')['modules']['template']['tasks']['choose'];
}

it('opens at /m/template with the banner and the shared intro', function () {
    $this->get('/m/template')
        ->assertOk()
        ->assertSee('Demo – fictieve gegevens, er wordt niets bewaard')
        ->assertSee('Sjabloonmodule')
        ->assertSee('Sessiecode');
});

it('runs from the intro to the shared results', function () {
    $module = Livewire::test(Template::class)
        ->set('sessionCode', 'T1')
        ->call('start')
        ->assertSet('screen', 'task')
        ->assertSee('Kies een manier om af te spreken');

    // Nothing chosen yet: an error, and the screen stays.
    $module->call('next')->assertHasErrors('choice')->assertSet('screen', 'task');

    // Picking an option clears the error right away.
    $module->set('choice', 'meet')->assertHasNoErrors('choice');

    $this->travel(20)->seconds();
    $module->call('next')
        ->assertSet('screen', 'confirm')
        ->assertSee('Afspraak op kantoor');

    // Back is counted, then forward again with another choice.
    $module->call('back')->assertSet('screen', 'task');
    $module->set('choice', 'call')->call('next');

    $this->travel(10)->seconds();
    $module->call('finishModule')->assertRedirect('/results');

    expect(templateTask())
        ->finished_at->not->toBeNull()
        ->back_clicks->toBe(1)
        ->outcome->toBe('other')
        ->details->toBe(['choice' => 'call'])
        ->and(templateTask()['finished_at'] - templateTask()['started_at'])->toBe(30_000);

    Livewire::test(Results::class)
        ->assertSee('Sjabloonmodule')
        ->assertSee('Kies een manier om af te spreken: 0:30, andere keuze, terugklikken: 1');
});

it('marks the expected choice', function () {
    Livewire::test(Template::class)
        ->set('sessionCode', 'T1')->call('start')
        ->set('choice', 'meet')->call('next')
        ->call('finishModule');

    expect(templateTask()['outcome'])->toBe('expected');
});

it('rejects an option that is not in the config', function () {
    Livewire::test(Template::class)
        ->set('sessionCode', 'T1')->call('start')
        ->set('choice', 'fax')->call('next')
        ->assertHasErrors('choice')
        ->assertSet('screen', 'task');
});

it('ignores buttons of another screen', function () {
    Livewire::test(Template::class)
        ->set('sessionCode', 'T1')->call('start')
        ->call('finishModule')->call('back')
        ->assertSet('screen', 'task')
        ->assertNoRedirect();
});
