<?php

use App\Livewire\StartPage;
use App\Support\Modules;
use Livewire\Livewire;

it('lists the five modules with the banner', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('Demo – fictieve gegevens, er wordt niets bewaard')
        ->assertSeeInOrder([
            'Check door de hulpverlener',
            'Overdracht van de gsm en gegevens',
            'Bewijskluis',
            'Kant van de organisatie',
            'Gsm-donor en refurbpartner',
        ])
        // The template is reachable, but not listed.
        ->assertDontSee('Sjabloonmodule');
});

it('links modules that exist and marks the others as coming soon', function () {
    $this->get('/')
        ->assertSee('href="'.route('module.caseworker').'"', escape: false)
        ->assertDontSee('href="'.route('module.handover').'"', escape: false)
        ->assertSee('Binnenkort');
});

it('shows a coming soon page for a module that is not merged yet', function (string $key) {
    expect(Modules::all()[$key]['available'])->toBeFalse();

    $this->get("/m/{$key}")
        ->assertOk()
        ->assertSee('Deze module komt binnenkort')
        ->assertSee(__("demo.modules.{$key}.title"));
})->with(['handover', 'vault', 'organisation', 'donor']);

it('switches language through the session', function () {
    Livewire::test(StartPage::class)->call('switchLocale', 'en')->assertRedirect('/');

    expect(session('locale'))->toBe('en');

    $this->get('/')->assertSee('<html lang="en">', escape: false)->assertSee('Caseworker check');
});

it('treats a module as available only once its class exists', function () {
    config(['safestart.modules.extra' => ['number' => 6, 'class' => 'App\\Livewire\\Modules\\DoesNotExist']]);

    expect(Modules::all()['extra']['available'])->toBeFalse()
        ->and(Modules::classFor('extra'))->toBeNull()
        ->and(Modules::classFor('caseworker'))->not->toBeNull();
});
