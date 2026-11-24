<?php

use App\Support\SessionLog;

const TEN_MINUTES = 600_000;

beforeEach(function () {
    $this->log = SessionLog::begin('C3', 'nl', at: 0);
});

it('times a case from start to finish', function () {
    $this->log->startCase('A', at: 1_000);
    $this->log->finishCase('A', at: 61_000);

    $case = $this->log->summary(['A'], TEN_MINUTES)['cases'][0];

    expect($case['finished'])->toBeTrue()
        ->and($case['time_ms'])->toBe(60_000);
});

it('keeps the first start time when a case is started twice', function () {
    $this->log->startCase('A', at: 1_000);
    $this->log->startCase('A', at: 5_000);
    $this->log->finishCase('A', at: 11_000);

    expect($this->log->summary(['A'], TEN_MINUTES)['cases'][0]['time_ms'])->toBe(10_000);
});

it('counts back clicks', function () {
    $this->log->startCase('A', at: 0);
    $this->log->countBackClick('A');
    $this->log->countBackClick('A');

    expect($this->log->summary(['A'], TEN_MINUTES)['cases'][0]['back_clicks'])->toBe(2);
});

describe('scan animation', function () {
    beforeEach(function () {
        $this->log->startCase('A', at: 0);
    });

    it('reports case time with and without the animation', function () {
        $this->log->scanAnimationStarted('A', at: 20_000);
        $this->log->scanAnimationEnded('A', at: 23_500);
        $this->log->finishCase('A', at: 50_000);

        $case = $this->log->summary(['A'], TEN_MINUTES)['cases'][0];

        expect($case['time_ms'])->toBe(50_000)
            ->and($case['scan_animation_ms'])->toBe(3_500)
            ->and($case['time_without_scan_ms'])->toBe(46_500);
    });

    it('counts only the part watched when the animation is skipped', function () {
        $this->log->scanAnimationStarted('A', at: 20_000);
        $this->log->scanAnimationEnded('A', at: 21_000);

        expect($this->log->toArray()['cases']['A']['scan_animation_ms'])->toBe(1_000);
    });

    it('adds up when the animation runs twice', function () {
        $this->log->scanAnimationStarted('A', at: 10_000);
        $this->log->scanAnimationEnded('A', at: 13_500);
        $this->log->scanAnimationStarted('A', at: 30_000);
        $this->log->scanAnimationEnded('A', at: 33_500);

        expect($this->log->toArray()['cases']['A']['scan_animation_ms'])->toBe(7_000);
    });

    it('closes a running animation when the participant leaves the screen', function () {
        $this->log->scanAnimationStarted('A', at: 10_000);
        $this->log->enterScreen('A', 'route', at: 12_000);

        expect($this->log->toArray()['cases']['A']['scan_animation_ms'])->toBe(2_000);
    });

    it('ignores an end without a start', function () {
        $this->log->scanAnimationEnded('A', at: 10_000);

        expect($this->log->toArray()['cases']['A']['scan_animation_ms'])->toBe(0);
    });
});

it('adds up time per screen, including revisits', function () {
    $this->log->startCase('A', at: 0);
    $this->log->enterScreen('A', 'case-number', at: 0);
    $this->log->enterScreen('A', 'check', at: 5_000);
    $this->log->enterScreen('A', 'route', at: 25_000);
    $this->log->enterScreen('A', 'check', at: 30_000);   // went back
    $this->log->enterScreen('A', 'route', at: 40_000);
    $this->log->finishCase('A', at: 45_000);

    expect($this->log->summary(['A'], TEN_MINUTES)['cases'][0]['screen_times_ms'])->toBe([
        'case-number' => 5_000,
        'check' => 30_000,
        'route' => 10_000,
    ]);
});

it('keeps only the latest check answers', function () {
    $this->log->startCase('C', at: 0);
    $this->log->recordCheck('C', answers(q2: 'unknown'), ['scan', 'shared'], 'extra');
    $this->log->recordCheck('C', answers(q2: 'no', q4: 'yes'), ['shared'], 'exact');

    $case = $this->log->summary(['C'], TEN_MINUTES)['cases'][0];

    expect($case['answers']['q2'])->toBe('no')
        ->and($case['routes'])->toBe(['shared']);
});

