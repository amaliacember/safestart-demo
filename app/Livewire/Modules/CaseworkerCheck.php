<?php

namespace App\Livewire\Modules;

use App\Support\Caseworker\ScanSimulator;
use App\Support\Caseworker\Triage;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Locked;

/**
 * Module 1: the caseworker check. A small state machine over screens:
 *
 *   intro -> [ scenario -> case-number -> check -> route -> (scan) -> advice -> (order) ] x3 -> results
 *
 * Each made-up case is one task in the results. The intro, the state in the
 * session and the results screen come from the shared Module base class.
 *
 * Properties marked #[Locked] cannot be changed from the browser; only the
 * form fields (case number, answers) can, and those are validated before use.
 */
class CaseworkerCheck extends Module
{
    /** Screens that belong to a case: they show the step indicator and a back button. */
    private const CASE_SCREENS = ['case-number', 'check', 'route', 'scan', 'advice', 'order'];

    #[Locked]
    public int $scenarioIndex = 0;

    /** idle = not started, running = progress bar showing, done = result showing */
    #[Locked]
    public string $scanState = 'idle';

    /** Short message shown on the next screen, e.g. after closing a case. */
    #[Locked]
    public ?string $notice = null;

    public string $caseNumber = '';

    /** @var array<string, string|null> */
    public array $answers = [];

    // ---------------------------------------------------------------------
    // Module settings
    // ---------------------------------------------------------------------

    public static function key(): string
    {
        return 'caseworker';
    }

    /** One task per made-up case: A, B, C. */
    public static function tasks(): array
    {
        return array_keys(config('safestart.caseworker.scenarios'));
    }

    public static function outcomes(): array
    {
        return [
            Triage::OUTCOME_EXACT => 'good',
            Triage::OUTCOME_EXTRA => 'warn',
            Triage::OUTCOME_MISSED => 'bad',
        ];
    }

    public static function targetMinutes(): ?int
    {
        return config('safestart.caseworker.timing.target_total_minutes');
    }

    /** The scan's progress bar is left out in the "without scan" column. */
    public static function waits(): bool
    {
        return true;
    }

    public static function taskLabel(string $task): string
    {
        return __('caseworker.scenario.title', ['letter' => $task, 'name' => __("caseworker.scenarios.{$task}.name")]);
    }

    /** The route(s) the participant reached, for the copy text. */
    public static function taskDetail(array $task): ?string
    {
        $routes = $task['details']['routes'] ?? [];

        if ($routes === []) {
            return null;
        }

        return __('caseworker.results.routes', [
            'routes' => implode(', ', array_map(fn ($route) => __("caseworker.routes.{$route}.title"), $routes)),
        ]);
    }

    protected function remember(): array
    {
        return ['scenarioIndex', 'scanState', 'notice', 'caseNumber', 'answers'];
    }

    protected function freshState(): void
    {
        $this->scenarioIndex = 0;
        $this->notice = null;
        $this->resetCaseFields();
    }

    protected function afterIntro(): void
    {
        $this->screen = 'scenario';
    }

    // ---------------------------------------------------------------------
    // Case flow
    // ---------------------------------------------------------------------

    /**
     * "Start case" on the scenario card. Starts the timer (only the first time).
     */
    public function startCase(): void
    {
        if ($this->screen !== 'scenario') {
            return;
        }

        if (! $this->log()->hasStarted(self::key(), $this->scenario())) {
            $this->resetCaseFields();
            $this->startTask($this->scenario());
        }

        $this->notice = null;
        $this->go('case-number');
    }

    public function saveCaseNumber(): void
    {
        if ($this->screen !== 'case-number') {
            return;
        }

        $this->validate(
            ['caseNumber' => ['required', 'regex:'.config('safestart.caseworker.case_number_pattern')]],
            [
                'caseNumber.required' => __('caseworker.case_number.required'),
                'caseNumber.regex' => __('caseworker.case_number.invalid'),
            ],
        );

        $this->record($this->scenario(), ['case_number' => $this->caseNumber]);
        $this->go('check');
    }

    public function submitCheck(): void
    {
        if ($this->screen !== 'check') {
            return;
        }

        $questions = config('safestart.caseworker.questions');

        $rules = [];
        $messages = [];
        foreach ($questions as $question) {
            $rules["answers.{$question}"] = ['required', Rule::in(config('safestart.caseworker.answers'))];
            $messages["answers.{$question}.required"] = __('caseworker.check.answer_required');
            $messages["answers.{$question}.in"] = __('caseworker.check.answer_required');
        }

        $this->validate($rules, $messages);

        // Only keep the known questions, whatever the browser sent.
        $answers = Arr::only($this->answers, $questions);

        $triage = Triage::fromConfig();
        $routes = $triage->routes($answers);

        // Saved again if the participant comes back and changes the answers.
        $this->record($this->scenario(), ['answers' => $answers, 'routes' => $routes]);
        $this->setOutcome($this->scenario(), $triage->routeOutcome($this->scenario(), $routes));

        // Changed answers can remove the scan; then an old result is irrelevant.
        if (! in_array('scan', $routes, true)) {
            $this->scanState = 'idle';
        }

        $this->go('route');
    }

    /**
     * Runs when an answer is picked: clears that question's error message.
     * The answer itself is only checked and used on submit.
     */
    public function updatedAnswers(mixed $value, string $question): void
    {
        $this->resetErrorBag("answers.{$question}");
    }

    public function continueFromRoute(): void
    {
        if ($this->screen === 'route') {
            $this->go($this->scanApplies() ? 'scan' : 'advice');
        }
    }

