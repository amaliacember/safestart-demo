{{--
    Screen 1: the task. Every text comes from lang/<locale>/template.php,
    the options from config/safestart/template.php.
    The h1 has id="screen-title" and tabindex="-1": after each screen change
    the browser code moves focus to it (resources/js/app.js).
--}}
<h1 id="screen-title" tabindex="-1" class="text-[1.75rem] sm:text-[2rem]">{{ __('template.task.title') }}</h1>

{{-- A made-up situation, set like a note in a case file. --}}
<p class="case-note mt-4 max-w-prose">{{ __('template.task.situation') }}</p>

<form wire:submit="next" class="mt-6" novalidate>
    <fieldset @error('choice') aria-describedby="choice-error" @enderror>
        <legend class="field-label">{{ __('template.task.label') }}</legend>

        <div class="mt-3 grid gap-2">
            @foreach ($options as $option)
                <label class="group relative">
                    <input type="radio" name="choice" value="{{ $option }}" wire:model.live="choice" class="peer sr-only" @error('choice') aria-invalid="true" @enderror>
                    <span class="flex min-h-12 cursor-pointer items-center rounded-[var(--radius-control)] border-2 border-edge bg-sheet px-4 font-bold text-ink
                                 peer-checked:border-pine peer-checked:bg-pine-wash peer-checked:text-pine
                                 peer-focus-visible:outline-3 peer-focus-visible:outline-offset-3 peer-focus-visible:outline-pine
                                 group-hover:border-pine">
                        {{ __('template.options.'.$option) }}
                    </span>
                </label>
            @endforeach
        </div>

        @error('choice')
            <p id="choice-error" class="field-error">{{ $message }}</p>
        @enderror
    </fieldset>

    {{-- No back button on the first screen of the task. --}}
    <x-actions :back="false">
        <button type="submit" class="btn-primary">{{ __('template.task.next') }}</button>
    </x-actions>
</form>
