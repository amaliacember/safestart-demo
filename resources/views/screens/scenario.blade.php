{{-- Scenario card: the made-up situation. "Start case" starts the clock. --}}
@if ($notice)
    <p role="status" class="mb-8 border-l-4 border-pine bg-pine-wash px-4 py-3 font-bold text-pine">
        {{ __('demo.notices.'.$notice) }}
    </p>
@endif

<p class="font-bold text-ink-soft">{{ __('demo.progress.case', ['n' => $scenarioIndex + 1, 'total' => $caseCount]) }}</p>

<h1 id="screen-title" tabindex="-1" class="mt-2 text-[2rem] sm:text-[2.5rem]">
    {{ __('demo.scenario.title', ['letter' => $scenario, 'name' => __('demo.scenarios.'.$scenario.'.name')]) }}
</h1>

{{-- The story, set like a note in a case file. --}}
<div class="mt-8 rounded-[var(--radius-sheet)] border-2 border-ink bg-sheet px-5 py-6 sm:px-8 sm:py-8">
    <p class="case-note max-w-prose">{{ __('demo.scenarios.'.$scenario.'.body') }}</p>
</div>

<p class="mt-6 max-w-prose text-ink-soft">{{ __('demo.scenario.lead') }}</p>

<button type="button" wire:click="startCase" class="btn-primary mt-6 w-full sm:w-auto">
    {{ $caseStarted ? __('demo.scenario.continue') : __('demo.scenario.start') }}
</button>
