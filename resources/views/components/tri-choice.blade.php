@props(['question', 'number', 'label', 'options'])

{{--
    One question with a few fixed answers (e.g. Yes / No / Don't know).

    question: the field key; the value lands in $answers[question]
    number:   shown in front of the question
    label:    the question text
    options:  ['yes' => 'Yes', 'no' => 'No', ...] (up to three fit on a phone)

    Real radio buttons in a fieldset, so keyboard (arrow keys) and screen
    readers work as usual. wire:model.live sends each pick to the server, so
    a "Choose an answer" error can disappear as soon as the question is
    answered (see CaseworkerCheck::updatedAnswers).
--}}
@php
    $field = "answers.{$question}";
    $errorId = "error-{$question}";
@endphp

<fieldset
    class="border-t border-line py-5 first:border-t-0"
    @error($field) aria-describedby="{{ $errorId }}" @enderror
>
    <legend class="float-left flex w-full gap-3 font-bold leading-snug">
        <span class="w-5 shrink-0 font-serif text-ink-soft tabular-nums">{{ $number }}</span>
        <span>{{ $label }}</span>
    </legend>

    <div class="clear-both grid grid-cols-3 gap-2 pt-3 sm:pl-8">
        @foreach ($options as $answer => $answerLabel)
            <label class="group relative">
                <input
                    type="radio"
                    name="{{ $question }}"
                    value="{{ $answer }}"
                    wire:model.live="{{ $field }}"
                    class="peer sr-only"
                    @error($field) aria-invalid="true" @enderror
                >
                <span class="flex min-h-12 cursor-pointer items-center justify-center border-2 border-edge bg-sheet px-2 text-center font-bold leading-tight text-ink
                             peer-checked:border-pine peer-checked:bg-pine-wash peer-checked:text-pine
                             peer-focus-visible:outline-3 peer-focus-visible:outline-offset-3 peer-focus-visible:outline-pine
                             group-hover:border-pine rounded-[var(--radius-control)]">
                    {{-- A tick, so the choice does not rely on colour alone. --}}
                    <svg class="mr-1.5 hidden size-4 shrink-0 group-has-checked:block" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                        <path fill="currentColor" d="M6.2 11.6 2.6 8l1.3-1.3 2.3 2.3 5.9-5.9 1.3 1.3-7.2 7.2Z"/>
                    </svg>
                    {{ $answerLabel }}
                </span>
            </label>
        @endforeach
    </div>

    @error($field)
        <p id="{{ $errorId }}" class="field-error sm:pl-8">{{ $message }}</p>
    @enderror
</fieldset>
