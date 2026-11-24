<?php

use App\Support\ScanSimulator;
use App\Support\Triage;

beforeEach(function () {
    $this->triage = new Triage(safestartConfig());
});

describe('routes', function () {
    it('gives "none" when every answer is no', function () {
        expect($this->triage->routes(answers()))->toBe(['none']);
    });

    it('advises a scan when the ex had the phone or passwords (q2)', function (string $q2) {
        expect($this->triage->routes(answers(q2: $q2)))->toBe(['scan']);
    })->with(['yes', 'unknown']);

    it('advises a scan when the phone has changed (q6)', function (string $q6) {
        expect($this->triage->routes(answers(q6: $q6)))->toBe(['scan']);
    })->with(['yes', 'unknown']);

    it('advises a scan when the ex knows things and nothing is shared', function (string $q1) {
        expect($this->triage->routes(answers(q1: $q1)))->toBe(['scan']);
    })->with(['yes', 'unknown']);

    it('does not advise a scan for q1 alone when a location app or plan is shared', function (string $q4) {
        expect($this->triage->routes(answers(q1: 'yes', q4: $q4)))->toBe(['shared']);
    })->with(['yes', 'unknown']);

    it('does not advise a scan for q1 alone when an account or cloud is shared', function (string $q5) {
        expect($this->triage->routes(answers(q1: 'yes', q5: $q5)))->toBe(['shared']);
    })->with(['yes', 'unknown']);

    it('still advises a scan via q2 or q6 when something is shared', function () {
        expect($this->triage->routes(answers(q2: 'yes', q4: 'yes')))->toBe(['scan', 'shared'])
            ->and($this->triage->routes(answers(q6: 'yes', q5: 'yes')))->toBe(['scan', 'shared']);
    });

    it('gives the message route only for a clear yes on q3', function () {
        expect($this->triage->routes(answers(q3: 'yes')))->toBe(['message'])
            ->and($this->triage->routes(answers(q3: 'unknown')))->toBe(['none']);
    });

    it('gives the shared route for q4 or q5, "don\'t know" included', function (string $question, string $answer) {
        expect($this->triage->routes(answers(...[$question => $answer])))->toBe(['shared']);
    })->with([
        ['q4', 'yes'], ['q4', 'unknown'], ['q5', 'yes'], ['q5', 'unknown'],
    ]);

    it('returns every route that applies, in config order', function () {
        $all = answers(q1: 'yes', q2: 'yes', q3: 'yes', q4: 'yes', q5: 'yes', q6: 'yes');

        expect($this->triage->routes($all))->toBe(['scan', 'message', 'shared']);
    });

    it('rejects incomplete, extra or invalid answers', function (array $bad) {
        $this->triage->routes($bad);
    })->with([
        'missing q6' => [['q1' => 'no', 'q2' => 'no', 'q3' => 'no', 'q4' => 'no', 'q5' => 'no']],
        'extra key' => [answers() + ['q7' => 'no']],
        'bad value' => [answers(q3: 'maybe')],
        'empty' => [[]],
    ])->throws(InvalidArgumentException::class);
});

describe('package advice', function () {
    it('advises a package when the scan finds something', function () {
        expect($this->triage->packageAdvised(answers(q6: 'yes'), scanFoundSomething: true))->toBeTrue();
    });

    it('advises a package when q2 is yes and something is shared', function () {
        expect($this->triage->packageAdvised(answers(q2: 'yes', q5: 'yes'), scanFoundSomething: false))->toBeTrue();
    });

    it('does not advise a package when q2 is only "don\'t know"', function () {
        expect($this->triage->packageAdvised(answers(q2: 'unknown', q5: 'yes'), scanFoundSomething: false))->toBeFalse();
    });

    it('does not advise a package for q2 without sharing', function () {
        expect($this->triage->packageAdvised(answers(q2: 'yes'), scanFoundSomething: false))->toBeFalse();
    });

    it('does not advise a package for sharing without q2', function () {
        expect($this->triage->packageAdvised(answers(q4: 'yes', q5: 'yes'), scanFoundSomething: false))->toBeFalse();
    });
});

