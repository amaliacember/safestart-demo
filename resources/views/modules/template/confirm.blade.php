{{-- Screen 2: check the choice, then finish. "Back" is counted in the results. --}}
<h1 id="screen-title" tabindex="-1" class="text-[1.75rem] sm:text-[2rem]">{{ __('template.confirm.title') }}</h1>

<p class="mt-4">{{ __('template.confirm.chosen') }}</p>
<p class="mt-1 border-l-4 border-pine bg-pine-wash px-4 py-3 font-bold text-pine">{{ __('template.options.'.$choice) }}</p>

<p class="mt-6 max-w-prose text-ink-soft">{{ __('template.confirm.lead') }}</p>

<x-actions>
    <button type="button" wire:click="finishModule" class="btn-primary">{{ __('template.confirm.finish') }}</button>
</x-actions>
