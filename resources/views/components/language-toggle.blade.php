{{--
    NL / EN switch. Calls switchLocale() on the Livewire component it sits in
    (the start page and every module intro have one). The language is kept in
    the session, never in the URL.
--}}
<div class="flex justify-end">
    <div role="group" aria-label="{{ __('demo.language_label') }}" class="flex rounded-[var(--radius-control)] border-2 border-ink">
        @foreach (config('safestart.demo.locales') as $locale)
            <button
                type="button"
                wire:click="switchLocale('{{ $locale }}')"
                lang="{{ $locale }}"
                aria-pressed="{{ app()->getLocale() === $locale ? 'true' : 'false' }}"
                @class([
                    'min-h-11 min-w-11 px-3 font-bold uppercase',
                    'bg-ink text-sheet' => app()->getLocale() === $locale,
                    'text-ink hover:bg-pine-wash' => app()->getLocale() !== $locale,
                ])
            >
                <span aria-hidden="true">{{ $locale }}</span>
                <span class="sr-only">{{ __('demo.locales.'.$locale) }}</span>
            </button>
        @endforeach
    </div>
</div>
