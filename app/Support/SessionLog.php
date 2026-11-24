<?php

namespace App\Support;

use LogicException;

/**
 * Records what happens during one participant's session, for every module,
 * and builds the results summary.
 *
 * Shared file: every module uses it, so change it only after telling the group.
 *
 * Shape of the data:
 *
 *   session_code, locale, started_at
 *   modules.<module>.tasks.<task>:
 *     started_at, finished_at   when the task's timer ran
 *     screens                   every screen visit, for time per screen
 *     back_clicks               counted "Back" clicks
 *     wait_ms                   time spent waiting on something fake (a scan
 *                               animation, a "sending" bar), so results can
 *                               show the task time with and without it
 *     outcome                   one word the module chooses, e.g. "exact"
 *     details                   anything else the module wants in the results
 *
 * This is a plain value object around an array. Modules keep that array in
 * the participant's own Laravel session (toArray / new self(...)) and
 * nowhere else: nothing is written to disk for the team.
 *
 * All times are passed in as millisecond timestamps rather than read from the
 * clock here. That keeps the class easy to test, and the server (not the
 * participant's browser) decides what time it is.
 */
final class SessionLog
{
    private array $data;

    public function __construct(array $data = [])
    {
        $this->data = $data + [
            'session_code' => null,
            'locale' => null,
            'started_at' => null,
            'modules' => [],
        ];
    }

