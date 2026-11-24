<?php

use App\Support\SessionLog;

const M = 'caseworker';

beforeEach(function () {
    $this->log = SessionLog::begin('C3', 'nl', at: 0);
});

function taskRow(SessionLog $log, string $task, string $module = M): array
{
    return collect($log->moduleSummary($module, [$task])['tasks'])->firstWhere('task', $task);
}

it('times a task from start to finish', function () {
    $this->log->startTask(M, 'A', at: 1_000);
    $this->log->finishTask(M, 'A', at: 61_000);

    expect(taskRow($this->log, 'A'))->finished->toBeTrue()->time_ms->toBe(60_000);
});

it('keeps the first start time when a task is started twice', function () {
    $this->log->startTask(M, 'A', at: 1_000);
    $this->log->startTask(M, 'A', at: 5_000);
    $this->log->finishTask(M, 'A', at: 11_000);

    expect(taskRow($this->log, 'A')['time_ms'])->toBe(10_000);
});

it('counts back clicks', function () {
    $this->log->startTask(M, 'A', at: 0);
    $this->log->countBackClick(M, 'A');
    $this->log->countBackClick(M, 'A');

    expect(taskRow($this->log, 'A')['back_clicks'])->toBe(2);
});

describe('waiting time', function () {
    beforeEach(function () {
        $this->log->startTask(M, 'A', at: 0);
    });

    it('reports task time with and without the wait', function () {
        $this->log->waitStarted(M, 'A', at: 20_000);
        $this->log->waitEnded(M, 'A', at: 23_500);
        $this->log->finishTask(M, 'A', at: 50_000);

        expect(taskRow($this->log, 'A'))
            ->time_ms->toBe(50_000)
            ->wait_ms->toBe(3_500)
            ->time_without_wait_ms->toBe(46_500);
    });

    it('counts only the part watched when the wait is skipped', function () {
        $this->log->waitStarted(M, 'A', at: 20_000);
        $this->log->waitEnded(M, 'A', at: 21_000);

        expect($this->log->task(M, 'A')['wait_ms'])->toBe(1_000);
    });

    it('adds up when the wait runs twice', function () {
        $this->log->waitStarted(M, 'A', at: 10_000);
        $this->log->waitEnded(M, 'A', at: 13_500);
        $this->log->waitStarted(M, 'A', at: 30_000);
        $this->log->waitEnded(M, 'A', at: 33_500);

        expect($this->log->task(M, 'A')['wait_ms'])->toBe(7_000);
    });

    it('closes a running wait when the participant leaves the screen', function () {
        $this->log->waitStarted(M, 'A', at: 10_000);
        $this->log->enterScreen(M, 'A', 'route', at: 12_000);

        expect($this->log->task(M, 'A')['wait_ms'])->toBe(2_000);
    });

    it('ignores an end without a start', function () {
        $this->log->waitEnded(M, 'A', at: 10_000);

        expect($this->log->task(M, 'A')['wait_ms'])->toBe(0);
    });
});

it('adds up time per screen, including revisits', function () {
    $this->log->startTask(M, 'A', at: 0);
    $this->log->enterScreen(M, 'A', 'case-number', at: 0);
    $this->log->enterScreen(M, 'A', 'check', at: 5_000);
    $this->log->enterScreen(M, 'A', 'route', at: 25_000);
    $this->log->enterScreen(M, 'A', 'check', at: 30_000);   // went back
    $this->log->enterScreen(M, 'A', 'route', at: 40_000);
    $this->log->finishTask(M, 'A', at: 45_000);

    expect(taskRow($this->log, 'A')['screen_times_ms'])->toBe([
        'case-number' => 5_000,
        'check' => 30_000,
        'route' => 10_000,
    ]);
});

it('merges details and replaces the outcome', function () {
    $this->log->startTask(M, 'C', at: 0);
    $this->log->record(M, 'C', ['case_number' => 'DEMO-0417', 'routes' => ['scan', 'shared']]);
    $this->log->setOutcome(M, 'C', 'extra');
    $this->log->record(M, 'C', ['routes' => ['shared']]);
    $this->log->setOutcome(M, 'C', 'exact');

    expect(taskRow($this->log, 'C'))
        ->details->toBe(['case_number' => 'DEMO-0417', 'routes' => ['shared']])
        ->outcome->toBe('exact')
        ->and($this->log->detail(M, 'C', 'routes'))->toBe(['shared'])
        ->and($this->log->detail(M, 'C', 'missing', 'default'))->toBe('default');
});

it('freezes a task once it is finished', function () {
    $this->log->startTask(M, 'A', at: 0);
    $this->log->finishTask(M, 'A', at: 10_000);
    $this->log->countBackClick(M, 'A');
    $this->log->setOutcome(M, 'A', 'missed');
    $this->log->finishTask(M, 'A', at: 99_000);

    expect(taskRow($this->log, 'A'))
        ->time_ms->toBe(10_000)
        ->back_clicks->toBe(0)
        ->outcome->toBeNull();
});

