{{--
    The shared intro before every module: language, what to expect, session
    code. Shared file. The title, lead and "how" lines come from the module's
    own lang file (<module>.intro.*); the rest is shared (demo.intro.*).
--}}
<div data-screen="intro">
    <main class="mx-auto max-w-2xl px-4 pb-16 pt-8 sm:px-6 sm:pt-14">
        {{-- Language first and top right, so on phones it does not wedge between title and text. --}}
        <x-language-toggle />

        <h1 id="screen-title" tabindex="-1" class="mt-4 text-[2rem] sm:text-[2.5rem]">{{ __($module.'.intro.title') }}</h1>

        <p class="mt-6 max-w-prose font-serif text-[1.3125rem] leading-snug">{{ __($module.'.intro.lead') }}</p>

        <ul class="mt-6 max-w-prose space-y-2 pl-5 [list-style:square] marker:text-pine">
            @foreach (__($module.'.intro.how') as $line)
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
    </main>
</div>