    public function startScan(): void
    {
        if ($this->screen !== 'scan') {
            return;
        }

        $this->waitStarted($this->scenario());
        $this->scanState = 'running';
    }

    /**
     * Called by the progress bar when it reaches 100 % or is skipped.
     */
    public function finishScan(): void
    {
        if ($this->scanState !== 'running') {
            return;
        }

        $this->waitEnded($this->scenario());
        $this->record($this->scenario(), ['scan_found' => $this->scanResult()['found']]);

        $this->scanState = 'done';
    }

    public function continueFromScan(): void
    {
        if ($this->screen === 'scan' && $this->scanState === 'done') {
            $this->go('advice');
        }
    }

    public function continueFromAdvice(): void
    {
        if ($this->screen !== 'advice') {
            return;
        }

        $this->packageAdvised() ? $this->go('order') : $this->closeCase('case_closed');
    }

    public function orderPackage(): void
    {
        if ($this->screen !== 'order') {
            return;
        }

        $this->record($this->scenario(), ['package_advised' => true, 'package_ordered' => true]);
        $this->closeCase('case_closed_ordered');
    }

    public function closeWithoutOrder(): void
    {
        if ($this->screen === 'order') {
            $this->closeCase('case_closed');
        }
    }

    /**
     * The back button on every case screen. Each click is counted.
     */
    public function back(): void
    {
        $previous = match ($this->screen) {
            'case-number' => 'scenario',
            'check' => 'case-number',
            'route' => 'check',
            'scan' => 'route',
            'advice' => $this->scanApplies() ? 'scan' : 'route',
            'order' => 'advice',
            default => null,
        };

        if ($previous === null) {
            return;
        }

        $this->countBack($this->scenario());

        // Leaving the scan mid-animation cancels it; it starts over next time.
        if ($this->scanState === 'running') {
            $this->scanState = 'idle';
        }

        $this->go($previous);
    }

    // ---------------------------------------------------------------------
    // Rendering
    // ---------------------------------------------------------------------

    protected function renderScreen(): View
    {
        $onCase = in_array($this->screen, self::CASE_SCREENS, true);

        return view('modules.caseworker.page', [
            'scenario' => $this->scenario(),
            'caseCount' => count(self::tasks()),
            'caseStarted' => $this->log()->hasStarted(self::key(), $this->scenario()),
            'onCase' => $onCase,
            'steps' => $onCase ? $this->steps() : [],
            'currentStep' => $onCase ? array_search($this->screen, config('safestart.caseworker.steps'), true) + 1 : null,
            'routes' => in_array($this->screen, ['route', 'scan', 'advice', 'order'], true) ? $this->routes() : [],
            'scanResult' => $this->screen === 'scan' ? $this->scanResult() : null,
            'packageAdvised' => in_array($this->screen, ['advice', 'order'], true) ? $this->packageAdvised() : null,
            'packageReason' => $this->screen === 'advice' ? $this->packageReason() : null,
        ]);
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    private function go(string $screen): void
    {
        $this->enterScreen($screen, $this->scenario());

        // Record the advice when it is shown; ordering updates it later.
        if ($screen === 'advice') {
            $this->record($this->scenario(), ['package_advised' => $this->packageAdvised(), 'package_ordered' => false]);
        }
    }

    private function closeCase(string $notice): void
    {
        $this->finishTask($this->scenario());

        if ($this->scenarioIndex + 1 < count(self::tasks())) {
            $this->notice = $notice;
            $this->scenarioIndex++;
            $this->screen = 'scenario';
        } else {
            $this->finish();
        }
    }

    /**
     * The six steps with their state for the indicator:
     * done, current, todo or skipped ("not needed").
     */
    private function steps(): array
    {
        $order = config('safestart.caseworker.steps');
        $current = array_search($this->screen, $order, true);
        $routeIndex = array_search('route', $order, true);
        $adviceIndex = array_search('advice', $order, true);

        $steps = [];
        foreach ($order as $i => $key) {
            $skipped = ($key === 'scan' && $current >= $routeIndex && ! $this->scanApplies())
                || ($key === 'order' && $current >= $adviceIndex && ! $this->packageAdvised());

            $steps[] = [
                'label' => __("caseworker.steps.{$key}"),
                'state' => match (true) {
                    $skipped => 'skipped',
                    $i === $current => 'current',
                    $i < $current => 'done',
                    default => 'todo',
                },
            ];
        }

        return $steps;
    }

    private function routes(): array
    {
        return $this->log()->detail(self::key(), $this->scenario(), 'routes', []);
    }

    private function scanApplies(): bool
    {
        return in_array('scan', $this->routes(), true);
    }

    private function scanResult(): array
    {
        return ScanSimulator::fromConfig()->resultFor($this->scenario());
    }

    private function scanFoundSomething(): bool
    {
        return $this->scanApplies() && $this->scanState === 'done' && $this->scanResult()['found'];
    }

    private function packageAdvised(): bool
    {
        $answers = $this->log()->detail(self::key(), $this->scenario(), 'answers');

        return $answers !== null && Triage::fromConfig()->packageAdvised($answers, $this->scanFoundSomething());
    }

    /**
     * Why the package is (not) advised, as a translation key suffix.
     */
    private function packageReason(): string
    {
        if (! $this->packageAdvised()) {
            return 'not_advised';
        }

        return $this->scanFoundSomething() ? 'advised_scan' : 'advised_access';
    }

    private function resetCaseFields(): void
    {
        $this->caseNumber = config('safestart.caseworker.defaults.case_number');
        $this->answers = array_fill_keys(config('safestart.caseworker.questions'), null);
        $this->scanState = 'idle';
    }

    private function scenario(): string
    {
        return self::tasks()[$this->scenarioIndex];
    }
}