it('refuses to record on a task that has not started', function () {
    $this->log->countBackClick(M, 'B');
})->throws(LogicException::class);

it('survives a round trip through the session array', function () {
    $this->log->startTask(M, 'A', at: 0);
    $this->log->record(M, 'A', ['case_number' => 'DEMO-0417']);

    $restored = new SessionLog($this->log->toArray());

    expect($restored->toArray())->toBe($this->log->toArray())
        ->and($restored->hasStarted(M, 'A'))->toBeTrue()
        ->and($restored->isFinished(M, 'A'))->toBeFalse();
});

describe('several modules', function () {
    it('keeps modules apart and lists them in the order they started', function () {
        $this->log->startTask('template', 'choose', at: 0);
        $this->log->startTask(M, 'A', at: 10);
        $this->log->countBackClick(M, 'A');

        expect($this->log->modules())->toBe(['template', M])
            ->and($this->log->task('template', 'choose')['back_clicks'])->toBe(0);
    });

    it('can reset one module and keep the others', function () {
        $this->log->startTask('template', 'choose', at: 0);
        $this->log->startTask(M, 'A', at: 10);

        $this->log->resetModule(M);

        expect($this->log->modules())->toBe(['template'])
            ->and($this->log->hasStarted(M, 'A'))->toBeFalse();
    });

    it('keeps one session code for the whole session', function () {
        $this->log->setSessionCode('C4');

        expect($this->log->sessionCode())->toBe('C4');
    });
});

describe('module summary', function () {
    // Plays three tasks: A (2 min, 3.5 s wait), B (1 min), C (3 min, 3.5 s wait).
    function playThreeTasks(SessionLog $log, string $outcomeC = 'extra'): void
    {
        $log->startTask(M, 'A', at: 0);
        $log->setOutcome(M, 'A', 'exact');
        $log->waitStarted(M, 'A', at: 30_000);
        $log->waitEnded(M, 'A', at: 33_500);
        $log->countBackClick(M, 'A');
        $log->finishTask(M, 'A', at: 120_000);

        $log->startTask(M, 'B', at: 130_000);
        $log->setOutcome(M, 'B', 'exact');
        $log->finishTask(M, 'B', at: 190_000);

        $log->startTask(M, 'C', at: 200_000);
        $log->setOutcome(M, 'C', $outcomeC);
        $log->waitStarted(M, 'C', at: 230_000);
        $log->waitEnded(M, 'C', at: 233_500);
        $log->finishTask(M, 'C', at: 380_000);
    }

    $outcomes = ['exact', 'extra', 'missed'];

    it('adds up totals across all tasks', function () use ($outcomes) {
        playThreeTasks($this->log);

        $summary = $this->log->moduleSummary(M, ['A', 'B', 'C'], $outcomes, 600_000);

        expect($summary)
            ->module->toBe(M)
            ->all_tasks_finished->toBeTrue()
            ->total_time_ms->toBe(360_000)
            ->total_time_without_wait_ms->toBe(353_000)
            ->total_back_clicks->toBe(1)
            ->outcomes->toBe(['exact' => 2, 'extra' => 1, 'missed' => 0])
            ->target_ms->toBe(600_000)
            ->under_time_target->toBeTrue()
            ->and(array_column($summary['tasks'], 'time_ms'))->toBe([120_000, 60_000, 180_000]);
    });

    it('counts outcomes that did not match', function () use ($outcomes) {
        playThreeTasks($this->log, outcomeC: 'missed');

        expect($this->log->moduleSummary(M, ['A', 'B', 'C'], $outcomes)['outcomes'])->toBe(['exact' => 2, 'extra' => 0, 'missed' => 1]);
    });

    it('misses the time target when the total is too long', function () use ($outcomes) {
        playThreeTasks($this->log);

        expect($this->log->moduleSummary(M, ['A', 'B', 'C'], $outcomes, targetMs: 300_000)['under_time_target'])->toBeFalse();
    });

    it('has no target verdict when the module has no target', function () {
        playThreeTasks($this->log);

        expect($this->log->moduleSummary(M, ['A', 'B', 'C']))
            ->target_ms->toBeNull()
            ->under_time_target->toBeNull();
    });

    it('lists unstarted tasks and does not claim the target is met', function () {
        $this->log->startTask(M, 'A', at: 0);
        $this->log->finishTask(M, 'A', at: 60_000);
        $this->log->startTask(M, 'B', at: 70_000);   // started, never finished

        $summary = $this->log->moduleSummary(M, ['A', 'B', 'C'], [], 600_000);

        expect($summary['all_tasks_finished'])->toBeFalse()
            ->and($summary['under_time_target'])->toBeFalse()
            ->and($summary['total_time_ms'])->toBe(60_000)
            ->and(array_column($summary['tasks'], 'finished'))->toBe([true, false, false])
            ->and($summary['tasks'][1]['time_ms'])->toBeNull()
            ->and($summary['tasks'][2]['task'])->toBe('C');
    });
});
