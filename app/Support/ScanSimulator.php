<?php

namespace App\Support;

use InvalidArgumentException;

/**
 * Returns the scripted "scan" result for a scenario.
 *
 * There is no real scan. The result is fixed in config/safestart.php, with no
 * randomness, so every participant sees exactly the same thing and the
 * timings can be compared.
 */
final class ScanSimulator
{
    /**
     * @param  array  $scenarios  config('safestart.scenarios')
     */
    public function __construct(private array $scenarios) {}

    public static function fromConfig(): self
    {
        return new self(config('safestart.scenarios'));
    }

    /**
     * @return array{found: bool, apps: list<array{name: string, finding: string}>}
     */
    public function resultFor(string $scenario): array
    {
        $apps = $this->scenarios[$scenario]['scan_findings']
            ?? throw new InvalidArgumentException("Unknown scenario [{$scenario}].");

        return [
            'found' => $apps !== [],
            'apps' => $apps,
        ];
    }
}
