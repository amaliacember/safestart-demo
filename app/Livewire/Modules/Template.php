<?php

namespace App\Livewire\Modules;

use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * The template module: copy this to start your own.
 *
 *   intro (shared) -> task -> confirm -> results (shared)
 *
 * One timed task: the participant picks one of three options, checks the
 * choice and finishes. Open it at /m/template.
 *
 * To make your own module, copy these files and rename "template" /
 * "Template" everywhere (see the README, "Making your module"):
 *   app/Livewire/Modules/Template.php
 *   config/safestart/template.php
 *   lang/nl/template.php, lang/en/template.php
 *   resources/views/modules/template/*.blade.php
 *   tests/Feature/Modules/TemplateTest.php
 *   e2e/template.spec.ts
 * Use the key and class name your module already has in
 * config/safestart/modules.php (e.g. handover / PhoneHandover). Once your
 * class exists, /m/<key> shows your module instead of "coming soon".
 */
class Template extends Module
{
    /** The task this module times. Name your own tasks after what people do. */
    private const TASK = 'choose';

    /** A form field: the browser may change it, so it is validated on submit. */
    public ?string $choice = null;

    // ---------------------------------------------------------------------
    // Module settings (read by the shared start page and results screen)
    // ---------------------------------------------------------------------

    /** Must match the key in config/safestart/modules.php. */
    public static function key(): string
    {
        return 'template';
    }

    /** Every timed task, in order. Labels: lang/<locale>/template.php "tasks". */
    public static function tasks(): array
    {
        return [self::TASK];
    }

    /** The outcomes a task can end with, and their colour in the results. */
    public static function outcomes(): array
    {
        return ['expected' => 'good', 'other' => 'warn'];
    }

    /** Properties to keep when the page reloads (besides $screen). */
    protected function remember(): array
    {
        return ['choice'];
    }

    protected function freshState(): void
    {
        $this->choice = null;
    }

    /** "Start" on the shared intro was pressed. */
    protected function afterIntro(): void
    {
        $this->startTask(self::TASK);
        $this->enterScreen('task', self::TASK);
    }

    // ---------------------------------------------------------------------
    // One public method per button
    // ---------------------------------------------------------------------

    public function next(): void
    {
        // Each action checks it is on the right screen, so a crafted
        // request cannot skip ahead.
        if ($this->screen !== 'task') {
            return;
        }

        $this->validate(
            ['choice' => ['required', Rule::in(config('safestart.template.options'))]],
            ['choice.required' => __('template.task.required'), 'choice.in' => __('template.task.required')],
        );

        // Saved in the participant's session, shown in the download.
        $this->record(self::TASK, ['choice' => $this->choice]);
        $this->setOutcome(self::TASK, $this->choice === config('safestart.template.expected') ? 'expected' : 'other');

        $this->enterScreen('confirm', self::TASK);
    }

    /**
     * Runs when an option is picked (wire:model.live): clears the error.
     * The choice itself is only checked and used in next().
     */
    public function updatedChoice(): void
    {
        $this->resetErrorBag('choice');
    }

    /** Back is counted in the results. */
    public function back(): void
    {
        if ($this->screen !== 'confirm') {
            return;
        }

        $this->countBack(self::TASK);
        $this->enterScreen('task', self::TASK);
    }

    public function finishModule(): void
    {
        if ($this->screen !== 'confirm') {
            return;
        }

        $this->finishTask(self::TASK);
        $this->finish();
    }

    // ---------------------------------------------------------------------
    // Rendering
    // ---------------------------------------------------------------------

    protected function renderScreen(): View
    {
        $order = ['task', 'confirm'];
        $current = array_search($this->screen, $order, true);

        $steps = [];
        foreach ($order as $i => $screen) {
            $steps[] = [
                'label' => __("template.steps.{$screen}"),
                'state' => $i < $current ? 'done' : ($i === $current ? 'current' : 'todo'),
            ];
        }

        return view('modules.template.page', [
            'steps' => $steps,
            'options' => config('safestart.template.options'),
        ]);
    }
}
