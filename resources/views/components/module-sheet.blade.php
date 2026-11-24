@props(['tab' => null, 'tabNote' => null])

{{--
    The case-file frame for a module's screens: an index in the margin
    (laptop) or on top (phone), and the screen itself on one sheet with an
    optional file tab above it (tab: the title, tabNote: a number after it).

    <x-module-sheet :tab="'Case A: Sara'">
        <x-slot:aside> <x-step-indicator ... /> </x-slot:aside>
        ...the screen...
    </x-module-sheet>
--}}
<div class="mx-auto max-w-5xl px-4 pb-16 pt-6 sm:px-6 lg:grid lg:grid-cols-[14rem_1fr] lg:gap-12 lg:pt-10">
    <aside class="mb-6 lg:mb-0">
        <div class="lg:sticky lg:top-8">
            {{ $aside ?? '' }}
        </div>
    </aside>

    <main class="min-w-0">
        @if ($tab !== null)
            <div class="flex">
                <p class="rounded-t-[var(--radius-sheet)] border-2 border-b-0 border-ink bg-sheet px-4 pb-1.5 pt-2 font-serif text-[0.9375rem] text-ink">
                    {{ $tab }}
                    @if ($tabNote)
                        <span class="ml-2 font-sans font-bold tabular-nums">{{ $tabNote }}</span>
                    @endif
                </p>
            </div>
        @endif
        <div @class([
            'rounded-[var(--radius-sheet)] border-2 border-ink bg-sheet px-5 py-7 sm:px-8 sm:py-9',
            'rounded-tl-none' => $tab !== null,
        ])>
            {{ $slot }}
        </div>
    </main>
</div>
