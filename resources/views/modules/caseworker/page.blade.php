{{--
    Module 1's frame around every screen. Case screens get the case-file
    layout: step index in the margin (laptop) or on top (phone), and the
    screen itself on one sheet with the case number on its tab.
--}}
<div data-screen="{{ $screen }}">
    @if ($onCase)
        <x-module-sheet
            :tab="__('caseworker.scenario.title', ['letter' => $scenario, 'name' => __('caseworker.scenarios.'.$scenario.'.name')])"
            :tab-note="$caseNumber"
        >
            <x-slot:aside>
                <x-step-indicator
                    :steps="$steps"
                    :heading="__('caseworker.progress.case', ['n' => $scenarioIndex + 1, 'total' => $caseCount])"
                    :label="__('caseworker.progress.steps_label')"
                />
            </x-slot:aside>

            @include('modules.caseworker.'.$screen)
        </x-module-sheet>
    @else
        <main class="mx-auto max-w-2xl px-4 pb-16 pt-8 sm:px-6 sm:pt-14">
            @include('modules.caseworker.'.$screen)
        </main>
    @endif
</div>