    public static function begin(string $sessionCode, string $locale, int $at): self
    {
        return new self([
            'session_code' => $sessionCode,
            'locale' => $locale,
            'started_at' => $at,
        ]);
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function sessionCode(): ?string
    {
        return $this->data['session_code'];
    }

    public function setSessionCode(string $sessionCode): void
    {
        $this->data['session_code'] = $sessionCode;
    }

    public function setLocale(string $locale): void
    {
        $this->data['locale'] = $locale;
    }

    /** @return list<string> the modules that logged something, in the order they started */
    public function modules(): array
    {
        return array_keys($this->data['modules']);
    }

    /**
     * Forgets everything a module logged, so it can be done again from the
     * start. Other modules in the same session are kept.
     */
    public function resetModule(string $module): void
    {
        unset($this->data['modules'][$module]);
    }

    /**
     * Starts the timer for a task. Calling it again (for example after going
     * back to the start of the task) keeps the original start time.
     */
    public function startTask(string $module, string $task, int $at): void
    {
        if ($this->hasStarted($module, $task)) {
            return;
        }

        $this->data['modules'][$module]['tasks'][$task] = [
            'started_at' => $at,
            'finished_at' => null,
            'screens' => [],
            'back_clicks' => 0,
            'wait_ms' => 0,
            'wait_started_at' => null,
            'outcome' => null,
            'details' => [],
        ];
    }

    public function enterScreen(string $module, string $task, string $screen, int $at): void
    {
        $this->update($module, $task, function (array &$entry) use ($screen, $at) {
            // Leaving a screen mid-wait still counts the time spent waiting.
            $this->closeWait($entry, $at);
            $entry['screens'][] = ['screen' => $screen, 'at' => $at];
        });
    }

    public function countBackClick(string $module, string $task): void
    {
        $this->update($module, $task, function (array &$entry) {
            $entry['back_clicks']++;
        });
    }

    /**
     * A fake wait (progress bar, animation) starts. Timed separately, so the
     * summary can show the task time with and without it.
     */
    public function waitStarted(string $module, string $task, int $at): void
    {
        $this->update($module, $task, function (array &$entry) use ($at) {
            $this->closeWait($entry, $at);
            $entry['wait_started_at'] = $at;
        });
    }

    public function waitEnded(string $module, string $task, int $at): void
    {
        $this->update($module, $task, function (array &$entry) use ($at) {
            $this->closeWait($entry, $at);
        });
    }

    /**
     * Saves module-specific details, merged into what is already there.
     * Example: record('caseworker', 'A', ['routes' => ['scan']]).
     */
    public function record(string $module, string $task, array $details): void
    {
        $this->update($module, $task, function (array &$entry) use ($details) {
            $entry['details'] = array_replace($entry['details'], $details);
        });
    }

    /**
     * The task's outcome, one word chosen by the module (e.g. "exact",
     * "missed", "done"). Setting it again replaces it.
     */
    public function setOutcome(string $module, string $task, string $outcome): void
    {
        $this->update($module, $task, function (array &$entry) use ($outcome) {
            $entry['outcome'] = $outcome;
        });
    }

    public function finishTask(string $module, string $task, int $at): void
    {
        $this->update($module, $task, function (array &$entry) use ($at) {
            $this->closeWait($entry, $at);
            $entry['finished_at'] = $at;
        });
    }

    public function hasStarted(string $module, string $task): bool
    {
        return isset($this->data['modules'][$module]['tasks'][$task]);
    }

    public function isFinished(string $module, string $task): bool
    {
        return ($this->data['modules'][$module]['tasks'][$task]['finished_at'] ?? null) !== null;
    }

    /** The raw entry of one task, or null if it has not started. */
    public function task(string $module, string $task): ?array
    {
        return $this->data['modules'][$module]['tasks'][$task] ?? null;
    }

    /** One detail of a task, or $default. */
    public function detail(string $module, string $task, string $key, mixed $default = null): mixed
    {
        return $this->task($module, $task)['details'][$key] ?? $default;
    }

    /**
     * The results of one module, for the results screen and the download.
     *
     * @param  list<string>  $tasks  task keys in order, so tasks that never
     *                               started still show up
     * @param  list<string>  $outcomes  every outcome the module can give, so
     *                                  each one is counted (also when 0)
     * @param  int|null  $targetMs  success target for the total time, if any
     */
    public function moduleSummary(string $module, array $tasks, array $outcomes = [], ?int $targetMs = null): array
    {
        $rows = array_map(fn ($task) => $this->taskSummary($module, $task), $tasks);

        $finished = array_filter($rows, fn ($row) => $row['finished']);
        $allFinished = count($finished) === count($tasks);
        $total = array_sum(array_column($finished, 'time_ms'));

        // How many tasks ended with each outcome. Tasks without one are not counted.
        $counts = array_fill_keys($outcomes, 0);
        foreach ($rows as $row) {
            if ($row['outcome'] !== null) {
                $counts[$row['outcome']] = ($counts[$row['outcome']] ?? 0) + 1;
            }
        }

        return [
            'module' => $module,
            'tasks' => $rows,
            'all_tasks_finished' => $allFinished,
            // Totals only count finished tasks.
            'total_time_ms' => $total,
            'total_time_without_wait_ms' => array_sum(array_column($finished, 'time_without_wait_ms')),
            'total_back_clicks' => array_sum(array_column($rows, 'back_clicks')),
            'outcomes' => $counts,
            'target_ms' => $targetMs,
            // null when the module has no time target.
            'under_time_target' => $targetMs === null ? null : $allFinished && $total < $targetMs,
        ];
    }

    private function taskSummary(string $module, string $task): array
    {
        $entry = $this->task($module, $task);

        if ($entry === null) {
            return [
                'task' => $task,
                'finished' => false,
                'time_ms' => null,
                'time_without_wait_ms' => null,
                'wait_ms' => 0,
                'back_clicks' => 0,
                'outcome' => null,
                'details' => [],
                'screen_times_ms' => [],
            ];
        }

        $finished = $entry['finished_at'] !== null;
        $time = $finished ? $entry['finished_at'] - $entry['started_at'] : null;

        return [
            'task' => $task,
            'finished' => $finished,
            'time_ms' => $time,
            'time_without_wait_ms' => $finished ? $time - $entry['wait_ms'] : null,
            'wait_ms' => $entry['wait_ms'],
            'back_clicks' => $entry['back_clicks'],
            'outcome' => $entry['outcome'],
            'details' => $entry['details'],
            'screen_times_ms' => $this->screenTimes($entry),
        ];
    }

    /**
     * Total time per screen name. A screen visited twice (after a back click)
     * adds up. The last screen runs until the task finished.
     */
    private function screenTimes(array $entry): array
    {
        $times = [];
        $screens = $entry['screens'];

        foreach ($screens as $i => $visit) {
            $end = $screens[$i + 1]['at'] ?? $entry['finished_at'];

            if ($end === null) {
                continue;
            }

            $times[$visit['screen']] = ($times[$visit['screen']] ?? 0) + ($end - $visit['at']);
        }

        return $times;
    }

    private function closeWait(array &$entry, int $at): void
    {
        if ($entry['wait_started_at'] !== null) {
            $entry['wait_ms'] += max(0, $at - $entry['wait_started_at']);
            $entry['wait_started_at'] = null;
        }
    }

    /**
     * Applies a change to a started task. A finished task is frozen, so the
     * results cannot change after the timer stopped.
     */
    private function update(string $module, string $task, callable $change): void
    {
        if (! $this->hasStarted($module, $task)) {
            throw new LogicException("Task [{$module}.{$task}] has not started.");
        }

        if ($this->isFinished($module, $task)) {
            return;
        }

        $change($this->data['modules'][$module]['tasks'][$task]);
    }
}
