<?php

use App\Support\ScanSimulator;

beforeEach(function () {
    $this->scanner = new ScanSimulator(safestartConfig()['scenarios']);
});

it('finds the two scripted apps for scenario A', function () {
    $result = $this->scanner->resultFor('A');

    expect($result['found'])->toBeTrue()
        ->and(array_column($result['apps'], 'name'))->toBe(['Kalvo Sync', 'Pernik System Service']);
});

it('finds nothing for scenarios B and C', function (string $scenario) {
    expect($this->scanner->resultFor($scenario))->toBe(['found' => false, 'apps' => []]);
})->with(['B', 'C']);

it('gives the same result every time', function () {
    expect($this->scanner->resultFor('A'))->toBe($this->scanner->resultFor('A'));
});

it('throws for an unknown scenario', function () {
    $this->scanner->resultFor('Z');
})->throws(InvalidArgumentException::class);
