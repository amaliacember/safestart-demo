{{-- Step 5: new phone package advised or not, and what the client keeps. --}}
<h1 id="screen-title" tabindex="-1" class="text-[1.75rem] sm:text-[2rem]">{{ __('caseworker.advice.title') }}</h1>

<div @class([
    'mt-6 border-l-4 px-4 py-4',
    'border-amber bg-amber-wash' => $packageAdvised,
    'border-pine bg-pine-wash' => ! $packageAdvised,
])>
    <h2 class="text-[1.25rem]">{{ $packageAdvised ? __('caseworker.advice.advised') : __('caseworker.advice.not_advised') }}</h2>
    <p class="mt-1">{{ __('caseworker.advice.reasons.'.$packageReason) }}</p>
</div>

{{-- What moves to the new phone only matters when a new phone is advised. --}}
@if ($packageAdvised)
<h2 class="mt-8 text-[1.25rem]">{{ __('caseworker.advice.keeps_title') }}</h2>
<dl class="mt-3 divide-y divide-line border-t border-line">
    <div class="flex gap-3 py-3">
        <dt class="shrink-0">
            <svg class="mt-1 size-5 text-pine" viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path fill="currentColor" d="M6.2 11.6 2.6 8l1.3-1.3 2.3 2.3 5.9-5.9 1.3 1.3-7.2 7.2Z"/></svg>
            <span class="sr-only">{{ __('caseworker.answers.yes') }}</span>
        </dt>
        <dd>{{ __('caseworker.advice.moves') }}</dd>
    </div>
    <div class="flex gap-3 py-3">
        <dt class="shrink-0">
            <svg class="mt-1 size-5 text-alert" viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path fill="currentColor" d="M4.4 3 8 6.6 11.6 3 13 4.4 9.4 8l3.6 3.6-1.4 1.4L8 9.4 4.4 13 3 11.6 6.6 8 3 4.4 4.4 3Z"/></svg>
            <span class="sr-only">{{ __('caseworker.answers.no') }}</span>
        </dt>
        <dd>{{ __('caseworker.advice.stays') }}</dd>
    </div>
</dl>
@endif

<x-actions>
    @if ($packageAdvised)
        <button type="button" wire:click="continueFromAdvice" class="btn-primary">{{ __('caseworker.advice.next_order') }}</button>
    @else
        <button type="button" wire:click="continueFromAdvice" class="btn-primary">{{ __('caseworker.advice.close_case') }}</button>
    @endif
</x-actions>
