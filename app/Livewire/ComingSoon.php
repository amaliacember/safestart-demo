<?php

namespace App\Livewire;

use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * GET /m/<key> for a module that is not merged yet (see routes/web.php).
 */
class ComingSoon extends Component
{
    #[Locked]
    public string $module = '';

    public function mount(string $module): void
    {
        $this->module = $module;
    }

    public function render(): View
    {
        return view('livewire.coming-soon')
            ->title(__("demo.modules.{$this->module}.title").' – '.__('demo.app_title'));
    }
}
