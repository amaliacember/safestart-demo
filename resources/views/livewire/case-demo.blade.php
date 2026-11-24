{{--
    The frame around every screen. Case screens get the case-file layout:
    step index in the margin (laptop) or on top (phone), and the screen
    itself on one sheet with the case number on its tab.
--}}
<div data-screen="{{ $screen }}">
    @if ($onCase)
        <div class="mx-auto max-w-5xl px-4 pb-16 pt-6 sm:px-6 lg:grid lg:grid-cols-[14rem_1fr] lg:gap-12 lg:pt-10">
            <aside class="mb-6 lg:mb-0">
                <div class="lg:sticky lg:top-8">
                    <x-step-indicator
                        :steps="$steps"
                        :current-step="$currentStep"
                        :case-index="$scenarioIndex + 1"
                        :case-count="$caseCount"
                    />
                </div>
            </aside>

            <main class="min-w-0">
                {{-- The file tab: which case and which number this file has. --}}
                <div class="flex">
                    <p class="rounded-t-[var(--radius-sheet)] border-2 border-b-0 border-ink bg-sheet px-4 pb-1.5 pt-2 font-serif text-[0.9375rem] text-ink">
                        {{ __('demo.scenario.title', ['letter' => $scenario, 'name' => __('demo.scenarios.'.$scenario.'.name')]) }}
                        <span class="ml-2 font-sans font-bold tabular-nums">{{ $caseNumber }}</span>
                    </p>
                </div>
                <div class="rounded-[var(--radius-sheet)] rounded-tl-none border-2 border-ink bg-sheet px-5 py-7 sm:px-8 sm:py-9">
                    @include('screens.'.$screen)
                </div>
            </main>
        </div>
    @else
        <main class="mx-auto max-w-2xl px-4 pb-16 pt-8 sm:px-6 sm:pt-14">
            @include('screens.'.$screen)
        </main>
    @endif
</div>
