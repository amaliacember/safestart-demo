<?php

namespace App\Livewire\Modules;

use App\Support\SessionLog;
use Illuminate\Support\Facades\App;
use Illuminate\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * The base class of every module. Shared file: change it only after telling
 * the group.
 *
 * What it does for you:
 *  - the shared intro screen (language, session code) before your screens;
 *  - remembers your screen and the properties you list in remember(), so a
 *    page reload continues where the participant was;
 *  - small helpers to time tasks, count back clicks and save results in the
 *    participant's session (startTask, enterScreen, countBack, record,
 *    finishTask, ...);
 *  - finish(): sends the participant to the shared results screen.
 *
 * What you write in your module (see Template.php for a small example):
 *  - key(), tasks() and maybe outcomes() / targetMinutes() / waits();
 *  - afterIntro(): what happens when the participant presses "Start";
 *  - renderScreen(): the view for your screens;
 *  - one public method per button.
 */
abstract class Module extends Component
{
    /** The screen being shown. "intro" is the shared intro screen. */
    #[Locked]
    public string $screen = 'intro';

    public string $sessionCode = '';

    /** Set while leaving the module, so the state is not saved again. */
    private bool $leaving = false;

    // ---------------------------------------------------------------------
    // What every module defines
    // ---------------------------------------------------------------------

    /**
     * The module's key, as in config/safestart/modules.php. It is also the
     * name of its config file, lang files and view folder.
     */
    abstract public static function key(): string;

    /**
     * The timed tasks, in order. Each one becomes a row in the results.
     * Their labels come from lang/<locale>/<key>.php under "tasks.<task>".
     *
     * @return list<string>
     */
    abstract public static function tasks(): array;

    /**
     * Runs when the participant presses "Start" on the intro: set the first
     * screen and start the first task.
     */
    abstract protected function afterIntro(): void;

    /** The view for every screen after the intro. */
    abstract protected function renderScreen(): View;

    /**
     * The outcomes a task can end with, and how the results colour them:
     * good (green), warn (amber) or bad (red). Their labels come from
     * lang/<locale>/<key>.php under "outcomes.<outcome>".
     *
     * @return array<string, 'good'|'warn'|'bad'>
     */
    public static function outcomes(): array
    {
        return ['done' => 'good'];
    }

    /** A time target for all tasks together, shown in the results. */
    public static function targetMinutes(): ?int
    {
        return null;
    }

    /**
     * True if the module has fake waiting (an animation) that the results
     * should leave out in a "without waiting" column. See waitStarted().
     */
    public static function waits(): bool
    {
        return false;
    }

    /** How a task is named in the results. */
    public static function taskLabel(string $task): string
    {
        return __(static::key().".tasks.{$task}");
    }

    /**
     * An optional extra line about a task for the results, for example which
     * route the participant reached. Gets the task's row from the summary.
     */
    public static function taskDetail(array $task): ?string
    {
        return null;
    }

    /**
     * Names of the public properties to keep when the page reloads, besides
     * $screen and $sessionCode.
     *
     * @return list<string>
     */
    protected function remember(): array
    {
        return [];
    }

    /** Sets the properties to their starting values for a fresh run. */
    protected function freshState(): void {}

    // ---------------------------------------------------------------------
    // Lifecycle: restore and save the state in the session
    // ---------------------------------------------------------------------

    public function mount(): void
    {
        $state = session($this->stateKey());

        if (is_array($state)) {
            foreach (['screen', 'sessionCode', ...$this->remember()] as $property) {
                if (array_key_exists($property, $state)) {
                    $this->{$property} = $state[$property];
                }
            }
        } else {
            $this->freshState();
            // Doing a second module in the same call: keep the session code.
            $this->sessionCode = $this->log()->sessionCode() ?? '';
        }
    }

    /**
     * Runs at the end of every request: save the state so a reload resumes.
     */
    public function dehydrate(): void
    {
        if ($this->leaving) {
            return;
        }

        $state = [];
        foreach (['screen', 'sessionCode', ...$this->remember()] as $property) {
            $state[$property] = $this->{$property};
        }

        session([$this->stateKey() => $state]);
    }

    public function render(): View
    {
        $view = $this->screen === 'intro'
            ? view('modules.intro', ['module' => static::key()])
            : $this->renderScreen();

        return $view->title(__(static::key().'.title'));
    }

