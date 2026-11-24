@props(['back' => true])

{{--
    The bottom bar of a case screen: the one main action on the right (full
    width on phones), the counted "Back" button on the left.
--}}
<div class="mt-8 flex flex-col-reverse gap-3 border-t border-line pt-6 sm:flex-row sm:items-center sm:justify-between">
    @if ($back)
        <button type="button" wire:click="back" class="btn-quiet self-start">
            <svg class="size-4" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
                <path fill="currentColor" d="M10.3 2.3 11.7 3.7 7.4 8l4.3 4.3-1.4 1.4L4.6 8l5.7-5.7Z"/>
            </svg>
            {{ __('demo.back') }}
        </button>
    @else
        <span></span>
    @endif

    <div class="flex flex-col gap-3 sm:flex-row-reverse sm:items-center">
        {{ $slot }}
    </div>
</div>