it('freezes a case once it is finished', function () {
    $this->log->startCase('A', at: 0);
    $this->log->finishCase('A', at: 10_000);
    $this->log->countBackClick('A');
    $this->log->finishCase('A', at: 99_000);

    $case = $this->log->summary(['A'], TEN_MINUTES)['cases'][0];

    expect($case['time_ms'])->toBe(10_000)
        ->and($case['back_clicks'])->toBe(0);
});

it('refuses to record on a case that has not started', function () {
    $this->log->countBackClick('B');
})->throws(LogicException::class);

it('survives a round trip through the session array', function () {
    $this->log->startCase('A', at: 0);
    $this->log->recordCaseNumber('A', 'DEMO-0417');

    $restored = new SessionLog($this->log->toArray());

    expect($restored->toArray())->toBe($this->log->toArray())
        ->and($restored->hasStarted('A'))->toBeTrue()
        ->and($restored->isFinished('A'))->toBeFalse();
});

describe('summary', function () {
    // Plays three cases: A (2 min, 3.5 s scan), B (1 min), C (3 min, 3.5 s scan).
    function playAllCases(SessionLog $log, string $outcomeC = 'extra'): void
    {
        $log->startCase('A', at: 0);
        $log->recordCaseNumber('A', 'DEMO-0417');
        $log->recordCheck('A', answers(q1: 'yes', q2: 'yes', q6: 'yes'), ['scan'], 'exact');
        $log->scanAnimationStarted('A', at: 30_000);
        $log->scanAnimationEnded('A', at: 33_500);
        $log->recordScan('A', true);
        $log->recordPackage('A', advised: true, ordered: true);
        $log->countBackClick('A');
        $log->finishCase('A', at: 120_000);

        $log->startCase('B', at: 130_000);
        $log->recordCheck('B', answers(q3: 'yes'), ['message'], 'exact');
        $log->recordPackage('B', advised: false);
        $log->finishCase('B', at: 190_000);

        $log->startCase('C', at: 200_000);
        $log->recordCheck('C', answers(q1: 'yes', q2: 'unknown', q4: 'yes', q5: 'yes'), ['scan', 'shared'], $outcomeC);
        $log->scanAnimationStarted('C', at: 230_000);
        $log->scanAnimationEnded('C', at: 233_500);
        $log->recordScan('C', false);
        $log->recordPackage('C', advised: false);
        $log->finishCase('C', at: 380_000);
    }

    it('adds up totals across all cases', function () {
        playAllCases($this->log);

        $summary = $this->log->summary(['A', 'B', 'C'], TEN_MINUTES);

        expect($summary)
            ->session_code->toBe('C3')
            ->locale->toBe('nl')
            ->all_cases_finished->toBeTrue()
            ->total_time_ms->toBe(360_000)
            ->total_time_without_scan_ms->toBe(353_000)
            ->total_back_clicks->toBe(1)
            ->route_outcomes->toBe(['exact' => 2, 'extra' => 1, 'missed' => 0])
            ->under_time_target->toBeTrue()
            ->and(array_column($summary['cases'], 'time_ms'))->toBe([120_000, 60_000, 180_000])
            ->and($summary['cases'][0])
            ->case_number->toBe('DEMO-0417')
            ->scan_found->toBeTrue()
            ->package_advised->toBeTrue()
            ->package_ordered->toBeTrue();
    });

    it('counts routes that did not match', function () {
        playAllCases($this->log, outcomeC: 'missed');

        expect($this->log->summary(['A', 'B', 'C'], TEN_MINUTES)['route_outcomes'])->toBe(['exact' => 2, 'extra' => 0, 'missed' => 1]);
    });

    it('misses the time target when the total is too long', function () {
        playAllCases($this->log);

        expect($this->log->summary(['A', 'B', 'C'], targetTotalMs: 300_000)['under_time_target'])->toBeFalse();
    });

    it('lists unstarted cases and does not claim the target is met', function () {
        $this->log->startCase('A', at: 0);
        $this->log->finishCase('A', at: 60_000);
        $this->log->startCase('B', at: 70_000);   // started, never finished

        $summary = $this->log->summary(['A', 'B', 'C'], TEN_MINUTES);

        expect($summary['all_cases_finished'])->toBeFalse()
            ->and($summary['under_time_target'])->toBeFalse()
            ->and($summary['total_time_ms'])->toBe(60_000)
            ->and(array_column($summary['cases'], 'finished'))->toBe([true, false, false])
            ->and($summary['cases'][1]['time_ms'])->toBeNull()
            ->and($summary['cases'][2]['scenario'])->toBe('C');
    });
});