    // ---------------------------------------------------------------------
    // The shared intro
    // ---------------------------------------------------------------------

    /**
     * Switches language. A full page reload follows, so <html lang> changes too.
     */
    public function switchLocale(string $locale): void
    {
        if (! in_array($locale, config('safestart.demo.locales'), true)) {
            return;
        }

        session(['locale' => $locale]);

        $this->redirectRoute('module.'.static::key());
    }

    public function start(): void
    {
        if ($this->screen !== 'intro') {
            return;
        }

        $this->validate(
            ['sessionCode' => ['required', 'regex:'.config('safestart.demo.session_code_pattern')]],
            [
                'sessionCode.required' => __('demo.intro.code_required'),
                'sessionCode.regex' => __('demo.intro.code_invalid'),
            ],
        );

        $log = $this->log();

        if ($log->sessionCode() === null) {
            $log = SessionLog::begin($this->sessionCode, App::getLocale(), $this->now());
        } else {
            // Another module was done earlier in this session. Keep its
            // results, and start this module from scratch.
            $log->setSessionCode($this->sessionCode);
            $log->setLocale(App::getLocale());
            $log->resetModule(static::key());
        }

        $this->saveLog($log);
        $this->freshState();
        $this->afterIntro();
    }

    // ---------------------------------------------------------------------
    // Helpers for your module
    // ---------------------------------------------------------------------

    /** Starts the timer of a task. Calling it again keeps the first start time. */
    protected function startTask(string $task): void
    {
        $this->withLog(fn (SessionLog $log) => $log->startTask(static::key(), $task, $this->now()));
    }

    /**
     * Shows a screen and logs the visit for the time-per-screen numbers
     * (only while the task's timer runs).
     */
    protected function enterScreen(string $screen, ?string $task = null): void
    {
        $this->screen = $screen;

        if ($task !== null && $this->log()->hasStarted(static::key(), $task)) {
            $this->withLog(fn (SessionLog $log) => $log->enterScreen(static::key(), $task, $screen, $this->now()));
        }
    }

    /** Counts a click on "Back". Call it from your back() method. */
    protected function countBack(string $task): void
    {
        if ($this->log()->hasStarted(static::key(), $task)) {
            $this->withLog(fn (SessionLog $log) => $log->countBackClick(static::key(), $task));
        }
    }

    /** A fake wait starts (see waits()). */
    protected function waitStarted(string $task): void
    {
        $this->withLog(fn (SessionLog $log) => $log->waitStarted(static::key(), $task, $this->now()));
    }

    protected function waitEnded(string $task): void
    {
        $this->withLog(fn (SessionLog $log) => $log->waitEnded(static::key(), $task, $this->now()));
    }

    /** Saves details about a task for the results and the download. */
    protected function record(string $task, array $details): void
    {
        $this->withLog(fn (SessionLog $log) => $log->record(static::key(), $task, $details));
    }

    /** Sets a task's outcome; one of the keys of outcomes(). */
    protected function setOutcome(string $task, string $outcome): void
    {
        $this->withLog(fn (SessionLog $log) => $log->setOutcome(static::key(), $task, $outcome));
    }

    /** Stops a task's timer. After this the task cannot change any more. */
    protected function finishTask(string $task, ?string $outcome = null): void
    {
        $this->withLog(function (SessionLog $log) use ($task, $outcome) {
            if ($outcome !== null) {
                $log->setOutcome(static::key(), $task, $outcome);
            }
            $log->finishTask(static::key(), $task, $this->now());
        });
    }

    /**
     * The module is done: go to the shared results screen. The module's own
     * screen state is forgotten, so opening it again starts at the intro.
     */
    protected function finish(): void
    {
        $this->leaving = true;
        session()->forget($this->stateKey());

        $this->redirectRoute('results');
    }

    protected function log(): SessionLog
    {
        return new SessionLog(session('safestart.log', []));
    }

    protected function saveLog(SessionLog $log): void
    {
        session(['safestart.log' => $log->toArray()]);
    }

    protected function withLog(callable $change): void
    {
        $log = $this->log();
        $change($log);
        $this->saveLog($log);
    }

    /** Server time in milliseconds. The browser's clock is never used. */
    protected function now(): int
    {
        return now()->getTimestampMs();
    }

    private function stateKey(): string
    {
        return 'safestart.modules.'.static::key();
    }
}
