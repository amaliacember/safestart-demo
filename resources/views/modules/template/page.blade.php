{{--
    The frame around the template's screens: the case-file sheet with the
    step index, and the current screen inside it. data-screen lets the
    browser code (and the Playwright tests) see which screen is showing.
--}}
<div data-screen="{{ $screen }}">
    <x-module-sheet :tab="__('template.intro.title')">
        <x-slot:aside>
            <x-step-indicator :steps="$steps" heading="" :label="__('template.progress.steps_label')" />
        </x-slot:aside>

        @include('modules.template.'.$screen)
    </x-module-sheet>
</div>
