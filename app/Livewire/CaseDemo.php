<?php

namespace App\Livewire;

use App\Support\ResultsText;
use App\Support\ScanSimulator;
use App\Support\SessionLog;
use App\Support\Triage;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The whole demo in one component: a small state machine over screens.
 *
 *   intro -> [ scenario -> case-number -> check -> route -> (scan) -> advice -> (order) ] x3 -> results
 *
 * State is kept in the participant's Laravel session ("safestart.state" and
 * "safestart.log"), so a page reload continues where they were. Nothing is
 * stored anywhere else.
 *
 * Properties marked #[Locked] cannot be changed from the browser; only the
 * form fields (session code, case number, answers) can, and those are
 * validated before use.
 */
class CaseDemo extends Component
{
    /** Screens that belong to a case: they show the step indicator and a back button. */
    private const CASE_SCREENS = ['case-number', 'check', 'route', 'scan', 'advice', 'order'];

    #[Locked]
    public string $screen = 'intro';

    #[Locked]
    public int $scenarioIndex = 0;

    /** idle = not started, running = progress bar showing, done = result showing */
    #[Locked]
    public string $scanState = 'idle';

    /** Short message shown on the next screen, e.g. after closing a case. */
    #[Locked]
    public ?string $notice = null;

    public string $sessionCode = '';

    public string $caseNumber = '';

    /** @var array<string, string|null> */
    public array $answers = [];

    /** Set during "Close" only. Private, so it is never sent to the browser. */
    private bool $closing = false;

    public function mount(): void
    {
        $state = session('safestart.state');

        if (is_array($state)) {
            foreach (['screen', 'scenarioIndex', 'scanState', 'notice', 'sessionCode', 'caseNumber', 'answers'] as $key) {
                $this->{$key} = $state[$key] ?? $this->{$key};
            }
        } else {
            $this->resetCaseFields();
        }
    }

    /**
     * Runs at the end of every request: save the state so a reload resumes.
     */
    public function dehydrate(): void
    {
        // After "Close" the session is wiped; do not write the state back.
        if ($this->closing) {
            return;
        }

        session(['safestart.state' => [
            'screen' => $this->screen,
            'scenarioIndex' => $this->scenarioIndex,
            'scanState' => $this->scanState,
            'notice' => $this->notice,
            'sessionCode' => $this->sessionCode,
            'caseNumber' => $this->caseNumber,
            'answers' => $this->answers,
        ]]);
    }

    // ---------------------------------------------------------------------
    // Intro
    // ---------------------------------------------------------------------

    /**
     * Switches language. A full page reload follows, so <html lang> changes too.
     */
    public function switchLocale(string $locale): void
    {
        if (! in_array($locale, config('safestart.locales'), true)) {
            return;
        }

        session(['locale' => $locale]);

        $this->redirect('/');
    }

    public function start(): void
    {
        $this->validate(
            ['sessionCode' => ['required', 'regex:'.config('safestart.patterns.session_code')]],
            [
                'sessionCode.required' => __('demo.intro.code_required'),
                'sessionCode.regex' => __('demo.intro.code_invalid'),
            ],
        );

        $this->saveLog(SessionLog::begin($this->sessionCode, App::getLocale(), $this->now()));
        $this->scenarioIndex = 0;
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
        $log = $this->log();

        if (! $log->hasStarted($this->scenario())) {
            $this->resetCaseFields();
            $log->startCase($this->scenario(), $this->now());
            $this->saveLog($log);
        }

        $this->notice = null;
        $this->go('case-number');
    }

    public function saveCaseNumber(): void
    {
        $this->validate(
            ['caseNumber' => ['required', 'regex:'.config('safestart.patterns.case_number')]],
            [
                'caseNumber.required' => __('demo.case_number.required'),
                'caseNumber.regex' => __('demo.case_number.invalid'),
            ],
        );

        $this->withLog(fn (SessionLog $log) => $log->recordCaseNumber($this->scenario(), $this->caseNumber));
        $this->go('check');
    }

    public function submitCheck(): void
    {
        $questions = config('safestart.questions');

        $rules = [];
        $messages = [];
        foreach ($questions as $question) {
            $rules["answers.{$question}"] = ['required', Rule::in(config('safestart.answers'))];
            $messages["answers.{$question}.required"] = __('demo.check.answer_required');
            $messages["answers.{$question}.in"] = __('demo.check.answer_required');
        }

        $this->validate($rules, $messages);

        // Only keep the known questions, whatever the browser sent.
        $answers = Arr::only($this->answers, $questions);

        $triage = Triage::fromConfig();
        $routes = $triage->routes($answers);

        $this->withLog(fn (SessionLog $log) => $log->recordCheck(
            $this->scenario(),
            $answers,
            $routes,
            $triage->routeOutcome($this->scenario(), $routes),
        ));

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
        $this->go($this->scanApplies() ? 'scan' : 'advice');
    }

    public function startScan(): void
    {
        if ($this->screen !== 'scan') {
            return;
        }

        $this->withLog(fn (SessionLog $log) => $log->scanAnimationStarted($this->scenario(), $this->now()));
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

        $this->withLog(function (SessionLog $log) {
            $log->scanAnimationEnded($this->scenario(), $this->now());
            $log->recordScan($this->scenario(), $this->scanResult()['found']);
        });

        $this->scanState = 'done';
    }