describe('route outcome', function () {
    it('is exact when the routes are the expected ones', function () {
        expect($this->triage->routeOutcome('A', ['scan']))->toBe('exact');
    });

    it('is extra when every expected route is there, plus more', function () {
        expect($this->triage->routeOutcome('C', ['scan', 'shared']))->toBe('extra')
            ->and($this->triage->routeOutcome('B', ['message', 'shared']))->toBe('extra');
    });

    it('is missed when an expected route is absent', function () {
        expect($this->triage->routeOutcome('A', ['none']))->toBe('missed')
            ->and($this->triage->routeOutcome('B', ['scan']))->toBe('missed');
    });

    it('is missed, not extra, when one is missing and another added', function () {
        expect($this->triage->routeOutcome('C', ['scan', 'message']))->toBe('missed');
    });

    it('ignores the order of routes', function () {
        expect($this->triage->routeOutcome('C', ['shared', 'scan']))->toBe('extra');
    });

    it('throws for an unknown scenario', function () {
        $this->triage->routeOutcome('Z', ['none']);
    })->throws(InvalidArgumentException::class);
});

/*
| The three scenarios, run end to end through the real config:
| reference answers -> routes -> scripted scan -> package advice.
*/
describe('scenarios', function () {
    beforeEach(function () {
        $this->scanner = new ScanSimulator(safestartConfig()['scenarios']);

        // Mirrors what the app will do: only scan when the scan route applies.
        $this->outcome = function (string $scenario, array $answers): array {
            $routes = $this->triage->routes($answers);
            $found = in_array('scan', $routes, true) && $this->scanner->resultFor($scenario)['found'];

            return [
                'routes' => $routes,
                'outcome' => $this->triage->routeOutcome($scenario, $routes),
                'package' => $this->triage->packageAdvised($answers, $found),
            ];
        };
    });

    it('A (Sara): scan, two apps found, package advised', function () {
        $answers = safestartConfig()['scenarios']['A']['reference_answers'];

        expect(($this->outcome)('A', $answers))->toBe(['routes' => ['scan'], 'outcome' => 'exact', 'package' => true])
            ->and($this->scanner->resultFor('A')['apps'])->toHaveCount(2);
    });

    it('B (Lena): message route only, no scan, no package', function () {
        $answers = safestartConfig()['scenarios']['B']['reference_answers'];

        expect(($this->outcome)('B', $answers))->toBe(['routes' => ['message'], 'outcome' => 'exact', 'package' => false]);
    });

    it('C (Tom) with q2 "don\'t know": shared plus a cautious scan (extra), no package', function () {
        $answers = safestartConfig()['scenarios']['C']['reference_answers'];

        expect(($this->outcome)('C', $answers))->toBe(['routes' => ['scan', 'shared'], 'outcome' => 'extra', 'package' => false]);
    });

    it('C (Tom) with q2 no: shared only, no package', function () {
        $answers = [...safestartConfig()['scenarios']['C']['reference_answers'], 'q2' => 'no'];

        expect(($this->outcome)('C', $answers))->toBe(['routes' => ['shared'], 'outcome' => 'exact', 'package' => false]);
    });

    it('C (Tom) with q2 yes: package advised', function () {
        $answers = [...safestartConfig()['scenarios']['C']['reference_answers'], 'q2' => 'yes'];

        expect(($this->outcome)('C', $answers))->toBe(['routes' => ['scan', 'shared'], 'outcome' => 'extra', 'package' => true]);
    });

    it('B (Lena) with q1 "don\'t know" adds a scan: extra', function () {
        $answers = [...safestartConfig()['scenarios']['B']['reference_answers'], 'q1' => 'unknown'];

        expect(($this->outcome)('B', $answers))->toBe(['routes' => ['scan', 'message'], 'outcome' => 'extra', 'package' => false]);
    });
});
