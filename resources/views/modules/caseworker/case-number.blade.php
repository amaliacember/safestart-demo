{{-- Step 1: case number. Only a number is stored, never a name. --}}
<h1 id="screen-title" tabindex="-1" class="text-[1.75rem] sm:text-[2rem]">{{ __('caseworker.case_number.title') }}</h1>
<p class="mt-4 max-w-prose">{{ __('caseworker.case_number.lead') }}</p>

<form wire:submit="saveCaseNumber" class="mt-8" novalidate>
    <label for="case-number" class="field-label">{{ __('caseworker.case_number.label') }}</label>
    <span id="case-number-hint" class="field-hint">{{ __('caseworker.case_number.hint') }}</span>
    <input
        id="case-number"
        type="text"
        wire:model="caseNumber"
        class="field-input tabular-nums"
        maxlength="20"
        autocomplete="off"
        spellcheck="false"
        aria-describedby="case-number-hint @error('caseNumber') case-number-error @enderror"
        @error('caseNumber') aria-invalid="true" @enderror
    >
    @error('caseNumber')
        <p id="case-number-error" class="field-error">{{ $message }}</p>
    @enderror

    <x-actions>
        <button type="submit" class="btn-primary">{{ __('caseworker.case_number.next') }}</button>
    </x-actions>
</form>