    public function continueFromScan(): void
    {
        if ($this->scanState === 'done') {
            $this->go('advice');
        }
    }

    public function continueFromAdvice(): void
    {
        $this->packageAdvised() ? $this->go('order') : $this->closeCase('case_closed');
    }

    public function orderPackage(): void
    {
        if ($this->screen !== 'order') {
            return;
        }

        $this->withLog(fn (SessionLog $log) => $log->recordPackage($this->scenario(), advised: true, ordered: true));
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

        $this->withLog(fn (SessionLog $log) => $log->countBackClick($this->scenario()));

        // Leaving the scan mid-animation cancels it; it starts over next time.
        if ($this->scanState === 'running') {
            $this->scanState = 'idle';
        }

        $this->go($previous);
    }

    // ---------------------------------------------------------------------
    // Results
    // ---------------------------------------------------------------------

    /**
     * "Close": wipes the whole session, so nothing about this run is left.
     */
    public function closeSession(): void
    {
        $this->closing = true;

        session()->invalidate();
        session()->regenerateToken();

        $this->redirect('/');
    }

    // ---------------------------------------------------------------------
    // Rendering
    // ---------------------------------------------------------------------

    public function render()
    {
        $scenarios = $this->scenarioKeys();
        $onCase = in_array($this->screen, self::CASE_SCREENS, true);
        $hasCase = in_array($this->screen, [...self::CASE_SCREENS, 'scenario'], true);

        return view('livewire.case-demo', [
            'scenario' => $hasCase ? $this->scenario() : null,
            'caseCount' => count($scenarios),
            'caseStarted' => $hasCase && $this->log()->hasStarted($this->scenario()),
            'onCase' => $onCase,
            'steps' => $onCase ? $this->steps() : [],
            'currentStep' => $onCase ? array_search($this->screen, config('safestart.steps'), true) + 1 : null,
            'routes' => in_array($this->screen, ['route', 'scan', 'advice', 'order'], true) ? $this->routes() : [],
            'scanResult' => $this->screen === 'scan' ? $this->scanResult() : null,
            'packageAdvised' => in_array($this->screen, ['advice', 'order'], true) ? $this->packageAdvised() : null,
            'packageReason' => $this->screen === 'advice' ? $this->packageReason() : null,
            'summary' => $this->screen === 'results' ? $this->summary() : null,
            'resultsText' => $this->screen === 'results' ? ResultsText::make($this->summary()) : null,
        ])->title(__('demo.app_title'));
    }

    // ---------------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------------

    private function go(string $screen): void
    {
        $this->screen = $screen;

        // Log every screen visit of a started case, for the time-per-screen numbers.
        if (! $this->log()->hasStarted($this->scenario())) {
            return;
        }

        $this->withLog(function (SessionLog $log) use ($screen) {
            $log->enterScreen($this->scenario(), $screen, $this->now());

            // Record the advice when it is shown; ordering updates it later.
            if ($screen === 'advice') {
                $log->recordPackage($this->scenario(), $this->packageAdvised());
            }
        });
    }

    private function closeCase(string $notice): void
    {
        $this->withLog(fn (SessionLog $log) => $log->finishCase($this->scenario(), $this->now()));

        $this->notice = $notice;

        if ($this->scenarioIndex + 1 < count($this->scenarioKeys())) {
            $this->scenarioIndex++;
            $this->screen = 'scenario';
        } else {
            $this->screen = 'results';
        }
    }

    /**
     * The six steps with their state for the indicator:
     * done, current, todo or skipped ("not needed").
     */
    private function steps(): array
    {
        $order = config('safestart.steps');
        $current = array_search($this->screen, $order, true);
        $routeIndex = array_search('route', $order, true);
        $adviceIndex = array_search('advice', $order, true);

        $steps = [];
        foreach ($order as $i => $key) {
            $skipped = ($key === 'scan' && $current >= $routeIndex && ! $this->scanApplies())
                || ($key === 'order' && $current >= $adviceIndex && ! $this->packageAdvised());

            $steps[] = [
                'key' => $key,
                'number' => $i + 1,
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
        return $this->log()->toArray()['cases'][$this->scenario()]['routes'] ?? [];
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
        $answers = $this->log()->toArray()['cases'][$this->scenario()]['answers'] ?? null;

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

    private function summary(): array
    {
        return $this->log()->summary(
            $this->scenarioKeys(),
            config('safestart.timing.target_total_minutes') * 60_000,
        );
    }

    private function resetCaseFields(): void
    {
        $this->caseNumber = config('safestart.defaults.case_number');
        $this->answers = array_fill_keys(config('safestart.questions'), null);
        $this->scanState = 'idle';
    }

    private function scenarioKeys(): array
    {
        return array_keys(config('safestart.scenarios'));
    }

    private function scenario(): string
    {
        return $this->scenarioKeys()[$this->scenarioIndex];
    }

    private function log(): SessionLog
    {
        return new SessionLog(session('safestart.log', []));
    }

    private function saveLog(SessionLog $log): void
    {
        session(['safestart.log' => $log->toArray()]);
    }

    private function withLog(callable $change): void
    {
        $log = $this->log();
        $change($log);
        $this->saveLog($log);
    }

    /** Server time in milliseconds. The browser's clock is never used. */
    private function now(): int
    {
        return now()->getTimestampMs();
    }
}
