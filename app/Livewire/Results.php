<?php

namespace App\Livewire;

use App\Support\Modules;
use App\Support\ResultsText;
use App\Support\SessionLog;
use Illuminate\View\View;
use Livewire\Component;

/**
 * GET /results: the shared results screen every module ends on.
 *
 * Shows one block per module the participant did in this session, with the
 * plain text for "Copy results", the download link and "Close", which wipes
 * the whole session.
 */
class Results extends Component
{
    /**
     * "Close": wipes the whole session, so nothing about this run is left.
     */
    public function closeSession(): void
    {
        session()->invalidate();
        session()->regenerateToken();

        $this->redirectRoute('start');
    }

    public function render(): View
    {
        $results = Modules::results(new SessionLog(session('safestart.log', [])));

        return view('livewire.results', [
            'results' => $results,
            'resultsText' => $results['modules'] === [] ? null : ResultsText::make($results),
        ])->title(__('demo.results.title').' – '.__('demo.app_title'));
    }
}
