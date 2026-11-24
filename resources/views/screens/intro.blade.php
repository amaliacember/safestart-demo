{{-- Intro: language, what to expect, session code. --}}
<div class="flex flex-wrap items-center justify-between gap-4">
    <h1 id="screen-title" tabindex="-1" class="text-[2rem] sm:text-[2.5rem]">{{ __('demo.intro.title') }}</h1>

    <div role="group" aria-label="{{ __('demo.intro.language_label') }}" class="flex rounded-[var(--radius-control)] border-2 border-ink">
        @foreach (config('safestart.locales') as $locale)
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

<p class="mt-6 max-w-prose font-serif text-[1.3125rem] leading-snug">{{ __('demo.intro.lead') }}</p>

<ul class="mt-6 max-w-prose space-y-2 pl-5 [list-style:square] marker:text-pine">
    @foreach (__('demo.intro.how') as $line)
        <li>{{ $line }}</li>
    @endforeach
</ul>

<p class="mt-6 max-w-prose border-l-4 border-amber bg-amber-wash px-4 py-3">{{ __('demo.intro.privacy') }}</p>

<form wire:submit="start" class="mt-10 border-t-2 border-ink pt-8" novalidate>
    <label for="session-code" class="field-label">{{ __('demo.intro.code_label') }}</label>
    <span id="session-code-hint" class="field-hint">{{ __('demo.intro.code_hint') }}</span>
    <input
        id="session-code"
        type="text"
        wire:model="sessionCode"
        class="field-input uppercase"
        maxlength="10"
        autocomplete="off"
        autocapitalize="characters"
        spellcheck="false"
        aria-describedby="session-code-hint @error('sessionCode') session-code-error @enderror"
        @error('sessionCode') aria-invalid="true" @enderror
    >
    @error('sessionCode')
        <p id="session-code-error" class="field-error">{{ $message }}</p>
    @enderror

    <button type="submit" class="btn-primary mt-8 w-full sm:w-auto">{{ __('demo.intro.start') }}</button>
</form>
