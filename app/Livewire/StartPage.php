<?php

namespace App\Livewire;

use App\Support\Modules;
use Illuminate\View\View;
use Livewire\Component;

/**
 * GET /: the start page. Language choice and the list of the five modules.
 * Modules that are not merged yet show "coming soon".
 */
class StartPage extends Component
{
    /**
     * Switches language. A full page reload follows, so <html lang> changes too.
     */
    public function switchLocale(string $locale): void
    {
        if (! in_array($locale, config('safestart.demo.locales'), true)) {
            return;
        }

        session(['locale' => $locale]);

        $this->redirectRoute('start');
    }

    public function render(): View
    {
        return view('livewire.start-page', [
            'modules' => Modules::listed(),
        ])->title(__('demo.app_title'));
    }
}
