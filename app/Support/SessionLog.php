<?php

namespace App\Support;

use LogicException;

/**
 * Records what happens during one participant's demo session and builds the
 * results summary.
 *
 * This is a plain value object around an array. The Livewire component keeps
 * that array in the participant's own Laravel session (toArray / new self(...)),
 * and nowhere else: nothing is written to disk for the team.
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
            'cases' => [],
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

    public function setLocale(string $locale): void
    {
        $this->data['locale'] = $locale;
    }

    /**
     * Starts the timer for a case. Calling it again (for example after going
     * back to the scenario card) keeps the original start time.
     */
    public function startCase(string $scenario, int $at): void
    {
        if (isset($this->data['cases'][$scenario])) {
            return;
        }

        $this->data['cases'][$scenario] = [
            'started_at' => $at,
            'finished_at' => null,
            'screens' => [],
            'back_clicks' => 0,
            'scan_animation_ms' => 0,
            'scan_animation_started_at' => null,
            'case_number' => null,
            'answers' => null,
            'routes' => null,
            'route_outcome' => null,
            'scan_found' => null,
            'package_advised' => null,
            'package_ordered' => false,
        ];
    }

    public function enterScreen(string $scenario, string $screen, int $at): void
    {
        $this->update($scenario, function (array &$case) use ($screen, $at) {
            // Leaving the scan screen mid-animation still counts the time spent on it.
            $this->closeScanAnimation($case, $at);
            $case['screens'][] = ['screen' => $screen, 'at' => $at];
        });
    }

    public function countBackClick(string $scenario): void
    {
        $this->update($scenario, function (array &$case) {
            $case['back_clicks']++;
        });
    }

    /**
     * The fake progress bar is timed separately, so the summary can show the
     * case time with and without it.
     */
    public function scanAnimationStarted(string $scenario, int $at): void
    {
        $this->update($scenario, function (array &$case) use ($at) {
            $this->closeScanAnimation($case, $at);
            $case['scan_animation_started_at'] = $at;
        });
    }

    public function scanAnimationEnded(string $scenario, int $at): void
    {
        $this->update($scenario, function (array &$case) use ($at) {
            $this->closeScanAnimation($case, $at);
        });
    }

    public function recordCaseNumber(string $scenario, string $caseNumber): void
    {
        $this->update($scenario, function (array &$case) use ($caseNumber) {
            $case['case_number'] = $caseNumber;
        });
    }

    /**
     * Saves the latest check answers. If the participant goes back and changes
     * them, the new answers replace the old ones.
     */
    public function recordCheck(string $scenario, array $answers, array $routes, string $routeOutcome): void
    {
        $this->update($scenario, function (array &$case) use ($answers, $routes, $routeOutcome) {
            $case['answers'] = $answers;
            $case['routes'] = $routes;
            $case['route_outcome'] = $routeOutcome;
        });
    }

    public function recordScan(string $scenario, bool $found): void
    {
        $this->update($scenario, function (array &$case) use ($found) {
            $case['scan_found'] = $found;
        });
    }

    public function recordPackage(string $scenario, bool $advised, bool $ordered = false): void
    {
        $this->update($scenario, function (array &$case) use ($advised, $ordered) {
            $case['package_advised'] = $advised;
            $case['package_ordered'] = $ordered;
        });
    }

    public function finishCase(string $scenario, int $at): void
    {
        $this->update($scenario, function (array &$case) use ($at) {
            $this->closeScanAnimation($case, $at);
            $case['finished_at'] = $at;
        });
    }

    public function hasStarted(string $scenario): bool
    {
        return isset($this->data['cases'][$scenario]);
    }

    public function isFinished(string $scenario): bool
    {
        return ($this->data['cases'][$scenario]['finished_at'] ?? null) !== null;
    }

    /**
     * The results the participant copies or downloads at the end.
     *
     * @param  list<string>  $scenarios  scenario keys in order, so cases that
     *                                   never started still show up
     * @param  int  $targetTotalMs  success target for the total time
     */
    public function summary(array $scenarios, int $targetTotalMs): array
    {
        $cases = [];

        foreach ($scenarios as $scenario) {
            $cases[] = $this->caseSummary($scenario);
        }

        $finished = array_filter($cases, fn ($case) => $case['finished']);
        $allFinished = count($finished) === count($scenarios);
        $total = array_sum(array_column($finished, 'time_ms'));

        return [
            'session_code' => $this->data['session_code'],
            'locale' => $this->data['locale'],
            'cases' => $cases,
            'all_cases_finished' => $allFinished,
            // Totals only count finished cases.
            'total_time_ms' => $total,
            'total_time_without_scan_ms' => array_sum(array_column($finished, 'time_without_scan_ms')),
            'total_back_clicks' => array_sum(array_column($cases, 'back_clicks')),
            // How many cases ended exact / extra / missed. Cases without a
            // finished check are not counted.
            'route_outcomes' => [
                'exact' => count(array_keys(array_column($cases, 'route_outcome'), 'exact', true)),
                'extra' => count(array_keys(array_column($cases, 'route_outcome'), 'extra', true)),
                'missed' => count(array_keys(array_column($cases, 'route_outcome'), 'missed', true)),
            ],
            'under_time_target' => $allFinished && $total < $targetTotalMs,
        ];
    }

    private function caseSummary(string $scenario): array
    {
        $case = $this->data['cases'][$scenario] ?? null;

        if ($case === null) {
            return [
                'scenario' => $scenario,
                'finished' => false,
                'case_number' => null,
                'time_ms' => null,
                'time_without_scan_ms' => null,
                'scan_animation_ms' => 0,
                'back_clicks' => 0,
                'answers' => null,
                'routes' => null,
                'route_outcome' => null,
                'scan_found' => null,
                'package_advised' => null,
                'package_ordered' => false,
                'screen_times_ms' => [],
            ];
        }

        $finished = $case['finished_at'] !== null;
        $time = $finished ? $case['finished_at'] - $case['started_at'] : null;

        return [
            'scenario' => $scenario,
            'finished' => $finished,
            'case_number' => $case['case_number'],
            'time_ms' => $time,
            'time_without_scan_ms' => $finished ? $time - $case['scan_animation_ms'] : null,
            'scan_animation_ms' => $case['scan_animation_ms'],
            'back_clicks' => $case['back_clicks'],
            'answers' => $case['answers'],
            'routes' => $case['routes'],
            'route_outcome' => $case['route_outcome'],
            'scan_found' => $case['scan_found'],
            'package_advised' => $case['package_advised'],
            'package_ordered' => $case['package_ordered'],
            'screen_times_ms' => $this->screenTimes($case),
        ];
    }

    /**
     * Total time per screen name. A screen visited twice (after a back click)
     * adds up. The last screen runs until the case finished.
     */
    private function screenTimes(array $case): array
    {
        $times = [];
        $screens = $case['screens'];

        foreach ($screens as $i => $visit) {
            $end = $screens[$i + 1]['at'] ?? $case['finished_at'];

            if ($end === null) {
                continue;
            }

            $times[$visit['screen']] = ($times[$visit['screen']] ?? 0) + ($end - $visit['at']);
        }

        return $times;
    }

    private function closeScanAnimation(array &$case, int $at): void
    {
        if ($case['scan_animation_started_at'] !== null) {
            $case['scan_animation_ms'] += max(0, $at - $case['scan_animation_started_at']);
            $case['scan_animation_started_at'] = null;
        }
    }

    /**
     * Applies a change to a started case. A finished case is frozen, so the
     * results cannot change after the timer stopped.
     */
    private function update(string $scenario, callable $change): void
    {
        if (! isset($this->data['cases'][$scenario])) {
            throw new LogicException("Case [{$scenario}] has not started.");
        }

        if ($this->isFinished($scenario)) {
            return;
        }

        $change($this->data['cases'][$scenario]);
    }
}
